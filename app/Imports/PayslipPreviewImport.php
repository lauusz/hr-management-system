<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PayslipPreviewImport implements ToArray, WithStartRow, WithMultipleSheets
{
    public $mappedData = [];
    public $unmatchedRows = [];

    public function startRow(): int
    {
        return 11;
    }

    public function sheets(): array
    {
        // Force import to read only first worksheet (index 0), regardless of sheet name.
        return [
            0 => $this,
        ];
    }

    public function array(array $array)
    {
        $activeUsers = User::query()
            ->active()
            ->with('profile:id,user_id,nik')
            ->get(['id', 'name', 'email']);

        $userByName = [];
        $userByNik = [];

        foreach ($activeUsers as $activeUser) {
            $normalizedUserName = $this->normalizeLookupValue($activeUser->name);
            if ($normalizedUserName !== '' && !isset($userByName[$normalizedUserName])) {
                $userByName[$normalizedUserName] = $activeUser;
            }

            $normalizedUserNik = $this->normalizeLookupValue((string) ($activeUser->profile?->nik ?? ''));
            if ($normalizedUserNik !== '' && !isset($userByNik[$normalizedUserNik])) {
                $userByNik[$normalizedUserNik] = $activeUser;
            }
        }

        $tempUserIds = [];
        $tempUnmatched = [];

        foreach ($array as $index => $row) {
            $name = trim((string) ($row[2] ?? '')); // Index 2 is Name (Column C)
            $nik = trim((string) ($row[3] ?? '')); // Index 3 is NIK (Column D)

            // Skip if both Name and NIK are empty
            if (empty($name) && empty($nik)) {
                continue;
            }

            $user = null;
            $normalizedName = $this->normalizeLookupValue($name);
            $normalizedNik = $this->normalizeLookupValue($nik);

            // Rule: if name found, stop here and never check NIK.
            if ($normalizedName !== '' && isset($userByName[$normalizedName])) {
                $user = $userByName[$normalizedName];
            }

            // Only fallback to NIK when name is not found.
            if (!$user && $normalizedNik !== '') {
                $user = $userByNik[$normalizedNik] ?? null;
            }

            if (!$user) {
                $unmatchedKey = $normalizedName !== '' ? ('name:' . $normalizedName) : ('nik:' . $normalizedNik);

                if (!isset($tempUnmatched[$unmatchedKey])) {
                    $tempUnmatched[$unmatchedKey] = [
                        'row' => $this->startRow() + $index,
                        'name' => $name,
                        'nik' => $nik,
                    ];
                }

                continue;
            }

            // Prevent duplicate rows in preview for the same employee.
            if (isset($tempUserIds[$user->id])) {
                continue;
            }
            $tempUserIds[$user->id] = true;

            // Map Excel columns to Payslip attributes
            $formattedData = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email,
                'nik' => $user->profile?->nik ?? $row[3] ?? '-', // Get NIK from profile or Excel

                // Income (Kolom H - V / Index 7 - 21)
                'gaji_pokok'               => $this->cleanCurrency($row[7] ?? 0),
                'tunjangan_jabatan'        => $this->cleanCurrency($row[8] ?? 0),
                'tunjangan_makan'          => $this->cleanCurrency($row[9] ?? 0),
                'fee_marketing'            => $this->cleanCurrency($row[10] ?? 0),
                'bonus_bulanan'            => $this->cleanCurrency($row[11] ?? 0),
                'tunjangan_telekomunikasi' => $this->cleanCurrency($row[12] ?? 0),
                'tunjangan_lainnya'        => $this->cleanCurrency($row[13] ?? 0),
                'tunjangan_penempatan'     => $this->cleanCurrency($row[14] ?? 0),
                'tunjangan_asuransi'       => $this->cleanCurrency($row[15] ?? 0),
                'tunjangan_kelancaran'     => $this->cleanCurrency($row[16] ?? 0),
                'pendapatan_lain'          => $this->cleanCurrency($row[17] ?? 0),
                'tunjangan_transportasi'   => $this->cleanCurrency($row[18] ?? 0),
                'lembur'                   => $this->cleanCurrency($row[19] ?? 0),
                'thr'                      => $this->cleanCurrency($row[20] ?? 0),
                'bonus'                    => $this->cleanCurrency($row[21] ?? 0),

                // Deductions (Kolom X - AB / Index 23 - 27)
                'potongan_bpjs_tk'         => $this->cleanCurrency($row[23] ?? 0),
                'potongan_pph21'           => $this->cleanCurrency($row[24] ?? 0),
                'potongan_hutang'          => $this->cleanCurrency($row[25] ?? 0),
                'potongan_bpjs_kes'        => $this->cleanCurrency($row[26] ?? 0),
                'potongan_terlambat'       => $this->cleanCurrency($row[27] ?? 0),

                // Tambahan (Kolom AE / Index 30)
                'sisa_utang'               => $this->cleanText($row[30] ?? ''),
            ];

            // Calculate totals
            $totalPendapatan =
                $formattedData['gaji_pokok'] +
                $formattedData['tunjangan_jabatan'] +
                $formattedData['tunjangan_makan'] +
                $formattedData['fee_marketing'] +
                $formattedData['bonus_bulanan'] +
                $formattedData['tunjangan_telekomunikasi'] +
                $formattedData['tunjangan_lainnya'] +
                $formattedData['tunjangan_penempatan'] +
                $formattedData['tunjangan_asuransi'] +
                $formattedData['tunjangan_kelancaran'] +
                $formattedData['pendapatan_lain'] +
                $formattedData['tunjangan_transportasi'] +
                $formattedData['lembur'] +
                $formattedData['thr'] +
                $formattedData['bonus'];

            $totalPotongan =
                $formattedData['potongan_bpjs_tk'] +
                $formattedData['potongan_pph21'] +
                $formattedData['potongan_hutang'] +
                $formattedData['potongan_bpjs_kes'] +
                $formattedData['potongan_terlambat'];

            $formattedData['total_pendapatan'] = $totalPendapatan;
            $formattedData['total_potongan'] = $totalPotongan;
            $formattedData['gaji_bersih'] = $totalPendapatan - $totalPotongan;

            $this->mappedData[] = $formattedData;
        }

        $this->unmatchedRows = array_values($tempUnmatched);
    }

    private function normalizeLookupValue(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized ?? '';
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

        if ($text === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/', '', $text);
        $normalized = str_ireplace('rp', '', $normalized);

        if (preg_match('/^0+([.,]0+)?$/', $normalized)) {
            return null;
        }

        return $text;
    }
}
