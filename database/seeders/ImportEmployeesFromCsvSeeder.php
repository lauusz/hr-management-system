<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\Division;
use App\Models\Position;
use Carbon\Carbon;

class ImportEmployeesFromCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data-karyawan.csv');
        if (!file_exists($path)) {
            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return;
        }

        $header = fgetcsv($handle, 0, ';');
        if ($header === false) {
            fclose($handle);
            return;
        }

        $header = array_map('trim', $header);
        $now = now();
        $defaultPassword = bcrypt('123456');

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);
            if (!$data) {
                continue;
            }

            $name = trim($data['Name'] ?? '');
            if ($name === '') {
                continue;
            }

            $divisionName = trim($data['divisi'] ?? '');
            $positionName = trim($data['jabatan'] ?? '');

            if ($divisionName === '') {
                $divisionName = 'OPERASIONAL';
            }

            $division = Division::firstOrCreate(
                ['name' => $divisionName],
                ['created_at' => $now, 'updated_at' => $now]
            );

            $position = null;
            if ($positionName !== '') {
                $position = Position::firstOrCreate(
                    [
                        'division_id' => $division->id,
                        'name' => $positionName,
                    ],
                    [
                        'is_active' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $rawEmail = trim($data['email'] ?? '');
            $email = $rawEmail !== '' && strtolower($rawEmail) !== 'tidak ada email'
                ? $rawEmail
                : null;

            $rawPhone = preg_replace('/\D+/', '', (string)($data['mobile_phone'] ?? ''));
            if ($rawPhone === '') {
                $phone = null;
            } else {
                if (str_starts_with($rawPhone, '62')) {
                    $rawPhone = '0' . substr($rawPhone, 2);
                }
                if (str_starts_with($rawPhone, '8')) {
                    $rawPhone = '0' . $rawPhone;
                }
                $phone = $rawPhone;
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => 'EMPLOYEE',
                'division_id' => $division->id,
                'position_id' => $position ? $position->id : null,
                'status' => 'ACTIVE',
                'password' => $defaultPassword,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $tglLahir = $this->parseTanggalLahir($data['Tanggal Lahir'] ?? null);
            $tglBergabung = $this->parseTanggalBergabung($data['Tanggal Bergabung'] ?? null);
            $tglAkhirPercobaan = $this->parseTanggalBergabung($data['Tanggal Berakhir Percobaan'] ?? null);

            EmployeeProfile::create([
                'user_id' => $user->id,
                'pt' => trim($data['PT'] ?? ''),
                'kategori' => trim($data['Kategori'] ?? ''),
                'nik' => trim($data['NIK'] ?? ''),
                'email' => $email,
                'jabatan' => $positionName,
                'kewarganegaraan' => trim($data['Kewarganegaraan'] ?? ''),
                'agama' => trim($data['Agama'] ?? ''),
                'path_kartu_keluarga' => trim($data['Nomor Kartu Keluarga'] ?? ''),
                'path_ktp' => trim($data['Nomor KTP'] ?? ''),
                'nama_bank' => trim($data['Nama Bank'] ?? ''),
                'no_rekening' => trim($data['Nomor Rekening Bank'] ?? ''),
                'pendidikan' => trim($data['Pendidikan Terakhir'] ?? ''),
                'golongan_darah' => trim($data['Golongan Darah'] ?? ''),
                'jenis_kelamin' => trim($data['Jenis kelamin'] ?? ''),
                'tgl_lahir' => $tglLahir,
                'tempat_lahir' => trim($data['Tempat Lahir'] ?? ''),
                'alamat1' => trim($data['Nama Jalan 1'] ?? ''),
                'alamat2' => trim($data['Nama Jalan 2'] ?? ''),
                'provinsi' => trim($data['Provinsi'] ?? ''),
                'kab_kota' => trim($data['Kab/Kota'] ?? ''),
                'kecamatan' => trim($data['Kecamatan'] ?? ''),
                'desa_kelurahan' => trim($data['Desa/Kelurahan'] ?? ''),
                'kode_pos' => trim($data['Kode Pos'] ?? ''),
                'badge_id' => trim($data['Badge ID'] ?? ''),
                'pin' => trim($data['PIN'] ?? ''),
                'ptkp' => trim($data['PTKP'] ?? ''),
                'npwp' => trim($data['NPWP'] ?? ''),
                'nomor_npwp' => trim($data['Nomor NPWP'] ?? ''),
                'bpjs_tk' => trim($data['BPJS TK'] ?? ''),
                'nomor_bpjs_kesehatan' => trim($data['Nomor BPJS Kesehatan'] ?? ''),
                'kelas_bpjs' => trim($data['Kelas BPJS'] ?? ''),
                'masa_kerja' => trim($data['MASA KERJA'] ?? ''),
                'tgl_bergabung' => $tglBergabung,
                'tgl_akhir_percobaan' => $tglAkhirPercobaan,
                'lokasi_kerja' => trim($data['LOKASI KERJA'] ?? ''),
                'alamat_sesuai_ktp' => trim($data['Alamat domisili sama dengan alamat KTP?'] ?? ''),
            ]);
        }

        fclose($handle);
    }

    protected function parseTanggalLahir($value): ?string
    {
        if (!$value) {
            return null;
        }

        $s = trim((string)$value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return $s;
        }

        $bulan = [
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12',
        ];

        $parts = explode(' ', $s);
        if (count($parts) === 3) {
            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $monthName = $parts[1];
            $year = $parts[2];
            $month = $bulan[$monthName] ?? null;
            if ($month) {
                return $year . '-' . $month . '-' . $day;
            }
        }

        return null;
    }

    protected function parseTanggalBergabung($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $s = trim((string)$value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return $s;
        }

        if (is_numeric($s)) {
            $serial = (int)$s;
            if ($serial > 20000) {
                $timestamp = ($serial - 25569) * 86400;
                try {
                    return Carbon::createFromTimestamp($timestamp)->toDateString();
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        return null;
    }
}
