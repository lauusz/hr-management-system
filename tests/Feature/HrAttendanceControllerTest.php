<?php

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\User;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

it('attendance search ignores dots and spaces in employee names', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $matchingEmployee = User::factory()->create(['name' => 'MOH. AINUL YAQIN']);
    $otherEmployee = User::factory()->create(['name' => 'MOHAMMAD FAJAR']);

    Attendance::factory()->forUser($matchingEmployee)->today()->create();
    Attendance::factory()->forUser($otherEmployee)->today()->create();

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index', [
            'q' => 'mohainul',
            'date_start' => now()->subDay()->toDateString(),
            'date_end' => now()->addDay()->toDateString(),
        ]));

    $response->assertOk();
    expect($response->viewData('items')->total())->toBe(1)
        ->and($response->viewData('items')->first()->user_id)->toBe($matchingEmployee->id);
});

it('filters attendance recap by overnight schedule snapshot', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $overnightEmployee = User::factory()->create();
    $regularEmployee = User::factory()->create();
    $workDate = now()->startOfDay();

    Attendance::factory()->forUser($overnightEmployee)->create([
        'date' => $workDate->toDateString(),
        'clock_in_at' => $workDate->copy()->setTime(22, 0),
        'normal_start_time' => '22:00:00',
        'normal_end_time' => '06:00:00',
    ]);
    Attendance::factory()->forUser($regularEmployee)->create([
        'date' => $workDate->toDateString(),
        'clock_in_at' => $workDate->copy()->setTime(8, 0),
        'normal_start_time' => '08:00:00',
        'normal_end_time' => '17:00:00',
    ]);

    $overnightResponse = $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index', [
            'date_start' => $workDate->copy()->subDay()->toDateString(),
            'date_end' => $workDate->copy()->addDay()->toDateString(),
            'shift_type' => 'overnight',
        ]));

    $overnightResponse->assertOk()
        ->assertSee('Jenis Shift')
        ->assertSee('Bukan Lintas Hari');
    expect($overnightResponse->viewData('items')->total())->toBe(1)
        ->and($overnightResponse->viewData('items')->first()->user_id)->toBe($overnightEmployee->id);

    $regularResponse = $this->actingAs($hrd, 'web')
        ->get(route('hr.attendances.index', [
            'date_start' => $workDate->copy()->subDay()->toDateString(),
            'date_end' => $workDate->copy()->addDay()->toDateString(),
            'shift_type' => 'regular',
        ]));

    expect($regularResponse->viewData('items')->total())->toBe(1)
        ->and($regularResponse->viewData('items')->first()->user_id)->toBe($regularEmployee->id);
});

it('opens attendance coordinates in the location modal instead of a new maps tab', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create();

    Attendance::factory()->forUser($employee)->today()->create([
        'clock_in_lat' => -6.200000,
        'clock_in_lng' => 106.816666,
        'clock_out_lat' => -6.210000,
        'clock_out_lng' => 106.826666,
    ]);

    $response = $this->actingAs($hrd, 'web')->get(route('hr.attendances.index', [
        'date_start' => now()->subDay()->toDateString(),
        'date_end' => now()->addDay()->toDateString(),
    ]));

    expect($response->viewData('items')->total())->toBe(1);

    $response->assertOk()
        ->assertSee($employee->name)
        ->assertSee('data-attendance-location="clock-in"', false)
        ->assertSee('data-attendance-location="clock-out"', false)
        ->assertSee('id="attendance-location-map"', false)
        ->assertDontSee('https://www.google.com/maps/search/?api=1', false);
});
