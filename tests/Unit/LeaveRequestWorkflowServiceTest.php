<?php

uses(Tests\TestCase::class);

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Services\LeaveRequestStateMachine;
use App\Services\LeaveRequestWorkflowService;

beforeEach(function () {
    LeaveBalanceTransaction::query()->delete();
});

it('cancels pending leave without touching balance ledger', function () {
    $service = new LeaveRequestWorkflowService(new LeaveBalanceService, new LeaveRequestStateMachine);

    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 12,
    ]);

    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
        'type' => LeaveType::CUTI,
    ]);

    $result = $service->cancelLeaveRequest($leave, $employee);

    expect($result)->toBeTrue()
        ->and($leave->fresh()->status)->toBe('BATAL')
        ->and((float) $employee->fresh()->leave_balance)->toBe(12.0)
        ->and(LeaveBalanceTransaction::where('user_id', $employee->id)->count())->toBe(0);
});

it('cancels approved CUTI and refunds exact deduct ledger amount', function () {
    $balanceService = new LeaveBalanceService;
    $service = new LeaveRequestWorkflowService($balanceService, new LeaveRequestStateMachine);

    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 12,
    ]);

    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::STATUS_APPROVED,
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-15',
        'end_date' => '2026-06-16',
    ]);

    // Simulasikan potongan saldo sebelumnya oleh HRD.
    $balanceService->deductLeaveBalanceForLeave($leave);
    expect((float) $employee->fresh()->leave_balance)->toBe(10.0);

    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $result = $service->cancelLeaveRequest($leave, $supervisor);

    expect($result)->toBeTrue()
        ->and($leave->fresh()->status)->toBe('BATAL')
        ->and((float) $employee->fresh()->leave_balance)->toBe(12.0);

    $refund = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
        ->where('leave_request_id', $leave->id)
        ->first();

    expect($refund)->not->toBeNull()
        ->and((float) $refund->amount)->toBe(2.0);
});

it('cancels approved SAKIT with explicit deduct and refunds exact ledger amount', function () {
    $balanceService = new LeaveBalanceService;
    $service = new LeaveRequestWorkflowService($balanceService, new LeaveRequestStateMachine);

    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 12,
    ]);

    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::STATUS_APPROVED,
        'type' => LeaveType::SAKIT,
        'start_date' => '2026-06-15',
        'end_date' => '2026-06-16',
    ]);

    // HRD memutuskan potong saldo 3 hari untuk SAKIT.
    $balanceService->deductLeaveBalanceForLeave($leave, 3.0);
    expect((float) $employee->fresh()->leave_balance)->toBe(9.0);

    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $result = $service->cancelLeaveRequest($leave, $hrd);

    expect($result)->toBeTrue()
        ->and($leave->fresh()->status)->toBe('BATAL')
        ->and((float) $employee->fresh()->leave_balance)->toBe(12.0);

    $refund = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
        ->where('leave_request_id', $leave->id)
        ->first();

    expect($refund)->not->toBeNull()
        ->and((float) $refund->amount)->toBe(3.0);
});

it('does not double refund on duplicate cancel', function () {
    $balanceService = new LeaveBalanceService;
    $service = new LeaveRequestWorkflowService($balanceService, new LeaveRequestStateMachine);

    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 12,
    ]);

    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::STATUS_APPROVED,
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-15',
        'end_date' => '2026-06-16',
    ]);

    $balanceService->deductLeaveBalanceForLeave($leave);

    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $service->cancelLeaveRequest($leave, $hrd);
    $second = $service->cancelLeaveRequest($leave, $hrd);

    expect($second)->toBeFalse()
        ->and((float) $employee->fresh()->leave_balance)->toBe(12.0)
        ->and(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
            ->where('leave_request_id', $leave->id)
            ->count())->toBe(1);
});

it('does not cancel or refund APPROVED leave when restricted allowed source statuses exclude APPROVED', function () {
    $balanceService = new LeaveBalanceService;
    $service = new LeaveRequestWorkflowService($balanceService, new LeaveRequestStateMachine);

    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 12,
    ]);

    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::STATUS_APPROVED,
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-15',
        'end_date' => '2026-06-16',
    ]);

    $balanceService->deductLeaveBalanceForLeave($leave);
    expect((float) $employee->fresh()->leave_balance)->toBe(10.0);

    $result = $service->cancelLeaveRequest(
        $leave,
        $employee,
        null,
        [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR]
    );

    expect($result)->toBeFalse()
        ->and($leave->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED)
        ->and((float) $employee->fresh()->leave_balance)->toBe(10.0)
        ->and(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
            ->where('leave_request_id', $leave->id)
            ->count())->toBe(0);
});

it('does not cancel rejected leave', function () {
    $service = new LeaveRequestWorkflowService(new LeaveBalanceService, new LeaveRequestStateMachine);

    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::STATUS_REJECTED,
    ]);

    $result = $service->cancelLeaveRequest($leave, $employee);

    expect($result)->toBeFalse()
        ->and($leave->fresh()->status)->toBe(LeaveRequest::STATUS_REJECTED);
});

it('does not cancel already cancelled leave', function () {
    $service = new LeaveRequestWorkflowService(new LeaveBalanceService, new LeaveRequestStateMachine);

    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => 'BATAL',
    ]);

    $result = $service->cancelLeaveRequest($leave, $employee);

    expect($result)->toBeFalse()
        ->and($leave->fresh()->status)->toBe('BATAL');
});

it('throws when cancelling unsaved leave request', function () {
    $service = new LeaveRequestWorkflowService(new LeaveBalanceService, new LeaveRequestStateMachine);
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $leave = new LeaveRequest(['status' => LeaveRequest::PENDING_SUPERVISOR]);

    expect(fn () => $service->cancelLeaveRequest($leave, $employee))
        ->toThrow(RuntimeException::class);
});
