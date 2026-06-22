<?php

namespace App\Console\Commands;

use App\Enums\LeaveType;
use App\Models\Attendance;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Laporan read-only untuk audit kondisi saldo cuti dan ledger
 * selama fase paralel leave_requests + leave_balance_transactions.
 */
class LeaveBalanceAuditReportCommand extends Command
{
    protected $signature = 'leave:audit-report';

    protected $description = 'Tampilkan laporan audit leave balance (read-only).';

    public function handle(): int
    {
        $this->line('');
        $this->line('# ===========================================');
        $this->line('# Leave Balance Audit Report');
        $this->line('# ===========================================');
        $this->line('');

        $this->reportLedgerStatus();
        $this->reportApprovedCutiWithoutDeduct();
        $this->reportOrphanCancelRequests();
        $this->reportEmployeeShiftCoverage();
        $this->reportOpenAttendances();
        $this->reportSimpleReconciliation();

        $this->line('');
        $this->line('# -------------------------------------------');
        $this->line('# Catatan');
        $this->line('# -------------------------------------------');
        $this->line('* Laporan ini read-only. Tidak ada data yang diubah.');
        $this->line('* Gunakan `leave:initialize-balance-ledger --execute` untuk mengisi opening balance.');
        $this->line('* Gunakan `leave:backfill-deduct-ledger --execute` (jika tersedia) untuk backfill DEDUCT legacy.');
        $this->line('');

        return Command::SUCCESS;
    }

    private function reportLedgerStatus(): void
    {
        $this->line('## 1. Status Ledger (leave_balance_transactions)');
        $this->line('');

        $totalUsers = User::count();
        $openingBalanceCount = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::OPENING_BALANCE)->count();
        $usersWithoutOpening = $totalUsers - $openingBalanceCount;

        $deductCount = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::DEDUCT)->count();
        $refundCount = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)->count();
        $adjustmentCount = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::ADJUSTMENT)->count();
        $totalTransactions = LeaveBalanceTransaction::count();

        $this->line('| Metrik | Nilai | Status |');
        $this->line('| --- | --- | --- |');
        $this->line("| Total user | {$totalUsers} | - |");
        $this->line("| User dengan opening balance | {$openingBalanceCount} | ".($usersWithoutOpening === 0 ? '✅ Lengkap' : "⚠️ Kurang {$usersWithoutOpening}").' |');
        $this->line("| Ledger DEDUCT | {$deductCount} | - |");
        $this->line("| Ledger REFUND | {$refundCount} | - |");
        $this->line("| Ledger ADJUSTMENT | {$adjustmentCount} | - |");
        $this->line("| Total transaksi | {$totalTransactions} | - |");
        $this->line('');
    }

    private function reportApprovedCutiWithoutDeduct(): void
    {
        $this->line('## 2. APPROVED CUTI tanpa Ledger DEDUCT');
        $this->line('');

        $cutiType = LeaveType::CUTI->value;

        $approvedCuti = LeaveRequest::where('leave_requests.status', LeaveRequest::STATUS_APPROVED)
            ->where('leave_requests.type', $cutiType)
            ->count();

        $approvedCutiWithoutDeduct = LeaveRequest::where('leave_requests.status', LeaveRequest::STATUS_APPROVED)
            ->where('leave_requests.type', $cutiType)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('leave_balance_transactions')
                    ->whereColumn('leave_balance_transactions.leave_request_id', 'leave_requests.id')
                    ->where('leave_balance_transactions.transaction_type', LeaveBalanceTransaction::DEDUCT);
            })
            ->count();

        $this->line('| Metrik | Nilai | Status |');
        $this->line('| --- | --- | --- |');
        $this->line("| Total APPROVED CUTI | {$approvedCuti} | - |");
        $this->line("| APPROVED CUTI tanpa DEDUCT | {$approvedCutiWithoutDeduct} | ".($approvedCutiWithoutDeduct === 0 ? '✅ Aman' : '⚠️ Perlu backfill').' |');
        $this->line('');

        if ($approvedCutiWithoutDeduct > 0) {
            $grouped = LeaveRequest::where('leave_requests.status', LeaveRequest::STATUS_APPROVED)
                ->where('leave_requests.type', $cutiType)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('leave_balance_transactions')
                        ->whereColumn('leave_balance_transactions.leave_request_id', 'leave_requests.id')
                        ->where('leave_balance_transactions.transaction_type', LeaveBalanceTransaction::DEDUCT);
                })
                ->select('leave_requests.user_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(users.leave_balance) as user_balance'))
                ->join('users', 'users.id', '=', 'leave_requests.user_id')
                ->groupBy('leave_requests.user_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            if ($grouped->isNotEmpty()) {
                $this->line('### Top 10 User dengan APPROVED CUTI tanpa DEDUCT');
                $this->line('');
                $this->line('| User ID | Jumlah CUTI tanpa Ledger | Saldo User Saat Ini |');
                $this->line('| --- | --- | --- |');
                foreach ($grouped as $row) {
                    $this->line("| {$row->user_id} | {$row->total} | {$row->user_balance} |");
                }
                $this->line('');
            }
        }
    }

    private function reportOrphanCancelRequests(): void
    {
        $this->line('## 3. Record dengan Status CANCEL_REQ (Orphan)');
        $this->line('');

        $cancelReqCount = LeaveRequest::where('status', 'CANCEL_REQ')->count();

        $this->line('| Metrik | Nilai | Status |');
        $this->line('| --- | --- | --- |');
        $this->line("| Total CANCEL_REQ | {$cancelReqCount} | ".($cancelReqCount === 0 ? '✅ Tidak ada' : '⚠️ Perlu ditangani manual').' |');
        $this->line('');
    }

    private function reportEmployeeShiftCoverage(): void
    {
        $this->line('## 4. Cakupan Employee Shift & Shift ID');
        $this->line('');

        $activeUsers = User::where('status', 'ACTIVE')->count();
        $usersWithoutEmployeeShift = User::where('status', 'ACTIVE')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('employee_shifts')
                    ->whereColumn('employee_shifts.user_id', 'users.id');
            })
            ->count();

        $usersWithoutShiftId = User::where('status', 'ACTIVE')
            ->whereNull('shift_id')
            ->count();

        $this->line('| Metrik | Nilai | Status |');
        $this->line('| --- | --- | --- |');
        $this->line("| User aktif | {$activeUsers} | - |");
        $this->line("| Aktif tanpa employee_shifts | {$usersWithoutEmployeeShift} | ".($usersWithoutEmployeeShift === 0 ? '✅ Lengkap' : '⚠️ Perlu diperbaiki').' |');
        $this->line("| Aktif tanpa shift_id | {$usersWithoutShiftId} | ".($usersWithoutShiftId === 0 ? '✅ Lengkap' : '⚠️ Perlu diperbaiki').' |');
        $this->line('');
    }

    private function reportOpenAttendances(): void
    {
        $this->line('## 5. Sesi Attendance Terbuka (Clock In tanpa Clock Out)');
        $this->line('');

        $openSessions = Attendance::where('completion_status', Attendance::COMPLETION_OPEN)
            ->orWhere(function ($query) {
                $query->whereNotNull('clock_in_at')
                    ->whereNull('clock_out_at');
            })
            ->count();

        $this->line('| Metrik | Nilai | Status |');
        $this->line('| --- | --- | --- |');
        $this->line("| Sesi terbuka | {$openSessions} | ".($openSessions === 0 ? '✅ Aman' : '⚠️ Perlu dicek').' |');
        $this->line('');
    }

    private function reportSimpleReconciliation(): void
    {
        $this->line('## 6. Rekonsiliasi Sederhana (Ledger vs users.leave_balance)');
        $this->line('');

        if (LeaveBalanceTransaction::count() === 0) {
            $this->line('⚠️  Ledger masih kosong. Jalankan `leave:initialize-balance-ledger --execute` terlebih dahulu.');
            $this->line('');

            return;
        }

        $mismatches = DB::table('users')
            ->leftJoinSub(
                DB::table('leave_balance_transactions')
                    ->select('user_id', DB::raw("SUM(CASE WHEN transaction_type = 'DEDUCT' THEN amount ELSE 0 END) as total_deduct"))
                    ->selectRaw("SUM(CASE WHEN transaction_type = 'REFUND' THEN amount ELSE 0 END) as total_refund")
                    ->selectRaw("SUM(CASE WHEN transaction_type = 'ADJUSTMENT' THEN amount ELSE 0 END) as total_adjustment")
                    ->selectRaw("SUM(CASE WHEN transaction_type = 'OPENING_BALANCE' THEN amount ELSE 0 END) as total_opening")
                    ->groupBy('user_id'),
                'ledger',
                'ledger.user_id',
                '=',
                'users.id'
            )
            ->whereRaw('COALESCE(ledger.total_opening, 0) - COALESCE(ledger.total_deduct, 0) + COALESCE(ledger.total_refund, 0) + COALESCE(ledger.total_adjustment, 0) <> users.leave_balance')
            ->select('users.id', 'users.name', 'users.leave_balance', 'ledger.total_opening', 'ledger.total_deduct', 'ledger.total_refund', 'ledger.total_adjustment')
            ->orderByDesc('users.id')
            ->limit(20)
            ->get();

        $this->line('| Metrik | Nilai | Status |');
        $this->line('| --- | --- | --- |');
        $this->line("| User dengan ketidakcocokan | {$mismatches->count()} | ".($mismatches->isEmpty() ? '✅ Konsisten' : '⚠️ Perlu investigasi').' |');
        $this->line('');

        if ($mismatches->isNotEmpty()) {
            $this->line('### Contoh User dengan Ketidakcocokan (maks 20)');
            $this->line('');
            $this->line('| User ID | Nama | Saldo User | Opening | Deduct | Refund | Adjust |');
            $this->line('| --- | --- | --- | --- | --- | --- | --- |');
            foreach ($mismatches as $row) {
                $name = $row->name ?? '-';
                $opening = $row->total_opening ?? 0;
                $deduct = $row->total_deduct ?? 0;
                $refund = $row->total_refund ?? 0;
                $adjustment = $row->total_adjustment ?? 0;
                $this->line("| {$row->id} | {$name} | {$row->leave_balance} | {$opening} | {$deduct} | {$refund} | {$adjustment} |");
            }
            $this->line('');
        }
    }
}
