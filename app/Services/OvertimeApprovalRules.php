<?php

namespace App\Services;

use DomainException;

class OvertimeApprovalRules
{
    public static function decision(?int $supervisor, int $actor, array $actions, string $decision): array
    {
        if (! $supervisor) {
            throw new DomainException('Supervisor lembur harus ditetapkan terlebih dahulu.');
        }

        if ($actor !== $supervisor) {
            throw new DomainException('Hanya supervisor yang ditugaskan yang dapat mengambil keputusan lembur.');
        }
        foreach ($actions as $action) {
            if ($action['slot'] === 'SUPERVISOR' || (int) $action['actor_id'] === $actor) {
                throw new DomainException('Supervisor telah memberikan keputusan untuk pengajuan ini.');
            }
        }

        return [
            'slot' => 'SUPERVISOR',
            'status' => $decision === 'REJECT' ? 'REJECTED' : 'APPROVED_SUPERVISOR',
        ];
    }
}
