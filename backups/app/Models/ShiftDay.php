<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_holiday',
        'note',
    ];

    protected $casts = [
        'is_holiday' => 'boolean',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function getDayNameAttribute(): string
    {
        $names = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $names[$this->day_of_week] ?? '';
    }

    public function getStartTimeLabelAttribute(): string
    {
        if (!$this->start_time) {
            return '-';
        }

        return substr($this->start_time, 0, 5);
    }

    public function getEndTimeLabelAttribute(): string
    {
        if (!$this->end_time) {
            return '-';
        }

        return substr($this->end_time, 0, 5);
    }
}
