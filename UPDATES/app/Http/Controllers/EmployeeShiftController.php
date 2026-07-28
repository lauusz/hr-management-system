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
        $shifts = Shift::where('is_active', true)->orderBy('name')->get();
        $locations  = AttendanceLocation::where('is_active', true)
            ->orderBy('name')
            ->get();

        $schedule = EmployeeShift::firstOrNew(
            ['user_id' => $user->id],
            [
                'shift_id'    => null,
                'location_id' => null,
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

        if (empty($data['shift_id'])) {
            EmployeeShift::where('user_id', $user->id)->delete();
            $user->forceFill(['shift_id' => null])->save();

            return redirect()
                ->route('hr.employees.shift.edit', $user->id)
                ->with('success', 'Shift karyawan berhasil dihapus.');
        }

        $locationId = $data['location_id']
            ?? AttendanceLocation::where('is_active', true)->value('id');

        if (!$locationId) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Lokasi presensi aktif belum tersedia. Tambahkan lokasi aktif terlebih dahulu.');
        }

        EmployeeShift::updateOrCreate(
            ['user_id' => $user->id],
            [
                'shift_id'    => $data['shift_id'],
                'location_id' => $locationId,
            ]
        );

        $user->forceFill(['shift_id' => $data['shift_id']])->save();

        return redirect()
            ->route('hr.employees.shift.edit', $user->id)
            ->with('success', 'Shift dan lokasi karyawan berhasil diperbarui.');
    }

    public function updateInline(Request $request, User $user)
    {
        $validated = $request->validate([
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
        ]);

        $shiftId = $validated['shift_id'] ?? null;
        $existing = EmployeeShift::where('user_id', $user->id)->first();

        if (empty($shiftId)) {
            if ($existing) {
                $existing->delete();
            }

            $user->forceFill(['shift_id' => null])->save();

            return response()->json([
                'success' => true,
                'shift_name' => null,
                'message' => 'Shift berhasil dihapus'
            ]);
        }

        $shift = Shift::where('is_active', true)->find($shiftId);
        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift tidak aktif atau tidak ditemukan.'
            ], 422);
        }

        $data = [
            'shift_id' => $shift->id,
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

        if (empty($data['location_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi presensi aktif belum tersedia. Tambahkan lokasi aktif terlebih dahulu.'
            ], 422);
        }

        if ($existing) {
            $existing->fill($data);
            $existing->save();
        } else {
            $data['user_id'] = $user->id;
            EmployeeShift::create($data);
        }

        $user->forceFill(['shift_id' => $shift->id])->save();

        return response()->json([
            'success' => true,
            'shift_name' => $shift?->name,
            'message' => $shift ? "Shift {$shift->name} berhasil diterapkan" : 'Shift berhasil dihapus'
        ]);
    }
}
