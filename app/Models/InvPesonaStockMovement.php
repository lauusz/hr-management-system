<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvPesonaStockMovement extends Model
{
    public const TYPE_IN = 'IN';

    public const TYPE_OUT = 'OUT';

    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    protected $table = 'inv_pesona_stock_movements';

    protected $fillable = [
        'inv_pesona_item_id', 'movement_type', 'qty', 'unit_price', 'total_price',
        'stock_before', 'stock_after', 'source_type', 'source_id', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'integer',
            'total_price' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
        ];
    }

    public function item()
    {
        return $this->belongsTo(InvPesonaItem::class, 'inv_pesona_item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function source()
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }
}
