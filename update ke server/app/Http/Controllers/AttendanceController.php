<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\EmployeeShift;
use App\Models\OfficeHoliday;
use App\Services\Attendance\AllowedAttendanceLocationResolver;
use App\Services\Attendance\MissedClockOutService;
use App\Services\Image\ImageCompressor;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
    public function __construct(
        protected ImageCompressor $imageCompressor,
        protected MissedClockOutService $missedClockOutService,
        protected AllowedAttendanceLocationResolver $allowedAttendanceLocationResolver,
    ) {}

    // --- DASHBOARD ---
    public function dashboard()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::with(['shift', 'location', 'employeeShift'])
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $activeAttendance = $this->findOpenAttendance($user);
        $activeAttendanceEndAt = $activeAttendance?->is_overnight
            ? $this->getNormalEndAt($activeAttendance)
            : null;

        $previousIncompleteAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', '<', $today)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->whereIn('completion_status', [Attendance::COMPLETION_OPEN, Attendance::COMPLETION_MISSED_CLOCK_OUT])
            ->orderBy('date', 'desc')
            ->first();

        return view('attendance.dashboard', [
            'attendance' => $attendance,
            'activeAttendance' => $activeAttendance,
            'activeAttendanceEndAt' => $activeAttendanceEndAt,
            'previousIncompleteAttendance' => $previousIncompleteAttendance,
        ]);
    }

    public function showClockInForm()
    {
        return view('attendance.clock_in', [
            'officeHoliday' => $this->activeOfficeHoliday(),
        ]);
    }

    public function showClockOutForm()
    {
        $user = Auth::user();
        $attendance = $this->findOpenAttendance($user);

        return view('attendance.clock_out', compact('attendance'));
    }

    // --- DINAS LUAR (REMOTE) PAGES ---
    public function remoteIndex()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $activeAttendance = $this->findOpenAttendance($user);
        $activeRemoteAttendance = $activeAttendance && $activeAttendance->type === 'DINAS_LUAR' ? $activeAttendance : null;

        $previousIncompleteAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', '<', $today)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->whereIn('completion_status', [Attendance::COMPLETION_OPEN, Attendance::COMPLETION_MISSED_CLOCK_OUT])
            ->orderBy('date', 'desc')
            ->first();

        $history = Attendance::where('user_id', $user->id)
            ->where('type', 'DINAS_LUAR')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('attendance.remote.index', compact('todayAttendance', 'activeAttendance', 'activeRemoteAttendance', 'previousIncompleteAttendance', 'history'));
    }

    public function remoteClockIn(Request $request)
    {
        return $this->clockIn($request, true);
    }

    public function remotePurpose()
    {
        return view('attendance.remote.purpose');
    }

    public function remotePhoto()
    {
        return view('attendance.remote.photo', [
            'mode' => 'in',
            'officeHoliday' => $this->activeOfficeHoliday(),
        ]);
    }

    public function showRemoteClockOutForm()
    {
        return view('attendance.remote.photo', ['mode' => 'out']);
    }

    public function remoteClockOut(Request $request)
    {
        return $this->clockOut($request);
    }

    private function activeOfficeHoliday(): ?OfficeHoliday
    {
        return OfficeHoliday::query()
            ->whereDate('holiday_date', now()->toDateString())
            ->where('is_active', true)
            ->first();
    }

    // =========================================================================
    // LOGIC UTAMA: CLOCK IN
    // =========================================================================
    public function clockIn(Request $request, $isRemote = false)
    {
        // 1. Validasi Input (di luar try/catch agar validation error tidak jadi 500)
        $request->validate([
            'photo' => ['required', 'image', 'max:8192'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'notes' => $isRemote ? ['required', 'string'] : ['nullable', 'string'],
        ]);

        try {
            $user = Auth::user();
            $now = now();
            $today = $now->toDateString();

            // 2. Cek Shift & Lokasi User
            $employeeShift = EmployeeShift::with($this->employeeShiftRelations(true))
                ->where('user_id', $user->id)
                ->first();

            if (! $employeeShift || ! $employeeShift->shift) {
                return response()->json(['message' => 'Jadwal shift belum diatur. Hubungi HR.'], 400);
            }

            // 3. Cek Pola Shift Hari Ini (gunakan ISO dayOfWeek: Senin=1, Minggu=7)
            $dayOfWeek = Carbon::parse($today)->dayOfWeekIso;
            $pattern = $employeeShift->shift->patternDays()->where('day_of_week', $dayOfWeek)->first();

            if (! $pattern) {
                return response()->json(['message' => 'Tidak ada jadwal shift hari ini.'], 400);
            }
            if ($pattern->is_holiday) {
                return response()->json(['message' => 'Hari ini adalah hari libur.'], 400);
            }

            // 4. Setup Jam Kerja
            // Gabungkan Tanggal Hari Ini + Jam dari Pattern
            $shiftStart = Carbon::parse($today.' '.$pattern->start_time);
            $shiftEnd = Carbon::parse($today.' '.$pattern->end_time);

            // Handle Shift Lintas Hari
            if ($pattern->is_overnight) {
                $shiftEnd->addDay();
            }

            // 5. Blokir clock-in jika masih ada sesi sebelumnya yang aktif
            $activePrevious = $this->findActivePreviousOpenAttendance($user);
            if ($activePrevious) {
                return response()->json([
                    'message' => 'Masih ada sesi presensi sebelumnya yang berjalan. Silakan lakukan presensi keluar terlebih dahulu.',
                ], 400);
            }

            // 6. Cek Double Absen (fast path tanpa lock)
            $existing = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();
            if ($existing && $existing->clock_in_at) {
                return response()->json(['message' => 'Presensi masuk hari ini sudah tercatat.'], 400);
            }

            // 7. Validasi Radius (Hanya untuk WFO)
            $distance = 0;
            $matchedLocation = null;
            if (! $isRemote) {
                if (! $employeeShift->is_all_locations) {
                    $locations = $this->scheduleLocations($employeeShift);
                    if ($locations->isEmpty()) {
                        return response()->json(['message' => 'Lokasi kantor belum diatur.'], 400);
                    }
                    $match = $this->allowedAttendanceLocationResolver->resolve($locations, (float) $request->lat, (float) $request->lng);

                    if ($match === null) {
                        $nearest = $this->allowedAttendanceLocationResolver->nearest($locations, (float) $request->lat, (float) $request->lng);

                        return response()->json(['message' => "Anda berada di luar radius kantor ({$nearest['distance']} m)."], 400);
                    }

                    $matchedLocation = $match['location'];
                    $distance = $match['distance'];
                }
            }

            // 8. Hitung Keterlambatan
            $status = 'HADIR';
            $lateMinutes = 0;
            if ($now->gt($shiftStart)) {
                $status = 'TERLAMBAT';
                $lateMinutes = (int) $shiftStart->diffInMinutes($now);
            }

            // 9. Proses Upload Foto
            $folder = $isRemote ? 'remote_photos' : 'attendance_photos';
            $prefix = $isRemote ? 'remote_in_' : 'att_in_';

            $photoPath = $this->imageCompressor->compressAndStore(
                $request->file('photo'), 'photo', $folder, $prefix
            );

            // 10. Simpan ke Database dalam transaction + lock untuk hindari race condition
            try {
                $attendance = DB::transaction(function () use (
                    $user,
                    $today,
                    $employeeShift,
                    $shiftStart,
                    $shiftEnd,
                    $isRemote,
                    $matchedLocation,
                    $distance,
                    $lateMinutes,
                    $status,
                    $photoPath,
                    $request,
                    $now
                ) {
                    $existingLocked = Attendance::where('user_id', $user->id)
                        ->whereDate('date', $today)
                        ->lockForUpdate()
                        ->first();

                    if ($existingLocked && $existingLocked->clock_in_at) {
                        throw new \RuntimeException('ALREADY_CLOCKED_IN');
                    }

                    return Attendance::create([
                        'user_id' => $user->id,
                        'date' => $today,
                        'shift_id' => $employeeShift->shift_id,
                        'employee_shift_id' => $employeeShift->id,
                        'normal_start_time' => $shiftStart,
                        'normal_end_time' => $shiftEnd,
                        'location_id' => $isRemote || $employeeShift->is_all_locations ? null : $matchedLocation?->id,
                        'clock_in_at' => $now,
                        'clock_in_photo' => $photoPath,
                        'clock_in_lat' => $request->lat,
                        'clock_in_lng' => $request->lng,
                        'clock_in_distance_m' => $distance,
                        'late_minutes' => $lateMinutes,
                        'status' => $status,
                        'type' => $isRemote ? 'DINAS_LUAR' : 'WFO',
                        'approval_status' => 'PENDING',
                        'notes' => $request->notes ?? null,
                        'completion_status' => Attendance::COMPLETION_OPEN,
                    ]);
                });
            } catch (\RuntimeException $e) {
                if ($e->getMessage() === 'ALREADY_CLOCKED_IN') {
                    return response()->json(['message' => 'Presensi masuk hari ini sudah tercatat.'], 400);
                }

                throw $e;
            } catch (UniqueConstraintViolationException $e) {
                return response()->json(['message' => 'Presensi masuk hari ini sudah tercatat.'], 400);
            }

            return response()->json([
                'message' => 'Presensi masuk berhasil.',
                'data' => $attendance,
            ]);

        } catch (\Exception $e) {
            Log::error('ClockIn Error: '.$e->getMessage());

            return response()->json(['message' => 'Presensi masuk gagal. Silakan coba lagi.'], 500);
        }
    }

    // =========================================================================
    // LOGIC UTAMA: CLOCK OUT
    // =========================================================================
    public function clockOut(Request $request)
    {
        // 1. Validasi Input (di luar try/catch agar validation error tidak jadi 500)
        $request->validate([
            'photo' => ['required', 'image', 'max:8192'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        try {
            $user = Auth::user();
            $now = now();

            // 2. Cari Absensi Aktif
            $attendance = $this->findOpenAttendance($user);

            if (! $attendance) {
                return response()->json(['message' => 'Tidak ada sesi presensi aktif untuk ditutup.'], 400);
            }

            // [GUARD] Cegah menimpa clock out yang sudah ada
            if ($attendance->clock_out_at !== null) {
                return response()->json(['message' => 'Presensi pulang hari ini sudah tercatat.'], 400);
            }

            // 3. Deteksi Tipe Absensi
            $isDinasLuar = ($attendance->type === 'DINAS_LUAR');

            // 4. Validasi Radius (Hanya Jika WFO)
            $distance = 0;
            if (! $isDinasLuar) {
                $empShift = EmployeeShift::with($this->employeeShiftRelations())->where('user_id', $user->id)->first();

                if ($empShift && ! $empShift->is_all_locations) {
                    $locations = $this->scheduleLocations($empShift);
                    if ($locations->isEmpty()) {
                        return response()->json(['message' => 'Lokasi kantor belum diatur.'], 400);
                    }

                    $match = $this->allowedAttendanceLocationResolver->resolve($locations, (float) $request->lat, (float) $request->lng);
                    if ($match === null) {
                        $nearest = $this->allowedAttendanceLocationResolver->nearest($locations, (float) $request->lat, (float) $request->lng);

                        return response()->json(['message' => "Anda harus berada di kantor untuk melakukan presensi keluar ({$nearest['distance']} m)."], 400);
                    }

                    $distance = $match['distance'];
                }
            }

            // 5. Hitung Pulang Cepat / Lembur menggunakan helper normal end
            $normalEnd = $this->getNormalEndAt($attendance);

            $earlyLeaveMinutes = 0;
            $overtimeMinutes = 0;

            if ($now->lt($normalEnd)) {
                // Pulang Cepat
                $earlyLeaveMinutes = (int) abs($normalEnd->diffInMinutes($now));
            } elseif ($now->gt($normalEnd)) {
                // Lembur
                $overtimeMinutes = (int) abs($now->diffInMinutes($normalEnd));
            }

            // [SAFEGUARD] Pastikan tidak ada nilai negatif masuk database
            $earlyLeaveMinutes = max(0, $earlyLeaveMinutes);
            $overtimeMinutes = max(0, $overtimeMinutes);

            // 6. Tentukan Completion Status
            $toleranceHours = 4;
            if ($now->gt($normalEnd->copy()->addHours($toleranceHours))) {
                $completionStatus = Attendance::COMPLETION_LATE_CLOCK_OUT;
            } else {
                $completionStatus = Attendance::COMPLETION_CLOSED;
            }

            // Jika LATE_CLOCK_OUT, jangan hitung overtime/early_leave
            if ($completionStatus === Attendance::COMPLETION_LATE_CLOCK_OUT) {
                $overtimeMinutes = 0;
                $earlyLeaveMinutes = 0;
            }

            // 7. Proses Upload Foto Out
            $folder = $isDinasLuar ? 'remote_photos' : 'attendance_photos';
            $prefix = $isDinasLuar ? 'remote_out_' : 'att_out_';

            $photoPath = $this->imageCompressor->compressAndStore(
                $request->file('photo'), 'photo', $folder, $prefix
            );

            // 8. Update Database
            $attendance->update([
                'clock_out_at' => $now,
                'clock_out_lat' => $request->lat,
                'clock_out_lng' => $request->lng,
                'clock_out_distance_m' => $distance,
                'clock_out_photo' => $photoPath,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'overtime_minutes' => $overtimeMinutes,
                'completion_status' => $completionStatus,
            ]);

            return response()->json([
                'message' => 'Presensi keluar berhasil.',
                'data' => $attendance,
            ]);

        } catch (\Exception $e) {
            Log::error("ClockOut Error User {$user->id}: ".$e->getMessage());

            return response()->json(['message' => 'Presensi keluar gagal. Silakan coba lagi.'], 500);
        }
    }

    // --- HELPER FUNCTIONS ---

    private function findOpenAttendance($user)
    {
        $today = now()->toDateString();

        // 1. Cek Hari Ini (OPEN)
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->where('completion_status', Attendance::COMPLETION_OPEN)
            ->first();

        if ($attendance) {
            return $attendance;
        }

        // 2. Jika tidak ada, cek attendance sebelumnya yang masih aktif
        $previousActive = $this->findActivePreviousOpenAttendance($user);
        if ($previousActive) {
            return $previousActive;
        }

        return null;
    }

    /**
     * Reconstruct normal start datetime from attendance date + normal_start_time.
     */
    private function getNormalStartAt(Attendance $attendance): Carbon
    {
        $dateStr = $attendance->date instanceof Carbon
            ? $attendance->date->toDateString()
            : (string) $attendance->date;

        $timeStr = '08:00:00';
        if ($attendance->normal_start_time) {
            $timeStr = Carbon::parse($attendance->normal_start_time)->format('H:i:s');
        }

        return Carbon::parse($dateStr.' '.$timeStr);
    }

    /**
     * Reconstruct normal end datetime from attendance date + normal_end_time.
     * Cross-day is determined by comparing scheduled start vs scheduled end,
     * NOT actual clock_in_at.
     */
    private function getNormalEndAt(Attendance $attendance): Carbon
    {
        $dateStr = $attendance->date instanceof Carbon
            ? $attendance->date->toDateString()
            : (string) $attendance->date;

        $timeStr = '17:00:00';
        if ($attendance->normal_end_time) {
            $timeStr = Carbon::parse($attendance->normal_end_time)->format('H:i:s');
        }

        $normalEnd = Carbon::parse($dateStr.' '.$timeStr);
        $normalStart = $this->getNormalStartAt($attendance);

        if ($normalEnd->lessThanOrEqualTo($normalStart)) {
            $normalEnd->addDay();
        }

        return $normalEnd;
    }

    private function findActivePreviousOpenAttendance($user): ?Attendance
    {
        $this->missedClockOutService->flagPreviousOpenAttendances($user->id);

        $today = now()->toDateString();

        $previousOpen = Attendance::where('user_id', $user->id)
            ->whereDate('date', '<', $today)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->where('completion_status', Attendance::COMPLETION_OPEN)
            ->orderBy('date', 'desc')
            ->first();

        return $previousOpen;
    }

    /**
     * @return array<int, string>
     */
    private function employeeShiftRelations(bool $withShift = false): array
    {
        $relations = ['location'];

        if ($withShift) {
            $relations[] = 'shift';
        }

        if ($this->hasLocationAssignmentsTable()) {
            $relations[] = 'locations';
        }

        return $relations;
    }

    /**
     * @return Collection<int, \App\Models\AttendanceLocation>
     */
    private function scheduleLocations(EmployeeShift $employeeShift): Collection
    {
        if ($this->hasLocationAssignmentsTable()) {
            $locations = $employeeShift->relationLoaded('locations')
                ? $employeeShift->locations
                : $employeeShift->locations()->get();

            if ($locations->isNotEmpty()) {
                return $locations;
            }
        }

        return $employeeShift->location ? collect([$employeeShift->location]) : collect();
    }

    private function hasLocationAssignmentsTable(): bool
    {
        return Schema::hasTable('employee_shift_locations');
    }
}
