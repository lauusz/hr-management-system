<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * State machine terpusat untuk LeaveRequest berbasis action.
 *
 * Tidak mengandung business logic approval/authorization/saldo -
 * service ini hanya mengatur transisi status yang sah dan menyediakan
 * mekanisme lock + atomic write melalui perform().
 */
class LeaveRequestStateMachine
{
    public const FORWARD_TO_HR = 'FORWARD_TO_HR';

    public const APPROVE = 'APPROVE';

    public const REJECT = 'REJECT';

    public const CANCEL = 'CANCEL';

    public const REVISE_FOR_HR = 'REVISE_FOR_HR';

    public const EDIT_PENDING = 'EDIT_PENDING';

    public const EDIT_APPROVED_DATE = 'EDIT_APPROVED_DATE';

    private const TRANSITIONS = [
        self::FORWARD_TO_HR => [
            LeaveRequest::PENDING_SUPERVISOR => LeaveRequest::PENDING_HR,
        ],
        self::APPROVE => [
            LeaveRequest::PENDING_SUPERVISOR => LeaveRequest::STATUS_APPROVED,
            LeaveRequest::PENDING_HR => LeaveRequest::STATUS_APPROVED,
        ],
        self::REJECT => [
            LeaveRequest::PENDING_SUPERVISOR => LeaveRequest::STATUS_REJECTED,
            LeaveRequest::PENDING_HR => LeaveRequest::STATUS_REJECTED,
        ],
        self::CANCEL => [
            LeaveRequest::PENDING_SUPERVISOR => LeaveRequest::STATUS_CANCELLED,
            LeaveRequest::PENDING_HR => LeaveRequest::STATUS_CANCELLED,
            LeaveRequest::STATUS_APPROVED => LeaveRequest::STATUS_CANCELLED,
        ],
        self::REVISE_FOR_HR => [
            LeaveRequest::PENDING_SUPERVISOR => LeaveRequest::PENDING_HR,
            LeaveRequest::PENDING_HR => LeaveRequest::PENDING_HR,
        ],
        self::EDIT_PENDING => [
            LeaveRequest::PENDING_SUPERVISOR => LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR => LeaveRequest::PENDING_HR,
        ],
        self::EDIT_APPROVED_DATE => [
            LeaveRequest::STATUS_APPROVED => LeaveRequest::STATUS_APPROVED,
        ],
    ];

    /**
     * Cek apakah sebuah action boleh dijalankan dari status tertentu.
     */
    public function canPerform(string $status, string $action): bool
    {
        return array_key_exists($action, self::TRANSITIONS)
            && array_key_exists($status, self::TRANSITIONS[$action]);
    }

    /**
     * Dapatkan target status untuk pasangan (status, action).
     * Mengembalikan null jika transisi tidak sah.
     */
    public function getTargetStatus(string $status, string $action): ?string
    {
        return self::TRANSITIONS[$action][$status] ?? null;
    }

    /**
     * Dapatkan semua action yang sah dari sebuah status.
     */
    public function getAllowedActions(string $status): array
    {
        $actions = [];

        foreach (self::TRANSITIONS as $action => $map) {
            if (array_key_exists($status, $map)) {
                $actions[] = $action;
            }
        }

        return $actions;
    }

    /**
     * Dapatkan daftar semua action yang didefinisikan.
     *
     * @return string[]
     */
    public function getActions(): array
    {
        return array_keys(self::TRANSITIONS);
    }

    /**
     * Jalankan action pada LeaveRequest yang sudah tersimpan secara atomik.
     *
     * Proses:
     * 1. Buka DB transaction.
     * 2. Lock row LeaveRequest dengan lockForUpdate.
     * 3. Cek ulang action terhadap status terkunci.
     * 4. Jalankan callback (jika ada) dengan model terkunci.
     * 5. Tulis target status + atribut hasil callback + atribut tambahan.
     * 6. Jalankan afterUpdate hook (jika ada) dengan model yang sudah di-update.
     *
     * Jika status terkunci tidak memperbolehkan action, atau jika
     * allowedSourceStatuses diberikan dan status terkunci tidak termasuk
     * di dalamnya, method mengembalikan false tanpa mutasi dan tanpa
     * menjalankan callback. Parameter allowedSourceStatuses dapat berupa
     * string tunggal, array of string, atau null (tidak dicek).
     * Exception dari callback atau afterUpdate akan memicu rollback.
     *
     * @param  callable(LeaveRequest, array<string, mixed>): array<string, mixed>|null  $callback
     * @param  string|array<string>|null  $allowedSourceStatuses
     * @param  callable(LeaveRequest): void  $afterUpdate
     *
     * @throws RuntimeException Jika LeaveRequest belum tersimpan atau callback
     *                          mengembalikan tipe selain array/null.
     */
    public function perform(
        LeaveRequest $leave,
        string $action,
        ?callable $callback = null,
        array $attributes = [],
        string|array|null $allowedSourceStatuses = null,
        ?callable $afterUpdate = null,
    ): bool {
        if (! $leave->exists || ! $leave->id) {
            throw new RuntimeException('Pengajuan cuti belum tersimpan.');
        }

        return DB::transaction(function () use ($leave, $action, $callback, $attributes, $allowedSourceStatuses, $afterUpdate) {
            $lockedLeave = LeaveRequest::lockForUpdate()->findOrFail($leave->id);

            if ($allowedSourceStatuses !== null) {
                $allowedStatuses = is_array($allowedSourceStatuses)
                    ? $allowedSourceStatuses
                    : [$allowedSourceStatuses];

                if (! in_array($lockedLeave->status, $allowedStatuses, true)) {
                    return false;
                }
            }

            $targetStatus = $this->getTargetStatus($lockedLeave->status, $action);

            if ($targetStatus === null) {
                return false;
            }

            $callbackAttributes = [];
            if ($callback !== null) {
                $callbackAttributes = $callback($lockedLeave, $attributes);

                if ($callbackAttributes !== null && ! is_array($callbackAttributes)) {
                    throw new RuntimeException('Callback state machine harus mengembalikan array atau null.');
                }

                $callbackAttributes ??= [];
            }

            // Target status adalah otoritas tunggal; jangan biarkan callback
            // atau attributes menimpa dengan status arbitrer.
            $update = array_merge(
                ['status' => $targetStatus],
                $attributes,
                $callbackAttributes,
            );
            $update['status'] = $targetStatus;

            $lockedLeave->update($update);

            if ($afterUpdate !== null) {
                $afterUpdate($lockedLeave);
            }

            return true;
        });
    }
}
