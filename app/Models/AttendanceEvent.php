<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceEvent extends Model
{
    use HasFactory;

    public const CLOCK_IN = 'CLOCK_IN';

    public const CLOCK_OUT = 'CLOCK_OUT';

    protected $fillable = [
        'user_id',
        'event_type',
        'type',
        'occurred_at',
        'location_id',
        'photo',
        'photo_deleted_at',
        'latitude',
        'longitude',
        'distance_m',
        'approval_status',
        'rejection_note',
        'approved_by',
        'notes',
        'legacy_attendance_id',
        'legacy_side',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'photo_deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AttendanceLocation::class);
    }
}
