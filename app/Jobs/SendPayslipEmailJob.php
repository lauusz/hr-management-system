<?php

namespace App\Jobs;

use App\Mail\PayslipPublishedMail;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Satu-satunya lapisan antrean untuk email slip gaji. PayslipPublishedMail dikirim langsung
 * (bukan ShouldQueue) di dalam job ini.
 *
 * Status log (storage/logs/log_email_terkirim.txt):
 *  - SCHEDULED : job berhasil dimasukkan ke antrean (dicatat controller).
 *  - SUCCESS   : email berhasil DISERAHKAN ke transport mail. Bukan jaminan masuk inbox.
 *  - SKIPPED   : dilewati dengan alasan (slip berubah/kembali DRAFT/penerima bergeser/dll), tidak dikirim.
 *  - FAILED    : exception saat render/kirim; job gagal (tidak dicoba ulang otomatis, lihat $tries).
 */
class SendPayslipEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Satu percobaan: kegagalan transport bisa berarti email sebenarnya sudah terkirim sebagian,
     * sehingga percobaan ulang otomatis berisiko mengirim ganda. Kirim ulang dilakukan HR secara sengaja.
     */
    public int $tries = 1;

    /**
     * Metadata baru sengaja berupa properti ber-default (bukan parameter promosi wajib), supaya payload
     * lama yang sudah ada di antrean (tanpa properti ini) tetap dapat di-unserialize tanpa error.
     */
    public ?string $fingerprint = null;
    public ?string $operationId = null;

    public function __construct(
        public int $payslipId,
        public string $employeeName,
        public string $email,
        public ?string $ptName = null,
        public bool $thrOnly = false,
        public int $scheduledDelaySeconds = 0,
        ?string $fingerprint = null,
        ?string $operationId = null,
    ) {
        $this->fingerprint = $fingerprint;
        $this->operationId = $operationId;
    }

    public function handle(): void
    {
        $payslip = Payslip::with(['user.profile.pt', 'user.position'])->find($this->payslipId);

        $skipReason = $this->skipReason($payslip);
        if ($skipReason !== null) {
            $this->appendLog('SKIPPED', $skipReason);
            return;
        }

        // Render PDF + kirim langsung di job ini. Exception sengaja tidak ditelan: queue menandai job gagal
        // dan failed() mencatat FAILED satu kali.
        $mail = new PayslipPublishedMail($payslip, $this->ptName);
        $mail->thrOnly = $this->thrOnly;
        Mail::to(trim($this->email))->send($mail);

        // Pengiriman sudah diserahkan ke transport. Kegagalan menulis log tidak boleh membuat job gagal/terulang.
        $this->appendLog('SUCCESS');
    }

    public function failed(Throwable $throwable): void
    {
        $this->appendLog('FAILED', $throwable->getMessage());
    }

    /** Alasan slip tidak boleh dikirim saat ini, atau null bila boleh. */
    private function skipReason(?Payslip $payslip): ?string
    {
        if (!$payslip) {
            return 'Slip tidak ditemukan (sudah dihapus).';
        }

        if ($payslip->status !== 'PUBLISHED') {
            return 'Slip tidak lagi berstatus PUBLISHED.';
        }

        // Payload lama (sudah di antrean sebelum perbaikan) tidak punya fingerprint: pemeriksaan revisi
        // tidak bisa dilakukan, sehingga hanya pemeriksaan status/penerima yang berlaku.
        if ($this->fingerprint !== null && !hash_equals($this->fingerprint, $payslip->revisionFingerprint())) {
            return 'Isi slip berubah sejak dijadwalkan; gunakan kirim ulang untuk versi terbaru.';
        }

        $user = $payslip->user;
        if (!$user || $user->status !== User::STATUS_ACTIVE) {
            return 'Karyawan tidak lagi berstatus ACTIVE.';
        }

        if (strcasecmp(trim((string) $user->email), trim($this->email)) !== 0 || trim($this->email) === '') {
            return 'Email penerima berubah sejak dijadwalkan.';
        }

        return null;
    }

    /** Mencatat status penjadwalan (dipanggil controller setelah job masuk antrean). */
    public static function logScheduled(string $employeeName, string $email, int $delaySeconds, ?string $operationId = null): void
    {
        self::writeLog($employeeName, $email, $delaySeconds, 'SCHEDULED', $operationId ? 'op=' . $operationId : null);
    }

    private function appendLog(string $status, ?string $errorMessage = null): void
    {
        self::writeLog($this->employeeName, $this->email, $this->scheduledDelaySeconds, $status, $errorMessage);
    }

    private static function writeLog(string $employeeName, string $email, int $delay, string $status, ?string $note): void
    {
        try {
            $logPath = storage_path('logs/log_email_terkirim.txt');
            File::ensureDirectoryExists(dirname($logPath));

            $safeNote = $note !== null && trim($note) !== ''
                ? preg_replace('/\s+/', ' ', trim($note))
                : '-';

            $line = sprintf(
                "[%s] Nama: %s | Email: %s | Delay: %ss | Status: %s | Error: %s%s",
                now()->format('Y-m-d H:i:s'),
                $employeeName,
                $email,
                $delay,
                $status,
                $safeNote,
                PHP_EOL,
            );

            File::append($logPath, $line);
        } catch (Throwable $logError) {
            // Kegagalan log bukan kegagalan pengiriman; jangan memicu job gagal atau kirim ulang.
            Log::warning('Gagal menulis log_email_terkirim', ['message' => $logError->getMessage()]);
        }
    }
}
