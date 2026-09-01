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
