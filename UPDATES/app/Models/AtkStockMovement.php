<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkStockMovement extends Model
{
    /**
     * Jenis pergerakan stok.
     */
    public const TYPE_IN = 'IN';

    public const TYPE_OUT = 'OUT';

    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    /**
     * Sumber pergerakan stok (digunakan untuk query manual via source_type).
     *
     * Catatan: source_type disimpan sebagai nama class polymorphic untuk
     * mendukung relasi source() morphTo(). Konstanta di bawah dipakai untuk
     * memetakan label ke nama class saat create movement baru, dan untuk
     * filter query seperti where('source_type', AtkStockMovement::SOURCE_REQUEST).
     */
    public const SOURCE_MANUAL = 'MANUAL';

    public const SOURCE_INITIAL = 'INITIAL';

    public const SOURCE_REQUEST = AtkRequest::class;

    public const SOURCE_NEED_REQUEST = AtkNeedRequest::class;

    /**
     * Pemetaan label singkat → nama class morphic, untuk write konsisten.
     */
    public const SOURCE_CLASS_MAP = [
        self::SOURCE_MANUAL => null,
        self::SOURCE_INITIAL => null,
        'REQUEST' => AtkRequest::class,
        'NEED_REQUEST' => AtkNeedRequest::class,
    ];

    protected $fillable = [
        'atk_item_id',
        'movement_type',
        'qty',
        'unit_price',
        'total_price',
        'stock_before',
        'stock_after',
        'source_type',
        'source_id',
        'notes',
        'created_by',
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
        return $this->belongsTo(AtkItem::class, 'atk_item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi polymorphic ke entitas asal pergerakan stok.
     *
     * source_type berisi nama class (App\Models\AtkRequest, App\Models\AtkNeedRequest)
     * atau NULL untuk movement manual (MANUAL) dan saldo awal (INITIAL).
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Label ramah untuk ditampilkan di UI (mis. dashboard).
     * Mengembalikan label singkat walau source_type berisi nama class.
     */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            AtkRequest::class => 'REQUEST',
            AtkNeedRequest::class => 'NEED_REQUEST',
            'MANUAL' => 'MANUAL',
            'INITIAL' => 'INITIAL',
            null => '-',
            default => $this->source_type ?? '-',
        };
    }
}
