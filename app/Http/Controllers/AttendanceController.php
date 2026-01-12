<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\EmployeeShift;
use App\Services\Image\ImageCompressor; // Import Service
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Inject ImageCompressor ke Constructor
    public function __construct(protected ImageCompressor $imageCompressor)
    {
    }

    public function dashboard()
    {
        $user  = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::with(['shift', 'location', 'employeeShift'])
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
        $user = Auth::user();
        $now = now();
        $today = $now->toDateString();

        $request->validate([
            // Limit 8MB agar user bisa upload foto resolusi tinggi, 
            // nanti server yang akan resize jadi kecil (±100KB).
            'photo' => ['required', 'image', 'max:8192'], 
            'lat'   => ['required', 'numeric'],
            'lng'   => ['required', 'numeric'],
        ]);

        $employeeShift = EmployeeShift::with(['location', 'shift'])
            ->where('user_id', $user->id)
            ->first();

        if (!$employeeShift || !$employeeShift->shift) {
            return response()->json([
                'error' => 'Jadwal shift belum diatur. Silakan hubungi HR.',
            ], 400);
        }

        if (!$employeeShift->location) {
            return response()->json([
                'error' => 'Lokasi presensi belum diatur. Silakan hubungi HR.',
            ], 400);
        }

        $dayOfWeek = Carbon::parse($today)->dayOfWeek;

        $pattern = $employeeShift->shift
            ->patternDays()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$pattern) {
            return response()->json([
                'error' => 'Anda belum memiliki jadwal shift untuk hari ini. Silakan hubungi HR.',
            ], 400);
        }

        if ($pattern->is_holiday) {
            return response()->json([
                'error' => 'Hari ini merupakan hari libur pada jadwal Anda.',
            ], 400);
        }

        if (!$pattern->start_time || !$pattern->end_time) {
            return response()->json([
                'error' => 'Jam masuk atau pulang shift belum diatur dengan benar. Silakan hubungi HR.',
            ], 400);
        }

        $shiftStart = Carbon::parse($today . ' ' . $pattern->start_time);
        $shiftEnd = Carbon::parse($today . ' ' . $pattern->end_time);

        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }

        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->clock_in_at) {
            return response()->json([
                'error' => 'Anda sudah melakukan absensi masuk hari ini.',
            ], 400);
        }

        $location = $employeeShift->location;

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

        $status = 'HADIR';
        $lateMinutes = 0;

        if ($now->gt($shiftStart)) {
            $status = 'TERLAMBAT';
            $lateMinutes = $shiftStart->diffInMinutes($now);
        }

        // --- UPDATE: Menggunakan ImageCompressor ---
        // Foto akan di-resize ke 1280px, Quality 75, disimpan di folder 'attendance_photos'
        $photoPath = $this->imageCompressor->compressAndStore(
            $request->file('photo'), 
            'photo', 
            'attendance_photos', 
            'att_'
        );

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date'    => $today,
            ],
            [
                'shift_id'            => $employeeShift->shift_id,
                'employee_shift_id'   => $employeeShift->id,
                'normal_start_time'   => $shiftStart,
                'normal_end_time'     => $shiftEnd,
                'location_id'         => $employeeShift->location_id,
                'clock_in_at'         => $now,
                'clock_in_photo'      => $photoPath, // Menggunakan path hasil kompresi
                'clock_in_lat'        => $request->lat,
                'clock_in_lng'        => $request->lng,
                'clock_in_distance_m' => $distance,
                'late_minutes'        => $lateMinutes,
                'early_leave_minutes' => 0,
                'overtime_minutes'    => 0,
                'status'              => $status,
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
            'photo' => ['required', 'image', 'max:8192'],
            'lat'   => ['required', 'numeric'],
            'lng'   => ['required', 'numeric'],
        ]);

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

        $attendanceDate = $attendance->date instanceof Carbon
            ? $attendance->date->toDateString()
            : (string) $attendance->date;

        $employeeShiftQuery = EmployeeShift::with(['location', 'shift'])
            ->where('user_id', $user->id);

        if ($attendance->employee_shift_id) {
            $employeeShiftQuery->where('id', $attendance->employee_shift_id);
        }

        $employeeShift = $employeeShiftQuery->first();

        // Fallback jika shift history terhapus, ambil shift user saat ini
        if (!$employeeShift) {
             $employeeShift = EmployeeShift::with(['location', 'shift'])
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$employeeShift || !$employeeShift->location) {
            return response()->json([
                'error' => 'Jadwal shift atau lokasi belum diatur. Silakan hubungi HR.',
            ], 400);
        }

        $location = $employeeShift->location;

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

        $normalEnd = null;

        if ($attendance->normal_end_time) {
            $normalEnd = $attendance->normal_end_time instanceof Carbon
                ? $attendance->normal_end_time
                : Carbon::parse($attendance->normal_end_time);
        } elseif ($employeeShift->shift) {
            $dayOfWeek = Carbon::parse($attendanceDate)->dayOfWeek;
            $pattern = $employeeShift->shift
                ->patternDays()
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if ($pattern && !$pattern->is_holiday && $pattern->end_time) {
                $normalEnd = Carbon::parse($attendanceDate . ' ' . $pattern->end_time);
            }
        }

        if (!$normalEnd) {
            return response()->json([
                'error' => 'Format jam pulang shift tidak valid, hubungi HR.',
            ], 400);
        }

        $clockIn = $attendance->clock_in_at instanceof Carbon
            ? $attendance->clock_in_at
            : Carbon::parse($attendance->clock_in_at);

        if ($normalEnd->lessThanOrEqualTo($clockIn)) {
            $normalEnd->addDay();
        }

        // --- UPDATE: Menggunakan ImageCompressor ---
        $photoPath = $this->imageCompressor->compressAndStore(
            $request->file('photo'), 
            'photo', 
            'attendance_photos', 
            'att_out_'
        );

        $earlyLeaveMinutes = 0;
        $overtimeMinutes = 0;

        if ($now->lt($normalEnd)) {
            $earlyLeaveMinutes = $normalEnd->diffInMinutes($now);
        } elseif ($now->gt($normalEnd)) {
            $overtimeMinutes = $now->diffInMinutes($normalEnd);
        }

        // Sanitasi nilai integer
        $earlyLeaveMinutes = (int) max(0, round($earlyLeaveMinutes));
        $overtimeMinutes   = (int) max(0, round($overtimeMinutes));

        $attendance->update([
            'clock_out_at'         => $now,
            'clock_out_lat'        => $request->lat,
            'clock_out_lng'        => $request->lng,
            'clock_out_distance_m' => $distance,
            'clock_out_photo'      => $photoPath, // Menggunakan path hasil kompresi
            'early_leave_minutes'  => $earlyLeaveMinutes,
            'overtime_minutes'     => $overtimeMinutes,
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