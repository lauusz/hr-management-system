<?php

use App\Models\AttendanceLocation;
use App\Models\EmployeeShiftChange;
use App\Models\Shift;
use App\Models\User;

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
