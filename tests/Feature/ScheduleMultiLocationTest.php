<?php

use App\Enums\UserRole;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\User;

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
        ->assertSee('all_locations', false)
        ->assertSee('Kantor Pusat');
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

it('menolak All Locations yang dikirim bersama lokasi tertentu', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();
    $shift = Shift::factory()->create(['is_active' => true]);
    $location = AttendanceLocation::factory()->create();

    $this->actingAs($hrd, 'web')
        ->from(route('hr.schedules.index'))
        ->post(route('hr.schedules.store'), [
            'user_ids' => [$employee->id],
            'shift_id' => $shift->id,
            'all_locations' => true,
            'location_ids' => [$location->id],
        ])
        ->assertRedirect(route('hr.schedules.index'))
        ->assertSessionHasErrors('location_ids');
});
