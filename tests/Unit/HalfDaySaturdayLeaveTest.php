<?php

uses(Tests\TestCase::class);

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;

describe('Half-Day Saturday Leave Calculation', function () {
    $service = new LeaveBalanceService();

    // =====================================================================
    // TEST: 5-Day Work Week (HRD, MANAGER) - Saturday Skipped
    // =====================================================================

    it('hrd skips saturday completely', function () use ($service) {
        $user = new User(['role' => UserRole::HRD, 'leave_balance' => 12]);

        // Wed + Thu + Fri + Sat(skip) + Sun(skip) + Mon = 4 days
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-25', '2026-03-30');
        expect($days)->toBe(4.0);
    });

    it('manager skips saturday completely', function () use ($service) {
        $user = new User(['role' => UserRole::MANAGER, 'leave_balance' => 12]);

        // Wed + Thu + Fri + Sat(skip) + Sun(skip) + Mon = 4 days
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-25', '2026-03-30');
        expect($days)->toBe(4.0);
    });

    it('hrd with only saturday selected returns 0', function () use ($service) {
        $user = new User(['role' => UserRole::HRD, 'leave_balance' => 12]);

        // Only Saturday (skipped) = 0
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-28', '2026-03-28');
        expect($days)->toBe(0.0);
    });

    // =====================================================================
    // TEST: 6-Day Work Week (HR_STAFF, SUPERVISOR, EMPLOYEE) - Saturday = 0.5
    // =====================================================================

    it('hr_staff counts saturday as 0.5', function () use ($service) {
        $user = new User(['role' => UserRole::HR_STAFF, 'leave_balance' => 12]);

        // Wed(1) + Thu(1) + Fri(1) + Sat(0.5) + Sun(0) + Mon(1) = 4.5
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-25', '2026-03-30');
        expect($days)->toBe(4.5);
    });

    it('supervisor counts saturday as 0.5', function () use ($service) {
        $user = new User(['role' => UserRole::SUPERVISOR, 'leave_balance' => 12]);

        // Wed(1) + Thu(1) + Fri(1) + Sat(0.5) + Sun(0) + Mon(1) = 4.5
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-25', '2026-03-30');
        expect($days)->toBe(4.5);
    });

    it('employee counts saturday as 0.5', function () use ($service) {
        $user = new User(['role' => UserRole::EMPLOYEE, 'leave_balance' => 12]);

        // Wed(1) + Thu(1) + Fri(1) + Sat(0.5) + Sun(0) + Mon(1) = 4.5
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-25', '2026-03-30');
        expect($days)->toBe(4.5);
    });

    it('hr_staff with only saturday selected returns 0.5', function () use ($service) {
        $user = new User(['role' => UserRole::HR_STAFF, 'leave_balance' => 12]);

        // Only Saturday = 0.5
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-28', '2026-03-28');
        expect($days)->toBe(0.5);
    });

    // =====================================================================
    // TEST: Edge Cases - Multiple Saturdays
    // =====================================================================

    it('hr_staff counts 2 saturdays as 1.0 day', function () use ($service) {
        $user = new User(['role' => UserRole::HR_STAFF, 'leave_balance' => 12]);

        // Two weeks: Mon-Fri(5) + Sat(0.5) + Sun(0) + Mon-Fri(5) + Sat(0.5) = 11
        // 2026-03-23 (Mon) to 2026-03-30 (Mon) = 8 days
        // Week 1: Mon(1)+Tue(1)+Wed(1)+Thu(1)+Fri(1)+Sat(0.5) = 5.5
        // Week 2: Sun(0)+Mon(1) = 1
        // Total = 6.5
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-23', '2026-03-30');
        expect($days)->toBe(6.5);
    });

    it('manager counts 2 saturdays as 0', function () use ($service) {
        $user = new User(['role' => UserRole::MANAGER, 'leave_balance' => 12]);

        // Two weeks with both saturdays skipped
        // Mon(1)+Tue(1)+Wed(1)+Thu(1)+Fri(1)+Sat(0)+Sun(0)+Mon(1) = 6
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-23', '2026-03-30');
        expect($days)->toBe(6.0);
    });

    // =====================================================================
    // TEST: Edge Cases - Sunday Always Skipped
    // =====================================================================

    it('hr_staff skips sunday but counts saturday', function () use ($service) {
        $user = new User(['role' => UserRole::HR_STAFF, 'leave_balance' => 12]);

        // Saturday + Sunday + Monday
        // Sat(0.5) + Sun(0) + Mon(1) = 1.5
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-28', '2026-03-30');
        expect($days)->toBe(1.5);
    });

    it('manager skips both saturday and sunday', function () use ($service) {
        $user = new User(['role' => UserRole::MANAGER, 'leave_balance' => 12]);

        // Saturday + Sunday + Monday
        // Sat(0) + Sun(0) + Mon(1) = 1
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-28', '2026-03-30');
        expect($days)->toBe(1.0);
    });

    // =====================================================================
    // TEST: Single Day Requests
    // =====================================================================

    it('hrd on monday counts as 1', function () use ($service) {
        $user = new User(['role' => UserRole::HRD, 'leave_balance' => 12]);

        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-30', '2026-03-30');
        expect($days)->toBe(1.0);
    });

    it('hr_staff on monday counts as 1', function () use ($service) {
        $user = new User(['role' => UserRole::HR_STAFF, 'leave_balance' => 12]);

        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-30', '2026-03-30');
        expect($days)->toBe(1.0);
    });

    it('hr_staff on saturday counts as 0.5', function () use ($service) {
        $user = new User(['role' => UserRole::HR_STAFF, 'leave_balance' => 12]);

        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-28', '2026-03-28');
        expect($days)->toBe(0.5);
    });

    it('hrd on saturday counts as 0', function () use ($service) {
        $user = new User(['role' => UserRole::HRD, 'leave_balance' => 12]);

        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-28', '2026-03-28');
        expect($days)->toBe(0.0);
    });

    // =====================================================================
    // TEST: Friday to Monday (Spanning Weekend)
    // =====================================================================

    it('hr_staff friday to monday counts 2.5 days', function () use ($service) {
        $user = new User(['role' => UserRole::HR_STAFF, 'leave_balance' => 12]);

        // Fri(1) + Sat(0.5) + Sun(0) + Mon(1) = 2.5
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-27', '2026-03-30');
        expect($days)->toBe(2.5);
    });

    it('manager friday to monday counts 2 days', function () use ($service) {
        $user = new User(['role' => UserRole::MANAGER, 'leave_balance' => 12]);

        // Fri(1) + Sat(0) + Sun(0) + Mon(1) = 2
        $days = $service->calculateEffectiveDaysForUser($user, '2026-03-27', '2026-03-30');
        expect($days)->toBe(2.0);
    });
});

describe('LeaveBalanceService Integration with LeaveRequest', function () {
    $service = new LeaveBalanceService();

    it('calculates effective days for leave request correctly for HRD', function () use ($service) {
        $user = new User(['role' => UserRole::HRD, 'leave_balance' => 12]);
        $leave = new LeaveRequest([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-03-27',
            'end_date' => '2026-03-30',
        ]);
        $leave->setRelation('user', $user);

        // HRD: Fri(1) + Sat(0) + Sun(0) + Mon(1) = 2
        expect($service->calculateEffectiveDaysForLeave($leave))->toBe(2.0);
    });

    it('calculates effective days for leave request correctly for HR_STAFF', function () use ($service) {
        $user = new User(['role' => UserRole::HR_STAFF, 'leave_balance' => 12]);
        $leave = new LeaveRequest([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-03-27',
            'end_date' => '2026-03-30',
        ]);
        $leave->setRelation('user', $user);

        // HR_STAFF: Fri(1) + Sat(0.5) + Sun(0) + Mon(1) = 2.5
        expect($service->calculateEffectiveDaysForLeave($leave))->toBe(2.5);
    });

    it('isAnnualLeave returns true only for CUTI type', function () use ($service) {
        $user = new User(['role' => UserRole::HRD, 'leave_balance' => 12]);

        $cutiLeave = new LeaveRequest([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-03-27',
            'end_date' => '2026-03-30',
        ]);
        $cutiLeave->setRelation('user', $user);

        $sakitLeave = new LeaveRequest([
            'type' => LeaveType::SAKIT,
            'start_date' => '2026-03-27',
            'end_date' => '2026-03-30',
        ]);
        $sakitLeave->setRelation('user', $user);

        expect($service->isAnnualLeave($cutiLeave))->toBeTrue();
        expect($service->isAnnualLeave($sakitLeave))->toBeFalse();
    });
});

describe('Leave Balance Deduction and Refund', function () {
    $service = new LeaveBalanceService();

    beforeEach(function () {
        $this->email = 'test-halfday-' . uniqid() . '@example.com';
        $this->user = User::unguarded(function () {
            return User::create([
                'name' => 'Test Employee',
                'email' => $this->email,
                'password' => 'password',
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);
        });
    });

    it('deducts 2.5 days for employee leaving friday to monday', function () {
        $service = new LeaveBalanceService();
        $leave = LeaveRequest::unguarded(function () {
            return LeaveRequest::make([
                'type' => LeaveType::CUTI,
                'start_date' => '2026-03-27',
                'end_date' => '2026-03-30',
                'status' => LeaveRequest::PENDING_HR,
                'user_id' => $this->user->id,
            ]);
        });
        $leave->setRelation('user', $this->user);

        // EMPLOYEE: Fri(1) + Sat(0.5) + Sun(0) + Mon(1) = 2.5
        $deducted = $service->deductLeaveBalanceForLeave($leave);
        expect($deducted)->toBe(2.5);
        expect((float) $this->user->fresh()->leave_balance)->toBe(9.5);
    });

    it('refunds 2.5 days when leave is cancelled', function () {
        $service = new LeaveBalanceService();

        // First deduct
        $leave = LeaveRequest::unguarded(function () {
            return LeaveRequest::make([
                'type' => LeaveType::CUTI,
                'start_date' => '2026-03-27',
                'end_date' => '2026-03-30',
                'status' => LeaveRequest::PENDING_HR,
                'user_id' => $this->user->id,
            ]);
        });
        $leave->setRelation('user', $this->user);

        $service->deductLeaveBalanceForLeave($leave);
        expect((float) $this->user->fresh()->leave_balance)->toBe(9.5);

        // Then refund
        $leave->setRelation('user', $this->user->fresh());
        $refunded = $service->refundLeaveBalanceForLeave($leave);
        expect($refunded)->toBe(2.5);
        expect((float) $this->user->fresh()->leave_balance)->toBe(12.0);
    });

    it('does not deduct for non-CUTI leave types', function () {
        $service = new LeaveBalanceService();
        $leave = LeaveRequest::unguarded(function () {
            return LeaveRequest::make([
                'type' => LeaveType::SAKIT,
                'start_date' => '2026-03-27',
                'end_date' => '2026-03-30',
                'status' => LeaveRequest::PENDING_HR,
                'user_id' => $this->user->id,
            ]);
        });
        $leave->setRelation('user', $this->user);

        $deducted = $service->deductLeaveBalanceForLeave($leave);
        expect($deducted)->toBe(0.0);
        expect((float) $this->user->fresh()->leave_balance)->toBe(12.0);
    });

    it('throws exception when balance is insufficient', function () {
        $service = new LeaveBalanceService();
        $this->user->update(['leave_balance' => 1]);

        $leave = LeaveRequest::unguarded(function () {
            return LeaveRequest::make([
                'type' => LeaveType::CUTI,
                'start_date' => '2026-03-27',
                'end_date' => '2026-03-30',
                'status' => LeaveRequest::PENDING_HR,
                'user_id' => $this->user->id,
            ]);
        });
        $leave->setRelation('user', $this->user->fresh());

        expect(fn () => $service->deductLeaveBalanceForLeave($leave))
            ->toThrow(\RuntimeException::class);
    });
});

describe('isFiveDayWorkWeekForUser', function () {
    $service = new LeaveBalanceService();

    it('returns true for HRD', function () use ($service) {
        $user = new User(['role' => UserRole::HRD, 'leave_balance' => 12]);
        expect($service->isFiveDayWorkWeekForUser($user))->toBeTrue();
    });

    it('returns true for MANAGER', function () use ($service) {
        $user = new User(['role' => UserRole::MANAGER, 'leave_balance' => 12]);
        expect($service->isFiveDayWorkWeekForUser($user))->toBeTrue();
    });

    it('returns false for HR_STAFF', function () use ($service) {
        $user = new User(['role' => UserRole::HR_STAFF, 'leave_balance' => 12]);
        expect($service->isFiveDayWorkWeekForUser($user))->toBeFalse();
    });

    it('returns false for SUPERVISOR', function () use ($service) {
        $user = new User(['role' => UserRole::SUPERVISOR, 'leave_balance' => 12]);
        expect($service->isFiveDayWorkWeekForUser($user))->toBeFalse();
    });

    it('returns false for EMPLOYEE', function () use ($service) {
        $user = new User(['role' => UserRole::EMPLOYEE, 'leave_balance' => 12]);
        expect($service->isFiveDayWorkWeekForUser($user))->toBeFalse();
    });
});
