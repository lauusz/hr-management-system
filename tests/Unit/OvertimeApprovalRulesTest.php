<?php

use App\Services\OvertimeApprovalRules;

it('requires two different assigned approvers and preserves each decision', function () {
    expect(OvertimeApprovalRules::decision(10, 20, 10, [], 'APPROVE'))->toBe(['slot' => 'SUPERVISOR', 'status' => 'PENDING_SUPERVISOR'])
        ->and(OvertimeApprovalRules::decision(10, 20, 20, [['slot' => 'SUPERVISOR', 'actor_id' => 10, 'action' => 'APPROVE']], 'APPROVE'))->toBe(['slot' => 'MANAGER', 'status' => 'APPROVED_SUPERVISOR'])
        ->and(OvertimeApprovalRules::decision(10, 20, 10, [['slot' => 'MANAGER', 'actor_id' => 20, 'action' => 'APPROVE']], 'REJECT'))->toBe(['slot' => 'SUPERVISOR', 'status' => 'REJECTED']);
});

it('rejects incomplete, unauthorized, duplicate and same-person decisions', function () {
    foreach ([[10, null, 10, []], [10, 20, 30, []], [10, 20, 10, [['slot' => 'SUPERVISOR', 'actor_id' => 10, 'action' => 'APPROVE']]], [10, 20, 20, [['slot' => 'SUPERVISOR', 'actor_id' => 20, 'action' => 'APPROVE']]]] as [$supervisor, $manager, $actor, $actions]) {
        expect(fn () => OvertimeApprovalRules::decision($supervisor, $manager, $actor, $actions, 'APPROVE'))->toThrow(\DomainException::class);
    }
});

it('uses only decisions in the latest approval round and keeps legacy final status', function () {
    $request = new \App\Models\OvertimeRequest;
    $request->forceFill(['status' => \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR]);
    $request->setRelation('approvalActions', collect([
        new \App\Models\OvertimeApprovalAction(['approval_round' => 1, 'decision_slot' => 'SUPERVISOR', 'action' => 'APPROVE']),
        new \App\Models\OvertimeApprovalAction(['approval_round' => 2, 'decision_slot' => null, 'action' => 'RESET']),
        new \App\Models\OvertimeApprovalAction(['approval_round' => 2, 'decision_slot' => 'MANAGER', 'action' => 'APPROVE']),
    ]));
    expect($request->currentApprovalActions())->toHaveCount(1)
        ->and($request->currentApprovalActions()->first()->decision_slot)->toBe('MANAGER')
        ->and($request->status_label)->toBe('Disetujui');
});
