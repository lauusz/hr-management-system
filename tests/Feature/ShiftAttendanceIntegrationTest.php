<?php

use App\Enums\UserRole;
use App\Models\AttendanceEvent;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\ShiftDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\InstallsAttendanceEventSchema;
use Tests\Support\InstallsFlexibleScheduleSchema;

uses(InstallsAttendanceEventSchema::class);
uses(InstallsFlexibleScheduleSchema::class);

beforeEach(function () {
    $this->installAttendanceEventSchema();
    $this->installFlexibleScheduleSchema();
});

it('menghubungkan shift Minggu lintas hari yang dibuat HR ke absensi karyawan', function () {
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-04-19 20:55:00'));

    try {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
        ]);

        $this->actingAs($hrd, 'web')
            ->post(route('hr.shifts.store'), [
                'name' => 'Shift Minggu Malam',
                'is_active' => '1',
                'days' => [
                    7 => [
                        'start_time' => '21:00',
                        'end_time' => '05:00',
                        'is_overnight' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('hr.shifts.index'));

        $shift = Shift::query()->where('name', 'Shift Minggu Malam')->firstOrFail();

        $this->assertDatabaseHas('shift_days', [
            'shift_id' => $shift->id,
            'day_of_week' => 7,
            'is_holiday' => 0,
        ]);

        $sundayPattern = $shift->days()->where('day_of_week', 7)->firstOrFail();

        expect(substr($sundayPattern->start_time, 0, 5))->toBe('21:00')
            ->and(substr($sundayPattern->end_time, 0, 5))->toBe('05:00')
            ->and($sundayPattern->is_overnight)->toBeTrue();

        $this->actingAs($hrd, 'web')
            ->post(route('hr.schedules.store'), [
                'user_ids' => [$employee->id],
                'shift_id' => $shift->id,
                'location_id' => $location->id,
            ])
            ->assertRedirect(route('hr.schedules.index'));

        $employeeShift = EmployeeShift::query()->where('user_id', $employee->id)->firstOrFail();

        $this->actingAs($employee, 'web')
            ->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('clock-in.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ])
            ->assertOk()
            ->assertJson(['message' => 'Presensi masuk berhasil.']);

        $clockIn = AttendanceEvent::query()->where('user_id', $employee->id)->sole();

        expect($clockIn->event_type)->toBe(AttendanceEvent::CLOCK_IN)
            ->and($clockIn->occurred_at->format('Y-m-d H:i'))->toBe('2026-04-19 20:55')
            ->and($clockIn->location_id)->toBe($location->id);

        Carbon::setTestNow(Carbon::parse('2026-04-20 05:05:00'));

        $this->actingAs($employee, 'web')
            ->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clock-out.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ])
            ->assertOk()
            ->assertJson(['message' => 'Presensi keluar berhasil.']);

        expect(AttendanceEvent::query()->where('user_id', $employee->id)->count())->toBe(2)
            ->and(AttendanceEvent::query()->where('user_id', $employee->id)->latest('occurred_at')->value('event_type'))
            ->toBe(AttendanceEvent::CLOCK_OUT);
    } finally {
        Carbon::setTestNow();
    }
});

it('menolak penanda lintas hari yang tidak sesuai dengan jam shift', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $this->actingAs($hrd, 'web')
        ->from(route('hr.shifts.create'))
        ->post(route('hr.shifts.store'), [
            'name' => 'Shift Tidak Konsisten 1',
            'is_active' => '1',
            'days' => [
                1 => [
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'is_overnight' => '1',
                ],
            ],
        ])
        ->assertRedirect(route('hr.shifts.create'))
        ->assertSessionHasErrors('days.1.is_overnight');

    $this->actingAs($hrd, 'web')
        ->from(route('hr.shifts.create'))
        ->post(route('hr.shifts.store'), [
            'name' => 'Shift Tidak Konsisten 2',
            'is_active' => '1',
            'days' => [
                1 => [
                    'start_time' => '22:00',
                    'end_time' => '06:00',
                ],
            ],
        ])
        ->assertRedirect(route('hr.shifts.create'))
        ->assertSessionHasErrors('days.1.is_overnight');
});

it('allows HR to assign Fleksibel for WFO attendance outside an office radius', function () {
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-04-20 08:30:00'));

    try {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
        ]);
        $shift = Shift::factory()->create(['is_active' => true]);
        ShiftDay::factory()->create([
            'shift_id' => $shift->id,
            'day_of_week' => 1,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_holiday' => false,
        ]);

        $this->actingAs($hrd, 'web')
            ->post(route('hr.schedules.store'), [
                'user_ids' => [$employee->id],
                'shift_id' => $shift->id,
                'location_mode' => 'flexible',
                'location_ids' => [$location->id],
            ])
            ->assertRedirect(route('hr.schedules.index'));

        $this->assertDatabaseHas('employee_shifts', [
            'user_id' => $employee->id,
            'location_id' => $location->id,
            'is_all_locations' => false,
            'is_flexible' => true,
        ]);

        $this->actingAs($employee, 'web')
            ->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('all-locations.jpg', 800, 600),
                'lat' => -6.205000,
                'lng' => 106.816666,
                'notes' => 'Bekerja di luar kantor',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Presensi masuk berhasil.']);

        $this->assertDatabaseHas('attendance_events', [
            'user_id' => $employee->id,
            'type' => 'WFO',
            'location_id' => null,
            'is_outside_reference_location' => true,
        ]);
    } finally {
        Carbon::setTestNow();
    }
});

it('mencatat lokasi aktif baru yang cocok untuk All Locations pada Clock In dan Clock Out', function () {
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-04-20 08:30:00'));

    try {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create();
        $location = AttendanceLocation::factory()->create([
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
        ]);
        $shift = Shift::factory()->create(['is_active' => true]);
        ShiftDay::factory()->create([
            'shift_id' => $shift->id,
            'day_of_week' => 1,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_holiday' => false,
        ]);

        $this->actingAs($hrd, 'web')
            ->post(route('hr.schedules.store'), [
                'user_ids' => [$employee->id],
                'shift_id' => $shift->id,
                'location_mode' => 'all',
            ])
            ->assertRedirect(route('hr.schedules.index'));

        $newLocation = AttendanceLocation::factory()->create([
            'latitude' => -6.210000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
        ]);

        $this->actingAs($employee, 'web')
            ->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('all-locations-in.jpg', 800, 600),
                'lat' => -6.210000,
                'lng' => 106.816666,
            ])
            ->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-04-20 08:45:00'));

        $this->actingAs($employee, 'web')
            ->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('all-locations-out.jpg', 800, 600),
                'lat' => -6.210000,
                'lng' => 106.816666,
            ])
            ->assertOk();

        expect(AttendanceEvent::query()->where('user_id', $employee->id)->pluck('location_id')->all())
            ->toBe([$newLocation->id, $newLocation->id]);
    } finally {
        Carbon::setTestNow();
    }
});

it('menolak All Locations di luar seluruh radius lokasi aktif', function () {
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-04-20 08:30:00'));

    try {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create();
        AttendanceLocation::factory()->create([
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
        ]);
        $shift = Shift::factory()->create(['is_active' => true]);
        ShiftDay::factory()->create([
            'shift_id' => $shift->id,
            'day_of_week' => 1,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_holiday' => false,
        ]);

        $this->actingAs($hrd, 'web')
            ->post(route('hr.schedules.store'), [
                'user_ids' => [$employee->id],
                'shift_id' => $shift->id,
                'location_mode' => 'all',
            ])
            ->assertRedirect(route('hr.schedules.index'));

        $this->actingAs($employee, 'web')
            ->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('all-locations-outside.jpg', 800, 600),
                'lat' => -6.205000,
                'lng' => 106.816666,
            ])
            ->assertStatus(400);

        expect(AttendanceEvent::query()->where('user_id', $employee->id)->count())->toBe(0);
    } finally {
        Carbon::setTestNow();
    }
});

it('menimpa jadwal beberapa karyawan yang dipilih sekaligus', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $firstEmployee = User::factory()->create();
    $secondEmployee = User::factory()->create();
    $oldShift = Shift::factory()->create(['is_active' => true]);
    $newShift = Shift::factory()->create(['is_active' => true]);
    $oldLocation = AttendanceLocation::factory()->create();
    $newLocation = AttendanceLocation::factory()->create();

    EmployeeShift::create([
        'user_id' => $firstEmployee->id,
        'shift_id' => $oldShift->id,
        'location_id' => $oldLocation->id,
    ]);

    $this->actingAs($hrd, 'web')
        ->post(route('hr.schedules.store'), [
            'user_ids' => [$firstEmployee->id, $secondEmployee->id],
            'shift_id' => $newShift->id,
            'location_id' => $newLocation->id,
        ])
        ->assertRedirect(route('hr.schedules.index'));

    foreach ([$firstEmployee, $secondEmployee] as $employee) {
        $this->assertDatabaseHas('employee_shifts', [
            'user_id' => $employee->id,
            'shift_id' => $newShift->id,
            'location_id' => $newLocation->id,
            'is_all_locations' => false,
        ]);

        expect(EmployeeShift::query()->where('user_id', $employee->id)->count())->toBe(1);
    }
});

it('menampilkan semua user dan kontrol penetapan jadwal massal', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    foreach (range(1, 20) as $number) {
        User::factory()->create(['name' => sprintf('A User %02d', $number)]);
    }

    $lastUser = User::factory()->create(['name' => 'Z User Terakhir']);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.schedules.index'))
        ->assertOk()
        ->assertSee('Z User Terakhir')
        ->assertSee('Atur Jadwal Terpilih')
        ->assertSee('select-all-users', false);
});

it('menampilkan halaman master shift dengan ringkasan statistik, pencarian, dan filter status', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $activeShift = Shift::factory()->create([
        'name' => 'Shift Pagi Reguler',
        'description' => 'Jam kerja pagi kantor pusat',
        'is_active' => true,
    ]);
    ShiftDay::factory()->create([
        'shift_id' => $activeShift->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
        'is_holiday' => false,
    ]);

    Shift::factory()->create([
        'name' => 'Shift Malam Nonaktif',
        'description' => 'Shift cadangan lama',
        'is_active' => false,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.shifts.index'))
        ->assertOk()
        ->assertSee('Master Shift Kerja')
        ->assertSee('Total Shift')
        ->assertSee('Shift Pagi Reguler')
        ->assertSee('Shift Malam Nonaktif')
        ->assertSee('data-shift-live-search', false);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.shifts.index', ['status' => 'active']))
        ->assertOk()
        ->assertSee('Shift Pagi Reguler')
        ->assertDontSee('Shift Malam Nonaktif');

    $this->actingAs($hrd, 'web')
        ->get(route('hr.shifts.index', ['q' => 'Cadangan']))
        ->assertOk()
        ->assertSee('Shift Malam Nonaktif')
        ->assertDontSee('Shift Pagi Reguler');
});

it('menampilkan halaman edit shift dengan pola kartu detail dan aksi simpan tetap terlihat', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $shift = Shift::factory()->create([
        'name' => 'Shift Operasional',
        'description' => 'Jadwal operasional harian',
        'is_active' => true,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.shifts.edit', $shift))
        ->assertOk()
        ->assertSee('Edit Shift Kerja')
        ->assertSee('Shift Operasional')
        ->assertSee('schedule-edit-action-bar', false)
        ->assertSee('Simpan Perubahan');
});

it('menampilkan halaman tambah shift dengan pola kartu langkah dan bilah aksi bawah yang konsisten', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.shifts.create'))
        ->assertOk()
        ->assertSee('Tambah Shift Kerja')
        ->assertSee('Identitas Shift Kerja')
        ->assertSee('Pengaturan Jadwal Harian')
        ->assertSee('mlc-banner', false)
        ->assertSee('mlc-step', false)
        ->assertSee('apv-action-dock', false)
        ->assertSee('apv-action-btn--secondary', false)
        ->assertSee('apv-action-btn--primary', false)
        ->assertSee('Simpan Shift');
});
