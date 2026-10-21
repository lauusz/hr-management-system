<?php

use App\Enums\UserRole;
use App\Models\AttendanceLocation;
use App\Models\User;

it('uses coordinates only to move the map on the create location form', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.locations.create'))
        ->assertOk()
        ->assertDontSee('Cari di Peta')
        ->assertDontSee('Gunakan Lokasi Saya')
        ->assertDontSee('nominatim.openstreetmap.org')
        ->assertDontSee('navigator.geolocation')
        ->assertSee('syncMapFromCoordinates', false)
        ->assertSee('mlc-step', false)
        ->assertSee('mlc-banner', false)
        ->assertSee('apv-action-dock', false)
        ->assertSee('apv-action-btn--secondary', false)
        ->assertSee('apv-action-btn--primary', false)
        ->assertSee('Identitas Lokasi')
        ->assertSee('Titik Koordinat & Radius')
        ->assertSee('Simpan Lokasi');
});

it('uses coordinates only to move the map on the edit location form', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $location = AttendanceLocation::factory()->create([
        'name' => 'Kantor Cabang Bandung',
        'radius_meters' => 45,
        'is_active' => true,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.locations.edit', $location))
        ->assertOk()
        ->assertDontSee('Cari di Peta')
        ->assertDontSee('Gunakan Lokasi Saya')
        ->assertDontSee('nominatim.openstreetmap.org')
        ->assertDontSee('navigator.geolocation')
        ->assertSee('syncMapFromCoordinates', false)
        ->assertSee('Edit Lokasi Presensi')
        ->assertSee('Lokasi Presensi')
        ->assertSee('Kantor Cabang Bandung')
        ->assertSee('RADIUS 45M')
        ->assertSee('Lokasi Aktif')
        ->assertSee('Identitas & Status Lokasi')
        ->assertSee('Titik Koordinat & Radius')
        ->assertSee('apv-action-dock', false)
        ->assertSee('apv-action-btn--secondary', false)
        ->assertSee('apv-action-btn--primary', false)
        ->assertSee('Simpan Perubahan');
});

it('lists all attendance locations without pagination', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    AttendanceLocation::factory()
        ->count(21)
        ->sequence(fn ($sequence) => ['name' => 'Lokasi '.$sequence->index])
        ->create();

    $response = $this->actingAs($hrd, 'web')->get(route('hr.locations.index'));

    expect($response->viewData('items'))
        ->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->toHaveCount(21);

    $response
        ->assertOk()
        ->assertSee('Lokasi 20')
        ->assertDontSee('loc-pagination', false);
});

it('displays master location stats, search, and status filter matching leave master layout', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    AttendanceLocation::factory()->create([
        'name' => 'Kantor Pusat Jakarta',
        'address' => 'Jl. Sudirman No. 1',
        'is_active' => true,
    ]);

    AttendanceLocation::factory()->create([
        'name' => 'Gudang Lama Bekasi',
        'address' => 'Jl. Raya Bekasi',
        'is_active' => false,
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.locations.index'))
        ->assertOk()
        ->assertSee('Master Lokasi Presensi')
        ->assertSee('Total Lokasi')
        ->assertSee('Kantor Pusat Jakarta')
        ->assertSee('Gudang Lama Bekasi')
        ->assertSee('data-location-live-search', false);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.locations.index', ['status' => 'active']))
        ->assertOk()
        ->assertSee('Kantor Pusat Jakarta')
        ->assertDontSee('Gudang Lama Bekasi');

    $this->actingAs($hrd, 'web')
        ->get(route('hr.locations.index', ['q' => 'Sudirman']))
        ->assertOk()
        ->assertSee('Kantor Pusat Jakarta')
        ->assertDontSee('Gudang Lama Bekasi');
});
