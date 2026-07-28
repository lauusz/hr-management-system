<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function days()
    {
        return $this->hasMany(ShiftDay::class);
    }

    public function patternDays()
    {
        return $this->hasMany(ShiftDay::class);
    }

    public function schedules()
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function getDaysCountAttribute(): int
    {
        return $this->days()->count();
    }
}
