<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use App\Services\ShiftRotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $ptFilter = $request->get('pt_id');
        $positionFilter = $request->get('position_id');
        $shiftFilter = $request->get('shift_id');
        $locationModeFilter = $request->get('location_mode');

        if (! in_array($locationModeFilter, ['selected', 'all', 'flexible'], true)) {
            $locationModeFilter = null;
        }

        $query = User::active()
            ->leftJoin('employee_profiles', 'employee_profiles.user_id', '=', 'users.id')
            ->leftJoin('pts', 'pts.id', '=', 'employee_profiles.pt_id')
            ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
            ->leftJoin('employee_shifts', 'employee_shifts.user_id', '=', 'users.id')
            ->leftJoin('shifts', 'shifts.id', '=', 'employee_shifts.shift_id')
            ->leftJoin('attendance_locations', 'attendance_locations.id', '=', 'employee_shifts.location_id')
            ->select(
                'users.*',
                'employee_profiles.pt_id',
                'pts.name as pt_name',
                'positions.name as position_name',
                'employee_shifts.id as schedule_id',
                'employee_shifts.shift_id',
                'employee_shifts.location_id',
                'employee_shifts.is_all_locations',
                'employee_shifts.is_flexible',
                'shifts.name as shift_name',
                'attendance_locations.name as location_name'
            )
            ->orderBy('users.name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('users.phone', 'like', "%{$search}%");
            });
        }

        if ($ptFilter) {
            $query->where('employee_profiles.pt_id', $ptFilter);
        }

        if ($positionFilter) {
            $query->where('users.position_id', $positionFilter);
        }

        if ($shiftFilter === 'none') {
            $query->whereNull('employee_shifts.id');
        } elseif ($shiftFilter) {
            $query->where('employee_shifts.shift_id', $shiftFilter);
        }

        if ($locationModeFilter === 'all') {
            $query->where('employee_shifts.is_all_locations', true);
        } elseif ($locationModeFilter === 'flexible') {
            $query->where('employee_shifts.is_flexible', true);
        } elseif ($locationModeFilter === 'selected') {
            $query->whereNotNull('employee_shifts.id')
                ->where(fn ($query) => $query->whereNull('employee_shifts.is_all_locations')->orWhere('employee_shifts.is_all_locations', false))
                ->where(fn ($query) => $query->whereNull('employee_shifts.is_flexible')->orWhere('employee_shifts.is_flexible', false));
        }

        $items = $query->get();

        if ($this->hasLocationAssignmentsTable()) {
            $locationNames = DB::table('employee_shift_locations')
                ->join('attendance_locations', 'attendance_locations.id', '=', 'employee_shift_locations.location_id')
                ->whereIn('employee_shift_locations.employee_shift_id', $items->pluck('schedule_id')->filter())
                ->orderBy('attendance_locations.name')
                ->get(['employee_shift_locations.employee_shift_id', 'attendance_locations.name'])
                ->groupBy('employee_shift_id')
                ->map(fn ($locations) => $locations->pluck('name')->implode(', '));

            $items->each(function ($item) use ($locationNames) {
                $item->location_names = $locationNames->get($item->schedule_id);
            });
        }

        $ptOptions = \App\Models\Pt::orderBy('name')->get();
        $positionOptions = Position::orderBy('name')->get();

        $shiftOptions = Shift::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $locationOptions = AttendanceLocation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hr.schedules.index', [
            'items' => $items,
            'search' => $search,
            'pt' => $ptFilter,
            'ptOptions' => $ptOptions,
            'positionId' => $positionFilter,
            'positionOptions' => $positionOptions,
            'shiftId' => $shiftFilter,
            'shiftOptions' => $shiftOptions,
            'locationMode' => $locationModeFilter,
            'locationOptions' => $locationOptions,
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeLocationInput($request);
        $locationMode = $request->input('location_mode');
        $usesLocationAssignments = $this->usesLocationAssignments($request);
        $hasShift = $request->filled('shift_id');

        $validated = $request->validate([
            'user_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'user_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->where('status', User::STATUS_ACTIVE)],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'location_mode' => ['required', Rule::in(['selected', 'all', 'flexible'])],
            'location_ids' => in_array($locationMode, ['selected', 'flexible'], true)
                ? ['required', 'array', 'min:1']
                : ['prohibited'],
            'location_ids.*' => ['required', 'integer', 'distinct', Rule::exists('attendance_locations', 'id')->where('is_active', true)],
        ]);

        if ($hasShift) {
            app(ShiftRotationService::class)->ensureNotRotating($validated['user_ids']);
        }

        $this->ensureLocationAssignmentsReady($usesLocationAssignments);

        $isAllLocations = $validated['location_mode'] === 'all';
        $isFlexible = $validated['location_mode'] === 'flexible';
        $locationIds = $validated['location_ids'] ?? [];

        $skippedWithoutShift = DB::transaction(function () use ($validated, $hasShift, $isAllLocations, $isFlexible, $locationIds, $usesLocationAssignments) {
            $skipped = 0;

            foreach ($validated['user_ids'] as $userId) {
                $schedule = EmployeeShift::where('user_id', $userId)->first();

                if (! $schedule && ! $hasShift) {
                    $skipped++;
                    continue;
                }

                $attributes = [
                    'location_id' => $isAllLocations ? null : $locationIds[0],
                    'is_all_locations' => $isAllLocations,
                    'is_flexible' => $isFlexible,
                ];

                if ($hasShift) {
                    $attributes['shift_id'] = $validated['shift_id'];
                }

                if ($schedule) {
                    $schedule->update($attributes);
                } else {
                    $schedule = EmployeeShift::create($attributes + ['user_id' => $userId, 'shift_id' => $validated['shift_id']]);
                }

                if ($usesLocationAssignments) {
                    $schedule->locations()->sync($isAllLocations ? [] : $locationIds);
                }
            }

            return $skipped;
        });

        $updatedCount = count($validated['user_ids']) - $skippedWithoutShift;
        $message = 'Jadwal untuk '.$updatedCount.' karyawan berhasil disimpan.';

        if ($skippedWithoutShift > 0) {
            $message .= ' '.$skippedWithoutShift.' karyawan tanpa shift tidak diubah.';
        }

        return redirect()
            ->route('hr.schedules.index')
            ->with('success', $message);
    }

    public function edit(EmployeeShift $schedule)
    {
        $shifts = Shift::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = AttendanceLocation::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedLocationIds = $this->selectedLocationIds($schedule);

        return view('hr.schedules.edit', compact('schedule', 'shifts', 'locations', 'selectedLocationIds'));
    }

    public function update(Request $request, EmployeeShift $schedule)
    {
        $this->normalizeLocationInput($request);
        $locationMode = $request->input('location_mode');
        $usesLocationAssignments = $this->usesLocationAssignments($request);

        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('status', User::STATUS_ACTIVE),
                Rule::unique('employee_shifts', 'user_id')->ignore($schedule->id),
            ],
            'shift_id' => ['required', 'exists:shifts,id'],
            'location_mode' => ['required', Rule::in(['selected', 'all', 'flexible'])],
            'location_ids' => in_array($locationMode, ['selected', 'flexible'], true)
                ? ['required', 'array', 'min:1']
                : ['prohibited'],
            'location_ids.*' => ['required', 'integer', 'distinct', Rule::exists('attendance_locations', 'id')->where('is_active', true)],
        ], [
            'user_id.unique' => 'Karyawan ini sudah memiliki jadwal shift.',
        ]);

        if ((int) $schedule->shift_id !== (int) $validated['shift_id'] || (int) $schedule->user_id !== (int) $validated['user_id']) {
            app(ShiftRotationService::class)->ensureNotRotating([$schedule->user_id, $validated['user_id']]);
        }

        $this->ensureLocationAssignmentsReady($usesLocationAssignments);

        $isAllLocations = $validated['location_mode'] === 'all';
        $isFlexible = $validated['location_mode'] === 'flexible';
        $locationIds = $validated['location_ids'] ?? [];

        $schedule->update([
            'user_id' => $validated['user_id'],
            'shift_id' => $validated['shift_id'],
            'location_id' => $isAllLocations ? null : $locationIds[0],
            'is_all_locations' => $isAllLocations,
            'is_flexible' => $isFlexible,
        ]);

        if ($usesLocationAssignments) {
            $schedule->locations()->sync($isAllLocations ? [] : $locationIds);
        }

        return redirect()
            ->route('hr.schedules.index')
            ->with('success', 'Jadwal karyawan berhasil diperbarui.');
    }

    public function destroy(EmployeeShift $schedule)
    {
        app(ShiftRotationService::class)->ensureNotRotating([$schedule->user_id]);
        $schedule->delete();

        return redirect()
            ->route('hr.schedules.index')
            ->with('success', 'Jadwal karyawan berhasil dihapus.');
    }

    private function usesLocationAssignments(Request $request): bool
    {
        return in_array($request->input('location_mode'), ['selected', 'flexible'], true);
    }

    private function normalizeLocationInput(Request $request): void
    {
        $locationMode = $request->input('location_mode');

        if ($locationMode === null) {
            // Form lama memakai All Locations untuk akses tanpa batas.
            $locationMode = ($request->boolean('all_locations') || $request->input('location_id') === 'all')
                ? 'flexible'
                : 'selected';
        }

        $locationIds = $request->input('location_ids');

        if (! $request->has('location_ids')) {
            $locationIds = $locationMode !== 'selected' || ! $request->filled('location_id')
                ? []
                : [$request->input('location_id')];
        }

        $request->merge([
            'location_mode' => $locationMode,
            'location_ids' => $locationIds,
        ]);
    }

    private function ensureLocationAssignmentsReady(bool $usesLocationAssignments): void
    {
        if ($usesLocationAssignments && ! $this->hasLocationAssignmentsTable()) {
            throw ValidationException::withMessages([
                'location_ids' => 'Konfigurasi beberapa lokasi presensi belum tersedia. Hubungi tim IT.',
            ]);
        }
    }

    private function selectedLocationIds(EmployeeShift $schedule): array
    {
        if ($this->hasLocationAssignmentsTable()) {
            $locationIds = $schedule->locations()->pluck('attendance_locations.id')->all();

            if ($locationIds !== []) {
                return $locationIds;
            }
        }

        return $schedule->location_id ? [$schedule->location_id] : [];
    }

    private function hasLocationAssignmentsTable(): bool
    {
        return Schema::hasTable('employee_shift_locations');
    }
}
