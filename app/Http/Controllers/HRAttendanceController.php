<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class HRAttendanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isHR(), 403);

        return $this->attendanceIndex($request, false);
    }

    public function team(Request $request)
    {
        $viewer = $request->user();
        abort_unless(! $viewer->isHR() && $viewer->canViewTeamAttendance(), 403);

        return $this->attendanceIndex($request, true);
    }

    private function attendanceIndex(Request $request, bool $team)
    {
        $viewer = $request->user();

        $rangeStart = Carbon::parse($request->query('date_start') ?: $request->query('date_end') ?: now())->startOfDay();
        $rangeEnd = Carbon::parse($request->query('date_end') ?: $request->query('date_start') ?: now())->endOfDay();

        if ($rangeStart->gt($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
        }

        $q = trim((string) $request->query('q'));
        $events = AttendanceEvent::query()
            ->with(['attendanceShift', 'user.employeeShift.shift.days', 'user.employeeShift.location', 'user.employeeShift.locations'])
            ->whereBetween('occurred_at', [$rangeStart->copy()->subDay(), $rangeEnd->copy()->addDay()])
            ->when($team, fn ($query) => $query->whereIn('user_id', $viewer->attendanceVisibleEmployees()->select('users.id')))
            ->whereHas('user', function ($userQuery) use ($q) {
                $userQuery->active();

                if ($q !== '') {
                    $userQuery->whereNormalizedNameContains($q);
                }
            })
            ->orderBy('user_id')
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        $items = $this->buildAttendanceSessions($events)
            ->filter(fn (object $session) => $session->work_date->betweenIncluded($rangeStart->toDateString(), $rangeEnd->toDateString()))
            ->sortByDesc('latest_occurred_at')
            ->values();

        return view($team ? 'supervisor.attendances.index' : 'hr.attendances.index', [
            'items' => $items,
            'date_start' => $rangeStart->toDateString(),
            'date_end' => $rangeEnd->toDateString(),
            'q' => $q,
        ]);
    }

    public function moveEventType(Request $request, AttendanceEvent $event)
    {
        abort_unless($request->user()->isHR(), 403);

        $types = [AttendanceEvent::CLOCK_IN, AttendanceEvent::CLOCK_OUT];
        $data = $request->validate([
            'event_type' => ['required', 'in:'.implode(',', $types)],
            'from_event_type' => ['required', 'in:'.implode(',', $types), 'different:event_type'],
        ]);

        // Guarded update: hanya berubah bila event masih berjenis asal yang dilihat HR di modal,
        // sehingga request stale/berulang tidak membalik event. Status approval tidak disentuh.
        $updated = AttendanceEvent::query()
            ->whereKey($event->getKey())
            ->where('event_type', $data['from_event_type'])
            ->update(['event_type' => $data['event_type']]);

        if ($updated === 1) {
            $message = 'Tipe event absensi berhasil diperbarui.';

            return $request->expectsJson()
                ? response()->json(['message' => $message])
                : back()->with('success', $message);
        }

        $message = 'Event absensi sudah berubah (kemungkinan dikoreksi pengguna lain). Muat ulang halaman, lalu coba lagi.';

        return $request->expectsJson()
            ? response()->json(['message' => $message], 409)
            : back()->withErrors(['attendance' => $message]);
    }

    public function scheduleSnapshot(Request $request, AttendanceEvent $event)
    {
        abort_unless($request->user()->isHR(), 403);

        return response()->json($this->correctionPayload($this->correctionContext($event)));
    }

    public function updateScheduleSnapshot(Request $request, AttendanceEvent $event)
    {
        abort_unless($request->user()->isHR(), 403);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'fingerprint' => ['required', 'string', 'size:40'],
            'event_ids' => ['required', 'array', 'min:1'],
            'event_ids.*' => ['integer'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'shift_name' => ['nullable', 'string', 'max:100'],
            'is_wita' => ['required', 'boolean'],
            'is_overnight' => ['required', 'boolean'],
            'is_holiday' => ['required', 'boolean'],
            'source_schedule_id' => ['nullable', 'integer'],
            'source_fingerprint' => ['nullable', 'string', 'size:40'],
        ], [
            'reason.required' => 'Alasan koreksi wajib diisi.',
            'reason.max' => 'Alasan koreksi maksimal 500 karakter.',
            'start_time.date_format' => 'Jam mulai tidak valid.',
            'end_time.date_format' => 'Jam selesai tidak valid.',
            'shift_name.max' => 'Nama shift maksimal 100 karakter.',
            'is_wita.required' => 'Pilih zona waktu (WIB atau WITA).',
            'is_wita.boolean' => 'Zona waktu tidak valid.',
            'is_overnight.required' => 'Pilih apakah jadwal lintas hari.',
            'is_overnight.boolean' => 'Nilai lintas hari tidak valid.',
            'is_holiday.required' => 'Pilih apakah hari libur.',
            'is_holiday.boolean' => 'Nilai hari libur tidak valid.',
        ]);

        $holiday = $request->boolean('is_holiday');
        $overnight = $request->boolean('is_overnight');
        $start = $data['start_time'] ?? null;
        $end = $data['end_time'] ?? null;
        if (! $holiday) {
            if (! $start || ! $end) {
                throw ValidationException::withMessages(['start_time' => 'Jam mulai dan jam selesai wajib diisi untuk hari kerja.']);
            }
            if ($overnight ? $end >= $start : $end <= $start) {
                throw ValidationException::withMessages(['end_time' => $overnight
                    ? 'Untuk jadwal lintas hari, jam selesai harus lebih awal dari jam mulai.'
                    : 'Jam selesai harus setelah jam mulai, atau pilih lintas hari.']);
            }
        }

        // Hari libur tidak memiliki jam kerja; jam dikosongkan agar konsisten.
        $target = [
            'shift_start_time' => $start && ! $holiday ? $start.':00' : null,
            'shift_end_time' => $end && ! $holiday ? $end.':00' : null,
            'is_overnight' => $overnight,
            'is_wita' => $request->boolean('is_wita'),
            'schedule_is_holiday' => $holiday,
        ];
        if (filled($data['shift_name'] ?? null)) {
            $target['shift_name_snapshot'] = trim($data['shift_name']);
        }
        $submittedIds = collect($data['event_ids'])->map(fn ($id) => (int) $id)->sort()->values()->all();

        $result = DB::transaction(function () use ($event, $data, &$target, $submittedIds, $request) {
            $context = $this->correctionContext($event, true);
            $ids = $context['events']->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();

            // Modal basi: event, cakupan sesi, atau snapshot sudah berubah sejak modal dibuka.
            if ($ids !== $submittedIds || ! hash_equals($context['fingerprint'], $data['fingerprint'])) {
                return ['status' => 409, 'message' => 'Data absensi sudah berubah. Muat ulang halaman, lalu coba lagi.'];
            }

            // Form diisi dari Jadwal Kerja: sumber harus masih sama; nilai form tetap yang disimpan.
            if (filled($data['source_schedule_id'] ?? null)) {
                $source = $this->workScheduleFor($event->user_id, $context['work_date']);
                if (! $source || $source->id !== (int) $data['source_schedule_id']
                    || ! hash_equals($this->scheduleFingerprint($source), (string) ($data['source_fingerprint'] ?? ''))) {
                    return ['status' => 409, 'message' => 'Jadwal Kerja sumber sudah berubah. Muat ulang halaman, lalu coba lagi.'];
                }
                $target['attendance_shift_id'] = $source->shift_id;
            }

            $unchanged = $context['events']->every(function (AttendanceEvent $e) use ($target) {
                $snapshot = $this->snapshotOf($e);

                return collect($target)->every(fn ($value, $key) => json_encode($snapshot[$key] ?? $e->getAttribute($key)) === json_encode($value));
            });
            if ($unchanged) {
                return ['status' => 422, 'message' => 'Tidak ada perubahan: snapshot seluruh log sudah sama dengan isian.'];
            }

            // Tanggal kerja tidak boleh berpindah baris di Master karena nilai baru (mis. zona waktu atau lintas hari).
            $moves = $context['events']->contains(function (AttendanceEvent $e) use ($target, $context) {
                $copy = clone $e;
                $copy->forceFill($target);

                return $this->workDateForEvent($copy)->toDateString() !== $context['work_date'];
            });
            if ($moves) {
                return ['status' => 422, 'message' => 'Dengan nilai ini, sebagian log akan pindah ke tanggal kerja lain. Koreksi dibatalkan agar log tidak berpindah baris.'];
            }

            AttendanceEvent::query()->whereIn('id', $ids)->update($target);

            // shortcut: belum ada tabel audit; jejak koreksi hanya di log aplikasi, upgrade bila pemilik menyetujui tabel audit.
            Log::info('attendance.schedule_snapshot_corrected', [
                'by_user_id' => $request->user()->id,
                'user_id' => $event->user_id,
                'work_date' => $context['work_date'],
                'event_ids' => $ids,
                'reason' => $data['reason'],
                'before' => $context['events']->mapWithKeys(fn ($e) => [$e->id => $this->snapshotOf($e)])->all(),
                'after' => $target,
            ]);

            return ['status' => 200, 'message' => 'Jadwal absensi berhasil dikoreksi.'];
        });

        return response()->json(['message' => $result['message']], $result['status']);
    }

    /**
     * Konteks koreksi: event satu sesi (karyawan + tanggal kerja Master) dan sidik jari kondisinya.
     */
    private function correctionContext(AttendanceEvent $anchor, bool $lock = false): array
    {
        $query = AttendanceEvent::query()
            ->with(['attendanceShift', 'user.employeeShift.shift.days'])
            ->where('user_id', $anchor->user_id)
            ->whereBetween('occurred_at', [$anchor->occurred_at->copy()->subDays(2), $anchor->occurred_at->copy()->addDays(2)])
            ->orderBy('occurred_at')->orderBy('id');
        $lock && $query->lockForUpdate();
        $all = $query->get();

        $current = $all->firstWhere('id', $anchor->id) ?? $anchor;
        $workDate = $this->workDateForEvent($current)->toDateString();
        $events = $all->filter(fn (AttendanceEvent $e) => $this->workDateForEvent($e)->toDateString() === $workDate)->values();

        $fingerprint = sha1(json_encode($events->mapWithKeys(fn ($e) => [$e->id => $this->snapshotOf($e)])->all()));

        return ['events' => $events, 'work_date' => $workDate, 'fingerprint' => $fingerprint];
    }

    private function workScheduleFor(int $userId, string $workDate): ?WorkSchedule
    {
        if (! WorkSchedule::scheduleReadable()) {
            return null;
        }

        // Hanya tanggal kerja yang sama persis; tidak ada fallback ke tanggal lain atau jadwal reguler.
        $query = WorkSchedule::active()->where('user_id', $userId)->whereDate('work_date', $workDate);
        WorkSchedule::schemaReady() && $query->with('group');

        return $query->first();
    }

    private function scheduleFingerprint(WorkSchedule $schedule): string
    {
        return sha1(json_encode([$schedule->id, $schedule->shift_id, $schedule->shift_name, $schedule->start_time, $schedule->end_time,
            (bool) $schedule->is_holiday, (bool) $schedule->is_overnight, (bool) $schedule->is_wita]));
    }

    private function snapshotOf(AttendanceEvent $event): array
    {
        return [
            'shift_name_snapshot' => $event->shift_name_snapshot,
            'shift_start_time' => $event->shift_start_time,
            'shift_end_time' => $event->shift_end_time,
            'is_overnight' => $event->is_overnight,
            'is_wita' => $event->is_wita,
            'schedule_is_holiday' => $event->schedule_is_holiday,
        ];
    }

    private function correctionPayload(array $context): array
    {
        $events = $context['events'];
        $first = $events->first();
        $initial = $first ? $this->snapshotOf($first) : [];
        $time = fn ($value) => $value ? substr($value, 0, 5) : '';
        $schedule = $first ? $this->workScheduleFor($first->user_id, $context['work_date']) : null;

        return [
            'employee' => $first?->user?->name,
            'work_date' => $context['work_date'],
            'event_ids' => $events->pluck('id')->all(),
            'events' => $events->map(fn (AttendanceEvent $e) => [
                'id' => $e->id,
                'type' => $e->event_type,
                'time' => $e->local_occurred_at->format('H:i'),
                'snapshot' => [
                    'shift' => $e->shift_name_snapshot,
                    'start' => $time($e->shift_start_time),
                    'end' => $time($e->shift_end_time),
                    'is_wita' => $e->is_wita,
                    'is_overnight' => $e->is_overnight,
                    'is_holiday' => $e->schedule_is_holiday,
                ],
            ])->values(),
            'snapshots_differ' => $events->unique(fn (AttendanceEvent $e) => json_encode($this->snapshotOf($e)))->count() > 1,
            // Isian awal = snapshot existing log pertama; nilai kosong dibiarkan kosong agar HR melengkapi.
            'form' => [
                'shift_name' => $initial['shift_name_snapshot'] ?? '',
                'start_time' => $time($initial['shift_start_time'] ?? null),
                'end_time' => $time($initial['shift_end_time'] ?? null),
                'is_wita' => $initial['is_wita'] ?? null,
                'is_overnight' => $initial['is_overnight'] ?? null,
                'is_holiday' => $initial['schedule_is_holiday'] ?? null,
            ],
            'fingerprint' => $context['fingerprint'],
            'schedule' => $schedule ? [
                'id' => $schedule->id,
                'fingerprint' => $this->scheduleFingerprint($schedule),
                'group' => $schedule->group?->name,
                'shift_name' => $schedule->shift_name,
                'start_time' => $time($schedule->start_time),
                'end_time' => $time($schedule->end_time),
                'is_wita' => (bool) $schedule->is_wita,
                'is_overnight' => (bool) $schedule->is_overnight,
                'is_holiday' => (bool) $schedule->is_holiday,
            ] : null,
        ];
    }

    /**
     * @param  Collection<int, AttendanceEvent>  $events
     * @return Collection<int, object>
     */
    private function buildAttendanceSessions(Collection $events): Collection
    {
        $sessions = collect();

        $events->groupBy('user_id')
            ->each(function (Collection $group) use ($sessions): void {
                $eventsByWorkDate = [];

                foreach ($group as $event) {
                    $workDate = $this->workDateForEvent($event);
                    $key = $workDate->toDateString();
                    $eventsByWorkDate[$key] ??= ['work_date' => $workDate, 'events' => collect()];
                    $eventsByWorkDate[$key]['events']->push($event);
                }

                foreach ($eventsByWorkDate as $session) {
                    $sessions->push($this->attendanceSession($session['events'], $session['work_date']));
                }
            });

        return $sessions;
    }

    /**
     * @param  Collection<int, AttendanceEvent>  $events
     */
    private function attendanceSession(Collection $events, Carbon $workDate): object
    {
        $clockIns = $events->where('event_type', AttendanceEvent::CLOCK_IN)->values();
        $clockOuts = $events->where('event_type', AttendanceEvent::CLOCK_OUT)->values();
        $event = $events->first();
        $employeeShift = $event->user?->employeeShift;
        $scheduleEvent = $clockIns->first() ?? $clockOuts->first();
        $shiftDay = $employeeShift?->shift?->days
            ->first(fn ($day) => (int) $day->day_of_week === $workDate->dayOfWeekIso && ! $day->is_holiday);
        $startTime = $scheduleEvent->schedule_is_holiday ? null : ($scheduleEvent->shift_start_time ?? $shiftDay?->start_time);
        $endTime = $scheduleEvent->schedule_is_holiday ? null : ($scheduleEvent->shift_end_time ?? $shiftDay?->end_time);
        $isOvernight = $scheduleEvent->is_overnight ?? $shiftDay?->is_overnight;
        $selectedLocations = $employeeShift?->locations->pluck('name')->filter()->implode(', ') ?? '';

        return (object) [
            'user' => $event->user,
            'work_date' => $workDate,
            'events' => $events,
            'clock_ins' => $clockIns,
            'clock_outs' => $clockOuts,
            'latest_occurred_at' => $events->last()->occurred_at,
            'attendance_location_scope' => $scheduleEvent->attendance_location_scope ?? match (true) {
                $employeeShift?->is_flexible === true => 'Fleksibel',
                $employeeShift?->is_all_locations === true => 'All Locations',
                default => $selectedLocations ?: ($employeeShift?->location?->name ?? '-'),
            },
            'work_schedule' => $scheduleEvent->schedule_is_holiday ? 'Libur' : ($startTime && $endTime
                ? substr($startTime, 0, 5).'–'.substr($endTime, 0, 5).($isOvernight ? ' (lintas hari)' : '')
                : '-'),
        ];
    }

    private function workDateForEvent(AttendanceEvent $event): Carbon
    {
        $isOvernight = (bool) ($event->is_overnight ?? $event->attendanceShift?->is_overnight);

        if (! $isOvernight) {
            return $event->local_occurred_at->copy()->startOfDay();
        }

        if ($event->work_date !== null) {
            return $event->work_date->copy()->startOfDay();
        }

        $workDate = $event->local_occurred_at->copy()->startOfDay();
        $previousWorkDate = $workDate->copy()->subDay();
        $previousShiftDay = $event->user?->employeeShift?->shift?->days
            ->first(fn ($day) => (int) $day->day_of_week === $previousWorkDate->dayOfWeekIso && ! $day->is_holiday);

        if ($previousShiftDay?->is_overnight && $previousShiftDay->end_time
            && $event->occurred_at->format('H:i:s') <= $previousShiftDay->end_time) {
            return $previousWorkDate;
        }

        return $workDate;
    }
}
