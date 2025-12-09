<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'shift_id',
        'late_minutes',
        'location_id',
        'clock_in_at',
        'clock_in_photo',
        'clock_in_photo_deleted_at',
        'clock_in_lat',
        'clock_in_lng',
        'clock_in_distance_m',
        'clock_out_at',
        'clock_out_photo',
        'clock_out_photo_deleted_at',
        'clock_out_lat',
        'clock_out_lng',
        'clock_out_distance_m',
        'status',
        'notes',
    ];

    protected $casts = [
        'date'                      => 'date',
        'clock_in_at'               => 'datetime',
        'clock_out_at'              => 'datetime',
        'clock_in_photo_deleted_at' => 'datetime',
        'clock_out_photo_deleted_at'=> 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function location()
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id');
    }
}
