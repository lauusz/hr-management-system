<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\EmployeeShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function dashboard()
    {
        $user  = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::with(['shift', 'location'])
            ->where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        return view('attendance.dashboard', [
            'attendance' => $attendance,
        ]);
    }

    public function showClockInForm()
    {
        return view('attendance.clock_in');
    }

    public function showClockOutForm()
    {
        return view('attendance.clock_out');
    }

    public function clockIn(Request $request)
    {
        $user  = Auth::user();
        $today = now()->toDateString();

        $request->validate([
            'photo' => ['required', 'image', 'max:4096'],
            'lat'   => ['required', 'numeric'],
            'lng'   => ['required', 'numeric'],
        ]);

        $schedule = EmployeeShift::where('user_id', $user->id)
            ->with(['shift', 'location'])
            ->first();

        if (!$schedule || !$schedule->shift) {
            return response()->json([
                'error' => 'Anda belum memiliki jadwal shift. Silakan hubungi HR.',
            ], 400);
        }

        if (!$schedule->location) {
            return response()->json([
                'error' => 'Lokasi presensi belum diatur. Silakan hubungi HR.',
            ], 400);
        }

        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->clock_in_at) {
            return response()->json([
                'error' => 'Anda sudah melakukan absensi masuk hari ini.',
            ], 400);
        }

        $location = $schedule->location;

        $distance = (int) round($this->calculateDistance(
            $request->lat,
            $request->lng,
            $location->latitude,
            $location->longitude
        ));

        if ($distance > $location->radius_meters) {
            return response()->json([
                'error' => 'Anda berada di luar radius lokasi presensi.',
            ], 400);
        }

        $now   = now();
        $shift = $schedule->shift;

        $shiftFrom = $shift->start_time instanceof Carbon
            ? $shift->start_time
            : Carbon::parse($shift->start_time);

        $status      = 'HADIR';
        $lateMinutes = 0;

        if ($now->gt($shiftFrom)) {
            $status      = 'TERLAMBAT';
            $lateMinutes = $shiftFrom->diffInMinutes($now);
        }

        $photoPath = $request->file('photo')->store('attendance_photos', 'public');

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date'    => $today,
            ],
            [
                'shift_id'       => $schedule->shift_id,
                'location_id'    => $schedule->location_id,
                'clock_in_at'    => $now,
                'late_minutes'   => $lateMinutes,
                'status'         => $status,
                'clock_in_photo' => $photoPath,
                'clock_in_lat'   => $request->lat,
                'clock_in_lng'   => $request->lng,
            ]
        );

        return response()->json([
            'message'    => 'Absensi masuk berhasil dicatat.',
            'attendance' => $attendance,
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $now = now();
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();

        $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $schedule = EmployeeShift::where('user_id', $user->id)
            ->with(['shift', 'location'])
            ->first();

        if (!$schedule || !$schedule->shift || !$schedule->location) {
            return response()->json([
                'error' => 'Jadwal shift atau lokasi belum diatur. Silakan hubungi HR.',
            ], 400);
        }

        $attendanceTodayOpen = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->first();

        $attendance = $attendanceTodayOpen;

        if (!$attendance) {
            $hasTodayClockIn = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->whereNotNull('clock_in_at')
                ->exists();

            if (!$hasTodayClockIn) {
                $attendanceYesterdayOpen = Attendance::where('user_id', $user->id)
                    ->where('date', $yesterday)
                    ->whereNotNull('clock_in_at')
                    ->whereNull('clock_out_at')
                    ->first();

                $attendance = $attendanceYesterdayOpen;
            }
        }

        if (!$attendance || !$attendance->clock_in_at) {
            return response()->json([
                'error' => 'Tidak ada presensi yang bisa di-clock-out.',
            ], 400);
        }

        if ($attendance->clock_out_at) {
            return response()->json([
                'error' => 'Presensi ini sudah memiliki clock-out.',
            ], 400);
        }

        $location = $schedule->location;

        $distance = (int) round($this->calculateDistance(
            $request->lat,
            $request->lng,
            $location->latitude,
            $location->longitude
        ));

        if ($distance > $location->radius_meters) {
            return response()->json([
                'error' => 'Anda berada di luar radius lokasi presensi saat clock-out.',
            ], 400);
        }

        $attendance->update([
            'clock_out_at'  => $now,
            'clock_out_lat' => $request->lat,
            'clock_out_lng' => $request->lng,
        ]);

        return response()->json([
            'message'    => 'Clock-out berhasil dicatat.',
            'attendance' => $attendance,
        ]);
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371000;

        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $latDelta = $lat2 - $lat1;
        $lngDelta = $lng2 - $lng1;

        $angle = 2 * asin(
            sqrt(
                pow(sin($latDelta / 2), 2) +
                cos($lat1) * cos($lat2) * pow(sin($lngDelta / 2), 2)
            )
        );

        return $earthRadius * $angle;
    }
}
