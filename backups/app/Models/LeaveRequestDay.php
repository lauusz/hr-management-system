<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestDay extends Model
{
    use HasFactory;

    public const NONE = 'NONE';

    public const MEAL_ALLOWANCE = 'MEAL_ALLOWANCE';

    public const LEAVE_BALANCE = 'LEAVE_BALANCE';

    protected $fillable = [
        'leave_request_id',
        'leave_date',
        'treatment',
        'deduction_amount',
        'decided_by',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'leave_request_id' => 'integer',
            'leave_date' => 'date',
            'deduction_amount' => 'float',
            'decided_by' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
