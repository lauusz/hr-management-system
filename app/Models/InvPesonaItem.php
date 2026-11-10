<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvPesonaItem extends Model
{
    protected $table = 'inv_pesona_items';

    protected $fillable = [
        'name', 'description', 'image_path', 'unit_name', 'unit_size',
        'content_unit_name', 'stock_qty', 'minimum_stock', 'min_request_qty',
        'is_active', 'created_by', 'deleted_at', 'deleted_by', 'deletion_note',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unit_size' => 'integer',
            'stock_qty' => 'integer',
            'minimum_stock' => 'integer',
            'min_request_qty' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('deleted_at');
    }

    public function requestItems()
    {
        return $this->hasMany(InvPesonaRequestItem::class, 'inv_pesona_item_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(InvPesonaStockMovement::class, 'inv_pesona_item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
