<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvPesonaRequestItem extends Model
{
    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    protected $table = 'inv_pesona_request_items';

    protected $fillable = [
        'inv_pesona_request_id', 'inv_pesona_item_id', 'qty', 'item_name_snapshot',
        'unit_name_snapshot', 'unit_size_snapshot', 'content_unit_name_snapshot',
        'status', 'reviewed_by', 'reviewed_at', 'admin_note',
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
        return $this->belongsTo(InvPesonaRequest::class, 'inv_pesona_request_id');
    }

    public function item()
    {
        return $this->belongsTo(InvPesonaItem::class, 'inv_pesona_item_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
