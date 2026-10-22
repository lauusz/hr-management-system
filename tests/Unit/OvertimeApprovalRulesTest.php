<?php

use App\Services\OvertimeApprovalRules;

it('finalizes the request with the supervisor decision', function () {
    expect(OvertimeApprovalRules::decision(10, 10, [], 'APPROVE'))->toBe(['slot' => 'SUPERVISOR', 'status' => 'APPROVED_SUPERVISOR'])
        ->and(OvertimeApprovalRules::decision(10, 10, [], 'REJECT'))->toBe(['slot' => 'SUPERVISOR', 'status' => 'REJECTED']);
});

it('rejects incomplete, unauthorized, and repeated supervisor decisions', function () {
    foreach ([[null, 10, []], [10, 20, []], [10, 10, [['slot' => 'SUPERVISOR', 'actor_id' => 10, 'action' => 'APPROVE']]]] as [$supervisor, $actor, $actions]) {
        expect(fn () => OvertimeApprovalRules::decision($supervisor, $actor, $actions, 'APPROVE'))->toThrow(\DomainException::class);
    }
});

it('uses only decisions in the latest approval round and keeps legacy final status', function () {
    $request = new \App\Models\OvertimeRequest;
    $request->forceFill(['status' => \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR]);
    $request->setRelation('approvalActions', collect([
        new \App\Models\OvertimeApprovalAction(['approval_round' => 1, 'decision_slot' => 'SUPERVISOR', 'action' => 'APPROVE']),
        new \App\Models\OvertimeApprovalAction(['approval_round' => 2, 'decision_slot' => null, 'action' => 'RESET']),
        new \App\Models\OvertimeApprovalAction(['approval_round' => 2, 'decision_slot' => 'SUPERVISOR', 'action' => 'APPROVE']),
    ]));
    expect($request->currentApprovalActions())->toHaveCount(1)
        ->and($request->currentApprovalActions()->first()->decision_slot)->toBe('SUPERVISOR')
        ->and($request->status_label)->toBe('Disetujui');
});
