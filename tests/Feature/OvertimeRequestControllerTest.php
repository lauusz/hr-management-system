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

it('keeps supervisor overtime routes closed for employee leave approver without subordinates', function () {
    $approver = User::factory()->create([
        'role' => \App\Enums\UserRole::EMPLOYEE,
    ]);

    User::factory()->create([
        'role' => \App\Enums\UserRole::EMPLOYEE,
        'approver_id' => $approver->id,
        'direct_supervisor_id' => null,
        'manager_id' => null,
    ]);

    actingAs($approver)
        ->get(route('supervisor.overtime-requests.index'))
        ->assertStatus(403);
});

it('shows overtime detail only to its owner', function () {
    $owner = User::factory()->create(['role' => \App\Enums\UserRole::EMPLOYEE]);
    $otherUser = User::factory()->create(['role' => \App\Enums\UserRole::EMPLOYEE]);
    $overtime = OvertimeRequest::create([
        'user_id' => $owner->id,
        'overtime_date' => now()->toDateString(),
        'start_time' => '17:00',
        'end_time' => '20:00',
        'duration_minutes' => 180,
        'description' => 'Lembur rutin',
        'status' => OvertimeRequest::STATUS_PENDING_SUPERVISOR,
    ]);

    actingAs($owner)
        ->get(route('overtime-requests.show', $overtime))
        ->assertOk()
        ->assertSee('Detail Pengajuan Lembur');

    actingAs($otherUser)
        ->get(route('overtime-requests.show', $overtime))
        ->assertRedirect(route('overtime-requests.index'))
        ->assertSessionHas('error', 'Anda tidak berhak melihat data pengajuan ini.');
});

it('allows editing only while overtime is waiting for supervisor approval', function () {
    $owner = User::factory()->create(['role' => \App\Enums\UserRole::EMPLOYEE]);
    $overtime = OvertimeRequest::create([
        'user_id' => $owner->id,
        'overtime_date' => now()->toDateString(),
        'start_time' => '17:00',
        'end_time' => '20:00',
        'duration_minutes' => 180,
        'description' => 'Lembur rutin',
        'status' => OvertimeRequest::STATUS_PENDING_SUPERVISOR,
    ]);

    actingAs($owner)
        ->get(route('overtime-requests.edit', $overtime))
        ->assertOk()
        ->assertSee('Ubah Pengajuan Lembur');

    $overtime->update(['status' => OvertimeRequest::STATUS_APPROVED_SUPERVISOR]);

    actingAs($owner)
        ->get(route('overtime-requests.edit', $overtime))
        ->assertRedirect(route('overtime-requests.index'))
        ->assertSessionHas('error', 'Pengajuan sudah diproses SPV, tidak dapat diubah.');
});
