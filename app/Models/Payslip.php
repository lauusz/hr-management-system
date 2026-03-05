<?php

namespace App\Models;

use App\Helpers\CompanyAssetHelper;
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
        'bonus_bulanan',
        'tunjangan_telekomunikasi',
        'tunjangan_lainnya',
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

    /**
     * Get the absolute path for the logo.
     */
    public function getLogoPath(?string $ptNameOverride = null): ?string
    {
        $ptName = $ptNameOverride ?: ($this->user->profile->pt->name ?? null);
        if (!$ptName) return null;

        $filename = CompanyAssetHelper::getLogo($ptName);
        return $filename ? public_path('images/' . $filename) : null;
    }

    /**
     * Get the absolute path for the stamp.
     */
    public function getStampPath(?string $ptNameOverride = null): ?string
    {
        $ptName = $ptNameOverride ?: ($this->user->profile->pt->name ?? null);
        if (!$ptName) return null;

        $filename = CompanyAssetHelper::getStamp($ptName);
        return $filename ? public_path('images/' . $filename) : null;
    }

    /**
     * Get the public URL for the logo.
     */
    public function getLogoUrl(?string $ptNameOverride = null): ?string
    {
        $ptName = $ptNameOverride ?: ($this->user->profile->pt->name ?? null);
        if (!$ptName) return null;

        $filename = CompanyAssetHelper::getLogo($ptName);
        return $filename ? asset('images/' . $filename) : null;
    }

    /**
     * Get the public URL for the stamp.
     */
    public function getStampUrl(?string $ptNameOverride = null): ?string
    {
        $ptName = $ptNameOverride ?: ($this->user->profile->pt->name ?? null);
        if (!$ptName) return null;

        $filename = CompanyAssetHelper::getStamp($ptName);
        return $filename ? asset('images/' . $filename) : null;
    }

    /**
     * Get the base64 encoded logo for PDF.
     */
    public function getLogoBase64(?string $ptNameOverride = null): ?string
    {
        $path = $this->getLogoPath($ptNameOverride);
        if ($path && file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    /**
     * Get the base64 encoded stamp for PDF.
     */
    public function getStampBase64(?string $ptNameOverride = null): ?string
    {
        $path = $this->getStampPath($ptNameOverride);
        if ($path && file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    public function getDisplaySisaUtangAttribute(): string
    {
        if ($this->sisa_utang === null) {
            return '-';
        }

        $text = trim((string) $this->sisa_utang);
        if ($text === '') {
            return '-';
        }

        $normalized = preg_replace('/\s+/', '', $text);
        $normalized = str_ireplace('rp', '', $normalized);

        if (preg_match('/^0+([.,]0+)?$/', $normalized)) {
            return '-';
        }

        return $text;
    }
}
