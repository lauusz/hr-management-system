<?php

uses(Tests\TestCase::class);

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    LeaveBalanceTransaction::query()->delete();
});

it('backfill deduct ledger defaults to dry-run', function () {
    $user = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 9.5,
    ]);
    $leave = LeaveRequest::factory()->forUser($user)->create([
        'type' => LeaveType::CUTI,
        'status' => LeaveRequest::STATUS_APPROVED,
        'start_date' => '2026-07-18',
        'end_date' => '2026-07-18',
    ]);

    expect(Artisan::call('leave:backfill-deduct-ledger', ['--leave-request' => $leave->id]))->toBe(0)
        ->and(LeaveBalanceTransaction::where('leave_request_id', $leave->id)->count())->toBe(0)
        ->and((float) $user->fresh()->leave_balance)->toBe(9.5);
});

it('backfills one approved cuti without changing balance and remains idempotent', function () {
    $user = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 9.5,
    ]);
    $leave = LeaveRequest::factory()->forUser($user)->create([
        'type' => LeaveType::CUTI,
        'status' => LeaveRequest::STATUS_APPROVED,
        'start_date' => '2026-07-18',
        'end_date' => '2026-07-18',
    ]);

    $arguments = ['--execute' => true, '--leave-request' => $leave->id];

    expect(Artisan::call('leave:backfill-deduct-ledger', $arguments))->toBe(0)
        ->and(Artisan::call('leave:backfill-deduct-ledger', $arguments))->toBe(0)
        ->and((float) $user->fresh()->leave_balance)->toBe(9.5);

    $deduct = LeaveBalanceTransaction::where('idempotency_key', "DEDUCT:LEAVE:{$leave->id}")->first();

    expect($deduct)->not->toBeNull()
        ->and((float) $deduct->amount)->toBe(0.5)
        ->and(LeaveBalanceTransaction::where('idempotency_key', "DEDUCT:LEAVE:{$leave->id}")->count())->toBe(1);
});
