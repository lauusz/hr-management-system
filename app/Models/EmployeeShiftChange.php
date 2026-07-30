<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShiftChange extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPLIED = 'APPLIED';

    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'user_id',
        'shift_id',
        'location_id',
        'effective_date',
        'status',
        'pending_slot',
        'created_by',
        'shift_name_snapshot',
        'location_name_snapshot',
        'applied_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'applied_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

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
        return $this->belongsTo(AttendanceLocation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
