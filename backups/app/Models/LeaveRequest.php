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
        'off_spv_period_id',
        'type',
        'special_leave_category',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'reason',
        'photo',
        'status',
        'notes',
        'notes_hrd', // [BARU] Kolom untuk alasan penolakan HRD
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
        'deduct_um', // [BARU] Flag potong uang makan
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
        'deduct_um' => 'boolean',
    ];

    public const PENDING_SUPERVISOR = 'PENDING_SUPERVISOR';

    public const PENDING_HR = 'PENDING_HR';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_CANCELLED = 'BATAL';

    public const MAX_EVIDENCE_FILES = 3;

    public const STATUS_OPTIONS = [
        self::PENDING_SUPERVISOR => 'Menunggu Atasan',
        self::PENDING_HR => 'Menunggu HRD',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
        'CANCEL_REQ' => 'Pengajuan Batal',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    public function getStatusLabelAttribute(): string
    {
        // Custom Label untuk HRD yang sudah diapprove
        if ($this->status === self::STATUS_APPROVED && $this->user) {
            $roleVal = $this->user->role instanceof \App\Enums\UserRole ? $this->user->role->value : $this->user->role;
            if (in_array(strtoupper((string) $roleVal), ['HRD', 'HR MANAGER'])) {
                return 'Disetujui';
            }
        }

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

    public function offSpvPeriod()
    {
        return $this->belongsTo(OffSpvPeriod::class);
    }

    public function scopePendingSupervisor($q)
    {
        return $q->where('status', self::PENDING_SUPERVISOR);
    }

    public function scopePendingHr($q)
    {
        return $q->where('status', self::PENDING_HR);
    }

    public function leaveBalanceTransactions()
    {
        return $this->hasMany(LeaveBalanceTransaction::class);
    }

    public function days()
    {
        return $this->hasMany(LeaveRequestDay::class)->orderBy('leave_date');
    }

    public function attachments()
    {
        return $this->hasMany(LeaveRequestAttachment::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function approvalActions()
    {
        return $this->hasMany(LeaveRequestApprovalAction::class)
            ->orderBy('acted_at')
            ->orderBy('id');
    }

    /**
     * Jumlah total bukti pendukung: baris attachments + kolom photo legacy
     * (bila belum ter-backfill ke attachments).
     */
    public function evidenceFileCount(): int
    {
        return count($this->evidenceFiles());
    }

    /**
     * Daftar gabungan bukti pendukung: attachments + kolom photo legacy.
     * Dedupe by file_name agar foto lama yang sudah di-backfill tidak tampil dobel.
     *
     * Tiap item: ['name', 'original_name', 'is_image', 'attachment_id', 'is_legacy']
     *
     * @return array<int, array{name: string, original_name: string, is_image: bool, attachment_id: ?int, is_legacy: bool}>
     */
    public function evidenceFiles(): array
    {
        $files = [];
        $seen = [];

        foreach ($this->attachments as $attachment) {
            $seen[$attachment->file_name] = true;
            $files[] = [
                'name' => $attachment->file_name,
                'original_name' => $attachment->original_name ?: $attachment->file_name,
                'is_image' => $attachment->is_image,
                'attachment_id' => $attachment->id,
                'is_legacy' => false,
            ];
        }

        $legacyPhoto = $this->photo;
        if ($legacyPhoto && ! isset($seen[$legacyPhoto])) {
            $extension = strtolower(pathinfo($legacyPhoto, PATHINFO_EXTENSION));
            $files[] = [
                'name' => $legacyPhoto,
                'original_name' => $legacyPhoto,
                'is_image' => in_array($extension, LeaveRequestAttachment::IMAGE_EXTENSIONS, true),
                'attachment_id' => null,
                'is_legacy' => true,
            ];
        }

        return $files;
    }
}
