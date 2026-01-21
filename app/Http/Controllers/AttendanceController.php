<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\EmployeeShift;
use App\Services\Image\ImageCompressor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function __construct(protected ImageCompressor $imageCompressor)
    {
    }

    // --- DASHBOARD ---
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

    // --- DINAS LUAR (REMOTE) PAGES ---
    public function remoteIndex()
    {
        $user = Auth::user();
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();
            
        $history = Attendance::where('user_id', $user->id)
            ->where('type', 'DINAS_LUAR')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('attendance.remote.index', compact('todayAttendance', 'history'));
    }

    public function remoteClockIn(Request $request)
    {
        return $this->clockIn($request, true);
    }

    public function remoteClockOut(Request $request)
    {
        return $this->clockOut($request); 
    }

    // =========================================================================
    // LOGIC UTAMA: CLOCK IN
    // =========================================================================
    public function clockIn(Request $request, $isRemote = false)
    {
        try {
            $user = Auth::user();
            $now = now();
            $today = $now->toDateString();

            // 1. Validasi Input
            $request->validate([
                'photo' => ['required', 'image', 'max:8192'],
                'lat'   => ['required', 'numeric'],
                'lng'   => ['required', 'numeric'],
                'notes' => $isRemote ? ['required', 'string'] : ['nullable', 'string'],
            ]);

            // 2. Cek Shift & Lokasi User
            $employeeShift = EmployeeShift::with(['location', 'shift'])
                ->where('user_id', $user->id)
                ->first();

            if (!$employeeShift || !$employeeShift->shift) {
                return response()->json(['message' => 'Jadwal shift belum diatur. Hubungi HR.'], 400);
            }

            // 3. Cek Pola Shift Hari Ini
            $dayOfWeek = Carbon::parse($today)->dayOfWeek;
            $pattern = $employeeShift->shift->patternDays()->where('day_of_week', $dayOfWeek)->first();

            if (!$pattern) {
                return response()->json(['message' => 'Tidak ada jadwal shift hari ini.'], 400);
            }
            if ($pattern->is_holiday) {
                return response()->json(['message' => 'Hari ini adalah hari libur.'], 400);
            }

            // 4. Setup Jam Kerja
            // Gabungkan Tanggal Hari Ini + Jam dari Pattern
            $shiftStart = Carbon::parse($today . ' ' . $pattern->start_time);
            $shiftEnd   = Carbon::parse($today . ' ' . $pattern->end_time);

            // Handle Shift Lintas Hari
            if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
                $shiftEnd->addDay();
            }

            // 5. Cek Double Absen
            $existing = Attendance::where('user_id', $user->id)->where('date', $today)->first();
            if ($existing && $existing->clock_in_at) {
                return response()->json(['message' => 'Anda sudah melakukan clock-in hari ini.'], 400);
            }

            // 6. Validasi Radius (Hanya untuk WFO)
            $distance = 0;
            if (!$isRemote) {
                if (!$employeeShift->location) {
                    return response()->json(['message' => 'Lokasi kantor belum diatur.'], 400);
                }
                $loc = $employeeShift->location;
                $distance = (int) round($this->calculateDistance(
                    $request->lat, $request->lng, $loc->latitude, $loc->longitude
                ));

                if ($distance > $loc->radius_meters) {
                    return response()->json(['message' => "Anda berada di luar radius kantor ($distance m)."], 400);
                }
            }

            // 7. Hitung Keterlambatan
            $status = 'HADIR';
            $lateMinutes = 0;
            if ($now->gt($shiftStart)) {
                $status = 'TERLAMBAT';
                $lateMinutes = (int) $shiftStart->diffInMinutes($now);
            }

            // 8. Proses Upload Foto
            $folder = $isRemote ? 'remote_photos' : 'attendance_photos';
            $prefix = $isRemote ? 'remote_in_' : 'att_in_';
            
            $photoPath = $this->imageCompressor->compressAndStore(
                $request->file('photo'), 'photo', $folder, $prefix
            );

            // 9. Simpan ke Database
            $attendance = Attendance::updateOrCreate(
                ['user_id' => $user->id, 'date' => $today],
                [
                    'shift_id'            => $employeeShift->shift_id,
                    'employee_shift_id'   => $employeeShift->id,
                    'normal_start_time'   => $shiftStart,
                    'normal_end_time'     => $shiftEnd,
                    'location_id'         => $isRemote ? null : $employeeShift->location_id,
                    'clock_in_at'         => $now,
                    'clock_in_photo'      => $photoPath,
                    'clock_in_lat'        => $request->lat,
                    'clock_in_lng'        => $request->lng,
                    'clock_in_distance_m' => $distance,
                    'late_minutes'        => $lateMinutes,
                    'status'              => $status,
                    'type'                => $isRemote ? 'DINAS_LUAR' : 'WFO',
                    'approval_status'     => $isRemote ? 'PENDING' : 'APPROVED',
                    'notes'               => $request->notes ?? null,
                ]
            );

            return response()->json([
                'message' => 'Clock In Berhasil.',
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error("ClockIn Error: " . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // LOGIC UTAMA: CLOCK OUT
    // =========================================================================
    public function clockOut(Request $request)
    {
        try {
            $user = Auth::user();
            $now = now();
            
            // 1. Cari Absensi Aktif
            $attendance = $this->findOpenAttendance($user);

            if (!$attendance) {
                return response()->json(['message' => 'Tidak ada sesi absensi aktif untuk di-close.'], 400);
            }

            // 2. Deteksi Tipe Absensi
            $isDinasLuar = ($attendance->type === 'DINAS_LUAR');

            // 3. Validasi Radius (Hanya Jika WFO)
            $distance = 0;
            if (!$isDinasLuar) {
                $empShift = EmployeeShift::with('location')->where('user_id', $user->id)->first();
                
                if ($empShift && $empShift->location) {
                    $loc = $empShift->location;
                    $distance = (int) round($this->calculateDistance(
                        $request->lat, $request->lng, $loc->latitude, $loc->longitude
                    ));

                    if ($distance > $loc->radius_meters) {
                        return response()->json(['message' => "Anda harus berada di kantor untuk Clock Out ($distance m)."], 400);
                    }
                }
            }

            // 4. Hitung Pulang Cepat / Lembur (FIXED LOGIC)
            
            // Ambil tanggal dari data absensi (bukan hari ini, untuk handle shift kemarin)
            $attendanceDateStr = $attendance->date instanceof Carbon ? $attendance->date->toDateString() : $attendance->date;
            
            // Construct Normal End Time yang benar
            $normalEnd = null;
            if ($attendance->normal_end_time) {
                // Parse hanya jam-nya
                $timeString = Carbon::parse($attendance->normal_end_time)->format('H:i:s');
                $normalEnd = Carbon::parse($attendanceDateStr . ' ' . $timeString);
            } else {
                $normalEnd = Carbon::parse($attendanceDateStr . ' 17:00:00');
            }

            // Handle Shift Lintas Hari (jika jam pulang < jam masuk)
            $clockInTime = Carbon::parse($attendance->clock_in_at);
            if ($normalEnd->lessThan($clockInTime)) {
                $normalEnd->addDay();
            }

            $earlyLeaveMinutes = 0;
            $overtimeMinutes = 0;

            if ($now->lt($normalEnd)) {
                // Pulang Cepat
                $diff = $normalEnd->diffInMinutes($now); // Absolute diff
                $earlyLeaveMinutes = (int) abs($diff); // Pastikan positif
            } elseif ($now->gt($normalEnd)) {
                // Lembur
                $diff = $now->diffInMinutes($normalEnd); // Absolute diff
                $overtimeMinutes = (int) abs($diff); // Pastikan positif
            }

            // [SAFEGUARD] Pastikan tidak ada nilai negatif masuk database
            $earlyLeaveMinutes = max(0, $earlyLeaveMinutes);
            $overtimeMinutes   = max(0, $overtimeMinutes);

            // 5. Proses Upload Foto Out
            $folder = $isDinasLuar ? 'remote_photos' : 'attendance_photos';
            $prefix = $isDinasLuar ? 'remote_out_' : 'att_out_';

            $photoPath = $this->imageCompressor->compressAndStore(
                $request->file('photo'), 'photo', $folder, $prefix
            );

            // 6. Update Database
            $attendance->update([
                'clock_out_at'         => $now,
                'clock_out_lat'        => $request->lat,
                'clock_out_lng'        => $request->lng,
                'clock_out_distance_m' => $distance,
                'clock_out_photo'      => $photoPath,
                'early_leave_minutes'  => $earlyLeaveMinutes,
                'overtime_minutes'     => $overtimeMinutes,
            ]);

            return response()->json([
                'message' => 'Clock Out Berhasil.',
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error("ClockOut Error User {$user->id}: " . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    // --- HELPER FUNCTIONS ---

    private function findOpenAttendance($user)
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // 1. Cek Hari Ini
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->first();

        // 2. Jika tidak ada, cek Kemarin (Shift Malam)
        if (!$attendance) {
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $yesterday)
                ->whereNotNull('clock_in_at')
                ->whereNull('clock_out_at')
                ->first();
        }

        return $attendance;
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

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lngDelta / 2), 2)));
            
        return $earthRadius * $angle;
    }
}