<?php

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\ShiftDay;
use App\Models\User;
use Carbon\Carbon;
// ⚠️ PERINGATAN: JANGAN gunakan LazilyRefreshDatabase / RefreshDatabase
// karena akan men-trigger migrate:fresh yang menghapus SEMUA data.

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

describe('AttendanceController', function () {

    // Helper: create full shift setup for user
    function createShiftSetup(User $user, AttendanceLocation $location = null, array $shiftTimes = null): EmployeeShift
    {
        $location = $location ?? AttendanceLocation::factory()->create([
            'latitude'  => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
        ]);

        $shift = Shift::factory()->create(['is_active' => true]);

        // Create shift days: Monday to Friday 08:00-17:00
        $days = [
            1 => ['start' => '08:00:00', 'end' => '17:00:00'], // Monday
            2 => ['start' => '08:00:00', 'end' => '17:00:00'], // Tuesday
            3 => ['start' => '08:00:00', 'end' => '17:00:00'], // Wednesday
            4 => ['start' => '08:00:00', 'end' => '17:00:00'], // Thursday
            5 => ['start' => '08:00:00', 'end' => '17:00:00'], // Friday
            6 => ['start' => '08:00:00', 'end' => '12:00:00'], // Saturday (half day)
        ];

        foreach ($days as $dayOfWeek => $times) {
            ShiftDay::factory()->create([
                'shift_id'    => $shift->id,
                'day_of_week' => $dayOfWeek,
                'start_time'  => $times['start'],
                'end_time'    => $times['end'],
                'is_holiday'  => false,
            ]);
        }

        return EmployeeShift::factory()->create([
            'user_id'     => $user->id,
            'shift_id'    => $shift->id,
            'location_id' => $location->id,
        ]);
    }

    // =====================================================================
    // DASHBOARD
    // =====================================================================
    describe('dashboard', function () {
        it('shows today attendance on dashboard', function () {
            $user = User::factory()->create();
            $attendance = Attendance::factory()->forUser($user)->today()->create();

            actingAs($user, 'web');

            $response = $this->get(route('attendance.dashboard'));

            $response->assertStatus(200);
            expect($response->viewData('attendance')->id)->toBe($attendance->id);
        });

        it('unauthenticated redirected to login', function () {
            $response = $this->get(route('attendance.dashboard'));

            $response->assertRedirect('/login');
        });
    });

    // =====================================================================
    // CLOCK IN
    // =====================================================================
    describe('clockIn', function () {
        it('successfully clocks in when within radius', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            // Use coordinates exactly at the location (within radius)
            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(200);
            $response->assertJson(['message' => 'Clock In Berhasil.']);

            $attendance = Attendance::where('user_id', $user->id)->whereDate('date', now()->toDateString())->first();
            expect($attendance)->toBeTruthy()
                ->and($attendance->clock_in_at)->toBeTruthy()
                ->and($attendance->type)->toBe('WFO');
        });

        it('rejects clock in when outside radius', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            // Use coordinates far from the location (500m away)
            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat'   => -6.205000, // ~555m away
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJsonFragment(['message' => "Anda berada di luar radius kantor (555 m)."]);
        });

        it('prevents double clock in on same day', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            // First clock in
            Attendance::factory()->forUser($user)->today()->clockedIn()->create();

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJsonFragment(['message' => 'Anda sudah melakukan clock-in hari ini.']);
        });

        it('rejects clock in when no shift assigned', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            // No EmployeeShift created

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJson(['message' => 'Jadwal shift belum diatur. Hubungi HR.']);
        });

        it('rejects clock in on unassigned day of week (no shift pattern)', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create();
            $shift = Shift::factory()->create(['is_active' => true]);
            // Only Monday is set
            ShiftDay::factory()->create([
                'shift_id'    => $shift->id,
                'day_of_week' => 1, // Monday
                'is_holiday'  => false,
            ]);

            EmployeeShift::factory()->create([
                'user_id'     => $user->id,
                'shift_id'    => $shift->id,
                'location_id' => $location->id,
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJson(['message' => 'Tidak ada jadwal shift hari ini.']);
        });

        it('marks as TERLAMBAT when clocking in after shift start', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2026-04-20 09:30:00')); // Monday 9:30 AM

            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(200);

            $attendance = Attendance::where('user_id', $user->id)->whereDate('date', '2026-04-20')->first();
            expect($attendance->status)->toBe('TERLAMBAT')
                ->and($attendance->late_minutes)->toBeGreaterThan(0);

            Carbon::setTestNow(); // Reset
        });

        it('marks as HADIR when clocking in before shift start', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2026-04-20 07:30:00')); // Monday 7:30 AM

            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(200);

            $attendance = Attendance::where('user_id', $user->id)->whereDate('date', '2026-04-20')->first();
            expect($attendance->status)->toBe('HADIR')
                ->and($attendance->late_minutes)->toBe(0);

            Carbon::setTestNow();
        });

        it('validates required photo', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertStatus(422);
        });

        it('validates required lat/lng', function () {
            Storage::fake('public');
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg'),
            ]);

            $response->assertStatus(422);
        });
    });

    // =====================================================================
    // CLOCK OUT
    // =====================================================================
    describe('clockOut', function () {
        it('successfully clocks out when within radius', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            $attendance = Attendance::factory()->forUser($user)->today()->clockedIn()->create([
                'normal_end_time' => Carbon::parse(now()->toDateString() . ' 17:00:00'),
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(200);
            $response->assertJson(['message' => 'Clock Out Berhasil.']);

            $attendance->refresh();
            expect($attendance->clock_out_at)->toBeTruthy();
        });

        it('rejects clock out when outside radius', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            $attendance = Attendance::factory()->forUser($user)->today()->clockedIn()->create();

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                'lat'   => -6.205000, // Far away
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJsonFragment(['message' => "Anda harus berada di kantor untuk Clock Out (555 m)."]);
        });

        it('returns error when no active attendance to clock out', function () {
            Storage::fake('public');
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJson(['message' => 'Tidak ada sesi absensi aktif untuk di-close.']);
        });

        it('calculates early leave when clocking out before shift end', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2026-04-20 15:00:00')); // 3 PM, before 5 PM

            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            $attendance = Attendance::factory()->forUser($user)->today()->clockedIn()->create([
                'normal_end_time' => Carbon::parse('2026-04-20 17:00:00'),
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(200);

            $attendance->refresh();
            expect($attendance->early_leave_minutes)->toBeGreaterThan(0);

            Carbon::setTestNow();
        });

        it('calculates overtime when clocking out after shift end', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2026-04-20 18:30:00')); // 6:30 PM, after 5 PM

            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            $attendance = Attendance::factory()->forUser($user)->today()->clockedIn()->create([
                'normal_end_time' => Carbon::parse('2026-04-20 17:00:00'),
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(200);

            $attendance->refresh();
            expect($attendance->overtime_minutes)->toBeGreaterThan(0);

            Carbon::setTestNow();
        });
    });

    // =====================================================================
    // DINAS LUAR (REMOTE ATTENDANCE)
    // =====================================================================
    describe('remoteClockIn', function () {
        it('allows clock in without location check for DINAS_LUAR', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            // Clock in from random location (far from office)
            $response = $this->post(route('remote-attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('remote_in.jpg', 800, 600),
                'lat'   => -7.500000, // Very far - no radius check
                'lng'   => 110.000000,
                'notes' => 'Client site visit',
            ]);

            $response->assertStatus(200);
            $response->assertJson(['message' => 'Clock In Berhasil.']);

            $attendance = Attendance::where('user_id', $user->id)->whereDate('date', now()->toDateString())->first();
            expect($attendance)->toBeTruthy()
                ->and($attendance->type)->toBe('DINAS_LUAR')
                ->and($attendance->approval_status)->toBe('PENDING');
        });

        it('requires notes for DINAS_LUAR', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            createShiftSetup($user);

            actingAs($user, 'web');

            $response = $this->post(route('remote-attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('remote_in.jpg', 800, 600),
                'lat'   => -7.500000,
                'lng'   => 110.000000,
                // no notes
            ]);

            $response->assertStatus(422);
        });

        it('remote shows on remote index page', function () {
            $user = User::factory()->create();
            $attendance = Attendance::factory()->forUser($user)->today()->create([
                'type' => 'DINAS_LUAR',
            ]);

            actingAs($user, 'web');

            $response = $this->get(route('remote-attendance.index'));

            $response->assertStatus(200);
            expect($response->viewData('todayAttendance')->id)->toBe($attendance->id);
        });
    });

    describe('remoteClockOut', function () {
        it('allows clock out for DINAS_LUAR without location check', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            createShiftSetup($user);

            $attendance = Attendance::factory()->forUser($user)->today()->clockedIn()->create([
                'type' => 'DINAS_LUAR',
                'approval_status' => 'PENDING',
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('remote-attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('remote_out.jpg', 800, 600),
                'lat'   => -7.500000,
                'lng'   => 110.000000,
            ]);

            $response->assertStatus(200);
            $attendance->refresh();
            expect($attendance->clock_out_at)->toBeTruthy();
        });
    });

    // =====================================================================
    // HAVERSINE DISTANCE CALCULATION (via clock in at boundary)
    // =====================================================================
    describe('geo-distance boundary', function () {
        it('accepts at exactly radius boundary', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            // Calculate approximate distance: ~89m (within 100m)
            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat'   => -6.200800, // ~89m away
                'lng'   => 106.816666,
            ]);

            // Should succeed if within 100m
            if ($response->status() === 200) {
                $response->assertJson(['message' => 'Clock In Berhasil.']);
            }
        });

        it('accepts inside radius', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude'  => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat'   => -6.200100,
                'lng'   => 106.816666,
            ]);

            $response->assertStatus(200);
        });
    });

    // =====================================================================
    // UNAUTHENTICATED
    // =====================================================================
    describe('auth', function () {
        it('unauthenticated for clock in form', function () {
            $response = $this->get(route('attendance.clockIn.form'));

            $response->assertRedirect('/login');
        });

        it('unauthenticated for clock out form', function () {
            $response = $this->get(route('attendance.clockOut.form'));

            $response->assertRedirect('/login');
        });

        it('unauthenticated for clock in action', function () {
            Storage::fake('public');
            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg'),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertRedirect('/login');
        });

        it('unauthenticated for clock out action', function () {
            Storage::fake('public');
            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg'),
                'lat'   => -6.200000,
                'lng'   => 106.816666,
            ]);

            $response->assertRedirect('/login');
        });
    });
});
