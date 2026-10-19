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
        ->assertSee('syncMapFromCoordinates', false);
});

it('uses coordinates only to move the map on the edit location form', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $location = AttendanceLocation::factory()->create();

    $this->actingAs($hrd, 'web')
        ->get(route('hr.locations.edit', $location))
        ->assertOk()
        ->assertDontSee('Cari di Peta')
        ->assertDontSee('Gunakan Lokasi Saya')
        ->assertDontSee('nominatim.openstreetmap.org')
        ->assertDontSee('navigator.geolocation')
        ->assertSee('syncMapFromCoordinates', false);
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
