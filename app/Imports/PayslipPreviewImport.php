<?php

namespace App\Imports;

use App\Models\EmployeeProfile;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PayslipPreviewImport implements ToArray, WithStartRow
{
    // 1. BUAT KANTONG PENAMPUNGAN DI SINI
    public $mappedData = [];

    public function startRow(): int
    {
        return 11;
    }

    public function array(array $array)
    {
        foreach ($array as $row) {
            $name = trim($row[2] ?? ''); // Index 2 is Name (Column C)
            $nik = trim($row[3] ?? ''); // Index 3 is NIK (Column D)

            // Skip if both Name and NIK are empty
            if (empty($name) && empty($nik)) {
                continue;
            }

            // Find User using strict logic: Try to match both if both exist.
            $user = null;

            if (!empty($name) && !empty($nik)) {
                // Try to find the user matching both exactly
                $user = \App\Models\User::where('name', $name)->whereHas('profile', function ($q) use ($nik) {
                    $q->where('nik', $nik);
                })->first();
            }

            // Fallback 1: Match by NIK only (if name mismatch or empty)
            if (!$user && !empty($nik)) {
                $profile = \App\Models\EmployeeProfile::where('nik', $nik)->first();
                if ($profile) {
                    $user = $profile->user;
                }
            }

            // Fallback 2: Match by Name only (if NIK mismatch or empty)
            if (!$user && !empty($name)) {
                $user = \App\Models\User::where('name', $name)->first();
            }

            // Skip if user not found at all
            if (!$user) {
                continue;
            }

            // Map Excel columns to Payslip attributes
            $formattedData = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'nik' => $user->profile?->nik ?? $row[3] ?? '-', // Get NIK from profile or Excel

                // Income (Kolom H - R / Index 7 - 17)
                'gaji_pokok'               => $this->cleanCurrency($row[7] ?? 0),
                'tunjangan_jabatan'        => $this->cleanCurrency($row[8] ?? 0),
                'tunjangan_makan'          => $this->cleanCurrency($row[9] ?? 0),
                'fee_marketing'            => $this->cleanCurrency($row[10] ?? 0),
                'tunjangan_telekomunikasi' => $this->cleanCurrency($row[11] ?? 0),
                'tunjangan_penempatan'     => $this->cleanCurrency($row[12] ?? 0),
                'tunjangan_asuransi'       => $this->cleanCurrency($row[13] ?? 0),
                'tunjangan_kelancaran'     => $this->cleanCurrency($row[14] ?? 0),
                'pendapatan_lain'          => $this->cleanCurrency($row[15] ?? 0),
                'tunjangan_transportasi'   => $this->cleanCurrency($row[16] ?? 0),
                'lembur'                   => $this->cleanCurrency($row[17] ?? 0),

                // Deductions (Kolom T - X / Index 19 - 23)
                'potongan_bpjs_tk'         => $this->cleanCurrency($row[19] ?? 0),
                'potongan_pph21'           => $this->cleanCurrency($row[20] ?? 0),
                'potongan_hutang'          => $this->cleanCurrency($row[21] ?? 0),
                'potongan_bpjs_kes'        => $this->cleanCurrency($row[22] ?? 0),
                'potongan_terlambat'       => $this->cleanCurrency($row[23] ?? 0),

                // Tambahan (Kolom AA / Index 26)
                'sisa_utang'               => $this->cleanText($row[26] ?? ''),
            ];

            // Calculate totals
            $totalPendapatan =
                $formattedData['gaji_pokok'] +
                $formattedData['tunjangan_jabatan'] +
                $formattedData['tunjangan_makan'] +
                $formattedData['fee_marketing'] +
                $formattedData['tunjangan_telekomunikasi'] +
                $formattedData['tunjangan_penempatan'] +
                $formattedData['tunjangan_asuransi'] +
                $formattedData['tunjangan_kelancaran'] +
                $formattedData['pendapatan_lain'] +
                $formattedData['tunjangan_transportasi'] +
                $formattedData['lembur'];

            $totalPotongan =
                $formattedData['potongan_bpjs_tk'] +
                $formattedData['potongan_pph21'] +
                $formattedData['potongan_hutang'] +
                $formattedData['potongan_bpjs_kes'] +
                $formattedData['potongan_terlambat'];

            $formattedData['total_pendapatan'] = $totalPendapatan;
            $formattedData['total_potongan'] = $totalPotongan;
            $formattedData['gaji_bersih'] = $totalPendapatan - $totalPotongan;

            // 2. MASUKKAN KE DALAM KANTONG (Bukan di-return)
            $this->mappedData[] = $formattedData;
        }
    }

    private function cleanCurrency($value)
    {
        if (is_numeric($value)) return (float) $value;
        if (empty($value)) return 0;

        $value = trim($value);
        if ($value === '-' || $value === 'Rp-' || $value === 'Rp -') return 0;

        $value = str_replace(['Rp', ' '], '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    private function cleanText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
