<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\OfficeHoliday;
use App\Models\User;
use App\Services\Attendance\AllowedAttendanceLocationResolver;
use App\Services\Image\ImageCompressor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    private const EVENT_COOLDOWN_MINUTES = 15;
    private const MAX_LOCATION_ACCURACY_METERS = 100;

    public function __construct(
        protected ImageCompressor $imageCompressor,
        protected AllowedAttendanceLocationResolver $allowedAttendanceLocationResolver,
    ) {}

    public function dashboard()
    {
        $events = AttendanceEvent::query()
            ->where('user_id', Auth::id())
            ->whereDate('occurred_at', now()->toDateString())
            ->latest('occurred_at')
            ->get();

        $latestAttendanceEvent = AttendanceEvent::query()
            ->where('user_id', Auth::id())
            ->latest('occurred_at')
            ->first();

        return view('attendance.dashboard', [
            'events' => $events,
            'latestAttendanceEvent' => $latestAttendanceEvent,
            'attendance' => null,
            'activeAttendance' => null,
            'activeAttendanceEndAt' => null,
            'previousIncompleteAttendance' => null,
        ]);
    }

    public function showClockInForm()
    {
        return view('attendance.clock_in', [
            'officeHoliday' => $this->activeOfficeHoliday(),
            ...$this->flexibleAttendanceContext(),
        ]);
    }

    public function showClockOutForm()
    {
        return view('attendance.clock_out', [
            'attendance' => null,
            ...$this->flexibleAttendanceContext(),
        ]);
    }

    public function remoteIndex()
    {
        $events = AttendanceEvent::query()
            ->where('user_id', Auth::id())
            ->where('type', 'DINAS_LUAR')
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        return view('attendance.remote.index', [
            'events' => $events,
            'history' => $events,
            'todayAttendance' => null,
            'activeAttendance' => null,
            'activeRemoteAttendance' => null,
            'previousIncompleteAttendance' => null,
        ]);
    }

    public function remoteClockIn(Request $request)
    {
        return $this->recordEvent($request, AttendanceEvent::CLOCK_IN, true);
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
        return $this->recordEvent($request, AttendanceEvent::CLOCK_OUT, true);
    }

    public function clockIn(Request $request)
    {
        return $this->recordEvent($request, AttendanceEvent::CLOCK_IN);
    }

    public function clockOut(Request $request)
    {
        return $this->recordEvent($request, AttendanceEvent::CLOCK_OUT);
    }

    private function recordEvent(Request $request, string $eventType, bool $isRemote = false)
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:8192'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'accuracy' => ['required', 'numeric', 'min:0', 'max:'.self::MAX_LOCATION_ACCURACY_METERS],
            'notes' => $isRemote && $eventType === AttendanceEvent::CLOCK_IN ? ['required', 'string'] : ['nullable', 'string'],
        ], [
            'accuracy.required' => 'Aktifkan Lokasi Presisi sebelum melakukan presensi.',
            'accuracy.max' => 'Akurasi lokasi terlalu lemah. Aktifkan Lokasi Presisi, lalu coba lagi.',
        ]);

        $user = Auth::user();
        $photoPath = null;

        if ($cooldownEvent = $this->activeCooldownEvent($user->id)) {
            return $this->cooldownResponse($cooldownEvent);
        }

        try {
            $employeeShift = EmployeeShift::with($this->employeeShiftRelations(true))
                ->where('user_id', $user->id)
                ->first();

            if (! $employeeShift || ! $employeeShift->shift) {
                return response()->json(['message' => 'Jadwal shift belum diatur. Hubungi HR.'], 400);
            }

            foreach (['work_date', 'attendance_shift_id', 'shift_start_time', 'shift_end_time', 'is_overnight', 'attendance_location_scope'] as $column) {
                if (! Schema::hasColumn('attendance_events', $column)) {
                    return response()->json(['message' => 'Pembaruan absensi belum tersedia. Hubungi HR atau IT.'], 503);
                }
            }

            if (! $isRemote && (! Schema::hasColumn('attendance_events', 'is_flexible') || ! Schema::hasColumn('attendance_events', 'is_outside_reference_location'))) {
                return response()->json(['message' => 'Pembaruan absensi belum tersedia. Hubungi HR atau IT.'], 503);
            }

            $occurredAt = now();
            $shiftSnapshot = $this->attendanceShiftSnapshot($employeeShift, $occurredAt);

            $matchedLocation = null;
            $distance = 0;
            $isFlexibleEvent = ! $isRemote && $employeeShift->is_flexible;
            $isOutsideReferenceLocation = null;
            $referenceLocationIds = [];

            if (! $isRemote) {
                $locations = $this->scheduleLocations($employeeShift);
                if ($locations->isEmpty()) {
                    return response()->json([
                        'message' => $isFlexibleEvent
                            ? 'Lokasi absen Fleksibel belum diatur. Hubungi HR.'
                            : 'Lokasi kantor belum diatur.',
                    ], 400);
                }

                $match = $this->allowedAttendanceLocationResolver->resolve($locations, (float) $request->lat, (float) $request->lng);
                if ($match === null) {
                    if ($isFlexibleEvent) {
                        if (trim((string) $request->notes) === '') {
                            $action = $eventType === AttendanceEvent::CLOCK_IN ? 'Clock In' : 'Clock Out';
                            $message = "Alasan {$action} di luar lokasi absen wajib diisi.";

                            return response()->json(['message' => $message, 'errors' => ['notes' => [$message]]], 422);
                        }

                        $isOutsideReferenceLocation = true;
                        $referenceLocationIds = $locations->pluck('id')->sort()->values()->all();
                    } else {
                        $nearest = $this->allowedAttendanceLocationResolver->nearest($locations, (float) $request->lat, (float) $request->lng);
                        $action = $eventType === AttendanceEvent::CLOCK_IN ? 'berada di luar radius kantor' : 'harus berada di kantor untuk melakukan presensi keluar';

                        return response()->json(['message' => "Anda {$action} ({$nearest['distance']} m)."], 400);
                    }
                } else {
                    $matchedLocation = $match['location'];
                    $distance = $match['distance'];

                    if ($isFlexibleEvent) {
                        $isOutsideReferenceLocation = false;
                        $referenceLocationIds = $locations->pluck('id')->sort()->values()->all();
                    }
                }
            }

            $folder = $isRemote ? 'remote_photos' : 'attendance_photos';
            $prefix = $isRemote
                ? ($eventType === AttendanceEvent::CLOCK_IN ? 'remote_in_' : 'remote_out_')
                : ($eventType === AttendanceEvent::CLOCK_IN ? 'att_in_' : 'att_out_');
            $photoPath = $this->imageCompressor->compressAndStore($request->file('photo'), 'photo', $folder, $prefix);

            $recordedEvent = DB::transaction(function () use ($user, $eventType, $isRemote, $employeeShift, $matchedLocation, $photoPath, $request, $distance, $isFlexibleEvent, $isOutsideReferenceLocation, $referenceLocationIds, $occurredAt, $shiftSnapshot) {
                User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

                if ($cooldownEvent = $this->activeCooldownEvent($user->id)) {
                    return ['event' => $cooldownEvent, 'is_cooldown' => true];
                }

                if ($eventType === AttendanceEvent::CLOCK_OUT) {
                    $previousEvent = AttendanceEvent::query()
                        ->where('user_id', $user->id)
                        ->latest('occurred_at')
                        ->latest('id')
                        ->first();

                    if ($previousEvent?->event_type === AttendanceEvent::CLOCK_IN) {
                        $shiftSnapshot = $this->clockOutShiftSnapshot($previousEvent, $employeeShift);
                    }
                }

                if (! $isRemote) {
                    $currentShift = EmployeeShift::with($this->employeeShiftRelations(true))
                        ->where('user_id', $user->id)
                        ->lockForUpdate()
                        ->first();
                    if (! $currentShift || $currentShift->id !== $employeeShift->id || $currentShift->is_flexible !== $employeeShift->is_flexible) {
                        return ['is_cooldown' => false, 'is_schedule_changed' => true];
                    }

                    if ($isFlexibleEvent) {
                        $currentLocations = $this->scheduleLocations($currentShift);
                        $currentMatch = $currentLocations->isEmpty()
                            ? null
                            : $this->allowedAttendanceLocationResolver->resolve($currentLocations, (float) $request->lat, (float) $request->lng);
                        $currentReferenceLocationIds = $currentLocations->pluck('id')->sort()->values()->all();

                        if ($currentLocations->isEmpty()
                            || $currentReferenceLocationIds !== $referenceLocationIds
                            || ($currentMatch !== null ? $currentMatch['location']->id : null) !== ($matchedLocation?->id)
                            || ($currentMatch === null) !== $isOutsideReferenceLocation) {
                            return ['is_cooldown' => false, 'is_schedule_changed' => true];
                        }
                    }
                }

                return [
                    'event' => AttendanceEvent::query()->create([
                        'user_id' => $user->id,
                        'event_type' => $eventType,
                        'type' => $isRemote ? 'DINAS_LUAR' : 'WFO',
                        'occurred_at' => $occurredAt,
                        ...$shiftSnapshot,
                        'location_id' => $isRemote ? null : $matchedLocation?->id,
                        'photo' => $photoPath,
                        'latitude' => $request->lat,
                        'longitude' => $request->lng,
                        'distance_m' => $distance,
                        'approval_status' => 'PENDING',
                        'notes' => $request->notes,
                        ...($isRemote ? [] : [
                            'is_flexible' => $employeeShift->is_flexible,
                            'is_outside_reference_location' => $employeeShift->is_flexible ? $isOutsideReferenceLocation : null,
                        ]),
                    ]),
                    'is_cooldown' => false,
                ];
            });

            if ($recordedEvent['is_cooldown']) {
                Storage::disk('public')->delete($photoPath);

                return $this->cooldownResponse($recordedEvent['event']);
            }

            if ($recordedEvent['is_schedule_changed'] ?? false) {
                Storage::disk('public')->delete($photoPath);

                return response()->json(['message' => 'Jadwal presensi berubah. Muat ulang halaman dan coba lagi.'], 409);
            }

            $event = $recordedEvent['event'];

            return response()->json([
                'message' => $eventType === AttendanceEvent::CLOCK_IN ? 'Presensi masuk berhasil.' : 'Presensi keluar berhasil.',
                'data' => $event,
            ]);
        } catch (\Exception $e) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error("Attendance event {$eventType} error for user {$user->id}: ".$e->getMessage());

            return response()->json([
                'message' => $eventType === AttendanceEvent::CLOCK_IN ? 'Presensi masuk gagal. Silakan coba lagi.' : 'Presensi keluar gagal. Silakan coba lagi.',
            ], 500);
        }
    }

    private function activeOfficeHoliday(): ?OfficeHoliday
    {
        return OfficeHoliday::query()
            ->whereDate('holiday_date', now()->toDateString())
            ->where('is_active', true)
            ->first();
    }

    private function activeCooldownEvent(int $userId): ?AttendanceEvent
    {
        return AttendanceEvent::query()
            ->where('user_id', $userId)
            ->where('occurred_at', '>', now()->subMinutes(self::EVENT_COOLDOWN_MINUTES))
            ->latest('occurred_at')
            ->first();
    }

    private function cooldownResponse(AttendanceEvent $event)
    {
        $label = $event->event_type === AttendanceEvent::CLOCK_IN ? 'Clock In' : 'Clock Out';

        return response()->json([
            'code' => 'attendance_cooldown',
            'message' => "Anda sudah {$label} pada pukul {$event->occurred_at->format('H:i')}.",
        ], 429);
    }

    /**
     * @return array<int, string>
     */
    private function employeeShiftRelations(bool $withShift = false): array
    {
        $relations = ['location'];

        if ($withShift) {
            $relations[] = 'shift.days';
        }

        if ($this->hasLocationAssignmentsTable()) {
            $relations[] = 'locations';
        }

        return $relations;
    }

    /**
     * @return array{work_date: string, attendance_shift_id: int, shift_start_time: ?string, shift_end_time: ?string, is_overnight: bool, attendance_location_scope: string}
     */
    private function attendanceShiftSnapshot(EmployeeShift $employeeShift, Carbon $occurredAt): array
    {
        $workDate = $occurredAt->copy()->startOfDay();
        $shiftDay = $employeeShift->shift?->days
            ->first(fn ($day) => (int) $day->day_of_week === $workDate->dayOfWeekIso && ! $day->is_holiday);
        $previousWorkDate = $workDate->copy()->subDay();
        $previousShiftDay = $employeeShift->shift?->days
            ->first(fn ($day) => (int) $day->day_of_week === $previousWorkDate->dayOfWeekIso && ! $day->is_holiday);

        if ($previousShiftDay?->is_overnight && $previousShiftDay->end_time
            && $occurredAt->format('H:i:s') <= $previousShiftDay->end_time) {
            $workDate = $previousWorkDate;
            $shiftDay = $previousShiftDay;
        }

        return [
            'work_date' => $workDate->toDateString(),
            'attendance_shift_id' => $employeeShift->shift_id,
            'shift_start_time' => $shiftDay?->start_time,
            'shift_end_time' => $shiftDay?->end_time,
            'is_overnight' => (bool) $shiftDay?->is_overnight,
            'attendance_location_scope' => $this->attendanceLocationScope($employeeShift),
        ];
    }

    /**
     * Clock Out selalu mengikuti hari kerja Clock In sebelumnya, termasuk bila melewati jam akhir shift.
     *
     * @return array{work_date: string, attendance_shift_id: int, shift_start_time: ?string, shift_end_time: ?string, is_overnight: bool, attendance_location_scope: string}
     */
    private function clockOutShiftSnapshot(AttendanceEvent $clockIn, EmployeeShift $employeeShift): array
    {
        $fallback = $this->attendanceShiftSnapshot($employeeShift, $clockIn->occurred_at);

        return [
            'work_date' => $clockIn->work_date?->toDateString() ?? $fallback['work_date'],
            'attendance_shift_id' => $clockIn->attendance_shift_id ?? $fallback['attendance_shift_id'],
            'shift_start_time' => $clockIn->shift_start_time ?? $fallback['shift_start_time'],
            'shift_end_time' => $clockIn->shift_end_time ?? $fallback['shift_end_time'],
            'is_overnight' => $clockIn->is_overnight ?? $fallback['is_overnight'],
            'attendance_location_scope' => $clockIn->attendance_location_scope ?? $fallback['attendance_location_scope'],
        ];
    }

    private function attendanceLocationScope(EmployeeShift $employeeShift): string
    {
        if ($employeeShift->is_flexible) {
            return 'Fleksibel';
        }

        if ($employeeShift->is_all_locations) {
            return 'All Locations';
        }

        $selectedLocations = $this->scheduleLocations($employeeShift)
            ->pluck('name')
            ->filter()
            ->implode(', ');

        return $selectedLocations ?: '-';
    }

    /**
     * @return array{isFlexible: bool, flexibleReferenceLocations: array<int, array{latitude: float, longitude: float, radius_meters: int}>}
     */
    private function flexibleAttendanceContext(): array
    {
        $employeeShift = EmployeeShift::with($this->employeeShiftRelations())
            ->where('user_id', Auth::id())
            ->first();

        return [
            'isFlexible' => (bool) $employeeShift?->is_flexible,
            'flexibleReferenceLocations' => $employeeShift?->is_flexible
                ? $this->scheduleLocations($employeeShift)
                    ->map(fn (AttendanceLocation $location) => [
                        'latitude' => (float) $location->latitude,
                        'longitude' => (float) $location->longitude,
                        'radius_meters' => (int) $location->radius_meters,
                    ])
                    ->values()
                    ->all()
                : [],
        ];
    }

    /**
     * @return Collection<int, \App\Models\AttendanceLocation>
     */
    private function scheduleLocations(EmployeeShift $employeeShift): Collection
    {
        if ($employeeShift->is_all_locations) {
            return AttendanceLocation::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        if ($this->hasLocationAssignmentsTable()) {
            $locations = $employeeShift->relationLoaded('locations')
                ? $employeeShift->locations
                : $employeeShift->locations()->get();

            if ($locations->isNotEmpty()) {
                return $locations->where('is_active', true)->values();
            }
        }

        return $employeeShift->location?->is_active ? collect([$employeeShift->location]) : collect();
    }

    private function hasLocationAssignmentsTable(): bool
    {
        return Schema::hasTable('employee_shift_locations');
    }
}
