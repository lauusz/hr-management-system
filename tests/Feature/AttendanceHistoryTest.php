<?php

use App\Models\AttendanceEvent;
use App\Models\AttendanceLocation;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Tests\Support\InstallsAttendanceEventSchema;
use Tests\Support\InstallsFlexibleScheduleSchema;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->extend(Tests\TestCase::class)->in('Feature');

uses(InstallsAttendanceEventSchema::class);
uses(InstallsFlexibleScheduleSchema::class);

beforeEach(function () {
    $this->installAttendanceEventSchema();
    $this->installFlexibleScheduleSchema();
});

test('it redirects guest to login when accessing attendance history', function () {
    get(route('attendance.history'))
        ->assertRedirect(route('login'));
});

test('it renders attendance history page for authenticated user', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('attendance.history'))
        ->assertOk()
        ->assertSee('Riwayat Absensi')
        ->assertSee('Catatan riwayat kehadiran harian Anda');
});

test('it isolates attendance history so user only sees their own events', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $shift = Shift::factory()->create(['name' => 'Shift Kantor Utama']);
    $location = AttendanceLocation::factory()->create(['name' => 'Kantor Surabaya']);

    $myEvent = AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-10-20 08:15:00'),
        'work_date' => Carbon::parse('2026-10-20'),
        'attendance_shift_id' => $shift->id,
        'location_id' => $location->id,
        'approval_status' => 'APPROVED',
        'notes' => 'Catatan pribadi saya',
    ]);

    $otherEvent = AttendanceEvent::query()->create([
        'user_id' => $otherUser->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-10-20 08:30:00'),
        'work_date' => Carbon::parse('2026-10-20'),
        'attendance_shift_id' => $shift->id,
        'location_id' => $location->id,
        'approval_status' => 'APPROVED',
        'notes' => 'Catatan orang lain rahasia',
    ]);

    actingAs($user)
        ->get(route('attendance.history', ['date_start' => '2026-10-01', 'date_end' => '2026-10-31']))
        ->assertOk()
        ->assertSee('Catatan pribadi saya')
        ->assertDontSee('Catatan orang lain rahasia');
});

test('it filters attendance history by date range', function () {
    $user = User::factory()->create();
    $shift = Shift::factory()->create(['name' => 'Shift Pagi']);

    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-09-15 08:00:00'),
        'work_date' => Carbon::parse('2026-09-15'),
        'attendance_shift_id' => $shift->id,
        'approval_status' => 'APPROVED',
        'notes' => 'Kehadiran September',
    ]);

    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-10-15 08:00:00'),
        'work_date' => Carbon::parse('2026-10-15'),
        'attendance_shift_id' => $shift->id,
        'approval_status' => 'APPROVED',
        'notes' => 'Kehadiran Oktober',
    ]);

    actingAs($user)
        ->get(route('attendance.history', ['date_start' => '2026-10-01', 'date_end' => '2026-10-31']))
        ->assertOk()
        ->assertSee('Kehadiran Oktober')
        ->assertDontSee('Kehadiran September');
});

test('it pairs clock in and clock out into one session card', function () {
    $user = User::factory()->create();
    $shift = Shift::factory()->create(['name' => 'Shift Reguler']);

    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-10-25 08:00:00'),
        'work_date' => Carbon::parse('2026-10-25'),
        'attendance_shift_id' => $shift->id,
        'approval_status' => 'APPROVED',
    ]);

    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_OUT,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-10-25 17:05:00'),
        'work_date' => Carbon::parse('2026-10-25'),
        'attendance_shift_id' => $shift->id,
        'approval_status' => 'APPROVED',
    ]);

    actingAs($user)
        ->get(route('attendance.history', ['date_start' => '2026-10-01', 'date_end' => '2026-10-31']))
        ->assertOk()
        ->assertSee('08:00')
        ->assertSee('17:05');
});

test('it auto swaps date_start and date_end if start is after end', function () {
    $user = User::factory()->create();
    $shift = Shift::factory()->create(['name' => 'Shift Reguler']);

    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-10-15 08:00:00'),
        'work_date' => Carbon::parse('2026-10-15'),
        'attendance_shift_id' => $shift->id,
        'approval_status' => 'APPROVED',
        'notes' => 'Event Valid',
    ]);

    // Send inverted dates: date_start=2026-10-31 and date_end=2026-10-01
    actingAs($user)
        ->get(route('attendance.history', ['date_start' => '2026-10-31', 'date_end' => '2026-10-01']))
        ->assertOk()
        ->assertSee('Event Valid');
});

test('it displays notes, hrd notes, and photo viewer attributes', function () {
    $user = User::factory()->create();
    $shift = Shift::factory()->create(['name' => 'Shift Reguler']);

    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'DINAS_LUAR',
        'occurred_at' => Carbon::parse('2026-10-20 09:00:00'),
        'work_date' => Carbon::parse('2026-10-20'),
        'attendance_shift_id' => $shift->id,
        'photo' => 'remote_photos/test_in.jpg',
        'approval_status' => 'APPROVED',
        'notes' => 'Tugas luar kota ke klien X',
        'hrd_note' => 'Disetujui untuk SPPD',
    ]);

    actingAs($user)
        ->get(route('attendance.history', ['date_start' => '2026-10-01', 'date_end' => '2026-10-31']))
        ->assertOk()
        ->assertSee('Dinas Luar')
        ->assertSee('Tugas luar kota ke klien X')
        ->assertSee('Disetujui untuk SPPD')
        ->assertSee('data-image-viewer-src', false)
        ->assertSee('remote_photos/test_in.jpg');
});

test('it displays empty state when no attendance records exist in date range', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('attendance.history', ['date_start' => '2025-01-01', 'date_end' => '2025-01-31']))
        ->assertOk()
        ->assertSee('Belum Ada Riwayat Absensi')
        ->assertSee('Tidak ditemukan catatan absensi pada rentang tanggal yang dipilih.');
});

test('it groups non-overnight events by date so stand-alone clock out appears on its own date', function () {
    $user = User::factory()->create();
    $shift = Shift::factory()->create(['name' => 'Shift Non-Overnight']);

    // Oct 29: Clock In 07:40, Clock In 16:04
    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-10-29 07:40:00'),
        'work_date' => Carbon::parse('2026-10-29'),
        'attendance_shift_id' => $shift->id,
        'is_overnight' => false,
        'approval_status' => 'APPROVED',
    ]);
    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_IN,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-10-29 16:04:00'),
        'work_date' => Carbon::parse('2026-10-29'),
        'attendance_shift_id' => $shift->id,
        'is_overnight' => false,
        'approval_status' => 'APPROVED',
    ]);

    // Oct 30: Clock Out 22:07 (even if work_date was mistakenly 2026-10-29)
    AttendanceEvent::query()->create([
        'user_id' => $user->id,
        'event_type' => AttendanceEvent::CLOCK_OUT,
        'type' => 'WFO',
        'occurred_at' => Carbon::parse('2026-10-30 22:07:00'),
        'work_date' => Carbon::parse('2026-10-29'),
        'attendance_shift_id' => $shift->id,
        'is_overnight' => false,
        'approval_status' => 'APPROVED',
    ]);

    $response = actingAs($user)
        ->get(route('attendance.history', ['date_start' => '2026-10-29', 'date_end' => '2026-10-30']))
        ->assertOk();

    // Oct 30 card should show 22:07 for Clock Out and --:-- for Clock In
    $response->assertSee('29 Oktober 2026')
        ->assertSee('30 Oktober 2026')
        ->assertSee('07:40')
        ->assertSee('22:07');
});
