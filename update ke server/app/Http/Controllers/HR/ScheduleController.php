<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
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

        $query = User::query()
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
            'locationOptions' => $locationOptions,
        ]);
    }

    public function store(Request $request)
    {
        $usesLocationAssignments = $this->usesLocationAssignments($request);
        $this->normalizeLocationInput($request);

        $validated = $request->validate([
            'user_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'user_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'all_locations' => ['required', 'boolean'],
            'location_ids' => $request->boolean('all_locations')
                ? ['prohibited']
                : ['required', 'array', 'min:1'],
            'location_ids.*' => ['required', 'integer', 'distinct', 'exists:attendance_locations,id'],
        ]);

        $this->ensureLocationAssignmentsReady($usesLocationAssignments);

        $isAllLocations = $validated['all_locations'];
        $locationIds = $validated['location_ids'] ?? [];

        DB::transaction(function () use ($validated, $isAllLocations, $locationIds, $usesLocationAssignments) {
            foreach ($validated['user_ids'] as $userId) {
                $schedule = EmployeeShift::updateOrCreate(['user_id' => $userId], [
                    'shift_id' => $validated['shift_id'],
                    'location_id' => $isAllLocations ? null : $locationIds[0],
                    'is_all_locations' => $isAllLocations,
                ]);

                if ($usesLocationAssignments) {
                    $schedule->locations()->sync($isAllLocations ? [] : $locationIds);
                }
            }
        });

        return redirect()
            ->route('hr.schedules.index')
            ->with('success', 'Jadwal untuk '.count($validated['user_ids']).' karyawan berhasil disimpan.');
    }

    public function edit(EmployeeShift $schedule)
    {
        $users = User::orderBy('name')->get();

        $shifts = Shift::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = AttendanceLocation::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedLocationIds = $this->selectedLocationIds($schedule);

        return view('hr.schedules.edit', compact('schedule', 'users', 'shifts', 'locations', 'selectedLocationIds'));
    }

    public function update(Request $request, EmployeeShift $schedule)
    {
        $usesLocationAssignments = $this->usesLocationAssignments($request);
        $this->normalizeLocationInput($request);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('employee_shifts', 'user_id')->ignore($schedule->id),
            ],
            'shift_id' => ['required', 'exists:shifts,id'],
            'all_locations' => ['required', 'boolean'],
            'location_ids' => $request->boolean('all_locations')
                ? ['prohibited']
                : ['required', 'array', 'min:1'],
            'location_ids.*' => ['required', 'integer', 'distinct', 'exists:attendance_locations,id'],
        ], [
            'user_id.unique' => 'Karyawan ini sudah memiliki jadwal shift.',
        ]);

        $this->ensureLocationAssignmentsReady($usesLocationAssignments);

        $isAllLocations = $validated['all_locations'];
        $locationIds = $validated['location_ids'] ?? [];

        $schedule->update([
            'user_id' => $validated['user_id'],
            'shift_id' => $validated['shift_id'],
            'location_id' => $isAllLocations ? null : $locationIds[0],
            'is_all_locations' => $isAllLocations,
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
        $schedule->delete();

        return redirect()
            ->route('hr.schedules.index')
            ->with('success', 'Jadwal karyawan berhasil dihapus.');
    }

    private function usesLocationAssignments(Request $request): bool
    {
        return $request->has('all_locations') || $request->has('location_ids');
    }

    private function normalizeLocationInput(Request $request): void
    {
        $isAllLocations = $request->boolean('all_locations') || $request->input('location_id') === 'all';
        $locationIds = $request->input('location_ids');

        if (! $request->has('location_ids')) {
            $locationIds = $isAllLocations || ! $request->filled('location_id')
                ? []
                : [$request->input('location_id')];
        }

        $request->merge([
            'all_locations' => $isAllLocations,
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
