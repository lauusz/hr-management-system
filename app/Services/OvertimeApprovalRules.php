<?php

namespace App\Services;

use DomainException;

class OvertimeApprovalRules
{
    public static function decision(?int $supervisor, ?int $manager, int $actor, array $actions, string $decision): array
    {
        if (! $supervisor || ! $manager || $supervisor === $manager) {
            throw new DomainException('Supervisor dan manager lembur harus ditetapkan terlebih dahulu.');
        }

        $slot = $actor === $supervisor ? 'SUPERVISOR' : ($actor === $manager ? 'MANAGER' : null);
        if (! $slot) {
            throw new DomainException('Anda bukan approver lembur yang ditugaskan.');
        }
        foreach ($actions as $action) {
            if ($action['slot'] === $slot || (int) $action['actor_id'] === $actor) {
                throw new DomainException('Keputusan sudah diberikan atau satu orang tidak boleh memutuskan kedua posisi. Hubungi HR untuk mengatur approver lain.');
            }
        }

        return [
            'slot' => $slot,
            'status' => $decision === 'REJECT' ? 'REJECTED' : (count($actions) === 1 ? 'APPROVED_SUPERVISOR' : 'PENDING_SUPERVISOR'),
        ];
    }
}
