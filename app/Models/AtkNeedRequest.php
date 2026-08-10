<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AtkNeedRequest extends Model
{
    public const MODULE_ATK = 'ATK';

    public const MODULE_OPS = 'OPS';

    /**
     * Status pengajuan kebutuhan barang / restock.
     */
    public const STATUS_PENDING = 'PENDING';

    public const STATUS_DONE = 'DONE';

    public const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'module',
        'user_id',
        'user_name_snapshot',
        'pt_id',
        'pt_name_snapshot',
        'atk_item_id',
        'requested_item_name',
        'qty',
        'unit_name',
        'reason',
        'status',
        'processed_by',
        'processed_at',
        'admin_note',
    ];

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function item()
    {
        return $this->belongsTo(AtkItem::class, 'atk_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pt()
    {
        return $this->belongsTo(Pt::class, 'pt_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
