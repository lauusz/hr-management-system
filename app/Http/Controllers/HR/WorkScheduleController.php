<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleGroup;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkScheduleController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->validate(['month' => ['nullable', 'date_format:Y-m']])['month'] ?? now()->format('Y-m');
        $search = trim((string) $request->get('q', ''));
        $scheduleSearch = trim((string) $request->get('schedule_q', ''));
        $statusFilter = $request->get('status');
        $aktif = $request->get('aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';
        $schemaReady = WorkSchedule::schemaReady();
        $lifecycleReady = WorkSchedule::lifecycleReady();

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

            if (WorkSchedule::hasLifecycleColumns()) {
                $aktif === 'nonaktif' ? $schedulesQuery->whereNull('is_active') : $schedulesQuery->where('is_active', 1);
            }

            if ($statusFilter === 'kerja') {
                $schedulesQuery->where('is_holiday', false);
            } elseif ($statusFilter === 'libur') {
                $schedulesQuery->where('is_holiday', true);
            }

            $schedules = $schedulesQuery->get();
        }

        // Cakupan tindakan kelompok = seluruh rincian aktif kelompok pada bulan ini, tidak terpengaruh pencarian/filter.
        $groupScopes = [];
        if ($lifecycleReady) {
            foreach ($schedules->pluck('work_schedule_group_id')->map(fn ($id) => $id ?? 'ungrouped')->unique() as $groupId) {
                $records = $this->scopeRecords($month, (string) $groupId);
                $groupScopes[$groupId] = ['employees' => $records->pluck('user_id')->unique()->count(), 'dates' => $records->pluck('work_date')->map->toDateString()->unique()->count(), 'records' => $records->count(), 'fingerprint' => $this->scopeFingerprint($records)];
            }
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
            'lifecycleReady' => $lifecycleReady,
            'aktif' => $aktif,
            'groupScopes' => $groupScopes,
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
                foreach ($users as $user) {
                    $key = $user->id.'|'.$date->toDateString();
                    if (isset($rows[$key])) {
                        throw ValidationException::withMessages(['ranges' => 'Rentang bertumpuk pada '.$user->name.' tanggal '.$date->toDateString().'.']);
                    }
                    $rows[$key] = $this->snapshotRow($user, $date, $shift, $regular, 'ranges');
                }
            }
        }
        $rows = array_values($rows);
        $lifecycle = WorkSchedule::lifecycleReady();
        try {
            $confirmation = DB::transaction(function () use ($request, $rows, $data, $lifecycle) {
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
                $batch = (string) Str::uuid();
                foreach ($rows as $row) {
                    unset($row['user_name']);
                    if ($lifecycle) {
                        // Jadwal aktif lama dinonaktifkan (data tetap tersimpan), lalu pengganti dibuat.
                        $old = WorkSchedule::active()->where('user_id', $row['user_id'])->whereDate('work_date', $row['work_date'])->lockForUpdate()->first();
                        $old && $this->deactivateRecord($old, $request->user()->id, $batch, 'REPLACE');
                        $new = new WorkSchedule($row + ['is_active' => 1, 'work_schedule_group_id' => $group->id, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
                        $new->save();
                        $old && $this->recordChange($batch, $new->id, $group->id, 'REPLACE', $this->snap($old), $this->snap($new), $request->user()->id);

                        continue;
                    }
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
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['schedule' => 'Jadwal karyawan/tanggal berubah bersamaan oleh pengguna lain. Muat ulang halaman, lalu coba lagi.'])
                ->redirectTo(route('hr.work-schedules.index', ['month' => $data['month'], 'q' => $request->input('q')]));
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
        $lifecycleReady = WorkSchedule::lifecycleReady();
        $aktif = $request->get('aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';
        $query = WorkSchedule::with('user')
            ->when($group, fn ($query) => $query->where('work_schedule_group_id', $group->id), fn ($query) => $query->whereNull('work_schedule_group_id'))
            ->whereBetween('work_date', [$data['month'].'-01', Carbon::parse($data['month'].'-01')->endOfMonth()->toDateString()])
            ->orderBy('work_date')->orderBy('user_id');
        if (WorkSchedule::hasLifecycleColumns()) {
            $aktif === 'nonaktif' ? $query->whereNull('is_active') : $query->where('is_active', 1);
        }
        $rows = $query->get();
        $scope = $lifecycleReady ? $this->scopeRecords($data['month'], $data['group_id']) : collect();

        return view('hr.work-schedules.preview', [
            'month' => $data['month'], 'groupId' => $data['group_id'], 'groupName' => $group?->name ?? 'Tanpa Kelompok', 'rows' => $rows,
            'blocks' => $this->patternBlocks($rows), 'aktif' => $aktif, 'lifecycleReady' => $lifecycleReady,
            'scopeFingerprint' => $this->scopeFingerprint($scope), 'scopeCount' => $scope->count(),
            'scopeEmployees' => $scope->pluck('user_id')->unique()->count(),
            'scopeDates' => $scope->pluck('work_date')->map->toDateString()->unique()->count(),
        ]);
    }

    public function edit(Request $request)
    {
        $data = $this->validateScope($request);
        if (! WorkSchedule::lifecycleReady()) {
            return redirect()->route('hr.work-schedules.index', ['month' => $data['month']])->withErrors(['schedule' => 'Pembaruan struktur Jadwal Kerja (status dan riwayat) belum diterapkan. Hubungi HR atau IT.']);
        }
        $records = $this->scopeRecords($data['month'], $data['group_id'], $data['ids']);
        if ($records->isEmpty() || ($data['ids'] && $records->count() !== count($data['ids']))) {
            return redirect()->route('hr.work-schedules.preview', ['month' => $data['month'], 'group_id' => $data['group_id']])->withErrors(['schedule' => 'Jadwal yang dipilih sudah berubah atau tidak aktif. Muat ulang halaman.']);
        }
        $group = $data['group_id'] === 'ungrouped' ? null : WorkScheduleGroup::findOrFail($data['group_id']);
        $monthRange = [$data['month'].'-01', Carbon::parse($data['month'].'-01')->endOfMonth()->toDateString()];

        return view('hr.work-schedules.edit', [
            'month' => $data['month'], 'groupId' => $data['group_id'], 'group' => $group, 'records' => $records->load('user'),
            'idsParam' => $data['ids'] ? implode(',', $data['ids']) : '', 'fingerprint' => $this->scopeFingerprint($records),
            'shifts' => Shift::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'otherMonthCount' => $group ? WorkSchedule::active()->where('work_schedule_group_id', $group->id)->whereNotBetween('work_date', $monthRange)->count() : 0,
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(WorkSchedule::lifecycleReady(), 422, 'Struktur Jadwal Kerja belum lengkap.');
        $data = $this->validateScope($request);
        $input = $request->validate([
            'fingerprint' => ['required', 'string', 'size:40'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.shift' => ['nullable', 'string'],
            'rows.*.include' => ['nullable', 'boolean'],
        ], ['group_name.max' => 'Nama kelompok maksimal 255 karakter.', 'rows.required' => 'Tidak ada rincian jadwal yang dikirim.']);
        $groupName = trim((string) ($input['group_name'] ?? ''));
        $back = route('hr.work-schedules.edit', ['month' => $data['month'], 'group_id' => $data['group_id'], 'ids' => $data['ids'] ? implode(',', $data['ids']) : null]);
        if ($data['group_id'] !== 'ungrouped' && $groupName === '') {
            throw ValidationException::withMessages(['group_name' => 'Nama kelompok wajib diisi.'])->redirectTo($back);
        }

        try {
            DB::transaction(function () use ($request, $data, $input, $groupName) {
                $records = $this->scopeRecords($data['month'], $data['group_id'], $data['ids'], true)->load('user');
                if ($records->isEmpty() || ($data['ids'] && $records->count() !== count($data['ids']))
                    || ! hash_equals($this->scopeFingerprint($records), $input['fingerprint'])) {
                    throw ValidationException::withMessages(['schedule' => 'Jadwal sudah berubah sejak form dibuka. Muat ulang halaman, lalu coba lagi.']);
                }
                if (collect($input['rows'])->keys()->map(fn ($id) => (int) $id)->sort()->values()->all() !== $records->pluck('id')->sort()->values()->all()) {
                    throw ValidationException::withMessages(['schedule' => 'Daftar rincian tidak sesuai dengan cakupan yang ditampilkan. Muat ulang halaman.']);
                }

                $shifts = Shift::with('days')->where('is_active', true)->get()->keyBy('id');
                $regular = EmployeeShift::with('shift')->whereIn('user_id', $records->pluck('user_id'))->get()->keyBy('user_id');
                $changes = [];
                $removed = [];
                foreach ($records as $record) {
                    $row = $input['rows'][$record->id];
                    if (! ($row['include'] ?? false)) {
                        $removed[] = $record;

                        continue;
                    }
                    $current = $record->shift_id ? (string) $record->shift_id : 'holiday';
                    $choice = (string) ($row['shift'] ?? '');
                    if ($choice === '' || $choice === $current) {
                        continue;
                    }
                    $shift = $choice === 'holiday' ? null : $shifts->get($choice);
                    if ($choice !== 'holiday' && ! $shift) {
                        throw ValidationException::withMessages(['schedule' => 'Pilih shift aktif atau libur untuk '.$record->user?->name.'.']);
                    }
                    $changes[$record->id] = $this->snapshotRow($record->user, $record->work_date, $shift, $regular, 'schedule');
                }

                $group = $data['group_id'] === 'ungrouped' ? null : WorkScheduleGroup::lockForUpdate()->findOrFail($data['group_id']);
                $renamed = $group && $group->name !== $groupName;
                if (! $changes && ! $removed && ! $renamed) {
                    throw ValidationException::withMessages(['schedule' => 'Tidak ada perubahan untuk disimpan.']);
                }

                if ($changes) {
                    // Rincian yang sedang diedit/dikeluarkan tidak dihitung sebagai bentrokan dengan dirinya sendiri.
                    $excluded = array_merge(array_keys($changes), collect($removed)->pluck('id')->all());
                    $conflicts = $this->conflicts(array_values($changes), $groupName, $excluded);
                    if ($conflicts['time']) {
                        throw ValidationException::withMessages(['schedule' => implode(' ', $conflicts['time'])]);
                    }
                }

                $batch = (string) Str::uuid();
                $userId = $request->user()->id;
                foreach ($changes as $id => $row) {
                    $record = $records->firstWhere('id', $id);
                    $before = $this->snap($record);
                    unset($row['user_name'], $row['user_id'], $row['work_date']);
                    $record->forceFill($row + ['updated_by' => $userId])->save();
                    $this->recordChange($batch, $record->id, $record->work_schedule_group_id, 'UPDATE', $before, $this->snap($record), $userId);
                }
                foreach ($removed as $record) {
                    $this->deactivateRecord($record, $userId, $batch);
                }
                if ($renamed) {
                    $before = ['name' => $group->name];
                    $group->update(['name' => $groupName, 'updated_by' => $userId]);
                    $this->recordChange($batch, null, $group->id, 'GROUP_RENAME', $before, ['name' => $groupName], $userId);
                }
            });
        } catch (ValidationException $exception) {
            $exception->redirectTo($back);
            throw $exception;
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['schedule' => 'Jadwal berubah bersamaan oleh pengguna lain. Muat ulang halaman, lalu coba lagi.'])->redirectTo($back);
        }

        return redirect()->route('hr.work-schedules.preview', ['month' => $data['month'], 'group_id' => $data['group_id']])->with('success', 'Jadwal kerja berhasil diubah.');
    }

    public function deactivate(Request $request)
    {
        abort_unless(WorkSchedule::lifecycleReady(), 422, 'Struktur Jadwal Kerja belum lengkap.');
        $data = $this->validateScope($request);
        $fingerprint = $request->validate(['fingerprint' => ['required', 'string', 'size:40']])['fingerprint'];
        $to = $request->input('return') === 'preview'
            ? route('hr.work-schedules.preview', ['month' => $data['month'], 'group_id' => $data['group_id']])
            : route('hr.work-schedules.index', ['month' => $data['month']]);

        try {
            $count = DB::transaction(function () use ($request, $data, $fingerprint) {
                $records = $this->scopeRecords($data['month'], $data['group_id'], $data['ids'], true);
                if ($records->isEmpty() || ($data['ids'] && $records->count() !== count($data['ids']))
                    || ! hash_equals($this->scopeFingerprint($records), $fingerprint)) {
                    throw ValidationException::withMessages(['schedule' => 'Jadwal sudah berubah sejak halaman dibuka. Muat ulang halaman, lalu coba lagi.']);
                }
                $batch = (string) Str::uuid();
                foreach ($records as $record) {
                    $this->deactivateRecord($record, $request->user()->id, $batch);
                }

                return $records->count();
            });
        } catch (ValidationException $exception) {
            $exception->redirectTo($to);
            throw $exception;
        }

        return redirect($to)->with('success', $count.' jadwal dinonaktifkan. Data tetap tersimpan.');
    }

    /**
     * @return array{month: string, group_id: string, ids: array<int, int>}
     */
    private function validateScope(Request $request): array
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'group_id' => ['required', 'string', 'regex:/^(ungrouped|[1-9][0-9]*)$/'],
            'ids' => ['nullable', 'string', 'regex:/^[1-9][0-9]*(,[1-9][0-9]*)*$/'],
        ]);

        return ['month' => $data['month'], 'group_id' => $data['group_id'], 'ids' => ($data['ids'] ?? '') === '' ? [] : array_values(array_unique(array_map('intval', explode(',', $data['ids']))))];
    }

    /** Rincian AKTIF satu kelompok pada satu bulan (opsional dibatasi ID), tanpa pengaruh pencarian/filter tampilan. */
    private function scopeRecords(string $month, string $groupId, array $ids = [], bool $lock = false)
    {
        $query = WorkSchedule::active()
            ->when($groupId === 'ungrouped', fn ($q) => $q->whereNull('work_schedule_group_id'), fn ($q) => $q->where('work_schedule_group_id', (int) $groupId))
            ->whereBetween('work_date', [$month.'-01', Carbon::parse($month.'-01')->endOfMonth()->toDateString()])
            ->when($ids, fn ($q) => $q->whereIn('id', $ids))
            ->orderBy('work_date')->orderBy('user_id')->orderBy('id');
        $lock && $query->lockForUpdate();

        return $query->get();
    }

    private function scopeFingerprint($records): string
    {
        return sha1(json_encode($records->map(fn (WorkSchedule $r) => [$r->id, $r->updated_at?->toIso8601String(), $this->snap($r)])->values()->all()));
    }

    private function snap(WorkSchedule $r): array
    {
        return [
            'id' => $r->id, 'user_id' => $r->user_id, 'work_date' => $r->work_date->toDateString(),
            'work_schedule_group_id' => $r->work_schedule_group_id, 'shift_id' => $r->shift_id, 'shift_name' => $r->shift_name,
            'start_time' => $r->start_time, 'end_time' => $r->end_time, 'is_holiday' => (bool) $r->is_holiday,
            'is_overnight' => (bool) $r->is_overnight, 'is_wita' => (bool) $r->is_wita, 'is_active' => $r->is_active !== null,
        ];
    }

    private function recordChange(string $batch, ?int $scheduleId, ?int $groupId, string $action, ?array $before, ?array $after, int $userId): void
    {
        DB::table('employee_work_schedule_changes')->insert([
            'batch_id' => $batch, 'work_schedule_id' => $scheduleId, 'work_schedule_group_id' => $groupId, 'action' => $action,
            'before_data' => $before ? json_encode($before) : null, 'after_data' => $after ? json_encode($after) : null,
            'changed_by' => $userId, 'created_at' => now(),
        ]);
    }

    /** Nonaktifkan = ubah status + metadata; tidak ada penghapusan fisik. */
    private function deactivateRecord(WorkSchedule $record, int $userId, string $batch, string $action = 'DEACTIVATE'): void
    {
        $before = $this->snap($record);
        $record->forceFill(['is_active' => null, 'deactivated_at' => now(), 'deactivated_by' => $userId, 'updated_by' => $userId])->save();
        $this->recordChange($batch, $record->id, $record->work_schedule_group_id, $action, $before, $this->snap($record), $userId);
    }

    /**
     * Snapshot satu karyawan/tanggal dari master shift (null = libur eksplisit), dipakai simpan dan edit.
     */
    private function snapshotRow(User $user, Carbon $date, ?Shift $shift, $regular, string $errorKey): array
    {
        $day = $shift?->days->firstWhere('day_of_week', $date->dayOfWeekIso);
        if ($shift && ! $day) {
            throw ValidationException::withMessages([$errorKey => 'Pola shift tidak tersedia pada '.$date->toDateString().'. Pilih shift lain atau libur.']);
        }
        if ($shift && ! $day->is_holiday && (! $day->start_time || ! $day->end_time)) {
            throw ValidationException::withMessages([$errorKey => 'Jam shift tidak lengkap pada '.$date->toDateString().'.']);
        }
        $holiday = ! $shift || $day->is_holiday;

        return [
            'user_id' => $user->id, 'user_name' => $user->name, 'work_date' => $date->toDateString(),
            'shift_id' => $shift?->id, 'shift_name' => $shift?->name ?? 'Libur',
            'start_time' => $holiday ? null : $day->start_time, 'end_time' => $holiday ? null : $day->end_time,
            'is_holiday' => $holiday, 'is_overnight' => $holiday ? false : $day->is_overnight,
            'is_wita' => $shift?->is_wita ?? (bool) $regular->get($user->id)?->shift?->is_wita,
        ];
    }

    /**
     * Pola jadwal: pengelompokan per (identitas shift, jam, zona, lintas hari, kerja/libur, status) + susunan karyawan,
     * lalu tanggal berurutan digabung. Tidak membuat kombinasi karyawan×tanggal yang tidak tersimpan.
     *
     * @return array<int, array<string, mixed>>
     */
    private function patternBlocks($rows): array
    {
        $cells = [];
        foreach ($rows->groupBy(fn ($r) => $r->work_date->toDateString()) as $date => $dateRows) {
            $bySignature = $dateRows->groupBy(fn ($r) => json_encode([$r->shift_id, $r->shift_name, substr((string) $r->start_time, 0, 5), substr((string) $r->end_time, 0, 5), (bool) $r->is_wita, (bool) $r->is_overnight, (bool) $r->is_holiday, $r->is_active !== null]));
            foreach ($bySignature as $signature => $sameRows) {
                $userIds = $sameRows->pluck('user_id')->unique()->sort()->values()->all();
                $key = $signature.'#'.implode(',', $userIds);
                $cells[$key] ??= ['sample' => $sameRows->first(), 'names' => $sameRows->sortBy('user_id')->map(fn ($r) => $r->user?->name ?? '—')->unique()->values()->all(), 'dates' => [], 'ids' => []];
                $cells[$key]['dates'][] = $date;
                $cells[$key]['ids'] = array_merge($cells[$key]['ids'], $sameRows->pluck('id')->all());
            }
        }

        return collect($cells)->map(function (array $cell) use ($rows) {
            $ranges = [];
            foreach ($cell['dates'] as $date) {
                $last = array_key_last($ranges);
                if ($last !== null && Carbon::parse($ranges[$last][1])->addDay()->toDateString() === $date) {
                    $ranges[$last][1] = $date;
                } else {
                    $ranges[] = [$date, $date];
                }
            }
            $month = Carbon::parse($cell['dates'][0])->locale('id')->translatedFormat('F Y');
            $label = collect($ranges)->map(fn ($range) => $range[0] === $range[1] ? Carbon::parse($range[0])->day : Carbon::parse($range[0])->day.'–'.Carbon::parse($range[1])->day)->implode(', ').' '.$month;

            $blockRows = $rows->whereIn('id', $cell['ids'])->sortBy(fn ($r) => [$r->work_date->toDateString(), $r->user_id, $r->id])->values();

            return $cell + ['fingerprint' => $this->scopeFingerprint($blockRows), 'label' => $label, 'date_count' => count($cell['dates']), 'record_count' => count($cell['ids'])];
        })->sortBy(fn ($block) => $block['dates'][0].'|'.implode(',', $block['names']))->values()->all();
    }

    private function conflicts(array $rows, string $groupName, array $excludeIds = []): array
    {
        $ids = collect($rows)->pluck('user_id')->unique()->all();
        $from = Carbon::parse(collect($rows)->min('work_date'))->subDay()->toDateString();
        $to = Carbon::parse(collect($rows)->max('work_date'))->addDay()->toDateString();
        $stored = WorkSchedule::active()->with('group')->whereIn('user_id', $ids)->whereBetween('work_date', [$from, $to])->when($excludeIds, fn ($query) => $query->whereNotIn('id', $excludeIds))->get()->keyBy(fn ($item) => $item->user_id.'|'.$item->work_date->toDateString());
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
                $oldGroup = $existing->group?->name ?? 'Tanpa Kelompok';
                $dates[] = $row['user_name'].', '.$row['work_date'].': '.$existing->shift_name.' ('.$existingTime.') → '.$row['shift_name'].' ('.$newTime.') · '.$oldGroup.' → '.$groupName;
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
