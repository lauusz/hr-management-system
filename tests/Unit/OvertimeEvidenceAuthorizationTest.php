<?php

use App\Enums\UserRole;
use App\Models\OvertimeRequest;
use App\Models\User;

it('only allows the owner, direct supervisor and HR roles to view overtime evidence', function () {
    expect(method_exists(OvertimeRequest::class, 'canViewEvidence'))->toBeTrue();

    $employee = new User;
    $employee->forceFill(['id' => 17, 'role' => UserRole::EMPLOYEE, 'direct_supervisor_id' => 42]);
    $employee->setRelation('overtimeApproverAssignment', null);
    $request = new OvertimeRequest;
    $request->forceFill(['user_id' => 17]);
    $request->setRelation('user', $employee);
    $request->setRelation('approvalActions', collect());

    $viewer = function (int $id, UserRole $role): User {
        $user = new User;
        $user->forceFill(['id' => $id, 'role' => $role]);

        return $user;
    };

    expect($request->canViewEvidence($viewer(17, UserRole::EMPLOYEE)))->toBeTrue()
        ->and($request->canViewEvidence($viewer(42, UserRole::SUPERVISOR)))->toBeTrue()
        ->and($request->canViewEvidence($viewer(50, UserRole::HRD)))->toBeTrue()
        ->and($request->canViewEvidence($viewer(51, UserRole::HR_STAFF)))->toBeTrue()
        ->and($request->canViewEvidence($viewer(52, UserRole::MANAGER)))->toBeFalse()
        ->and($request->canViewEvidence($viewer(53, UserRole::EMPLOYEE)))->toBeFalse();
});

it('allows current and former decision makers but not reset-only actors', function () {
    $employee = new User;
    $employee->forceFill(['id' => 17, 'role' => UserRole::EMPLOYEE]);
    $assignment = new \App\Models\OvertimeApproverAssignment;
    $assignment->forceFill(['employee_id' => 17, 'supervisor_id' => 22, 'manager_id' => 23]);
    $employee->setRelation('overtimeApproverAssignment', $assignment);
    $request = new OvertimeRequest;
    $request->forceFill(['user_id' => 17]);
    $request->setRelation('user', $employee);
    $request->setRelation('approvalActions', collect([
        new \App\Models\OvertimeApprovalAction(['actor_id' => 24, 'action' => 'APPROVE']),
        new \App\Models\OvertimeApprovalAction(['actor_id' => 25, 'action' => 'RESET']),
    ]));
    $viewer = fn ($id) => (new User)->forceFill(['id' => $id, 'role' => UserRole::MANAGER]);
    expect($request->canViewEvidence($viewer(22)))->toBeTrue()
        ->and($request->canViewEvidence($viewer(23)))->toBeTrue()
        ->and($request->canViewEvidence($viewer(24)))->toBeTrue()
        ->and($request->canViewEvidence($viewer(25)))->toBeFalse();
});
