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
    public function cancelLocked(OvertimeRequest $overtime, User $actor): void
    {
        if ($overtime->status === OvertimeRequest::STATUS_CANCELLED) {
            return;
        }

        $role = $actor->role instanceof UserRole ? $actor->role->value : (string) $actor->role;
        $actorLabel = match (strtoupper($role)) {
            'HRD', 'HR STAFF' => 'HR',
            'SUPERVISOR', 'MANAGER' => 'Supervisor/Atasan',
            default => 'Pemohon',
        };
        $note = '[System] Dibatalkan oleh '.$actorLabel.' ('.$actor->name.') pada '.now()->format('d M Y H:i');

        $overtime->update([
            'status' => OvertimeRequest::STATUS_CANCELLED,
            'system_note' => $overtime->system_note ? $overtime->system_note."\n".$note : $note,
        ]);
    }

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
            if (! $assignment || ! $assignment->supervisor_id) {
                throw new DomainException('Supervisor lembur harus ditetapkan terlebih dahulu.');
            }
            $assignment->load('supervisor');
            if (! $assignment->supervisor?->isActive() || $assignment->supervisor->role !== UserRole::SUPERVISOR
                || (int) $locked->user_id === (int) $assignment->supervisor_id) {
                throw new DomainException('Penugasan supervisor lembur tidak lengkap atau tidak lagi aktif. Hubungi HR.');
            }

            $actions = $locked->currentApprovalActions();
            $result = OvertimeApprovalRules::decision((int) $assignment->supervisor_id, (int) $actor->id,
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
            } else {
                $changes += ['approved_by_supervisor_id' => $actor->id, 'approved_by_supervisor_at' => now()];
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
