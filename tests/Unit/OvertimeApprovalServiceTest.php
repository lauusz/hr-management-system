<?php

use App\Enums\UserRole;
use App\Models\OvertimeApprovalAction;
use App\Models\OvertimeApproverAssignment;
use App\Models\OvertimeRequest;
use App\Models\User;
use App\Services\OvertimeApprovalService;

function overtimeServiceWithoutDatabase(OvertimeRequest $request, ?OvertimeApproverAssignment $assignment): OvertimeApprovalService
{
    return new class($request, $assignment) extends OvertimeApprovalService
    {
        public function __construct(private OvertimeRequest $request, private ?OvertimeApproverAssignment $assignment) {}

        public function locked(OvertimeRequest $overtime, callable $callback): mixed
        {
            return $callback($this->request, $this->assignment);
        }
    };
}

it('writes a supervisor decision and finalizes the request', function () {
    try {
        $supervisor = (new User)->forceFill(['id' => 10, 'name' => 'Supervisor', 'role' => UserRole::SUPERVISOR, 'status' => User::STATUS_ACTIVE]);
        $manager = (new User)->forceFill(['id' => 20, 'name' => 'Manager', 'role' => UserRole::MANAGER, 'status' => User::STATUS_ACTIVE]);
        $assignment = Mockery::mock(OvertimeApproverAssignment::class)->makePartial();
        $assignment->forceFill(['employee_id' => 1, 'supervisor_id' => 10, 'manager_id' => 20]);
        $assignment->setRelation('supervisor', $supervisor);
        $assignment->setRelation('manager', $manager);
        $assignment->shouldReceive('load')->once()->andReturnSelf();
        $request = Mockery::mock(OvertimeRequest::class)->makePartial();
        $request->forceFill(['id' => 7, 'user_id' => 1, 'status' => OvertimeRequest::STATUS_PENDING_SUPERVISOR]);
        $request->setRelation('approvalActions', collect());
        $relation = Mockery::mock();
        $relation->shouldReceive('create')->once()->with(Mockery::on(fn ($data) => $data['approval_round'] === 1 && $data['decision_slot'] === 'SUPERVISOR' && $data['actor_id'] === 10 && $data['action'] === 'APPROVE'));
        $request->shouldReceive('approvalActions')->once()->andReturn($relation);
        $request->shouldReceive('update')->once()->with(Mockery::on(fn ($data) => $data['status'] === OvertimeRequest::STATUS_APPROVED_SUPERVISOR
            && $data['approved_by_supervisor_id'] === 10
            && $data['approved_by_supervisor_at'] instanceof \DateTimeInterface));
        overtimeServiceWithoutDatabase($request, $assignment)->decide($request, $supervisor, 'APPROVE');
        expect(true)->toBeTrue();
    } finally {
        Mockery::close();
    }
});

it('refuses a missing supervisor mapping and a manager decision', function () {
    try {
        $request = (new OvertimeRequest)->forceFill(['id' => 7, 'user_id' => 1, 'status' => OvertimeRequest::STATUS_PENDING_SUPERVISOR]);
        $actor = (new User)->forceFill(['id' => 20, 'name' => 'Manager', 'role' => UserRole::MANAGER, 'status' => User::STATUS_ACTIVE]);
        expect(fn () => overtimeServiceWithoutDatabase($request, null)->decide($request, $actor, 'APPROVE'))->toThrow(DomainException::class);
        $assignment = Mockery::mock(OvertimeApproverAssignment::class)->makePartial();
        $assignment->forceFill(['employee_id' => 1, 'supervisor_id' => 10, 'manager_id' => 20]);
        $assignment->setRelation('supervisor', (new User)->forceFill(['id' => 10, 'role' => UserRole::SUPERVISOR, 'status' => User::STATUS_ACTIVE]));
        $assignment->setRelation('manager', $actor);
        $assignment->shouldReceive('load')->once()->andReturnSelf();
        $request->setRelation('approvalActions', collect());
        expect(fn () => overtimeServiceWithoutDatabase($request, $assignment)->decide($request, $actor, 'APPROVE'))->toThrow(DomainException::class);
    } finally {
        Mockery::close();
    }
});

it('records the cancellation actor and time while retaining previous notes', function () {
    try {
        $actor = (new User)->forceFill(['id' => 10, 'name' => 'Sinta', 'role' => UserRole::SUPERVISOR]);
        $request = Mockery::mock(OvertimeRequest::class)->makePartial();
        $request->forceFill(['status' => OvertimeRequest::STATUS_APPROVED_SUPERVISOR, 'system_note' => 'Catatan sebelumnya']);
        $request->shouldReceive('update')->once()->with(Mockery::on(fn ($data) => $data['status'] === OvertimeRequest::STATUS_CANCELLED
            && str_starts_with($data['system_note'], "Catatan sebelumnya\n[System] Dibatalkan oleh Supervisor/Atasan (Sinta) pada ")
            && preg_match('/\d{2} [A-Za-z]{3} \d{4} \d{2}:\d{2}$/', $data['system_note'])));

        (new OvertimeApprovalService)->cancelLocked($request, $actor);
    } finally {
        Mockery::close();
    }
});
