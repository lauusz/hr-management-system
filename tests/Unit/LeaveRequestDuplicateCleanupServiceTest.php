<?php

uses(Tests\TestCase::class);

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestDuplicateCleanupService;

beforeEach(function () {
    $this->service = new LeaveRequestDuplicateCleanupService;
});

it('deletes duplicate pending same-type overlapping leave requests', function () {
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $approvedLeave = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-10',
        'end_date' => '2026-06-12',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $duplicatePendingSupervisor = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-11',
        'end_date' => '2026-06-13',
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $duplicatePendingHr = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-09',
        'end_date' => '2026-06-11',
        'status' => LeaveRequest::PENDING_HR,
    ]);

    $duplicateWrapping = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-08',
        'end_date' => '2026-06-14',
        'status' => LeaveRequest::PENDING_HR,
    ]);

    $deletedCount = $this->service->deleteDuplicatePendingLeaveRequests($approvedLeave);

    expect($deletedCount)->toBe(3)
        ->and(LeaveRequest::find($duplicatePendingSupervisor->id))->toBeNull()
        ->and(LeaveRequest::find($duplicatePendingHr->id))->toBeNull()
        ->and(LeaveRequest::find($duplicateWrapping->id))->toBeNull()
        ->and(LeaveRequest::find($approvedLeave->id))->not->toBeNull();
});

it('does not delete non-overlapping pending leave requests', function () {
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $approvedLeave = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-10',
        'end_date' => '2026-06-12',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $before = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-05',
        'end_date' => '2026-06-09',
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $after = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-13',
        'end_date' => '2026-06-15',
        'status' => LeaveRequest::PENDING_HR,
    ]);

    $deletedCount = $this->service->deleteDuplicatePendingLeaveRequests($approvedLeave);

    expect($deletedCount)->toBe(0)
        ->and(LeaveRequest::find($before->id))->not->toBeNull()
        ->and(LeaveRequest::find($after->id))->not->toBeNull();
});

it('does not delete pending leave requests with different type', function () {
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $approvedLeave = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-10',
        'end_date' => '2026-06-12',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $izinDuplicate = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::IZIN,
        'start_date' => '2026-06-11',
        'end_date' => '2026-06-12',
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $deletedCount = $this->service->deleteDuplicatePendingLeaveRequests($approvedLeave);

    expect($deletedCount)->toBe(0)
        ->and(LeaveRequest::find($izinDuplicate->id))->not->toBeNull();
});

it('does not delete terminal or already approved leave requests', function () {
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $approvedLeave = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-10',
        'end_date' => '2026-06-12',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $otherApproved = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-11',
        'end_date' => '2026-06-12',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $rejected = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-11',
        'end_date' => '2026-06-12',
        'status' => LeaveRequest::STATUS_REJECTED,
    ]);

    $cancelled = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-11',
        'end_date' => '2026-06-12',
        'status' => 'BATAL',
    ]);

    $deletedCount = $this->service->deleteDuplicatePendingLeaveRequests($approvedLeave);

    expect($deletedCount)->toBe(0)
        ->and(LeaveRequest::find($otherApproved->id))->not->toBeNull()
        ->and(LeaveRequest::find($rejected->id))->not->toBeNull()
        ->and(LeaveRequest::find($cancelled->id))->not->toBeNull();
});

it('does not delete pending leave requests belonging to other users', function () {
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $otherEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $approvedLeave = LeaveRequest::factory()->forUser($employee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-10',
        'end_date' => '2026-06-12',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $otherUserDuplicate = LeaveRequest::factory()->forUser($otherEmployee)->create([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-06-11',
        'end_date' => '2026-06-12',
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $deletedCount = $this->service->deleteDuplicatePendingLeaveRequests($approvedLeave);

    expect($deletedCount)->toBe(0)
        ->and(LeaveRequest::find($otherUserDuplicate->id))->not->toBeNull();
});
