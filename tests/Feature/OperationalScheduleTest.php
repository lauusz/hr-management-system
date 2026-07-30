<?php

use App\Enums\UserRole;
use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\EmployeeShiftChange;
use App\Models\Shift;
use App\Models\User;
use App\Services\OperationalScheduleService;
use Carbon\Carbon;

it('maps OPS membership and pending shift change relationships', function () {
    $creator = User::factory()->create();
    $user = User::factory()->create(['is_ops_schedule_member' => true]);
    $shift = Shift::factory()->create();
    $location = AttendanceLocation::factory()->create();

    $change = EmployeeShiftChange::create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
        'effective_date' => '2026-08-01',
        'status' => EmployeeShiftChange::STATUS_PENDING,
        'pending_slot' => 1,
        'created_by' => $creator->id,
        'shift_name_snapshot' => $shift->name,
        'location_name_snapshot' => $location->name,
    ]);

    expect($user->fresh()->is_ops_schedule_member)->toBeTrue()
        ->and($user->pendingShiftChange->is($change))->toBeTrue()
        ->and($change->user->is($user))->toBeTrue()
        ->and($change->shift->is($shift))->toBeTrue()
        ->and($change->location->is($location))->toBeTrue()
        ->and($change->creator->is($creator))->toBeTrue();
});

it('applies an immediate bulk schedule and records applied changes', function () {
    $actor = User::factory()->create(['role' => UserRole::HRD]);
    $users = User::factory()->count(2)->create(['is_ops_schedule_member' => true]);
    $shift = Shift::factory()->create(['name' => 'Shift OPS Pagi']);
    $location = AttendanceLocation::factory()->create(['name' => 'Site OPS']);

    app(OperationalScheduleService::class)
        ->applyNow($users->modelKeys(), $shift, $location, $actor);

    foreach ($users as $user) {
        $this->assertDatabaseHas('employee_shifts', [
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'location_id' => $location->id,
        ]);
        $this->assertDatabaseHas('employee_shift_changes', [
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'location_id' => $location->id,
            'status' => EmployeeShiftChange::STATUS_APPLIED,
            'pending_slot' => null,
            'shift_name_snapshot' => 'Shift OPS Pagi',
            'location_name_snapshot' => 'Site OPS',
        ]);
    }
});

it('replaces one pending schedule without changing the active schedule', function () {
    $actor = User::factory()->create(['role' => UserRole::HRD]);
    $user = User::factory()->create(['is_ops_schedule_member' => true]);
    $oldShift = Shift::factory()->create(['name' => 'Shift Lama']);
    $firstShift = Shift::factory()->create(['name' => 'Shift Bulan Depan A']);
    $replacementShift = Shift::factory()->create(['name' => 'Shift Bulan Depan B']);
    $location = AttendanceLocation::factory()->create();
    EmployeeShift::factory()->create([
        'user_id' => $user->id,
        'shift_id' => $oldShift->id,
        'location_id' => $location->id,
    ]);
    $service = app(OperationalScheduleService::class);
    $now = Carbon::parse('2026-07-30 10:00', 'Asia/Jakarta');

    $service->scheduleNextMonth([$user->id], $firstShift, $location, $actor, $now);
    $service->scheduleNextMonth([$user->id], $replacementShift, $location, $actor, $now);

    expect($user->employeeShift()->value('shift_id'))->toBe($oldShift->id)
        ->and(EmployeeShiftChange::where('user_id', $user->id)
            ->where('status', EmployeeShiftChange::STATUS_CANCELLED)
            ->whereNull('pending_slot')
            ->count())->toBe(1)
        ->and(EmployeeShiftChange::where('user_id', $user->id)
            ->where('status', EmployeeShiftChange::STATUS_PENDING)
            ->where('shift_id', $replacementShift->id)
            ->whereDate('effective_date', '2026-08-01')
            ->count())->toBe(1);
});

it('applies a due pending schedule using the application date', function () {
    $actor = User::factory()->create(['role' => UserRole::HRD]);
    $user = User::factory()->create(['is_ops_schedule_member' => true]);
    $oldShift = Shift::factory()->create(['name' => 'Shift Lama']);
    $newShift = Shift::factory()->create(['name' => 'Shift Baru']);
    $location = AttendanceLocation::factory()->create();
    EmployeeShift::factory()->create([
        'user_id' => $user->id,
        'shift_id' => $oldShift->id,
        'location_id' => $location->id,
    ]);
    $change = EmployeeShiftChange::create([
        'user_id' => $user->id,
        'shift_id' => $newShift->id,
        'location_id' => $location->id,
        'effective_date' => '2026-08-01',
        'status' => EmployeeShiftChange::STATUS_PENDING,
        'pending_slot' => 1,
        'created_by' => $actor->id,
        'shift_name_snapshot' => $newShift->name,
        'location_name_snapshot' => $location->name,
    ]);

    $assignment = app(OperationalScheduleService::class)
        ->applyDueForUser($user, Carbon::parse('2026-08-01 00:01', 'Asia/Jakarta'));

    expect($assignment->shift_id)->toBe($newShift->id)
        ->and($change->fresh()->status)->toBe(EmployeeShiftChange::STATUS_APPLIED)
        ->and($change->fresh()->pending_slot)->toBeNull()
        ->and($change->fresh()->applied_at)->not->toBeNull();
});

it('applies every due operational schedule in one resolver call', function () {
    $actor = User::factory()->create(['role' => UserRole::HRD]);
    $users = User::factory()->count(2)->create(['is_ops_schedule_member' => true]);
    $shift = Shift::factory()->create();
    $location = AttendanceLocation::factory()->create();

    foreach ($users as $user) {
        EmployeeShiftChange::create([
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'location_id' => $location->id,
            'effective_date' => '2026-08-01',
            'status' => EmployeeShiftChange::STATUS_PENDING,
            'pending_slot' => 1,
            'created_by' => $actor->id,
            'shift_name_snapshot' => $shift->name,
            'location_name_snapshot' => $location->name,
        ]);
    }

    $count = app(OperationalScheduleService::class)
        ->applyAllDue(Carbon::parse('2026-08-01', 'Asia/Jakarta'));

    expect($count)->toBe(2)
        ->and(EmployeeShift::whereIn('user_id', $users->modelKeys())->count())->toBe(2)
        ->and(EmployeeShiftChange::where('status', EmployeeShiftChange::STATUS_PENDING)
            ->count())->toBe(0);
});

it('allows only HR users to open the operational schedule page', function () {
    $this->get('/hr/operational-schedules')->assertRedirect('/login');

    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $this->actingAs($employee)
        ->get('/hr/operational-schedules')
        ->assertForbidden();

    $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
    $this->actingAs($hrStaff)
        ->get('/hr/operational-schedules')
        ->assertOk();
});

it('shows only active OPS members with current and pending schedules', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $member = User::factory()->create([
        'name' => 'Anggota OPS Aktif',
        'status' => User::STATUS_ACTIVE,
        'is_ops_schedule_member' => true,
    ]);
    User::factory()->create([
        'name' => 'Bukan Anggota OPS',
        'status' => User::STATUS_ACTIVE,
        'is_ops_schedule_member' => false,
    ]);
    User::factory()->create([
        'name' => 'Anggota OPS Nonaktif',
        'status' => 'INACTIVE',
        'is_ops_schedule_member' => true,
    ]);

    $this->actingAs($hrd)
        ->get('/hr/operational-schedules')
        ->assertOk()
        ->assertSee('Anggota OPS Aktif')
        ->assertDontSee('Anggota OPS Nonaktif')
        ->assertViewHas(
            'items',
            fn ($items) => $items->pluck('id')->all() === [$member->id]
        )
        ->assertViewHas(
            'availableUsers',
            fn ($users) => $users->pluck('name')->contains('Bukan Anggota OPS')
        );
});

it('adds and removes OPS members without deleting their active schedule', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'is_ops_schedule_member' => false,
    ]);
    $shift = Shift::factory()->create();
    $location = AttendanceLocation::factory()->create();
    EmployeeShift::factory()->create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
    ]);
    $this->actingAs($hrd);

    $this->post('/hr/operational-schedules/members', [
        'user_ids' => [$user->id],
    ])->assertRedirect()->assertSessionHas('success');

    expect($user->fresh()->is_ops_schedule_member)->toBeTrue();

    EmployeeShiftChange::create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
        'effective_date' => '2026-08-01',
        'status' => EmployeeShiftChange::STATUS_PENDING,
        'pending_slot' => 1,
        'created_by' => $hrd->id,
        'shift_name_snapshot' => $shift->name,
        'location_name_snapshot' => $location->name,
    ]);

    $this->delete("/hr/operational-schedules/members/{$user->id}")
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->is_ops_schedule_member)->toBeFalse();
    $this->assertDatabaseHas('employee_shifts', ['user_id' => $user->id]);
    $this->assertDatabaseHas('employee_shift_changes', [
        'user_id' => $user->id,
        'status' => EmployeeShiftChange::STATUS_CANCELLED,
        'pending_slot' => null,
    ]);
});

it('bulk updates selected OPS members now and next month', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-30 10:00', 'Asia/Jakarta'));
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $users = User::factory()->count(2)->create([
        'status' => User::STATUS_ACTIVE,
        'is_ops_schedule_member' => true,
    ]);
    $shift = Shift::factory()->create(['is_active' => true]);
    $location = AttendanceLocation::factory()->create(['is_active' => true]);
    $this->actingAs($hrd);

    $payload = [
        'user_ids' => $users->modelKeys(),
        'shift_id' => $shift->id,
        'location_id' => $location->id,
    ];

    $this->post('/hr/operational-schedules/bulk', $payload + ['apply_mode' => 'NOW'])
        ->assertRedirect()
        ->assertSessionHas('success');
    $this->assertDatabaseHas('employee_shifts', [
        'user_id' => $users->first()->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
    ]);

    $this->post('/hr/operational-schedules/bulk', $payload + ['apply_mode' => 'NEXT_MONTH'])
        ->assertRedirect()
        ->assertSessionHas('success');
    $this->assertDatabaseHas('employee_shift_changes', [
        'user_id' => $users->first()->id,
        'effective_date' => '2026-08-01 00:00:00',
        'status' => EmployeeShiftChange::STATUS_PENDING,
    ]);

    Carbon::setTestNow();
});

it('rejects non OPS users and inactive schedule options in bulk updates', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'is_ops_schedule_member' => false,
    ]);
    $shift = Shift::factory()->create(['is_active' => false]);
    $location = AttendanceLocation::factory()->create(['is_active' => false]);

    $this->actingAs($hrd)
        ->post('/hr/operational-schedules/bulk', [
            'user_ids' => [$user->id],
            'shift_id' => $shift->id,
            'location_id' => $location->id,
            'apply_mode' => 'NOW',
        ])
        ->assertSessionHasErrors(['user_ids.0', 'shift_id', 'location_id']);

    $this->assertDatabaseMissing('employee_shifts', ['user_id' => $user->id]);
});

it('applies due OPS changes before Master Jadwal Karyawan is listed', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $user = User::factory()->create(['is_ops_schedule_member' => true]);
    $shift = Shift::factory()->create(['name' => 'Shift Efektif Hari Ini']);
    $location = AttendanceLocation::factory()->create();
    EmployeeShiftChange::create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
        'effective_date' => now()->toDateString(),
        'status' => EmployeeShiftChange::STATUS_PENDING,
        'pending_slot' => 1,
        'created_by' => $hrd->id,
        'shift_name_snapshot' => $shift->name,
        'location_name_snapshot' => $location->name,
    ]);

    $this->actingAs($hrd)
        ->get('/hr/schedules')
        ->assertOk();

    $this->assertDatabaseHas('employee_shifts', [
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'location_id' => $location->id,
    ]);
});
