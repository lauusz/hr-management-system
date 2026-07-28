<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffSpvChange extends Model
{
    public const TYPE_INITIALIZED = 'INITIALIZED';

    public const TYPE_MANUAL_ADJUSTMENT = 'MANUAL_ADJUSTMENT';

    public $timestamps = false;

    protected $fillable = [
        'off_spv_period_id',
        'change_type',
        'quota_before',
        'quota_after',
        'reason',
        'changed_by',
        'created_at',
    ];

    protected $casts = [
        'quota_before' => 'integer',
        'quota_after' => 'integer',
        'created_at' => 'datetime',
    ];

    public function period()
    {
        return $this->belongsTo(OffSpvPeriod::class, 'off_spv_period_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
