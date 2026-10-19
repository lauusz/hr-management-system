<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OvertimeRequest extends Model
{
    use HasFactory;

    protected $table = 'overtime_requests';

    protected $fillable = [
        'user_id',
        'overtime_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'description',
        'status',
        'approved_by_supervisor_id',
        'approved_by_supervisor_at',
        'approved_by_hrd_id',
        'approved_by_hrd_at',
        'rejected_by_id',
        'rejection_note',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'approved_by_supervisor_at' => 'datetime',
        'approved_by_hrd_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status Constants
    public const STATUS_PENDING_SUPERVISOR = 'PENDING_SUPERVISOR';
    public const STATUS_APPROVED_SUPERVISOR = 'APPROVED_SUPERVISOR';
    public const STATUS_APPROVED_HRD = 'APPROVED_HRD';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_CANCELLED = 'CANCELLED';

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(OvertimeRequestAttachment::class);
    }

    public function approvalActions()
    {
        return $this->hasMany(OvertimeApprovalAction::class)->orderBy('id');
    }

    public function currentApprovalActions()
    {
        $actions = $this->approvalActions;
        $round = $actions->max('approval_round') ?? 1;

        return $actions->where('approval_round', $round)->whereIn('action', ['APPROVE', 'REJECT']);
    }

    public function canViewEvidence(User $viewer): bool
    {
        if ((int) $viewer->id === (int) $this->user_id
            || ($this->user?->direct_supervisor_id !== null && (int) $viewer->id === (int) $this->user->direct_supervisor_id)
            || in_array($viewer->role, [UserRole::HRD, UserRole::HR_STAFF], true)) {
            return true;
        }

        $assignment = $this->user?->overtimeApproverAssignment;
        return ($assignment && in_array((int) $viewer->id, [(int) $assignment->supervisor_id, (int) $assignment->manager_id], true))
            || $this->approvalActions->whereIn('action', ['APPROVE', 'REJECT'])->contains('actor_id', $viewer->id);
    }

    public function supervisorApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_supervisor_id');
    }

    public function hrdApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_hrd_id');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by_id');
    }

    /**
     * Accessors & Mutators
     */
    public function getDurationHumanAttribute()
    {
        $minutes = abs($this->duration_minutes); // Ensure absolute value
        $hours = floor($minutes / 60);
        $min = $minutes % 60;
        
        if ($hours > 0 && $min > 0) {
            return "{$hours} jam {$min} menit";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        } else {
            return "{$min} menit";
        }
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING_SUPERVISOR => 'Menunggu SPV dan Manager',
            self::STATUS_APPROVED_SUPERVISOR => 'Disetujui',
            self::STATUS_APPROVED_HRD => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Unknown',
        };
    }

    /**
     * Status label khusus tampilan HRD (read-only recap flow).
     */
    public function getHrdStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING_SUPERVISOR => 'Menunggu SPV dan Manager',
            self::STATUS_APPROVED_SUPERVISOR => 'Disetujui',
            self::STATUS_APPROVED_HRD => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Unknown',
        };
    }

    public function getHrdStatusColorAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING_SUPERVISOR => 'warning',
            self::STATUS_APPROVED_SUPERVISOR => 'success',
            self::STATUS_APPROVED_HRD => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING_SUPERVISOR => 'warning',
            self::STATUS_APPROVED_SUPERVISOR => 'info',
            self::STATUS_APPROVED_HRD => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }
}
