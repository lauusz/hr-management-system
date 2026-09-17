<?php

use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveApprovalAssignmentService;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

function leaveApprovalAssignmentService(): LeaveApprovalAssignmentService
{
    return app(LeaveApprovalAssignmentService::class);
}

test('designated approver processes while supervisor and manager can only view', function () {
    $approver = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $manager = User::factory()->create(['role' => UserRole::MANAGER]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'approver_id' => $approver->id,
        'direct_supervisor_id' => $supervisor->id,
        'manager_id' => $manager->id,
    ]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $service = leaveApprovalAssignmentService();

    expect($service->initialApprovalCapacity($employee))->toBe('APPROVER')
        ->and($service->initialApproverFor($employee)?->is($approver))->toBeTrue()
        ->and($service->canProcessInitialStage($approver, $leave))->toBeTrue()
        ->and($service->canProcessInitialStage($supervisor, $leave))->toBeFalse()
        ->and($service->canProcessInitialStage($manager, $leave))->toBeFalse()
        ->and($service->canView($supervisor, $leave))->toBeTrue()
        ->and($service->canView($manager, $leave))->toBeTrue();
});

test('supervisor processes when no designated approver exists and manager can only view', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $manager = User::factory()->create(['role' => UserRole::MANAGER]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'approver_id' => null,
        'direct_supervisor_id' => $supervisor->id,
        'manager_id' => $manager->id,
    ]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $service = leaveApprovalAssignmentService();

    expect($service->initialApprovalCapacity($employee))->toBe('SUPERVISOR')
        ->and($service->initialApproverFor($employee)?->is($supervisor))->toBeTrue()
        ->and($service->canProcessInitialStage($supervisor, $leave))->toBeTrue()
        ->and($service->canProcessInitialStage($manager, $leave))->toBeFalse()
        ->and($service->canView($manager, $leave))->toBeTrue();
});

test('invalid designated approver routes request directly to HR without supervisor or manager fallback', function (array $approverAttributes) {
    $invalidApprover = User::factory()->create($approverAttributes);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $manager = User::factory()->create(['role' => UserRole::MANAGER]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'approver_id' => $invalidApprover->id,
        'direct_supervisor_id' => $supervisor->id,
        'manager_id' => $manager->id,
    ]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $service = leaveApprovalAssignmentService();

    expect($service->initialApprovalCapacity($employee))->toBe('HR')
        ->and($service->initialApproverFor($employee))->toBeNull()
        ->and($service->queryPendingFor($invalidApprover)->pluck('id')->all())->toBe([])
        ->and($service->queryPendingFor($supervisor)->pluck('id')->all())->toBe([])
        ->and($service->queryPendingFor($manager)->pluck('id')->all())->toBe([])
        ->and($service->canProcessInitialStage($supervisor, $leave))->toBeFalse()
        ->and($service->canProcessInitialStage($manager, $leave))->toBeFalse()
        ->and($service->canView($supervisor, $leave))->toBeTrue()
        ->and($service->canView($manager, $leave))->toBeTrue();
})->with([
    'inactive employee approver' => [[
        'role' => UserRole::EMPLOYEE,
        'status' => 'INACTIVE',
    ]],
    'non employee approver' => [[
        'role' => UserRole::MANAGER,
        'status' => User::STATUS_ACTIVE,
    ]],
]);

test('manager processes when approver and supervisor are empty', function () {
    $manager = User::factory()->create(['role' => UserRole::MANAGER]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'approver_id' => null,
        'direct_supervisor_id' => null,
        'manager_id' => $manager->id,
    ]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $service = leaveApprovalAssignmentService();

    expect($service->initialApprovalCapacity($employee))->toBe('MANAGER')
        ->and($service->initialApproverFor($employee)?->is($manager))->toBeTrue()
        ->and($service->canProcessInitialStage($manager, $leave))->toBeTrue();
});

test('request goes to HR when no initial approver exists', function () {
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'approver_id' => null,
        'direct_supervisor_id' => null,
        'manager_id' => null,
    ]);

    $service = leaveApprovalAssignmentService();

    expect($service->initialApprovalCapacity($employee))->toBe('HR')
        ->and($service->initialApproverFor($employee))->toBeNull();
});

test('HRD applicant without initial approver keeps final HRD behavior marker', function () {
    $hrd = User::factory()->create([
        'role' => UserRole::HRD,
        'approver_id' => null,
        'direct_supervisor_id' => null,
        'manager_id' => null,
    ]);

    $service = leaveApprovalAssignmentService();

    expect($service->initialApprovalCapacity($hrd))->toBe('FINAL_FOR_HRD')
        ->and($service->initialApproverFor($hrd))->toBeNull();
});

test('personal request by an approver never routes to themselves', function () {
    $approverEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $approverEmployee->forceFill([
        'approver_id' => $approverEmployee->id,
        'direct_supervisor_id' => $supervisor->id,
    ])->save();

    $service = leaveApprovalAssignmentService();

    expect($service->initialApprovalCapacity($approverEmployee))->toBe('SUPERVISOR')
        ->and($service->initialApproverFor($approverEmployee)?->is($supervisor))->toBeTrue();
});

test('pending and visible queries separate processing inbox from monitoring visibility', function () {
    $approver = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $manager = User::factory()->create(['role' => UserRole::MANAGER]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'approver_id' => $approver->id,
        'direct_supervisor_id' => $supervisor->id,
        'manager_id' => $manager->id,
    ]);
    $pendingLeave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);
    $approvedLeave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);

    $service = leaveApprovalAssignmentService();

    expect($service->queryPendingFor($approver)->pluck('id')->all())->toBe([$pendingLeave->id])
        ->and($service->queryPendingFor($supervisor)->pluck('id')->all())->toBe([])
        ->and($service->queryVisibleTo($supervisor)->pluck('id')->all())->toContain($pendingLeave->id, $approvedLeave->id)
        ->and($service->queryVisibleTo($manager)->pluck('id')->all())->toContain($pendingLeave->id, $approvedLeave->id);
});

test('reconciles pending initial requests dynamically to the latest supervisor or approver', function () {
    $oldSupervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $newSupervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $newApprover = User::factory()->create(['role' => UserRole::EMPLOYEE, 'status' => User::STATUS_ACTIVE]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'approver_id' => null,
        'direct_supervisor_id' => $oldSupervisor->id,
        'manager_id' => null,
    ]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $employee->forceFill(['direct_supervisor_id' => $newSupervisor->id])->save();

    $service = leaveApprovalAssignmentService();
    $service->reconcilePendingInitialRequests($employee->fresh());

    expect($leave->fresh()->status)->toBe(LeaveRequest::PENDING_SUPERVISOR)
        ->and($service->queryPendingFor($oldSupervisor)->pluck('id')->all())->toBe([])
        ->and($service->queryPendingFor($newSupervisor)->pluck('id')->all())->toBe([$leave->id]);

    $employee->forceFill(['approver_id' => $newApprover->id])->save();
    $service->reconcilePendingInitialRequests($employee->fresh());

    expect($leave->fresh()->status)->toBe(LeaveRequest::PENDING_SUPERVISOR)
        ->and($service->queryPendingFor($newSupervisor)->pluck('id')->all())->toBe([])
        ->and($service->queryPendingFor($newApprover)->pluck('id')->all())->toBe([$leave->id]);
});

test('reconciles pending initial requests to HR when no valid initial approver remains', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'approver_id' => null,
        'direct_supervisor_id' => $supervisor->id,
        'manager_id' => null,
    ]);
    $pendingInitial = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);
    $pendingHr = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_HR,
    ]);

    $employee->forceFill([
        'approver_id' => null,
        'direct_supervisor_id' => null,
        'manager_id' => null,
    ])->save();

    leaveApprovalAssignmentService()->reconcilePendingInitialRequests($employee->fresh());

    expect($pendingInitial->fresh()->status)->toBe(LeaveRequest::PENDING_HR)
        ->and($pendingInitial->fresh()->notes)->toContain('dialihkan otomatis ke HR')
        ->and($pendingHr->fresh()->status)->toBe(LeaveRequest::PENDING_HR)
        ->and($pendingHr->fresh()->notes)->toBeNull();
});

test('records approval action snapshot', function () {
    $actor = User::factory()->create(['role' => UserRole::EMPLOYEE, 'name' => 'Approver Satu']);
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE, 'approver_id' => $actor->id]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    leaveApprovalAssignmentService()->recordAction(
        $leave,
        $actor,
        'APPROVER',
        'APPROVE',
        LeaveRequest::PENDING_SUPERVISOR,
        LeaveRequest::PENDING_HR,
        'OK'
    );

    $action = $leave->approvalActions()->first();

    expect($action)->not->toBeNull()
        ->and($action->actor_id)->toBe($actor->id)
        ->and($action->actor_name)->toBe('Approver Satu')
        ->and($action->actor_role)->toBe(UserRole::EMPLOYEE->value)
        ->and($action->capacity)->toBe('APPROVER')
        ->and($action->action)->toBe('APPROVE')
        ->and($action->from_status)->toBe(LeaveRequest::PENDING_SUPERVISOR)
        ->and($action->to_status)->toBe(LeaveRequest::PENDING_HR)
        ->and($action->notes)->toBe('OK')
        ->and($action->acted_at)->not->toBeNull();
});
