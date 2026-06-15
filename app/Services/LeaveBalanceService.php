<?php

namespace App\Services;

use App\Enums\LeaveType;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class LeaveBalanceService
{
    private const FIVE_DAY_WORK_WEEK_ROLES = ['HRD', 'MANAGER'];

    public function isFiveDayWorkWeekForUser(User $user): bool
    {
        return in_array($this->getRoleString($user), self::FIVE_DAY_WORK_WEEK_ROLES, true);
    }

    public function calculateEffectiveDaysForUser(User $user, Carbon|string $startDate, Carbon|string $endDate): float
    {
        $start = $startDate instanceof Carbon ? $startDate->copy() : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate->copy() : Carbon::parse($endDate);
        $period = CarbonPeriod::create($start, $end);
        $isFiveDayWorkWeek = $this->isFiveDayWorkWeekForUser($user);

        $days = 0.0;

        foreach ($period as $date) {
            if ($isFiveDayWorkWeek) {
                // MANAGER (5-day): skip Saturday AND Sunday
                if ($date->isSaturday() || $date->isSunday()) {
                    continue;
                }
                $days += 1;
            } else {
                // Non-MANAGER (6-day): skip Sunday only, Saturday = 0.5
                if ($date->isSunday()) {
                    continue;
                }
                if ($date->isSaturday()) {
                    $days += 0.5;
                } else {
                    $days += 1;
                }
            }
        }

        return $days;
    }

    public function calculateEffectiveDaysForLeave(LeaveRequest $leave): float
    {
        return $this->calculateEffectiveDaysForUser($leave->user, $leave->start_date, $leave->end_date);
    }

    public function isAnnualLeave(LeaveRequest $leave): bool
    {
        $leaveType = $leave->type instanceof LeaveType ? $leave->type->value : (string) $leave->type;

        return strtoupper($leaveType) === LeaveType::CUTI->value;
    }

    public function deductLeaveBalanceForLeave(LeaveRequest $leave, ?float $amount = null): float
    {
        // Production deduction harus menggunakan LeaveRequest yang sudah tersimpan.
        if (! $leave->exists || ! $leave->id) {
            throw new RuntimeException('Pengajuan cuti belum tersimpan.');
        }

        // Jika HRD memberikan amount eksplisit (via form), proses tanpa cek tipe
        // karena HRD bisa memutuskan potong saldo cuti untuk CUTI_KHUSUS/SAKIT/IZIN/DINAS_LUAR.
        if ($amount === null && ! $this->isAnnualLeave($leave)) {
            return 0;
        }

        return DB::transaction(function () use ($leave, $amount) {
            // Kunci row user untuk mencegah race condition saat membaca dan mengurangi saldo.
            $lockedUser = User::lockForUpdate()->findOrFail($leave->user_id);

            $this->ensureOpeningBalanceLocked($lockedUser);

            $deductKey = "DEDUCT:LEAVE:{$leave->id}";
            $existing = LeaveBalanceTransaction::where('idempotency_key', $deductKey)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return (float) $existing->amount;
            }

            $daysToDeduct = $amount ?? $this->calculateEffectiveDaysForUser($lockedUser, $leave->start_date, $leave->end_date);
            $currentBalance = (float) $lockedUser->leave_balance;

            if ($currentBalance < $daysToDeduct) {
                throw new RuntimeException("Gagal Approve: Saldo cuti tidak cukup. User punya: {$currentBalance}, Butuh (Efektif): {$daysToDeduct} hari.");
            }

            $newBalance = $currentBalance - $daysToDeduct;

            if ($daysToDeduct > 0) {
                $lockedUser->update(['leave_balance' => $newBalance]);
            }

            LeaveBalanceTransaction::create([
                'user_id' => $lockedUser->id,
                'leave_request_id' => $leave->id,
                'transaction_type' => LeaveBalanceTransaction::DEDUCT,
                'amount' => $daysToDeduct,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'description' => "Potong saldo cuti untuk pengajuan #{$leave->id}",
                'idempotency_key' => $deductKey,
                'created_by' => auth()->id(),
            ]);

            return $daysToDeduct;
        });
    }

    public function refundLeaveBalanceForLeave(LeaveRequest $leave, ?float $amount = null): float
    {
        // Production refund harus menggunakan LeaveRequest yang sudah tersimpan.
        if (! $leave->exists || ! $leave->id) {
            throw new RuntimeException('Pengajuan cuti belum tersimpan.');
        }

        return DB::transaction(function () use ($leave, $amount) {
            $lockedUser = User::lockForUpdate()->findOrFail($leave->user_id);

            $this->ensureOpeningBalanceLocked($lockedUser);

            // Refund nominal harus bersumber dari ledger DEDUCT asli jika ada,
            // sehingga perubahan role/tanggal setelah approval tidak memengaruhi
            // nominal pengembalian.
            $deductKey = "DEDUCT:LEAVE:{$leave->id}";
            $deductTransaction = LeaveBalanceTransaction::where('idempotency_key', $deductKey)
                ->lockForUpdate()
                ->first();

            if ($deductTransaction) {
                $daysToRefund = (float) $deductTransaction->amount;
            } else {
                // Fallback hanya untuk CUTI legacy yang belum memiliki ledger DEDUCT.
                if (! $this->isAnnualLeave($leave)) {
                    return 0;
                }

                $daysToRefund = $amount ?? $this->calculateEffectiveDaysForUser($lockedUser, $leave->start_date, $leave->end_date);
            }

            if ($daysToRefund <= 0) {
                return 0;
            }

            $refundKey = "REFUND:LEAVE:{$leave->id}";
            $existingRefund = LeaveBalanceTransaction::where('idempotency_key', $refundKey)
                ->lockForUpdate()
                ->first();

            if ($existingRefund) {
                return (float) $existingRefund->amount;
            }

            $currentBalance = (float) $lockedUser->leave_balance;
            $newBalance = $currentBalance + $daysToRefund;

            $lockedUser->update(['leave_balance' => $newBalance]);

            LeaveBalanceTransaction::create([
                'user_id' => $lockedUser->id,
                'leave_request_id' => $leave->id,
                'transaction_type' => LeaveBalanceTransaction::REFUND,
                'amount' => $daysToRefund,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'description' => "Pengembalian saldo cuti untuk pengajuan #{$leave->id}",
                'idempotency_key' => $refundKey,
                'created_by' => auth()->id(),
            ]);

            return $daysToRefund;
        });
    }

    /**
     * Penyesuaian saldo cuti ke target tertentu.
     * Semua perubahan dilakukan dalam transaction dan mencatat ledger ADJUSTMENT.
     */
    public function adjustBalanceToTarget(
        User $user,
        float $targetBalance,
        ?string $description = null,
        ?string $idempotencyKey = null,
        ?int $createdBy = null,
    ): float {
        return DB::transaction(function () use ($user, $targetBalance, $description, $idempotencyKey, $createdBy) {
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);

            $this->ensureOpeningBalanceLocked($lockedUser);

            if ($idempotencyKey !== null) {
                $existing = LeaveBalanceTransaction::where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return (float) $existing->amount;
                }
            }

            $currentBalance = (float) $lockedUser->leave_balance;

            if (abs($targetBalance - $currentBalance) < 0.0001) {
                // Untuk key eksplisit, buat marker ADJUSTMENT amount 0 agar run berikutnya
                // dengan key yang sama tidak mengganggu saldo yang sudah berubah sejak marker.
                if ($idempotencyKey !== null) {
                    LeaveBalanceTransaction::create([
                        'user_id' => $lockedUser->id,
                        'leave_request_id' => null,
                        'transaction_type' => LeaveBalanceTransaction::ADJUSTMENT,
                        'amount' => 0,
                        'balance_before' => $currentBalance,
                        'balance_after' => $currentBalance,
                        'description' => $description ?? 'Penyesuaian saldo cuti',
                        'idempotency_key' => $idempotencyKey,
                        'created_by' => $createdBy,
                    ]);
                }

                return 0.0;
            }

            $newBalance = $targetBalance;
            $delta = $targetBalance - $currentBalance;
            $amount = abs($delta);

            $lockedUser->update(['leave_balance' => $newBalance]);

            LeaveBalanceTransaction::create([
                'user_id' => $lockedUser->id,
                'leave_request_id' => null,
                'transaction_type' => LeaveBalanceTransaction::ADJUSTMENT,
                'amount' => $amount,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'description' => $description ?? 'Penyesuaian saldo cuti',
                'idempotency_key' => $idempotencyKey ?? (string) Str::uuid(),
                'created_by' => $createdBy,
            ]);

            return $amount;
        });
    }

    /**
     * Buat record opening balance untuk user jika belum ada.
     * Aman dijalankan berulang kali.
     */
    public function ensureOpeningBalance(User $user): ?float
    {
        return DB::transaction(function () use ($user) {
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);

            $openingKey = "OPENING_BALANCE:USER:{$lockedUser->id}";
            $existing = LeaveBalanceTransaction::where('idempotency_key', $openingKey)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return null;
            }

            $balance = (float) $lockedUser->leave_balance;

            LeaveBalanceTransaction::create([
                'user_id' => $lockedUser->id,
                'leave_request_id' => null,
                'transaction_type' => LeaveBalanceTransaction::OPENING_BALANCE,
                'amount' => $balance,
                'balance_before' => $balance,
                'balance_after' => $balance,
                'description' => 'Saldo awal',
                'idempotency_key' => $openingKey,
                'created_by' => null,
            ]);

            return $balance;
        });
    }

    /**
     * Pastikan setiap user memiliki record opening balance sebelum mutasi pertama.
     * Method ini harus dipanggil setelah row user di-lock.
     */
    private function ensureOpeningBalanceLocked(User $lockedUser): void
    {
        $openingKey = "OPENING_BALANCE:USER:{$lockedUser->id}";

        $existing = LeaveBalanceTransaction::where('idempotency_key', $openingKey)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            return;
        }

        $balance = (float) $lockedUser->leave_balance;

        LeaveBalanceTransaction::create([
            'user_id' => $lockedUser->id,
            'leave_request_id' => null,
            'transaction_type' => LeaveBalanceTransaction::OPENING_BALANCE,
            'amount' => $balance,
            'balance_before' => $balance,
            'balance_after' => $balance,
            'description' => 'Saldo awal',
            'idempotency_key' => $openingKey,
            'created_by' => null,
        ]);
    }

    private function getRoleString(User $user): string
    {
        return strtoupper((string) ($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role));
    }
}
