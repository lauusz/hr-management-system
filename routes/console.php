<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =================================================================
// 1. ROBOT PENGHITUNG CUTI
// =================================================================

Artisan::command('leave:update-balances', function () {
    $today = Carbon::now()->startOfDay();
    $isNewYear = $today->format('m-d') === '01-01';
    $this->info('🤖 Robot Cuti Berjalan pada: '.$today->format('Y-m-d'));

    // Di luar 1 Januari, reset tahunan dilewati tetapi anniversary
    // pertama tetap diperiksa.
    if (! $isNewYear) {
        $this->info('📅 Hari ini bukan 1 Januari. Tidak ada reset saldo.');
    }

    $resetCount = 0;
    $firstYearGrantCount = 0;
    $service = app(LeaveBalanceService::class);

    // Proses per 100 user agar hemat memori
    User::query()
        ->active()
        ->with('profile')
        ->chunkById(100, function ($users) use (
            $today,
            $isNewYear,
            &$resetCount,
            &$firstYearGrantCount,
            $service
        ) {
            foreach ($users as $user) {
                // Skip jika tidak ada data tanggal bergabung
                if (! $user->profile || ! $user->profile->tgl_bergabung) {
                    continue;
                }

                $joinDate = Carbon::parse($user->profile->tgl_bergabung)->startOfDay();
                if ($joinDate->gt($today)) {
                    continue;
                }

                $firstAnniversary = $joinDate->copy()->addYearNoOverflow();

                // Hak cuti pertama diberikan tepat saat genap satu tahun.
                // Kebijakan prorata: 12 dikurangi nomor bulan anniversary.
                if ($firstAnniversary->isSameDay($today)) {
                    $targetBalance = max(0, 12 - (int) $firstAnniversary->month);
                    $grantKey = "FIRST_YEAR_PRORATA:USER:{$user->id}:ANNIVERSARY:{$firstAnniversary->year}";

                    $service->adjustBalanceToTarget(
                        $user,
                        $targetBalance,
                        "Hak cuti pertama prorata tahun {$firstAnniversary->year}",
                        $grantKey,
                        null,
                    );

                    $this->info("{$user->name}: hak cuti pertama ditetapkan menjadi {$targetBalance} hari.");
                    $firstYearGrantCount++;

                    // Pada 1 Januari, hak cuti pertama tidak boleh langsung ditimpa
                    // oleh reset tahunan 12 hari.
                    continue;
                }

                // Reset tahunan hanya untuk karyawan yang sudah melewati anniversary
                // pertama sebelum 1 Januari tahun berjalan.
                if ($isNewYear && $firstAnniversary->lt($today)) {
                    $resetKey = "ANNUAL_RESET:USER:{$user->id}:YEAR:{$today->year}";

                    $adjusted = $service->adjustBalanceToTarget(
                        $user,
                        12,
                        "Reset saldo cuti tahun {$today->year}",
                        $resetKey,
                        null,
                    );

                    if ($adjusted > 0) {
                        $this->info("🔄 [TAHUN BARU] {$user->name}: Saldo di-reset jadi 12.");
                        $resetCount++;
                    }
                }
            }
        });

    $this->info("🏁 Selesai. Total User Reset: {$resetCount}");

    $this->info("Total hak cuti pertama diproses: {$firstYearGrantCount}");
})->purpose('Berikan hak cuti pertama prorata dan reset saldo cuti tahunan');

// =================================================================
// 2. 🛡️ ROBOT AUTO BACKUP (MODE OVERWRITE - HEMAT STORAGE)
// =================================================================

Artisan::command('db:backup', function () {
    $this->info('📦 Memulai proses backup database (Mode Overwrite)...');

    $dbName = config('database.connections.mysql.database');
    $username = config('database.connections.mysql.username');
    $password = config('database.connections.mysql.password');
    $host = config('database.connections.mysql.host');

    // Folder & File Backup
    $folderPath = storage_path('app/backups');
    $fileName   = 'backup-latest.sql';
    $fullPath   = $folderPath . DIRECTORY_SEPARATOR . $fileName;

    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0755, true);
    }

    // Deteksi Path mysqldump (Windows/XAMPP vs Linux)
    $dumpBinary = 'mysqldump';
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $xamppPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe'; // Sesuaikan jika perlu
        if (file_exists($xamppPath)) {
            $dumpBinary = "\"$xamppPath\"";
        }
    }

    // Eksekusi Export
    $passwordPart = !empty($password) ? "--password=\"$password\"" : "";
    $command = "$dumpBinary --user=\"{$username}\" {$passwordPart} --host=\"{$host}\" {$dbName} > \"{$fullPath}\"";

    $output = null;
    $resultCode = null;
    exec($command, $output, $resultCode);

    if ($resultCode === 0 && file_exists($fullPath) && filesize($fullPath) > 0) {
        $this->info("✅ BACKUP SUKSES! File diperbarui:");
        $this->line("📂 {$fullPath}");
    } else {
        $this->error("❌ BACKUP GAGAL.");
    }

})->purpose('Backup database (Overwrite Mode)');


// =================================================================
// JADWAL GLOBAL (SCHEDULER)
// =================================================================

// 1. Robot Cuti: cek anniversary setiap hari dan reset tahunan pada 1 Januari.
Schedule::command('leave:update-balances')
        ->dailyAt('00:01')
        ->timezone('Asia/Jakarta');

// 2. Robot Backup: Jalan tiap hari jam 23:59
Schedule::command('db:backup')
        ->dailyAt('23:59')
        ->timezone('Asia/Jakarta');
