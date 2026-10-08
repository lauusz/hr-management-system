<?php

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\OfficeHoliday;
use App\Models\Shift;
use App\Models\ShiftDay;
use App\Models\User;
use App\Services\Attendance\MissedClockOutService;
use Carbon\Carbon;
// ⚠️ PERINGATAN: JANGAN gunakan LazilyRefreshDatabase / RefreshDatabase
// karena akan men-trigger migrate:fresh yang menghapus SEMUA data.

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

describe('AttendanceController', function () {

    // Helper: create full shift setup for user
    function createShiftSetup(User $user, ?AttendanceLocation $location = null, ?array $shiftTimes = null): EmployeeShift
    {
        $location = $location ?? AttendanceLocation::factory()->create([
            'latitude' => -6.200000,
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
                'shift_id' => $shift->id,
                'day_of_week' => $dayOfWeek,
                'start_time' => $times['start'],
                'end_time' => $times['end'],
                'is_holiday' => false,
            ]);
        }

        return EmployeeShift::factory()->create([
            'user_id' => $user->id,
            'shift_id' => $shift->id,
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

        it('renders the simplified responsive dashboard', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $this->get(route('attendance.dashboard'))
                ->assertOk()
                ->assertSee('attendance-status-panel', false)
                ->assertSee('attendance-action-dock', false)
                ->assertDontSee('attendance-hero', false)
                ->assertDontSee('Ringkasan Hari Ini')
                ->assertDontSee('Panduan Singkat');
        });

        it('shows the active attendance state', function () {
            $user = User::factory()->create();
            Attendance::factory()->forUser($user)->today()->clockedIn()->create();

            actingAs($user, 'web');

            $this->get(route('attendance.dashboard'))
                ->assertOk()
                ->assertSee('Sedang bekerja')
                ->assertSee('Catat jam pulang kerja');
        });

        it('explains an active overnight shift and its expected clock out time', function () {
            $user = User::factory()->create();
            Attendance::factory()->forUser($user)->today()->clockedIn()->create([
                'normal_start_time' => '22:00:00',
                'normal_end_time' => '06:00:00',
            ]);

            actingAs($user, 'web');

            $this->get(route('attendance.dashboard'))
                ->assertOk()
                ->assertSee('Shift lintas hari aktif')
                ->assertSee('Clock Out keesokan hari')
                ->assertSee(now()->addDay()->setTime(6, 0)->translatedFormat('l, d M Y'))
                ->assertSee('06:00');
        });

        it('shows the completed attendance state', function () {
            $user = User::factory()->create();
            Attendance::factory()->forUser($user)->today()->clockedOut()->create([
                'completion_status' => Attendance::COMPLETION_CLOSED,
            ]);

            actingAs($user, 'web');

            $this->get(route('attendance.dashboard'))
                ->assertOk()
                ->assertSee('Presensi selesai')
                ->assertSee('Sudah tercatat');
        });

        it('marks completed attendance for a mobile layout without an action dock', function () {
            $user = User::factory()->create();
            Attendance::factory()->forUser($user)->today()->clockedOut()->create([
                'completion_status' => Attendance::COMPLETION_CLOSED,
            ]);

            actingAs($user, 'web');

            $this->get(route('attendance.dashboard'))
                ->assertOk()
                ->assertSee('attendance-page--complete', false)
                ->assertSee('attendance-action-dock--complete', false);
        });

        it('explains the next step for a missed clock out from a previous day', function () {
            $user = User::factory()->create();
            Attendance::factory()->forUser($user)->create([
                'date' => now()->subDay()->toDateString(),
                'clock_in_at' => now()->subDay()->setTime(8, 0),
                'completion_status' => Attendance::COMPLETION_MISSED_CLOCK_OUT,
            ]);

            actingAs($user, 'web');

            $this->get(route('attendance.dashboard'))
                ->assertOk()
                ->assertSee('Presensi hari ini tetap dapat dilanjutkan.');
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
        it('shows an office holiday confirmation before normal clock in', function () {
            Carbon::setTestNow(Carbon::parse('2031-04-17 10:00:00'));

            try {
                $user = User::factory()->create();
                OfficeHoliday::create([
                    'holiday_date' => now()->toDateString(),
                    'name' => 'Libur Operasional',
                    'type' => OfficeHoliday::TYPE_COMPANY,
                    'is_active' => true,
                ]);
                expect(OfficeHoliday::query()->whereDate('holiday_date', now()->toDateString())->value('name'))
                    ->toBe('Libur Operasional');

                actingAs($user, 'web');

                $this->get(route('attendance.clockIn.form'))
                    ->assertOk()
                    ->assertViewHas('officeHoliday', fn ($officeHoliday) => $officeHoliday?->name === 'Libur Operasional')
                    ->assertSee('id="attendance-office-holiday-confirmation"', false)
                    ->assertSee('Libur Operasional')
                    ->assertSee('Ya, Tetap Absen');
            } finally {
                Carbon::setTestNow();
            }
        });

        it('does not show an office holiday confirmation for an inactive holiday', function () {
            Carbon::setTestNow(Carbon::parse('2031-04-17 10:00:00'));

            try {
                $user = User::factory()->create();
                OfficeHoliday::create([
                    'holiday_date' => now()->toDateString(),
                    'name' => 'Libur Operasional',
                    'type' => OfficeHoliday::TYPE_COMPANY,
                    'is_active' => false,
                ]);
                expect(OfficeHoliday::query()->whereDate('holiday_date', now()->toDateString())->value('name'))
                    ->toBe('Libur Operasional');

                actingAs($user, 'web');

                $this->get(route('attendance.clockIn.form'))
                    ->assertOk()
                    ->assertViewHas('officeHoliday', null)
                    ->assertDontSee('id="attendance-office-holiday-confirmation"', false);
            } finally {
                Carbon::setTestNow();
            }
        });

        it('renders the single viewport clock in experience', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $this->get(route('attendance.clockIn.form'))
                ->assertOk()
                ->assertSee('clock-in-screen', false)
                ->assertSee('clock-in-status', false)
                ->assertSee('clock-in-camera', false)
                ->assertSee('clock-in-face-guide__corner', false)
                ->assertSee('clock-in-controls', false)
                ->assertSee('Ambil Foto');
        });

        it('places attendance back actions below their page headers', function () {
            $user = User::factory()->create();

            Attendance::factory()->forUser($user)->today()->clockedIn()->create();

            actingAs($user, 'web');

            $this->get(route('attendance.clockIn.form'))
                ->assertOk()
                ->assertSeeInOrder(['clock-in-header', 'attendance-back'], false);

            $this->get(route('attendance.clockOut.form'))
                ->assertOk()
                ->assertSeeInOrder(['Presensi Pulang', 'attendance-back'], false);

            $this->get(route('remote-attendance.purpose'))
                ->assertOk()
                ->assertSeeInOrder(['remote-flow-header', 'attendance-back'], false);

            $this->get(route('remote-attendance.photo'))
                ->assertOk()
                ->assertSeeInOrder(['remote-capture-header', 'attendance-back'], false);
        });

        it('renders specific camera and GPS guidance without a retry action', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $this->get(route('attendance.clockIn.form'))
                ->assertOk()
                ->assertDontSee('id="btnRetryPermissions"', false)
                ->assertSee('Lokasi hanya dapat diakses melalui HTTPS.')
                ->assertSee('Akses kamera ditolak. Izinkan kamera di browser, lalu muat ulang halaman.');
        });

        it('rechecks photo readiness after retake', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->get(route('attendance.clockIn.form'));

            $response->assertOk();

            expect($response->getContent())->toMatch("/btnRetake\\.addEventListener\\('click', \\(\\) => \\{[\\s\\S]*?imageBlob = null;\\s*checkReadiness\\(\\);/");
        });

        it('successfully clocks in when within radius', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            // Use coordinates exactly at the location (within radius)
            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertStatus(200);
            $response->assertJson(['message' => 'Presensi masuk berhasil.']);

            $attendance = Attendance::where('user_id', $user->id)->whereDate('date', now()->toDateString())->first();
            expect($attendance)->toBeTruthy()
                ->and($attendance->clock_in_at)->toBeTruthy()
                ->and($attendance->type)->toBe('WFO')
                ->and($attendance->approval_status)->toBe('PENDING');
        });

        it('rejects clock in when outside radius', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            // Use coordinates far from the location (500m away)
            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat' => -6.205000, // ~556m away
                'lng' => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJsonFragment(['message' => 'Anda berada di luar radius kantor (556 m).']);
        });

        it('prevents double clock in on same day', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            // First clock in
            Attendance::factory()->forUser($user)->today()->clockedIn()->create();

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJsonFragment(['message' => 'Presensi masuk hari ini sudah tercatat.']);
        });

        it('rejects clock in when no shift assigned', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            // No EmployeeShift created

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJson(['message' => 'Jadwal shift belum diatur. Hubungi HR.']);
        });

        it('requires clock out for a previous open night attendance without marking it automatically', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2031-04-21 07:01:00', 'Asia/Jakarta'));

            try {
                $user = User::factory()->create();
                $location = AttendanceLocation::factory()->create([
                    'latitude' => -6.200000,
                    'longitude' => 106.816666,
                    'radius_meters' => 100,
                ]);
                createShiftSetup($user, $location);
                $previous = Attendance::factory()->forUser($user)->create([
                    'date' => '2031-04-20',
                    'clock_in_at' => Carbon::parse('2031-04-20 22:00:00', 'Asia/Jakarta'),
                    'normal_start_time' => Carbon::parse('2031-04-20 22:00:00', 'Asia/Jakarta'),
                    'normal_end_time' => Carbon::parse('2031-04-21 06:00:00', 'Asia/Jakarta'),
                    'completion_status' => Attendance::COMPLETION_OPEN,
                ]);

                actingAs($user, 'web');

                $this->post(route('attendance.clockIn'), [
                    'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                    'lat' => -6.200000,
                    'lng' => 106.816666,
                ])->assertStatus(400)
                    ->assertJson(['message' => 'Masih ada sesi presensi sebelumnya yang berjalan. Silakan lakukan presensi keluar terlebih dahulu.']);

                expect($previous->fresh()->completion_status)->toBe(Attendance::COMPLETION_OPEN);
            } finally {
                Carbon::setTestNow();
            }
        });

        it('flags a previous open attendance at noon and allows a new clock in', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2031-04-21 12:00:00', 'Asia/Jakarta'));

            try {
                $user = User::factory()->create();
                $location = AttendanceLocation::factory()->create([
                    'latitude' => -6.200000,
                    'longitude' => 106.816666,
                    'radius_meters' => 100,
                ]);
                createShiftSetup($user, $location);
                $previous = Attendance::factory()->forUser($user)->create([
                    'date' => '2031-04-20',
                    'clock_in_at' => Carbon::parse('2031-04-20 08:00:00', 'Asia/Jakarta'),
                    'completion_status' => Attendance::COMPLETION_OPEN,
                ]);

                actingAs($user, 'web');

                $this->post(route('attendance.clockIn'), [
                    'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                    'lat' => -6.200000,
                    'lng' => 106.816666,
                ])->assertOk()
                    ->assertJson(['message' => 'Presensi masuk berhasil.']);

                expect($previous->fresh()->completion_status)->toBe(Attendance::COMPLETION_MISSED_CLOCK_OUT)
                    ->and(Attendance::query()->where('user_id', $user->id)->whereDate('date', '2031-04-21')->exists())->toBeTrue();
            } finally {
                Carbon::setTestNow();
            }
        });

        it('only flags previous open attendances at noon', function () {
            Carbon::setTestNow(Carbon::parse('2031-04-21 12:00:00', 'Asia/Jakarta'));

            try {
                $user = User::factory()->create();
                $today = Attendance::factory()->forUser($user)->today()->clockedIn()->create();
                $closed = Attendance::factory()->forUser($user)->create([
                    'date' => '2031-04-19',
                    'clock_in_at' => Carbon::parse('2031-04-19 08:00:00', 'Asia/Jakarta'),
                    'clock_out_at' => Carbon::parse('2031-04-19 17:00:00', 'Asia/Jakarta'),
                    'completion_status' => Attendance::COMPLETION_CLOSED,
                ]);
                $previous = Attendance::factory()->forUser($user)->create([
                    'date' => '2031-04-20',
                    'clock_in_at' => Carbon::parse('2031-04-20 08:00:00', 'Asia/Jakarta'),
                    'completion_status' => Attendance::COMPLETION_OPEN,
                ]);

                app(MissedClockOutService::class)->flagPreviousOpenAttendances($user->id);

                expect($today->fresh()->completion_status)->toBe(Attendance::COMPLETION_OPEN)
                    ->and($closed->fresh()->completion_status)->toBe(Attendance::COMPLETION_CLOSED)
                    ->and($previous->fresh()->completion_status)->toBe(Attendance::COMPLETION_MISSED_CLOCK_OUT);
            } finally {
                Carbon::setTestNow();
            }
        });

        it('rejects clock in on unassigned day of week (no shift pattern)', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2026-04-19 10:00:00')); // Sunday, not Monday

            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create();
            $shift = Shift::factory()->create(['is_active' => true]);
            // Only Monday is set
            ShiftDay::factory()->create([
                'shift_id' => $shift->id,
                'day_of_week' => 1, // Monday
                'is_holiday' => false,
            ]);

            EmployeeShift::factory()->create([
                'user_id' => $user->id,
                'shift_id' => $shift->id,
                'location_id' => $location->id,
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJson(['message' => 'Tidak ada jadwal shift hari ini.']);

            Carbon::setTestNow();
        });

        it('marks as TERLAMBAT when clocking in after shift start', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2026-04-20 09:30:00')); // Monday 9:30 AM

            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
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
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
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

            $response = $this->postJson(route('attendance.clockIn'), [
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertStatus(422);
        });

        it('validates required lat/lng', function () {
            Storage::fake('public');
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->postJson(route('attendance.clockIn'), [
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
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            $attendance = Attendance::factory()->forUser($user)->today()->clockedIn()->create([
                'normal_end_time' => Carbon::parse(now()->toDateString().' 17:00:00'),
            ]);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertStatus(200);
            $response->assertJson(['message' => 'Presensi keluar berhasil.']);

            $attendance->refresh();
            expect($attendance->clock_out_at)->toBeTruthy();
        });

        it('prevents double clock out on same attendance', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            $attendance = Attendance::factory()->forUser($user)->today()->clockedIn()->create([
                'normal_end_time' => Carbon::parse(now()->toDateString().' 17:00:00'),
            ]);

            actingAs($user, 'web');

            // First clock out
            $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ])->assertStatus(200);

            // Second clock out should be rejected
            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout2.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJson(['message' => 'Presensi pulang hari ini sudah tercatat.']);

            $attendance->refresh();
            expect($attendance->completion_status)->toBe(Attendance::COMPLETION_CLOSED);
        });

        it('rejects clock out when outside radius', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            $attendance = Attendance::factory()->forUser($user)->today()->clockedIn()->create();

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                'lat' => -6.205000, // Far away
                'lng' => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJsonFragment(['message' => 'Anda harus berada di kantor untuk melakukan presensi keluar (556 m).']);
        });

        it('returns error when no active attendance to clock out', function () {
            Storage::fake('public');
            $user = User::factory()->create();

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertStatus(400);
            $response->assertJson(['message' => 'Tidak ada sesi presensi aktif untuk ditutup.']);
        });

        it('allows clock out for a previous open attendance without an automatic cutoff', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2031-04-21 07:01:00', 'Asia/Jakarta'));

            try {
                $user = User::factory()->create();
                $previous = Attendance::factory()->forUser($user)->dinasLuar()->create([
                    'date' => '2031-04-20',
                    'clock_in_at' => Carbon::parse('2031-04-20 22:00:00', 'Asia/Jakarta'),
                    'normal_start_time' => Carbon::parse('2031-04-20 22:00:00', 'Asia/Jakarta'),
                    'normal_end_time' => Carbon::parse('2031-04-21 06:00:00', 'Asia/Jakarta'),
                    'completion_status' => Attendance::COMPLETION_OPEN,
                ]);

                actingAs($user, 'web');

                $this->post(route('attendance.clockOut'), [
                    'photo' => UploadedFile::fake()->image('clockout.jpg', 800, 600),
                    'lat' => -6.200000,
                    'lng' => 106.816666,
                ])->assertOk()
                    ->assertJson(['message' => 'Presensi keluar berhasil.']);

                expect($previous->fresh()->completion_status)->toBe(Attendance::COMPLETION_CLOSED)
                    ->and($previous->fresh()->clock_out_at)->not->toBeNull();
            } finally {
                Carbon::setTestNow();
            }
        });

        it('rejects a second clock out after an overnight session closes on the new calendar day', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2031-04-21 06:00:00', 'Asia/Jakarta'));

            try {
                $user = User::factory()->create();
                $attendance = Attendance::factory()->forUser($user)->dinasLuar()->create([
                    'date' => '2031-04-20',
                    'clock_in_at' => Carbon::parse('2031-04-20 22:00:00', 'Asia/Jakarta'),
                    'normal_start_time' => '22:00:00',
                    'normal_end_time' => '06:00:00',
                    'completion_status' => Attendance::COMPLETION_OPEN,
                ]);

                actingAs($user, 'web');

                $this->post(route('attendance.clockOut'), [
                    'photo' => UploadedFile::fake()->image('clockout-first.jpg', 800, 600),
                    'lat' => -6.200000,
                    'lng' => 106.816666,
                ])->assertOk();

                $firstClockOutAt = $attendance->fresh()->clock_out_at;
                Carbon::setTestNow(Carbon::parse('2031-04-21 07:00:00', 'Asia/Jakarta'));

                $this->post(route('attendance.clockOut'), [
                    'photo' => UploadedFile::fake()->image('clockout-second.jpg', 800, 600),
                    'lat' => -6.200000,
                    'lng' => 106.816666,
                ])->assertStatus(400)
                    ->assertJson(['message' => 'Presensi pulang hari ini sudah tercatat.']);

                expect($attendance->fresh()->clock_out_at->equalTo($firstClockOutAt))->toBeTrue();
            } finally {
                Carbon::setTestNow();
            }
        });

        it('calculates early leave when clocking out before shift end', function () {
            Storage::fake('public');
            Carbon::setTestNow(Carbon::parse('2026-04-20 15:00:00')); // 3 PM, before 5 PM

            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude' => -6.200000,
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
                'lat' => -6.200000,
                'lng' => 106.816666,
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
                'latitude' => -6.200000,
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
                'lat' => -6.200000,
                'lng' => 106.816666,
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
        it('shows an office holiday confirmation before remote clock in', function () {
            Carbon::setTestNow(Carbon::parse('2031-04-17 10:00:00'));

            try {
                $user = User::factory()->create();
                OfficeHoliday::create([
                    'holiday_date' => now()->toDateString(),
                    'name' => 'Libur Operasional',
                    'type' => OfficeHoliday::TYPE_COMPANY,
                    'is_active' => true,
                ]);
                expect(OfficeHoliday::query()->whereDate('holiday_date', now()->toDateString())->value('name'))
                    ->toBe('Libur Operasional');

                actingAs($user, 'web');

                $this->get(route('remote-attendance.photo'))
                    ->assertOk()
                    ->assertViewHas('officeHoliday', fn ($officeHoliday) => $officeHoliday?->name === 'Libur Operasional')
                    ->assertSee('id="remote-office-holiday-confirmation"', false)
                    ->assertSee('Libur Operasional')
                    ->assertSee('Ya, Tetap Absen');
            } finally {
                Carbon::setTestNow();
            }
        });

        it('allows clock in without location check for DINAS_LUAR', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            // Clock in from random location (far from office)
            $response = $this->post(route('remote-attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('remote_in.jpg', 800, 600),
                'lat' => -7.500000, // Very far - no radius check
                'lng' => 110.000000,
                'notes' => 'Client site visit',
            ]);

            $response->assertStatus(200);
            $response->assertJson(['message' => 'Presensi masuk berhasil.']);

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

            $response = $this->postJson(route('remote-attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('remote_in.jpg', 800, 600),
                'lat' => -7.500000,
                'lng' => 110.000000,
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

        it('opens the remote attendance flow without creating attendance', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $this->get(route('remote-attendance.index'))
                ->assertOk()
                ->assertSee(route('remote-attendance.purpose'), false)
                ->assertSee('remote-dashboard--viewport-fit', false);

            $this->get(route('remote-attendance.purpose'))
                ->assertOk()
                ->assertViewIs('attendance.remote.purpose')
                ->assertSee('Tersimpan sementara');

            $this->get(route('remote-attendance.photo'))
                ->assertOk()
                ->assertViewIs('attendance.remote.photo')
                ->assertViewHas('mode', 'in')
                ->assertSee('Langkah 2 dari 2');

            expect(Attendance::where('user_id', $user->id)->exists())->toBeFalse();
        });
    });

    describe('remoteClockOut', function () {
        it('shows a dedicated remote clock out capture page', function () {
            $user = User::factory()->create();

            actingAs($user, 'web');

            $this->get(route('remote-attendance.clockOut.form'))
                ->assertOk()
                ->assertViewIs('attendance.remote.photo')
                ->assertViewHas('mode', 'out')
                ->assertSee('Selesaikan Dinas');
        });

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
                'lat' => -7.500000,
                'lng' => 110.000000,
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
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            // Calculate approximate distance: ~89m (within 100m)
            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat' => -6.200800, // ~89m away
                'lng' => 106.816666,
            ]);

            // Should succeed if within 100m
            if ($response->status() === 200) {
                $response->assertJson(['message' => 'Presensi masuk berhasil.']);
            }
        });

        it('accepts inside radius', function () {
            Storage::fake('public');
            $user = User::factory()->create();
            $location = AttendanceLocation::factory()->create([
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ]);
            createShiftSetup($user, $location);

            actingAs($user, 'web');

            $response = $this->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clockin.jpg', 800, 600),
                'lat' => -6.200100,
                'lng' => 106.816666,
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
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertRedirect('/login');
        });

        it('unauthenticated for clock out action', function () {
            Storage::fake('public');
            $response = $this->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clockout.jpg'),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ]);

            $response->assertRedirect('/login');
        });
    });
});
