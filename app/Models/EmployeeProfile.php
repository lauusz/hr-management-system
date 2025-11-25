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
        'no_kartu_keluarga',
        'no_ktp',
        'nama_bank',
        'no_rekening',
        'pendidikan',
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
        'ptkp',
        'no_npwp',
        'bpjs_tk',
        'no_bpjs_kesehatan',
        'kelas_bpjs',
        'masa_kerja',
        'tgl_bergabung',
        'tgl_berakhir_percobaan',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'tgl_bergabung' => 'date',
        'tgl_berakhir_percobaan' => 'date',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
