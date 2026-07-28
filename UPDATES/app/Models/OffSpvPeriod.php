<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffSpvPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_year',
        'period_month',
        'period_start',
        'period_end',
        'saturday_count',
        'base_quota',
        'effective_quota',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'period_year' => 'integer',
        'period_month' => 'integer',
        'saturday_count' => 'integer',
        'base_quota' => 'integer',
        'effective_quota' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function changes()
    {
        return $this->hasMany(OffSpvChange::class);
    }
}
