<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    use HasFactory;

    protected $table = 'employee_shifts';

    protected $fillable = [
        'user_id',
        'shift_id',
        'location_id',
    ];

    protected $casts = [
        //
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id', 'id');
    }
}
