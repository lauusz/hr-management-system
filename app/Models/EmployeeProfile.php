<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'pt',
        'kategori',
        'nik',
        'work_email',
        'jabatan',
        'kewarganegaraan',
        'agama',
        'path_kartu_keluarga',
        'path_ktp',
        'nama_bank',
        'no_rekening',
        'pendidikan',
        'golongan_darah',
        'jenis_kelamin',
        'tgl_lahir',
        'tempat_lahir',
        'alamat1',
        'alamat2',
        'provinsi',
        'kab_kota',
        'kecamatan',
        'desa_kelurahan',
        'kode_pos',
        'badge_id',
        'pin',
        'ptkp',
        'npwp',
        'nomor_npwp',
        'bpjs_tk',
        'nomor_bpjs_kesehatan',
        'kelas_bpjs',
        'masa_kerja',
        'tgl_bergabung',
        'tgl_akhir_percobaan',
        'lokasi_kerja',
        'alamat_sesuai_ktp',

        'exit_date',
        'exit_reason_code',
        'exit_reason_note',
    ];


    protected $casts = [
        'tgl_lahir' => 'date',
        'tgl_bergabung' => 'date',
        'tgl_akhir_percobaan' => 'date',
        'exit_date' => 'date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pt()
    {
        return $this->belongsTo(Pt::class, 'pt_id');
    }
}
