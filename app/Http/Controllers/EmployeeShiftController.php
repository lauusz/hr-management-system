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

    public function updateInline(Request $request, User $user)
    {
        $shiftId = $request->get('shift_id');
        $existing = EmployeeShift::where('user_id', $user->id)->first();

        if (empty($shiftId)) {
            if ($existing) {
                $existing->delete();
            }
            return response()->json([
                'success' => true,
                'shift_name' => null,
                'message' => 'Shift berhasil dihapus'
            ]);
        }

        $data = [
            'shift_id' => $shiftId,
            'date'     => now()->toDateString(),
        ];

        // Always set location_id (from existing or default)
        if ($existing && $existing->location_id) {
            $data['location_id'] = $existing->location_id;
        } else {
            $defaultLocation = AttendanceLocation::where('is_active', true)->first();
            if ($defaultLocation) {
                $data['location_id'] = $defaultLocation->id;
            }
        }

        if ($existing) {
            $existing->fill($data);
            $existing->save();
        } else {
            $data['user_id'] = $user->id;
            EmployeeShift::create($data);
        }

        $shift = Shift::find($shiftId);

        return response()->json([
            'success' => true,
            'shift_name' => $shift?->name,
            'message' => $shift ? "Shift {$shift->name} berhasil diterapkan" : 'Shift berhasil dihapus'
        ]);
    }
}
