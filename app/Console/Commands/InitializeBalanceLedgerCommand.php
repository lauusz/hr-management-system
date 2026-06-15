<?php

namespace App\Console\Commands;

use App\Models\LeaveBalanceTransaction;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class InitializeBalanceLedgerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:initialize-balance-ledger {--execute : Jalankan inisialisasi opening balance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inisialisasi opening balance ledger untuk semua user (default dry-run).';

    /**
     * Execute the console command.
     */
    public function handle(LeaveBalanceService $service): int
    {
        if (! Schema::hasTable('leave_balance_transactions')) {
            $this->error('Tabel leave_balance_transactions tidak ditemukan. Inisialisasi dibatalkan.');

            return Command::FAILURE;
        }

        $totalUsers = User::count();
        $hasOpeningUserIds = LeaveBalanceTransaction::where('idempotency_key', 'like', 'OPENING_BALANCE:USER:%')
            ->pluck('user_id')
            ->all();
        $toCreate = User::whereNotIn('id', $hasOpeningUserIds)->count();
        $totalSnapshotBalance = (float) (User::sum('leave_balance') ?? 0);

        $this->info('=== Inisialisasi Opening Balance Ledger ===');
        $this->info("Total user: {$totalUsers}");
        $this->info('Sudah punya opening balance: '.count($hasOpeningUserIds));
        $this->info("Akan dibuat opening balance: {$toCreate}");
        $this->info("Total snapshot saldo cuti: {$totalSnapshotBalance}");

        if (! $this->option('execute')) {
            $this->warn('Mode DRY-RUN. Tidak ada data yang ditulis. Gunakan --execute untuk menulis.');

            return Command::SUCCESS;
        }

        $created = 0;

        User::chunk(100, function ($users) use ($service, &$created) {
            foreach ($users as $user) {
                if ($service->ensureOpeningBalance($user) !== null) {
                    $created++;
                }
            }
        });

        $this->info("Opening balance berhasil dibuat untuk {$created} user.");

        return Command::SUCCESS;
    }
}
