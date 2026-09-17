<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkRequestItem extends Model
{
    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'atk_request_id',
        'atk_item_id',
        'qty',
        'item_name_snapshot',
        'unit_name_snapshot',
        'unit_size_snapshot',
        'content_unit_name_snapshot',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_size_snapshot' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function request()
    {
        return $this->belongsTo(AtkRequest::class, 'atk_request_id');
    }

    public function item()
    {
        return $this->belongsTo(AtkItem::class, 'atk_item_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
