<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    public const TYPE_SK = 'SK';
    public const TYPE_KONTRAK_KERJA = 'KONTRAK_KERJA';
    public const TYPE_SP = 'SP';
    public const TYPE_MUTASI = 'MUTASI';
    public const TYPE_DEMOSI = 'DEMOSI';
    public const TYPE_ROTASI = 'ROTASI';
    public const TYPE_PROMOSI = 'PROMOSI';
    public const TYPE_EXIT_DOCUMENT = 'EXIT_DOCUMENT';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'file_path',
        'effective_date',
        'expired_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expired_date' => 'date',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_SK,
            self::TYPE_KONTRAK_KERJA,
            self::TYPE_SP,
            self::TYPE_MUTASI,
            self::TYPE_DEMOSI,
            self::TYPE_ROTASI,
            self::TYPE_PROMOSI,
            self::TYPE_EXIT_DOCUMENT,
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_SK => 'SK Karyawan Tetap',
            self::TYPE_KONTRAK_KERJA => 'Kontrak Kerja',
            self::TYPE_SP => 'Surat Peringatan',
            self::TYPE_MUTASI => 'Mutasi',
            self::TYPE_DEMOSI => 'Demosi',
            self::TYPE_ROTASI => 'Rotasi',
            self::TYPE_PROMOSI => 'Promosi',
            self::TYPE_EXIT_DOCUMENT => 'Dokumen Keluar',
            default => $this->type,
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
