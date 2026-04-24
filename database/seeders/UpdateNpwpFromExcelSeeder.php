<?php

namespace Database\Seeders;

use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateNpwpFromExcelSeeder extends Seeder
{
    public function run(): void
    {
        $excelPath = base_path('backups/DATA ALL KARYAWAN.xlsx');

        if (!file_exists($excelPath)) {
            $this->command->error('File Excel tidak ditemukan: ' . $excelPath);
            return;
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Skip header rows (baris 1-6) dan mulai dari row 7 (NO 1)
        $dataRows = array_slice($rows, 6);

        $updated = 0;
        $skipped = 0;
        $notFound = 0;
        $corrupted = 0;

        $this->command->info('Memulai update NPWP dari Excel...');

        foreach ($dataRows as $row) {
            if (empty($row[0])) {
                continue; // skip empty rows
            }

            $no = $row[0];
            $name = trim((string) ($row[1] ?? ''));
            $npwpExcel = trim((string) ($row[32] ?? '')); // Kolom AH = index 32 (Nomor NPWP)
            $nikExcel = trim((string) ($row[4] ?? ''));   // Kolom E = index 4 (NIK)

            if (empty($name)) {
                continue;
            }

            // Cari user berdasarkan nama
            $user = User::where('name', $name)->first();

            if (!$user) {
                $this->command->warn("  [SKIP] '$name' (NIK Excel: $nikExcel) - tidak ditemukan di database");
                $skipped++;
                continue;
            }

            $profile = EmployeeProfile::where('user_id', $user->id)->first();

            if (!$profile) {
                $this->command->warn("  [SKIP] '$name' - profile tidak ditemukan");
                $skipped++;
                continue;
            }

            // Cek apakah NPWP corrupted (scientific notation)
            $isCorrupted = (str_contains(strtoupper($npwpExcel), 'E') || str_contains($npwpExcel, ','));

            if ($isCorrupted) {
                $this->command->warn("  [CORRUPTED] '$name' - NPWP: '$npwpExcel' (scientific notation, tidak bisa di-recover)");
                $corrupted++;
                // Tetap update dengan nilai corrupted agar terlihat
                $profile->nomor_npwp = $npwpExcel;
                $profile->save();
                $updated++;
                continue;
            }

            if (empty($npwpExcel) || $npwpExcel === '-' || $npwpExcel === 'tidak ada NPWP') {
                $this->command->info("  [KOSONG] '$name' - NPWP kosong/tidak ada di Excel, skip");
                $skipped++;
                continue;
            }

            // Valid NPWP - update
            $oldNpwp = $profile->nomor_npwp;
            $profile->nomor_npwp = $npwpExcel;
            $profile->save();
            $updated++;
            $this->command->info("  [OK] '$name' - NPWP: '$oldNpwp' → '$npwpExcel'");
        }

        $this->command->info('');
        $this->command->info('=== Hasil Update NPWP ===');
        $this->command->info("Updated : $updated");
        $this->command->info("Skipped : $skipped");
        $this->command->info("Corrupted: $corrupted");
        $this->command->info("Total diproses: " . ($updated + $skipped));
    }
}
