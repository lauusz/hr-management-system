<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =================================================================
// 1. 👑 ROBOT PENGHITUNG CUTI (RESET TAHUN BARU SAJA)
// =================================================================

Artisan::command('leave:update-balances', function () {
    $today = Carbon::now();
    $this->info("🤖 Robot Cuti Berjalan pada: " . $today->format('Y-m-d'));

    // Cek apakah hari ini tanggal 1 Januari?
    // Jika BUKAN 1 Januari, langsung berhenti (Hemat Resource)
    if ($today->format('m-d') !== '01-01') {
        $this->info("📅 Hari ini bukan 1 Januari. Tidak ada reset saldo.");
        return;
    }

    $resetCount = 0;

    // Proses per 100 user agar hemat memori
    User::with('profile')->chunk(100, function ($users) use ($today, &$resetCount) {
        foreach ($users as $user) {
            // Skip jika tidak ada data tanggal bergabung
            if (!$user->profile || !$user->profile->tgl_bergabung) continue;

            $joinDate = Carbon::parse($user->profile->tgl_bergabung);
            
            // Hitung masa kerja dalam tahun
            $yearsWorked = $joinDate->diffInYears($today);

            // SYARAT RESET:
            // 1. Hari ini adalah 1 Januari (Sudah dicek di atas)
            // 2. Masa kerja sudah >= 1 Tahun
            if ($yearsWorked >= 1) {
                
                // Reset saldo jadi 12
                $user->update(['leave_balance' => 12]);
                
                $this->info("🔄 [TAHUN BARU] {$user->name}: Saldo di-reset jadi 12.");
                $resetCount++;
            }
        }
    });

    $this->info("🏁 Selesai. Total User Reset: {$resetCount}");

})->purpose('Update saldo cuti (Khusus Reset Tahunan 1 Januari)');


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

// 1. Robot Cuti: Cek setiap hari jam 00:01 (Tapi cuma kerja pas 1 Jan)
Schedule::command('leave:update-balances')
        ->dailyAt('00:01')
        ->timezone('Asia/Jakarta');

// 2. Robot Backup: Jalan tiap hari jam 23:59
Schedule::command('db:backup')
        ->dailyAt('23:59')
        ->timezone('Asia/Jakarta');