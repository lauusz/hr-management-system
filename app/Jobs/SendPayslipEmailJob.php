<?php

namespace App\Jobs;

use App\Mail\PayslipPublishedMail;
use App\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPayslipEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $payslipId,
        public string $employeeName,
        public string $email,
        public ?string $ptName = null,
        public bool $thrOnly = false,
        public int $scheduledDelaySeconds = 0,
    ) {
    }

    public function handle(): void
    {
        try {
            $payslip = Payslip::with([
                'user.profile.pt',
                'user.position',
            ])->find($this->payslipId);

            if (!$payslip) {
                $this->appendLog('FAILED', 'Payslip tidak ditemukan.');
                return;
            }

            $mail = new PayslipPublishedMail($payslip, $this->ptName);
            $mail->thrOnly = $this->thrOnly;

            Mail::to($this->email)->send($mail);

            $this->appendLog('SUCCESS');
        } catch (Throwable $throwable) {
            $this->appendLog('FAILED', $throwable->getMessage());
        }
    }

    public function failed(Throwable $throwable): void
    {
        $this->appendLog('FAILED', $throwable->getMessage());
    }

    private function appendLog(string $status, ?string $errorMessage = null): void
    {
        $logPath = storage_path('logs/log_email_terkirim.txt');
        File::ensureDirectoryExists(dirname($logPath));

        $safeErrorMessage = $errorMessage !== null && trim($errorMessage) !== ''
            ? preg_replace('/\s+/', ' ', trim($errorMessage))
            : '-';

        $line = sprintf(
            "[%s] Nama: %s | Email: %s | Delay: %ss | Status: %s | Error: %s%s",
            now()->format('Y-m-d H:i:s'),
            $this->employeeName,
            $this->email,
            $this->scheduledDelaySeconds,
            $status,
            $safeErrorMessage,
            PHP_EOL,
        );

        File::append($logPath, $line);
    }
}