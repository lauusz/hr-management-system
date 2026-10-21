<?php

use App\Enums\UserRole;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\User;
use Tests\Support\InstallsFlexibleScheduleSchema;

uses(InstallsFlexibleScheduleSchema::class);

beforeEach(function () {
    $this->installFlexibleScheduleSchema();
});

it('menggunakan nama kolom pivot multi-location yang sesuai dengan schema', function () {
    $relation = (new EmployeeShift)->locations();

    expect($relation->getForeignPivotKeyName())->toBe('employee_shift_id')
        ->and($relation->getRelatedPivotKeyName())->toBe('location_id');
});

it('menampilkan pilihan beberapa lokasi presensi pada modal jadwal', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    AttendanceLocation::factory()->create(['name' => 'Kantor Pusat']);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.schedules.index'))
        ->assertOk()
        ->assertSee('location_ids[]', false)
        ->assertSee('location_mode', false)
        ->assertSee('bulk-select-all-locations', false)
        ->assertSee('Fleksibel')
        ->assertSee('Kantor Pusat');
});

it('menampilkan halaman edit jadwal dengan pola detail dan aksi simpan tetap terlihat', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create(['name' => 'Karyawan Jadwal']);
    $shift = Shift::factory()->create(['is_active' => true]);
    $schedule = EmployeeShift::create([
        'user_id' => $employee->id,
        'shift_id' => $shift->id,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.schedules.edit', $schedule))
        ->assertOk()
        ->assertSee('Edit Jadwal Karyawan')
        ->assertSee('schedule-edit-action-bar', false)
        ->assertSee('schedule-edit-select-all-locations', false)
        ->assertSee('Simpan Perubahan');
});

it('menampilkan pencarian dan filter jadwal dengan pola halaman karyawan', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.schedules.index', ['q' => 'Rina', 'shift_id' => 'none']))
        ->assertOk()
        ->assertSee('schedule-filter-card', false)
        ->assertSee('schedule-filter-toggle', false)
        ->assertSee('schedule-filter-panel', false)
        ->assertSee('value="Rina"', false)
        ->assertSee('Belum Ada Jadwal');
});

it('menyediakan penanda untuk pencarian jadwal langsung', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.schedules.index'))
        ->assertOk()
        ->assertSee('data-schedule-live-search', false)
        ->assertSee('data-schedule-results', false)
        ->assertSee('loadScheduleResults', false);
});

it('memfilter jadwal berdasarkan mode lokasi', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $shift = Shift::factory()->create(['is_active' => true]);
    $selected = User::factory()->create(['name' => 'FILTER LOKASI TERPILIH']);
    $all = User::factory()->create(['name' => 'FILTER ALL LOCATIONS']);
    $flexible = User::factory()->create(['name' => 'FILTER FLEKSIBEL']);

    EmployeeShift::create(['user_id' => $selected->id, 'shift_id' => $shift->id]);
    EmployeeShift::create(['user_id' => $all->id, 'shift_id' => $shift->id, 'is_all_locations' => true]);
    EmployeeShift::create(['user_id' => $flexible->id, 'shift_id' => $shift->id, 'is_flexible' => true]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.schedules.index', ['location_mode' => 'selected']))
        ->assertOk()
        ->assertSee('FILTER LOKASI TERPILIH')
        ->assertDontSee('FILTER ALL LOCATIONS')
        ->assertDontSee('FILTER FLEKSIBEL');
});

it('mempertahankan pilihan jadwal saat hasil pencarian diganti', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.schedules.index'))
        ->assertOk()
        ->assertSee('const selectedUserIds = new Set(', false)
        ->assertSee('selectedUserIds.add(checkbox.value);', false)
        ->assertSee('selectedUserIds.delete(checkbox.value);', false)
        ->assertSee('checkbox.checked = selectedUserIds.has(checkbox.value);', false);
});

it('menampilkan tanda strip tanpa chip untuk karyawan yang belum memiliki shift', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    User::factory()->create(['name' => 'KARYAWAN TANPA SHIFT']);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.schedules.index'))
        ->assertOk()
        ->assertSee('<span class="text-muted" style="font-size:12px; font-style:italic;">-</span>', false)
        ->assertDontSee('Belum diatur');
});

it('menolak mode All Locations yang dikirim bersama lokasi tertentu', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();
    $shift = Shift::factory()->create(['is_active' => true]);
    $location = AttendanceLocation::factory()->create();

    $this->actingAs($hrd, 'web')
        ->from(route('hr.schedules.index'))
        ->post(route('hr.schedules.store'), [
            'user_ids' => [$employee->id],
            'shift_id' => $shift->id,
            'location_mode' => 'all',
            'location_ids' => [$location->id],
        ])
        ->assertRedirect(route('hr.schedules.index'))
        ->assertSessionHasErrors('location_ids');
});

it('mewajibkan lokasi acuan aktif untuk mode Fleksibel', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();
    $shift = Shift::factory()->create(['is_active' => true]);

    $this->actingAs($hrd, 'web')
        ->from(route('hr.schedules.index'))
        ->post(route('hr.schedules.store'), [
            'user_ids' => [$employee->id],
            'shift_id' => $shift->id,
            'location_mode' => 'flexible',
        ])
        ->assertRedirect(route('hr.schedules.index'))
        ->assertSessionHasErrors('location_ids');
});

it('menyimpan lokasi acuan Fleksibel pada relasi jadwal', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();
    $shift = Shift::factory()->create(['is_active' => true]);
    $first = AttendanceLocation::factory()->create(['is_active' => true]);
    $second = AttendanceLocation::factory()->create(['is_active' => true]);

    $this->actingAs($hrd, 'web')
        ->post(route('hr.schedules.store'), [
            'user_ids' => [$employee->id],
            'shift_id' => $shift->id,
            'location_mode' => 'flexible',
            'location_ids' => [$first->id, $second->id],
        ])
        ->assertRedirect(route('hr.schedules.index'));

    $schedule = EmployeeShift::query()->where('user_id', $employee->id)->sole();
    expect($schedule->is_flexible)->toBeTrue()
        ->and($schedule->location_id)->toBe($first->id)
        ->and($schedule->locations()->pluck('attendance_locations.id')->all())->toBe([$first->id, $second->id]);
});

it('mempertahankan shift dan melewati karyawan tanpa shift saat bulk hanya mengubah lokasi', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employeeWithShift = User::factory()->create();
    $employeeWithoutShift = User::factory()->create();
    $shift = Shift::factory()->create(['is_active' => true]);

    EmployeeShift::create([
        'user_id' => $employeeWithShift->id,
        'shift_id' => $shift->id,
    ]);

    $this->actingAs($hrd, 'web')
        ->post(route('hr.schedules.store'), [
            'user_ids' => [$employeeWithShift->id, $employeeWithoutShift->id],
            'location_mode' => 'all',
        ])
        ->assertRedirect(route('hr.schedules.index'))
        ->assertSessionHas('success', 'Jadwal untuk 1 karyawan berhasil disimpan. 1 karyawan tanpa shift tidak diubah.');

    $this->assertDatabaseHas('employee_shifts', [
        'user_id' => $employeeWithShift->id,
        'shift_id' => $shift->id,
        'is_all_locations' => true,
    ]);
    $this->assertDatabaseMissing('employee_shifts', ['user_id' => $employeeWithoutShift->id]);
});
