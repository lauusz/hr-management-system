<?php

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\ShiftDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

        $attendance = Attendance::query()->where('user_id', $employee->id)->firstOrFail();

        expect($attendance->date->toDateString())->toBe('2026-04-19')
            ->and($attendance->shift_id)->toBe($shift->id)
            ->and($attendance->employee_shift_id)->toBe($employeeShift->id)
            ->and($attendance->status)->toBe('HADIR')
            ->and($attendance->normal_start_time->format('Y-m-d H:i'))->toBe('2026-04-19 21:00')
            ->and($attendance->normal_end_time->format('Y-m-d H:i'))->toBe('2026-04-20 05:00');

        Carbon::setTestNow(Carbon::parse('2026-04-20 05:05:00'));

        $this->actingAs($employee, 'web')
            ->post(route('attendance.clockOut'), [
                'photo' => UploadedFile::fake()->image('clock-out.jpg', 800, 600),
                'lat' => -6.200000,
                'lng' => 106.816666,
            ])
            ->assertOk()
            ->assertJson(['message' => 'Presensi keluar berhasil.']);

        expect($attendance->fresh()->completion_status)->toBe(Attendance::COMPLETION_CLOSED);
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

it('allows HR to assign All Locations for WFO attendance outside an office radius', function () {
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
                'location_id' => 'all',
            ])
            ->assertRedirect(route('hr.schedules.index'));

        $this->assertDatabaseHas('employee_shifts', [
            'user_id' => $employee->id,
            'location_id' => null,
            'is_all_locations' => true,
        ]);

        $this->actingAs($employee, 'web')
            ->post(route('attendance.clockIn'), [
                'photo' => UploadedFile::fake()->image('all-locations.jpg', 800, 600),
                'lat' => -6.205000,
                'lng' => 106.816666,
            ])
            ->assertOk()
            ->assertJson(['message' => 'Presensi masuk berhasil.']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $employee->id,
            'type' => 'WFO',
            'location_id' => null,
        ]);
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
