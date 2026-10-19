<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\OfficeHoliday;
use App\Models\User;
use App\Services\Attendance\AllowedAttendanceLocationResolver;
use App\Services\Image\ImageCompressor;
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
        ]);
    }

    public function showClockOutForm()
    {
        return view('attendance.clock_out', ['attendance' => null]);
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
            'notes' => $isRemote && $eventType === AttendanceEvent::CLOCK_IN ? ['required', 'string'] : ['nullable', 'string'],
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

            $matchedLocation = null;
            $distance = 0;
            if (! $isRemote && ! $employeeShift->is_flexible) {
                $locations = $this->scheduleLocations($employeeShift);
                if ($locations->isEmpty()) {
                    return response()->json(['message' => 'Lokasi kantor belum diatur.'], 400);
                }

                $match = $this->allowedAttendanceLocationResolver->resolve($locations, (float) $request->lat, (float) $request->lng);
                if ($match === null) {
                    $nearest = $this->allowedAttendanceLocationResolver->nearest($locations, (float) $request->lat, (float) $request->lng);
                    $action = $eventType === AttendanceEvent::CLOCK_IN ? 'berada di luar radius kantor' : 'harus berada di kantor untuk melakukan presensi keluar';

                    return response()->json(['message' => "Anda {$action} ({$nearest['distance']} m)."], 400);
                }

                $matchedLocation = $match['location'];
                $distance = $match['distance'];
            }

            $folder = $isRemote ? 'remote_photos' : 'attendance_photos';
            $prefix = $isRemote
                ? ($eventType === AttendanceEvent::CLOCK_IN ? 'remote_in_' : 'remote_out_')
                : ($eventType === AttendanceEvent::CLOCK_IN ? 'att_in_' : 'att_out_');
            $photoPath = $this->imageCompressor->compressAndStore($request->file('photo'), 'photo', $folder, $prefix);

            $recordedEvent = DB::transaction(function () use ($user, $eventType, $isRemote, $employeeShift, $matchedLocation, $photoPath, $request, $distance) {
                User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

                if ($cooldownEvent = $this->activeCooldownEvent($user->id)) {
                    return ['event' => $cooldownEvent, 'is_cooldown' => true];
                }

                return [
                    'event' => AttendanceEvent::query()->create([
                        'user_id' => $user->id,
                        'event_type' => $eventType,
                        'type' => $isRemote ? 'DINAS_LUAR' : 'WFO',
                        'occurred_at' => now(),
                        'location_id' => $isRemote || $employeeShift->is_flexible ? null : $matchedLocation?->id,
                        'photo' => $photoPath,
                        'latitude' => $request->lat,
                        'longitude' => $request->lng,
                        'distance_m' => $distance,
                        'approval_status' => 'PENDING',
                        'notes' => $request->notes,
                    ]),
                    'is_cooldown' => false,
                ];
            });

            if ($recordedEvent['is_cooldown']) {
                Storage::disk('public')->delete($photoPath);

                return $this->cooldownResponse($recordedEvent['event']);
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
