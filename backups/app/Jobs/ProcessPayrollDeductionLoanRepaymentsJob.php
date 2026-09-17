<?php

namespace App\Jobs;

use App\Services\LoanPayrollDeductionService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Throwable;

class ProcessPayrollDeductionLoanRepaymentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $paidAt = null) {}

    public function handle(LoanPayrollDeductionService $service): void
    {
        $result = $service->process($this->paidAt ? Carbon::parse($this->paidAt) : now());

        $this->appendLog('SUCCESS', $result);
    }

    public function failed(Throwable $throwable): void
    {
        $this->appendLog('FAILED', ['error' => $throwable->getMessage()]);
    }

    private function appendLog(string $status, array $context): void
    {
        $logPath = storage_path('logs/log_loan_auto_repayments.txt');
        File::ensureDirectoryExists(dirname($logPath));

        File::append($logPath, sprintf(
            "[%s] Status: %s | Context: %s%s",
            now()->format('Y-m-d H:i:s'),
            $status,
            json_encode($context),
            PHP_EOL,
        ));
    }
}
