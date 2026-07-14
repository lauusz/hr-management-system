<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkRequest extends Model
{
    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_PARTIAL = 'PARTIAL';

    protected $fillable = [
        'request_number',
        'user_id',
        'user_name_snapshot',
        'pt_id',
        'pt_name_snapshot',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pt()
    {
        return $this->belongsTo(Pt::class);
    }

    public function items()
    {
        return $this->hasMany(AtkRequestItem::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Rangkum status request dari status item-itemnya.
     * Dipanggil saat finalisasi review admin.
     *
     * - semua APPROVED  -> APPROVED
     * - semua REJECTED  -> REJECTED
     * - campuran         -> PARTIAL
     * - masih ada PENDING -> PENDING (tidak boleh difinalisasi)
     */
    public function refreshStatusFromItems(): string
    {
        $statuses = $this->items->pluck('status')->unique();

        if ($statuses->contains(AtkRequestItem::STATUS_PENDING)) {
            return self::STATUS_PENDING;
        }

        $allApproved = $statuses->count() === 1 && $statuses->first() === AtkRequestItem::STATUS_APPROVED;
        $allRejected = $statuses->count() === 1 && $statuses->first() === AtkRequestItem::STATUS_REJECTED;

        if ($allApproved) {
            return self::STATUS_APPROVED;
        }

        if ($allRejected) {
            return self::STATUS_REJECTED;
        }

        return self::STATUS_PARTIAL;
    }
}
