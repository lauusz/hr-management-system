<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    public function index()
    {
        $items = EmployeeShift::with(['user', 'shift', 'location'])
            ->orderBy('user_id')
            ->paginate(100);

        return view('hr.schedules.index', compact('items'));
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
