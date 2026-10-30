<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
        $eventType = $event->event_type === AttendanceEvent::CLOCK_IN
            ? AttendanceEvent::CLOCK_OUT
            : AttendanceEvent::CLOCK_IN;
        $updated = AttendanceEvent::query()
            ->whereKey($event)
            ->where('approval_status', 'PENDING')
            ->update([
                'event_type' => $eventType,
            ]);

        if ($request->expectsJson()) {
            return $updated === 1
                ? response()->json(['message' => 'Tipe event absensi berhasil diperbarui.'])
                : response()->json(['message' => 'Event absensi yang sudah diputus tidak dapat dikoreksi.'], 422);
        }

        return $updated === 1
            ? back()->with('success', 'Tipe event absensi berhasil diperbarui.')
            : back()->withErrors(['attendance' => 'Event absensi yang sudah diputus tidak dapat dikoreksi.']);
    }

    /**
     * @param Collection<int, AttendanceEvent> $events
     * @return Collection<int, object>
     */
    private function buildAttendanceSessions(Collection $events): Collection
    {
        $sessions = collect();

        $events->groupBy('user_id')
            ->each(function (Collection $group) use ($sessions): void {
                $openClockIn = null;
                $eventsByWorkDate = [];

                foreach ($group as $event) {
                    if ($event->event_type === AttendanceEvent::CLOCK_IN) {
                        $openClockIn = $event;
                        $workDate = $this->workDateForEvent($event);
                    } else {
                        $workDate = $openClockIn === null
                            ? $this->workDateForEvent($event)
                            : $this->workDateForEvent($openClockIn);
                        $openClockIn = null;
                    }

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
     * @param Collection<int, AttendanceEvent> $events
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
        $startTime = $scheduleEvent->shift_start_time ?? $shiftDay?->start_time;
        $endTime = $scheduleEvent->shift_end_time ?? $shiftDay?->end_time;
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
            'work_schedule' => $startTime && $endTime
                ? substr($startTime, 0, 5).'–'.substr($endTime, 0, 5).($isOvernight ? ' (lintas hari)' : '')
                : '-',
        ];
    }

    private function workDateForEvent(AttendanceEvent $event): Carbon
    {
        if ($event->work_date !== null) {
            return $event->work_date->copy()->startOfDay();
        }

        // shortcut: data lama belum menyimpan snapshot shift; akurasi historis penuh perlu backfill dari sumber jadwal historis.
        $workDate = $event->occurred_at->copy()->startOfDay();
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
