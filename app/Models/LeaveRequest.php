<?php

namespace App\Models;

use App\Enums\LeaveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'special_leave_category', // [BARU] Ditambahkan agar kategori cuti khusus tersimpan
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'reason',
        'photo',
        'status',
        'notes',
        'latitude',
        'longitude',
        'accuracy_m',
        'location_captured_at',
        'approved_by',
        'approved_at',
        'supervisor_ack_at',
        
        // Data PIC Pengganti
        'substitute_pic',
        'substitute_phone',
    ];

    protected $casts = [
        'type' => LeaveType::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
        'supervisor_ack_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy_m' => 'float',
        'location_captured_at' => 'datetime',
    ];

    public const PENDING_SUPERVISOR = 'PENDING_SUPERVISOR';
    public const PENDING_HR = 'PENDING_HR';

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_OPTIONS = [
        self::PENDING_SUPERVISOR => 'Menunggu Atasan',
        self::PENDING_HR => 'Menunggu HRD',
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_OPTIONS[$this->status] ?? $this->status;
    }

    public function setTypeAttribute($value): void
    {
        $this->attributes['type'] = is_string($value) ? strtoupper($value) : $value;
    }

    public function getTypeLabelAttribute(): string
    {
        if ($this->type instanceof LeaveType) {
            return $this->type->label();
        }

        return LeaveType::tryFrom((string) $this->type)?->label() ?? 'Tidak diketahui';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePendingSupervisor($q)
    {
        return $q->where('status', self::PENDING_SUPERVISOR);
    }

    public function scopePendingHr($q)
    {
        return $q->where('status', self::PENDING_HR);
    }
}