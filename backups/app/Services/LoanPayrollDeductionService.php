<?php

namespace App\Services;

use App\Models\LoanRepayment;
use App\Models\LoanRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanPayrollDeductionService
{
    public function process(?Carbon $paidAt = null): array
    {
        $paidAt ??= now();
        $periodStart = $paidAt->copy()->startOfMonth();
        $periodEnd = $paidAt->copy()->endOfMonth();
        $periodLabel = $paidAt->copy()->locale('id')->translatedFormat('F Y');

        $result = [
            'processed' => 0,
            'skipped_duplicate' => 0,
            'skipped_no_remaining' => 0,
        ];

        LoanRequest::query()
            ->with('repayments')
            ->where('status', 'APPROVED')
            ->where('payment_method', 'POTONG_GAJI')
            ->whereNotNull('monthly_installment')
            ->orderBy('id')
            ->chunkById(100, function ($loans) use ($paidAt, $periodStart, $periodEnd, $periodLabel, &$result): void {
                foreach ($loans as $loan) {
                    DB::transaction(function () use ($loan, $paidAt, $periodStart, $periodEnd, $periodLabel, &$result): void {
                        $loan = LoanRequest::with('repayments')->lockForUpdate()->find($loan->id);

                        if (! $loan || $this->hasAutomaticDeductionForPeriod($loan, $periodStart, $periodEnd)) {
                            $result['skipped_duplicate']++;

                            return;
                        }

                        $totalPaid = (float) $loan->repayments->sum('amount');
                        $remaining = max(0, (float) $loan->amount - $totalPaid);

                        if ($remaining <= 0) {
                            $loan->update(['status' => 'LUNAS']);
                            $result['skipped_no_remaining']++;

                            return;
                        }

                        $amount = min((float) $loan->monthly_installment, $remaining);

                        LoanRepayment::create([
                            'loan_request_id' => $loan->id,
                            'user_id' => null,
                            'paid_at' => $paidAt->toDateString(),
                            'amount' => $amount,
                            'method' => 'POTONG_GAJI',
                            'note' => "Auto potong gaji periode {$periodLabel}",
                        ]);

                        if (($totalPaid + $amount) >= (float) $loan->amount) {
                            $loan->update(['status' => 'LUNAS']);
                        }

                        $result['processed']++;
                    });
                }
            });

        return $result;
    }

    private function hasAutomaticDeductionForPeriod(LoanRequest $loan, Carbon $periodStart, Carbon $periodEnd): bool
    {
        return $loan->repayments()
            ->where('method', 'POTONG_GAJI')
            ->whereBetween('paid_at', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->where('note', 'like', 'Auto potong gaji periode%')
            ->exists();
    }
}
