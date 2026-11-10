<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkScheduleController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->validate(['month' => ['nullable', 'date_format:Y-m']])['month'] ?? now()->format('Y-m');
        $search = trim((string) $request->get('q', ''));
        $scheduleSearch = trim((string) $request->get('schedule_q', ''));
        $statusFilter = $request->get('status');
        $schemaReady = WorkSchedule::schemaReady();

        $allUsers = User::active()->orderBy('name')->get(['id', 'name']);

        $usersQuery = User::active()->orderBy('name');
        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('users.phone', 'like', "%{$search}%");
            });
        }

        $schedules = collect();
        if ($schemaReady) {
            $schedulesQuery = WorkSchedule::with(['user', 'group'])
                ->whereBetween('work_date', [$month.'-01', Carbon::parse($month.'-01')->endOfMonth()->toDateString()])
                ->orderBy('work_date');

            if ($scheduleSearch !== '') {
                $schedulesQuery->where(function ($query) use ($scheduleSearch) {
                    $query->where('shift_name', 'like', "%{$scheduleSearch}%")
                        ->orWhereHas('group', fn ($groupQuery) => $groupQuery->where('name', 'like', "%{$scheduleSearch}%"))
                        ->orWhereHas('user', function ($userQuery) use ($scheduleSearch) {
                            $userQuery->where('name', 'like', "%{$scheduleSearch}%")
                                ->orWhere('username', 'like', "%{$scheduleSearch}%");
                        });
                    if (mb_stripos('Tanpa Kelompok', $scheduleSearch) !== false) {
                        $query->orWhereNull('work_schedule_group_id');
                    }
                });
            }

            if ($statusFilter === 'kerja') {
                $schedulesQuery->where('is_holiday', false);
            } elseif ($statusFilter === 'libur') {
                $schedulesQuery->where('is_holiday', true);
            }

            $schedules = $schedulesQuery->get();
        }

        return view('hr.work-schedules.index', [
            'month' => $month,
            'search' => $search,
            'scheduleSearch' => $scheduleSearch,
            'statusFilter' => $statusFilter,
            'allUsers' => $allUsers,
            'users' => $usersQuery->get(['id', 'name']),
            'shifts' => Shift::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'schedules' => $schedules,
            'schemaReady' => $schemaReady,
        ]);
    }

    public function store(Request $request)
    {
        if (! WorkSchedule::schemaReady()) {
            return redirect()->route('hr.work-schedules.index')->withErrors(['schedule' => 'Pembaruan Jadwal Kerja belum lengkap. Hubungi HR atau IT.']);
        }
        if (is_string($request->input('group_name'))) {
            $request->merge(['group_name' => trim($request->input('group_name'))]);
        }
        $data = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'month' => ['required', 'date_format:Y-m'],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'ranges' => ['required', 'array', 'min:1'],
            'ranges.*.start' => ['required', 'date_format:Y-m-d'],
            'ranges.*.end' => ['required', 'date_format:Y-m-d', 'after_or_equal:ranges.*.start'],
            'ranges.*.shift' => ['required', 'string'],
        ]);
        $users = User::active()->whereIn('id', $data['users'])->orderBy('id')->get();
        if ($users->count() !== count($data['users'])) {
            throw ValidationException::withMessages(['users' => 'Karyawan harus aktif.']);
        }
        $shifts = Shift::with('days')->where('is_active', true)->get()->keyBy('id');
        $regular = EmployeeShift::with('shift')->whereIn('user_id', $data['users'])->get()->keyBy('user_id');
        $rows = [];
        foreach ($data['ranges'] as $range) {
            if (substr($range['start'], 0, 7) !== $data['month'] || substr($range['end'], 0, 7) !== $data['month']) {
                throw ValidationException::withMessages(['ranges' => 'Seluruh tanggal harus berada dalam bulan yang dipilih.']);
            }
            $shift = $range['shift'] === 'holiday' ? null : $shifts->get($range['shift']);
            if ($range['shift'] !== 'holiday' && ! $shift) {
                throw ValidationException::withMessages(['ranges' => 'Pilih shift aktif atau libur.']);
            }
            for ($date = Carbon::parse($range['start']); $date->lte(Carbon::parse($range['end'])); $date->addDay()) {
                $day = $shift?->days->firstWhere('day_of_week', $date->dayOfWeekIso);
                if ($shift && ! $day) {
                    throw ValidationException::withMessages(['ranges' => 'Pola shift tidak tersedia pada '.$date->toDateString().'. Pilih shift lain atau libur.']);
                }
                if ($shift && ! $day->is_holiday && (! $day->start_time || ! $day->end_time)) {
                    throw ValidationException::withMessages(['ranges' => 'Jam shift tidak lengkap pada '.$date->toDateString().'.']);
                }
                foreach ($users as $user) {
                    $key = $user->id.'|'.$date->toDateString();
                    if (isset($rows[$key])) {
                        throw ValidationException::withMessages(['ranges' => 'Rentang bertumpuk pada '.$user->name.' tanggal '.$date->toDateString().'.']);
                    }
                    $holiday = ! $shift || $day->is_holiday;
                    $rows[$key] = [
                        'user_id' => $user->id, 'user_name' => $user->name, 'work_date' => $date->toDateString(),
                        'shift_id' => $shift?->id, 'shift_name' => $shift?->name ?? 'Libur',
                        'start_time' => $holiday ? null : $day->start_time, 'end_time' => $holiday ? null : $day->end_time,
                        'is_holiday' => $holiday, 'is_overnight' => $holiday ? false : $day->is_overnight,
                        'is_wita' => $shift?->is_wita ?? (bool) $regular->get($user->id)?->shift?->is_wita,
                    ];
                }
            }
        }
        $rows = array_values($rows);
        try {
            $confirmation = DB::transaction(function () use ($request, $rows, $data) {
                $ids = collect($rows)->pluck('user_id')->unique()->sort()->values();
                $users = User::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
                if ($users->count() !== $ids->count() || $users->contains(fn ($user) => $user->status !== User::STATUS_ACTIVE)) {
                    throw ValidationException::withMessages(['schedule' => 'Data karyawan berubah. Periksa kembali pilihan karyawan.']);
                }
                $shiftIds = collect($rows)->pluck('shift_id')->filter()->unique()->sort()->values();
                $currentShifts = Shift::with('days')->whereIn('id', $shiftIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                foreach ($rows as $row) {
                    if ($row['shift_id'] === null) {
                        continue;
                    }
                    $shift = $currentShifts->get($row['shift_id']);
                    $day = $shift?->days->firstWhere('day_of_week', Carbon::parse($row['work_date'])->dayOfWeekIso);
                    if (! $shift?->is_active || ! $day || $shift->name !== $row['shift_name']
                        || (bool) $shift->is_wita !== (bool) $row['is_wita'] || (bool) $day->is_holiday !== (bool) $row['is_holiday']
                        || (! $row['is_holiday'] && ($day->start_time !== $row['start_time'] || $day->end_time !== $row['end_time'] || (bool) $day->is_overnight !== (bool) $row['is_overnight']))) {
                        throw ValidationException::withMessages(['schedule' => 'Master shift berubah. Periksa kembali jadwal sebelum menyimpan.']);
                    }
                }
                $conflicts = $this->conflicts($rows, $data['group_name']);
                if ($conflicts['time']) {
                    throw ValidationException::withMessages(['schedule' => implode(' ', $conflicts['time'])]);
                }
                $signature = hash_hmac('sha256', json_encode([$request->user()->id, $data, $rows, $conflicts['signature']]), config('app.key'));
                $submittedSignature = $request->input('conflict_confirmation');
                if ($conflicts['dates'] && (! $request->boolean('replace') || ! is_string($submittedSignature) || ! hash_equals($signature, $submittedSignature))) {
                    return ['dates' => $conflicts['dates'], 'signature' => $signature];
                }
                $group = WorkScheduleGroup::create([
                    'name' => $data['group_name'], 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
                ]);
                foreach ($rows as $row) {
                    unset($row['user_name']);
                    $schedule = WorkSchedule::firstOrNew(['user_id' => $row['user_id'], 'work_date' => $row['work_date']]);
                    $schedule->fill($row);
                    if (! $schedule->exists) {
                        $schedule->created_by = $request->user()->id;
                    }
                    $schedule->updated_by = $request->user()->id;
                    $schedule->work_schedule_group_id = $group->id;
                    $schedule->save();
                }
            });
        } catch (ValidationException $exception) {
            $exception->redirectTo(route('hr.work-schedules.index', ['month' => $data['month'], 'q' => $request->input('q')]));
            throw $exception;
        }
        if ($confirmation) {
            return redirect()->route('hr.work-schedules.index', ['month' => $data['month'], 'q' => $request->input('q')])
                ->withInput($request->except('replace', 'conflict_confirmation'))
                ->with('work_schedule_conflicts', $confirmation);
        }

        return redirect()->route('hr.work-schedules.index', ['month' => $data['month']])->with('success', 'Jadwal kerja berhasil disimpan.');
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'group_id' => ['required', 'string', 'regex:/^(ungrouped|[1-9][0-9]*)$/'],
        ]);
        if (! WorkSchedule::schemaReady()) {
            return redirect()->route('hr.work-schedules.index', ['month' => $data['month']])->withErrors(['schedule' => 'Pembaruan Jadwal Kerja belum lengkap. Hubungi HR atau IT.']);
        }
        $group = $data['group_id'] === 'ungrouped' ? null : WorkScheduleGroup::findOrFail($data['group_id']);
        $rows = WorkSchedule::with('user')
            ->when($group, fn ($query) => $query->where('work_schedule_group_id', $group->id), fn ($query) => $query->whereNull('work_schedule_group_id'))
            ->whereBetween('work_date', [$data['month'].'-01', Carbon::parse($data['month'].'-01')->endOfMonth()->toDateString()])
            ->orderBy('work_date')->orderBy('user_id')->get();

        return view('hr.work-schedules.preview', ['month' => $data['month'], 'groupName' => $group?->name ?? 'Tanpa Kelompok', 'rows' => $rows]);
    }

    private function conflicts(array $rows, string $groupName): array
    {
        $ids = collect($rows)->pluck('user_id')->unique()->all();
        $from = Carbon::parse(collect($rows)->min('work_date'))->subDay()->toDateString();
        $to = Carbon::parse(collect($rows)->max('work_date'))->addDay()->toDateString();
        $stored = WorkSchedule::with('group')->whereIn('user_id', $ids)->whereBetween('work_date', [$from, $to])->get()->keyBy(fn ($item) => $item->user_id.'|'.$item->work_date->toDateString());
        $planned = collect($rows)->keyBy(fn ($row) => $row['user_id'].'|'.$row['work_date']);
        $regular = EmployeeShift::with('shift.days')->whereIn('user_id', $ids)->get()->keyBy('user_id');
        $dates = [];
        $time = [];
        foreach ($rows as $row) {
            $key = $row['user_id'].'|'.$row['work_date'];
            if ($stored->has($key)) {
                $existing = $stored->get($key);
                $existingTime = $existing->is_holiday ? 'Libur' : substr($existing->start_time, 0, 5).'–'.substr($existing->end_time, 0, 5);
                $newTime = $row['is_holiday'] ? 'Libur' : substr($row['start_time'], 0, 5).'–'.substr($row['end_time'], 0, 5);
                $oldGroup = $existing->group ? $existing->group->name.' (#'.$existing->group->id.')' : 'Tanpa Kelompok';
                $dates[] = $row['user_name'].' — '.$row['work_date'].': '.$existing->shift_name.' ('.$existingTime.') → '.$row['shift_name'].' ('.$newTime.'); '.$oldGroup.' → kelompok baru '.$groupName.'.';
            }
            if ($row['is_holiday']) {
                continue;
            }
            foreach ([-1, 1] as $offset) {
                $date = Carbon::parse($row['work_date'])->addDays($offset);
                $otherKey = $row['user_id'].'|'.$date->toDateString();
                $other = $planned->get($otherKey) ?? $stored->get($otherKey)?->toArray();
                if (! $other) {
                    $shift = $regular->get($row['user_id'])?->shift;
                    $day = $shift?->days->firstWhere('day_of_week', $date->dayOfWeekIso);
                    $other = $day && ! $day->is_holiday ? ['work_date' => $date->toDateString(), 'start_time' => $day->start_time, 'end_time' => $day->end_time, 'is_overnight' => $day->is_overnight, 'is_wita' => $shift->is_wita, 'is_holiday' => false] : null;
                }
                if (! $other || $other['is_holiday'] || ! $other['start_time'] || ! $other['end_time']) {
                    continue;
                }
                $other['work_date'] = $date->toDateString();
                [$start, $end] = $this->interval($row);
                [$otherStart, $otherEnd] = $this->interval($other);
                if ($start->lt($otherEnd) && $otherStart->lt($end)) {
                    $time[] = 'Waktu '.$row['user_name'].' tanggal '.$row['work_date'].' bertabrakan dengan '.$date->toDateString().'.';
                }
            }
        }

        $signature = $planned->keys()->sort()->map(fn ($key) => $stored->get($key)?->toArray())->all();

        return ['dates' => array_values(array_unique($dates)), 'time' => array_values(array_unique($time)), 'signature' => hash('sha256', json_encode($signature))];
    }

    private function interval(array $row): array
    {
        $zone = $row['is_wita'] ? 'Asia/Makassar' : 'Asia/Jakarta';
        $start = Carbon::parse($row['work_date'].' '.$row['start_time'], $zone);
        $end = Carbon::parse($row['work_date'].' '.$row['end_time'], $zone);
        if ($row['is_overnight']) {
            $end->addDay();
        }

        return [$start, $end];
    }
}
