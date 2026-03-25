<?php

uses(Tests\TestCase::class);

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveBalanceService;

it('counts effective leave days consistently for every role', function (UserRole $role, int $expectedDays) {
    $service = new LeaveBalanceService();
    $user = new User([
        'role' => $role,
        'leave_balance' => 12,
    ]);

    $days = $service->calculateEffectiveDaysForUser($user, '2026-03-27', '2026-03-30');

    expect($days)->toBe($expectedDays);
})->with([
    'hrd uses 5 day work week' => [UserRole::HRD, 2],
    'hr staff uses 6 day work week' => [UserRole::HR_STAFF, 3],
    'manager uses 5 day work week' => [UserRole::MANAGER, 2],
    'supervisor uses 6 day work week' => [UserRole::SUPERVISOR, 3],
    'employee uses 6 day work week' => [UserRole::EMPLOYEE, 3],
]);

it('treats enum cast cuti as annual leave for shared balance logic', function () {
    $service = new LeaveBalanceService();
    $leave = new LeaveRequest([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-03-27',
        'end_date' => '2026-03-30',
    ]);

    $leave->setRelation('user', new User([
        'role' => UserRole::HRD,
        'leave_balance' => 12,
    ]));

    expect($service->isAnnualLeave($leave))->toBeTrue()
        ->and($service->calculateEffectiveDaysForLeave($leave))->toBe(2);
});

it('refunds and deducts annual leave with the same shared rule for all cuti flows', function () {
    $service = new LeaveBalanceService();
    $email = 'test-hrd-' . uniqid() . '@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test HRD',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::HRD,
            'leave_balance' => 12,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::make([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-03-27',
            'end_date' => '2026-03-30',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    $leave->setRelation('user', $user);

    expect($service->deductLeaveBalanceForLeave($leave))->toBe(2)
        ->and($user->fresh()->leave_balance)->toBe(10);

    $leave->setRelation('user', $user->fresh());

    expect($service->refundLeaveBalanceForLeave($leave))->toBe(2)
        ->and($user->fresh()->leave_balance)->toBe(12);
});