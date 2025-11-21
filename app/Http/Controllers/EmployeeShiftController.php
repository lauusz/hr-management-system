<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use App\Models\EmployeeShift;
use App\Models\AttendanceLocation;
use Illuminate\Http\Request;

class EmployeeShiftController extends Controller
{
    public function edit(User $user)
    {
        $shifts     = Shift::orderBy('start_time')->get();
        $locations  = AttendanceLocation::where('is_active', true)
            ->orderBy('name')
            ->get();

        $schedule = EmployeeShift::firstOrNew(
            ['user_id' => $user->id],
            [
                'shift_id'    => null,
                'location_id' => null,
                'date'        => now()->toDateString(),
            ]
        );

        return view('hr.employees.edit-shift', compact(
            'user',
            'shifts',
            'locations',
            'schedule'
        ));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'shift_id'    => ['nullable', 'exists:shifts,id'],
            'location_id' => ['nullable', 'exists:attendance_locations,id'],
        ]);

        EmployeeShift::updateOrCreate(
            ['user_id' => $user->id],
            [
                'shift_id'    => $data['shift_id'] ?? null,
                'location_id' => $data['location_id'] ?? null,
                'date'        => now()->toDateString(),
            ]
        );

        return redirect()
            ->route('hr.employees.shift.edit', $user->id)
            ->with('success', 'Shift dan lokasi karyawan berhasil diperbarui.');
    }
}
