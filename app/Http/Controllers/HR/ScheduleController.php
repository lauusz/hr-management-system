<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\EmployeeProfile;
use App\Models\EmployeeShift;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $items = $query->paginate(100)->withQueryString();

        $ptOptions = \App\Models\Pt::orderBy('name')->get();
        $positionOptions = Position::orderBy('name')->get();
        $shiftOptions = Shift::orderBy('start_time')->get();

        return view('hr.schedules.index', [
            'items' => $items,
            'search' => $search,
            'pt' => $ptFilter,
            'ptOptions' => $ptOptions,
            'positionId' => $positionFilter,
            'positionOptions' => $positionOptions,
            'shiftId' => $shiftFilter,
            'shiftOptions' => $shiftOptions,
        ]);
    }


    public function create()
    {
        $users = User::orderBy('name')->get();
        $shifts = Shift::orderBy('start_time')->get();
        $locations = AttendanceLocation::where('is_active', true)->orderBy('name')->get();

        return view('hr.schedules.create', compact('users', 'shifts', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('employee_shifts', 'user_id'),
            ],
            'shift_id' => ['required', 'exists:shifts,id'],
            'location_id' => ['required', 'exists:attendance_locations,id'],
        ], [
            'user_id.unique' => 'Karyawan ini sudah memiliki jadwal shift.',
        ]);

        EmployeeShift::create([
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'location_id' => $request->location_id,
        ]);

        return redirect()
            ->route('hr.schedules.index')
            ->with('success', 'Jadwal karyawan berhasil ditambahkan.');
    }

    public function edit(EmployeeShift $schedule)
    {
        $users = User::orderBy('name')->get();
        $shifts = Shift::orderBy('start_time')->get();
        $locations = AttendanceLocation::where('is_active', true)->orderBy('name')->get();

        return view('hr.schedules.edit', compact('schedule', 'users', 'shifts', 'locations'));
    }

    public function update(Request $request, EmployeeShift $schedule)
    {
        $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('employee_shifts', 'user_id')->ignore($schedule->id),
            ],
            'shift_id' => ['required', 'exists:shifts,id'],
            'location_id' => ['required', 'exists:attendance_locations,id'],
        ], [
            'user_id.unique' => 'Karyawan ini sudah memiliki jadwal shift.',
        ]);

        $schedule->update([
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'location_id' => $request->location_id,
        ]);

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
}
