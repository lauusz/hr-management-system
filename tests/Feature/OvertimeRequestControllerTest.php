<?php

use App\Models\OvertimeRequest;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->supervisor = User::factory()->create([
        'role' => \App\Enums\UserRole::SUPERVISOR,
    ]);
});

it('allows user with direct_supervisor_id to create overtime request', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::EMPLOYEE,
        'direct_supervisor_id' => $this->supervisor->id,
    ]);

    actingAs($user)
        ->post(route('overtime-requests.store'), [
            'overtime_date' => now()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '20:00',
            'description' => 'Lembur rutin',
        ])
        ->assertRedirect(route('overtime-requests.index'));

    expect(OvertimeRequest::where('user_id', $user->id)->exists())->toBeTrue();
});

it('creates request with status pending supervisor', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::EMPLOYEE,
        'direct_supervisor_id' => $this->supervisor->id,
    ]);

    actingAs($user)
        ->post(route('overtime-requests.store'), [
            'overtime_date' => now()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '20:00',
            'description' => 'Lembur rutin',
        ])
        ->assertRedirect(route('overtime-requests.index'));

    $overtime = OvertimeRequest::where('user_id', $user->id)->first();
    expect($overtime->status)->toBe(OvertimeRequest::STATUS_PENDING_SUPERVISOR);
});

it('rejects creation when user has no direct supervisor', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::EMPLOYEE,
        'direct_supervisor_id' => null,
    ]);

    actingAs($user)
        ->post(route('overtime-requests.store'), [
            'overtime_date' => now()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '20:00',
            'description' => 'Lembur rutin',
        ])
        ->assertRedirect()
        ->assertSessionHas('error', 'Pengajuan lembur tidak dapat dibuat karena supervisor langsung belum diatur. Hubungi HRD.');

    expect(OvertimeRequest::where('user_id', $user->id)->exists())->toBeFalse();
});

it('stores 480 minutes for cross midnight overtime 20:00 to 04:00', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::EMPLOYEE,
        'direct_supervisor_id' => $this->supervisor->id,
    ]);

    actingAs($user)
        ->post(route('overtime-requests.store'), [
            'overtime_date' => now()->toDateString(),
            'start_time' => '20:00',
            'end_time' => '04:00',
            'description' => 'Lembur malam',
        ])
        ->assertRedirect(route('overtime-requests.index'));

    $overtime = OvertimeRequest::where('user_id', $user->id)->first();
    expect($overtime->duration_minutes)->toBe(480);
});

it('rejects identical start and end time', function () {
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::EMPLOYEE,
        'direct_supervisor_id' => $this->supervisor->id,
    ]);

    actingAs($user)
        ->post(route('overtime-requests.store'), [
            'overtime_date' => now()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '17:00',
            'description' => 'Lembur rutin',
        ])
        ->assertSessionHasErrors(['end_time']);

    expect(OvertimeRequest::where('user_id', $user->id)->exists())->toBeFalse();
});
