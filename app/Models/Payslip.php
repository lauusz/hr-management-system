<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_month',
        'period_year',
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_makan',
        'fee_marketing',
        'tunjangan_telekomunikasi',
        'tunjangan_penempatan',
        'tunjangan_asuransi',
        'tunjangan_kelancaran',
        'pendapatan_lain',
        'tunjangan_transportasi',
        'lembur',
        'potongan_bpjs_tk',
        'potongan_pph21',
        'potongan_hutang',
        'potongan_bpjs_kes',
        'potongan_terlambat',
        'total_pendapatan',
        'total_potongan',
        'gaji_bersih',
        'sisa_utang',
        'status',
        'created_by'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
