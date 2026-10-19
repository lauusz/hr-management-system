<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\OvertimeApproverAssignment;
use App\Models\OvertimeRequest;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class OvertimeApprovalService
{
    public function locked(OvertimeRequest $overtime, callable $callback): mixed
    {
        return DB::transaction(function () use ($overtime, $callback) {
            User::whereKey($overtime->user_id)->lockForUpdate()->firstOrFail();
            $assignment = OvertimeApproverAssignment::whereKey($overtime->user_id)->lockForUpdate()->first();
            $locked = OvertimeRequest::whereKey($overtime->id)->lockForUpdate()->firstOrFail();

            return $callback($locked, $assignment);
        });
    }

    public function decide(OvertimeRequest $overtime, User $actor, string $decision, ?string $note = null): void
    {
        $this->locked($overtime, function ($locked, $assignment) use ($actor, $decision, $note) {
            if ($locked->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                throw new DomainException('Pengajuan sudah diproses atau dibatalkan.');
            }
            if (! $assignment || ! $assignment->supervisor_id || ! $assignment->manager_id) {
                throw new DomainException('Supervisor dan manager lembur harus ditetapkan terlebih dahulu.');
            }
            $assignment->load(['supervisor', 'manager']);
            if (! $assignment->supervisor?->isActive() || $assignment->supervisor->role !== UserRole::SUPERVISOR
                || ! $assignment->manager?->isActive() || $assignment->manager->role !== UserRole::MANAGER
                || (int) $locked->user_id === (int) $assignment->supervisor_id || (int) $locked->user_id === (int) $assignment->manager_id) {
                throw new DomainException('Penugasan approver lembur tidak lengkap atau tidak lagi aktif. Hubungi HR.');
            }

            $actions = $locked->currentApprovalActions();
            $result = OvertimeApprovalRules::decision((int) $assignment->supervisor_id, (int) $assignment->manager_id, (int) $actor->id,
                $actions->map(fn ($action) => ['slot' => $action->decision_slot, 'actor_id' => $action->actor_id, 'action' => $action->action])->values()->all(), $decision);
            $locked->approvalActions()->create([
                'approval_round' => $locked->approvalActions->max('approval_round') ?? 1,
                'decision_slot' => $result['slot'], 'action' => $decision,
                'actor_id' => $actor->id, 'actor_name' => $actor->name,
                'actor_role' => $actor->role instanceof UserRole ? $actor->role->value : $actor->role,
                'notes' => $note, 'acted_at' => now(),
            ]);
            $changes = ['status' => $result['status']];
            if ($decision === 'REJECT') {
                $changes += ['rejected_by_id' => $actor->id, 'rejection_note' => $note];
            }
            $locked->update($changes);
        });
    }

    public function resetIfApproved(OvertimeRequest $overtime, User $actor): void
    {
        if ($overtime->currentApprovalActions()->contains('action', 'APPROVE')) {
            $overtime->approvalActions()->create([
                'approval_round' => ($overtime->approvalActions->max('approval_round') ?? 1) + 1,
                'decision_slot' => null, 'action' => 'RESET', 'actor_id' => $actor->id,
                'actor_name' => $actor->name, 'actor_role' => $actor->role instanceof UserRole ? $actor->role->value : $actor->role,
                'notes' => 'Isi pengajuan diubah; persetujuan diulang.', 'acted_at' => now(),
            ]);
        }
    }
}
