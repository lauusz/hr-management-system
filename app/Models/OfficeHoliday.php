<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeHoliday extends Model
{
    use HasFactory;

    public const TYPE_NATIONAL = 'NATIONAL';

    public const TYPE_COMPANY = 'COMPANY';

    public const TYPE_COLLECTIVE = 'COLLECTIVE';

    public const TYPES = [
        self::TYPE_NATIONAL => 'Libur Nasional',
        self::TYPE_COMPANY => 'Libur Perusahaan',
        self::TYPE_COLLECTIVE => 'Cuti Bersama',
    ];

    protected $fillable = [
        'holiday_date',
        'name',
        'type',
        'deducts_leave',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'deducts_leave' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
