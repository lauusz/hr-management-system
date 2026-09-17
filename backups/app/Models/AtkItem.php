<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AtkItem extends Model
{
    public const MODULE_ATK = 'ATK';

    public const MODULE_OPS = 'OPS';

    public const MODULE_ATK_MKS = 'ATK_MKS';

    /**
     * Pilihan satuan ambil untuk dropdown master barang.
     * Tambah nilai baru di sini jika dibutuhkan — otomatis dipakai di form create & edit.
     */
    public const UNIT_OPTIONS = [
        'pcs',
        'box',
        'rim',
        'pack',
        'lembar',
        'dus',
        'lusin',
        'set',
    ];

    protected $fillable = [
        'module',
        'atk_category_id',
        'name',
        'description',
        'image_path',
        'unit_name',
        'unit_size',
        'content_unit_name',
        'stock_qty',
        'minimum_stock',
        'min_request_qty',
        'is_active',
        'created_by',
        'deleted_at',
        'deleted_by',
        'deletion_note',
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

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('deleted_at');
    }

    public function category()
    {
        return $this->belongsTo(AtkCategory::class, 'atk_category_id');
    }

    public function requestItems()
    {
        return $this->hasMany(AtkRequestItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(AtkStockMovement::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_qty <= 0) {
            return 'OUT';
        }

        if ($this->minimum_stock > 0 && $this->stock_qty <= $this->minimum_stock) {
            return 'LOW';
        }

        return 'AVAILABLE';
    }

    /**
     * Label konversi satuan: mis. "1 box = 20 pcs".
     * Dipakai di master barang & katalog agar relasi satuan ambil ↔ satuan isi jelas.
     */
    public function getUnitConversionLabelAttribute(): string
    {
        return "1 {$this->unit_name} = {$this->unit_size} {$this->content_unit_name}";
    }

    /**
     * Label stok dengan satuan ambil: mis. "61 box".
     */
    public function getStockWithUnitAttribute(): string
    {
        return "{$this->stock_qty} {$this->unit_name}";
    }

    /**
     * Nilai setara stok dalam satuan isi terkecil: mis. "1.220 pcs".
     * Hanya bermakna jika unit_size > 1; null jika barang sudah satuan terkecil.
     */
    public function getStockEquivalentLabelAttribute(): ?string
    {
        if ($this->unit_size <= 1) {
            return null;
        }

        return number_format($this->stock_qty * $this->unit_size, 0, ',', '.')
            .' '.$this->content_unit_name;
    }
}
