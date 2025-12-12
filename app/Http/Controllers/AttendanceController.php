<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\EmployeeShift;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceController extends Controller
{
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
            'photo' => ['required', 'image', 'max:4096'],
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

        $photoPath = $this->storeAttendancePhoto($request->file('photo'));

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
                'clock_in_photo'      => $photoPath,
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
            'photo' => ['required', 'image', 'max:4096'],
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

        $photoPath = $this->storeAttendancePhoto($request->file('photo'));

        $earlyLeaveMinutes = 0;
        $overtimeMinutes = 0;

        if ($now->lt($normalEnd)) {
            $earlyLeaveMinutes = $normalEnd->diffInMinutes($now);
        } elseif ($now->gt($normalEnd)) {
            $overtimeMinutes = $now->diffInMinutes($normalEnd);
        }

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

    protected function storeAttendancePhoto(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $dir = 'attendance_photos';
        $disk = Storage::disk('public');

        $canGd = function_exists('imagecreatetruecolor') && function_exists('imagejpeg');

        Log::info('Attendance storeAttendancePhoto called', [
            'ext' => $ext,
            'can_gd' => $canGd,
        ]);

        if ($canGd && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            try {
                $sourcePath = $file->getPathname();
                $info = getimagesize($sourcePath);
                if ($info === false) {
                    throw new \RuntimeException('Invalid image.');
                }

                $width = $info[0];
                $height = $info[1];

                $maxSide = 720;
                $scale = min($maxSide / max($width, 1), $maxSide / max($height, 1), 1);
                $newWidth = (int) round($width * $scale);
                $newHeight = (int) round($height * $scale);

                switch ($ext) {
                    case 'jpg':
                    case 'jpeg':
                        $srcImage = imagecreatefromjpeg($sourcePath);
                        break;
                    case 'png':
                        $srcImage = imagecreatefrompng($sourcePath);
                        break;
                    case 'webp':
                        if (!function_exists('imagecreatefromwebp')) {
                            throw new \RuntimeException('WEBP not supported.');
                        }
                        $srcImage = imagecreatefromwebp($sourcePath);
                        break;
                    default:
                        $srcImage = null;
                }

                if (!$srcImage) {
                    throw new \RuntimeException('Failed to create image resource.');
                }

                $dstImage = imagecreatetruecolor($newWidth, $newHeight);

                if ($ext === 'png' || $ext === 'webp') {
                    imagealphablending($dstImage, false);
                    imagesavealpha($dstImage, true);
                    $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
                    imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
                }

                imagecopyresampled(
                    $dstImage,
                    $srcImage,
                    0,
                    0,
                    0,
                    0,
                    $newWidth,
                    $newHeight,
                    $width,
                    $height
                );

                $filename = 'att_' . uniqid('', true) . '.jpg';

                ob_start();
                imagejpeg($dstImage, null, 70);
                $contents = ob_get_clean();

                imagedestroy($srcImage);
                imagedestroy($dstImage);

                if ($contents === false) {
                    throw new \RuntimeException('Failed to encode JPEG.');
                }

                $disk->put($dir . '/' . $filename, $contents);

                Log::info('Attendance compression success', [
                    'filename' => $filename,
                    'size_bytes' => strlen($contents),
                ]);

                return $dir . '/' . $filename;
            } catch (\Throwable $e) {
                Log::warning('Attendance photo compression failed, fallback to original store', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $stored = $file->store($dir, 'public');

        Log::info('Attendance store fallback stored', [
            'stored' => $stored,
        ]);

        return $stored;
    }
}
