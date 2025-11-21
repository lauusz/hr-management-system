<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function getStartTimeLabelAttribute(): string {
        return $this->start_time ? $this->start_time->format('H:i') : '-';
    }

    public function getEndTimeLabelAttribute(): string{
        return $this->end_time ? $this->end_time->format('H:i') : '-';
    }

    public function schedules(){
        return $this->hasMany(EmployeeShift::class);
    }

    public function attendances() {
        return $this->hasMany(Attendance::class);
    }
}
