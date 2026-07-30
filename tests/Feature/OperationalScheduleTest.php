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
