<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalanceTransaction extends Model
{
    use HasFactory;

    public const OPENING_BALANCE = 'OPENING_BALANCE';

    public const DEDUCT = 'DEDUCT';

    public const REFUND = 'REFUND';

    public const ADJUSTMENT = 'ADJUSTMENT';

    protected $fillable = [
        'user_id',
        'leave_request_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'idempotency_key',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'leave_request_id' => 'integer',
            'created_by' => 'integer',
            'amount' => 'float',
            'balance_before' => 'float',
            'balance_after' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
