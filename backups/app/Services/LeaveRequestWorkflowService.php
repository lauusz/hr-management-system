<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;

/**
 * Service reusable untuk workflow LeaveRequest.
 *
 * Semua mutasi status yang memengaruhi saldo cuti dilakukan dalam DB transaction
 * dan dengan locking row LeaveRequest untuk mencegah race condition.
 */
class LeaveRequestWorkflowService
{
    public function __construct(
        protected LeaveBalanceService $leaveBalanceService,
        protected LeaveRequestStateMachine $stateMachine,
    ) {}

    /**
     * Batalkan LeaveRequest dan kembalikan saldo cuti jika perlu.
     *
     * Hanya mengubah status untuk LeaveRequest dengan status:
     * - PENDING_SUPERVISOR
     * - PENDING_HR
     * - STATUS_APPROVED
     *
     * Untuk status APPROVED, refund saldokan dipanggil melalui
     * LeaveBalanceService::refundLeaveBalanceForLeave tanpa filter tipe.
     * Service refund akan menangani refund berdasarkan ledger DEDUCT asli.
     *
     * Status terminal (REJECTED/BATAL) atau duplikat cancel tidak mengubah apa pun.
     *
     * @param  string|array<string>|null  $allowedSourceStatuses  Batasan status sumber yang diizinkan;
     *                                                            null berarti gunakan matrix CANCEL bawaan state machine.
     * @return bool True jika pembatalan berhasil, false jika tidak ada perubahan.
     *
     * @throws \RuntimeException Jika LeaveRequest belum tersimpan.
     */
    public function cancelLeaveRequest(LeaveRequest $leave, User $actor, ?string $reason = null, string|array|null $allowedSourceStatuses = null): bool
    {
        return $this->stateMachine->perform(
            $leave,
            LeaveRequestStateMachine::CANCEL,
            function (LeaveRequest $lockedLeave) use ($actor, $reason) {
                // Refund hanya jika pengajuan pernah di-approve.
                if ($lockedLeave->status === LeaveRequest::STATUS_APPROVED) {
                    $this->leaveBalanceService->refundLeaveBalanceForLeave($lockedLeave);
                }

                $currentNotes = $lockedLeave->notes;
                $systemNote = $this->buildCancelSystemNote($actor, $reason);
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;

                return ['notes' => $newNotes];
            },
            [],
            $allowedSourceStatuses,
        );
    }

    private function buildCancelSystemNote(User $actor, ?string $reason): string
    {
        $role = $actor->role instanceof \App\Enums\UserRole
            ? $actor->role->value
            : (string) $actor->role;
        $roleUpper = strtoupper($role);

        $actorLabel = match ($roleUpper) {
            'HRD', 'HR STAFF' => 'HR',
            'SUPERVISOR', 'MANAGER' => 'Supervisor/Atasan',
            default => 'Pemohon',
        };

        $note = "[System] Dibatalkan oleh {$actorLabel} (".$actor->name.') pada '.now()->format('d M Y H:i');

        if (! empty($reason)) {
            $note .= ' - Alasan: '.$reason;
        }

        return $note;
    }
}
