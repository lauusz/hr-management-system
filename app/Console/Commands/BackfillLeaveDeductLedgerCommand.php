<?php

namespace App\Console\Commands;

use App\Enums\LeaveType;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Services\LeaveBalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillLeaveDeductLedgerCommand extends Command
{
    protected $signature = 'leave:backfill-deduct-ledger
        {--execute : Tulis ledger DEDUCT historis}
        {--leave-request= : Batasi ke satu ID pengajuan}';

    protected $description = 'Backfill ledger DEDUCT untuk CUTI APPROVED tanpa mengubah saldo user (default dry-run).';

    public function handle(LeaveBalanceService $service): int
    {
        if (! Schema::hasTable('leave_balance_transactions')) {
            $this->error('Tabel leave_balance_transactions tidak ditemukan.');

            return self::FAILURE;
        }

        $query = LeaveRequest::query()
            ->with('user')
            ->where('type', LeaveType::CUTI->value)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereDoesntHave('leaveBalanceTransactions', fn ($ledger) => $ledger
                ->whereColumn('leave_request_id', 'leave_requests.id')
                ->where('idempotency_key', 'like', 'DEDUCT:LEAVE:%'));

        if ($leaveRequestId = $this->option('leave-request')) {
            $query->whereKey((int) $leaveRequestId);
        }

        $leaves = $query->orderBy('id')->get();

        $this->info("Pengajuan yang perlu backfill: {$leaves->count()}");

        if (! $this->option('execute')) {
            $this->warn('Mode DRY-RUN. Tidak ada data yang ditulis. Gunakan --execute setelah backup database.');

            return self::SUCCESS;
        }

        $created = 0;
        foreach ($leaves as $leave) {
            DB::transaction(function () use ($leave, $service, &$created) {
                $lockedLeave = LeaveRequest::with('user')->lockForUpdate()->findOrFail($leave->id);
                $key = "DEDUCT:LEAVE:{$lockedLeave->id}";

                if (LeaveBalanceTransaction::where('idempotency_key', $key)->lockForUpdate()->exists()) {
                    return;
                }

                $service->ensureOpeningBalance($lockedLeave->user);
                $amount = $service->calculateEffectiveDaysForUser(
                    $lockedLeave->user,
                    $lockedLeave->start_date,
                    $lockedLeave->end_date,
                    false,
                );
                $currentBalance = (float) $lockedLeave->user->leave_balance;

                LeaveBalanceTransaction::create([
                    'user_id' => $lockedLeave->user_id,
                    'leave_request_id' => $lockedLeave->id,
                    'transaction_type' => LeaveBalanceTransaction::DEDUCT,
                    'amount' => $amount,
                    'balance_before' => $currentBalance + $amount,
                    'balance_after' => $currentBalance,
                    'description' => "Backfill ledger pemotongan historis untuk pengajuan #{$lockedLeave->id}",
                    'idempotency_key' => $key,
                    'created_by' => null,
                ]);

                $created++;
            });
        }

        $this->info("Ledger DEDUCT berhasil dibuat: {$created}");

        return self::SUCCESS;
    }
}
