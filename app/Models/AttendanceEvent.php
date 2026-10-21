<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'is_flexible',
        'is_outside_reference_location',
        'legacy_attendance_id',
        'legacy_side',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'photo_deleted_at' => 'datetime',
        'is_flexible' => 'boolean',
        'is_outside_reference_location' => 'boolean',
    ];

    public function scopePendingApproval(Builder $query): void
    {
        $query->where('approval_status', 'PENDING')
            ->where(function (Builder $query): void {
                $query->where('type', 'DINAS_LUAR')
                    ->orWhere(function (Builder $query): void {
                        $query->where('type', 'WFO')
                            ->where('is_flexible', true)
                            ->where(function (Builder $query): void {
                                $query->whereNull('is_outside_reference_location')
                                    ->orWhere('is_outside_reference_location', true);
                            });
                    });
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AttendanceLocation::class);
    }
}
