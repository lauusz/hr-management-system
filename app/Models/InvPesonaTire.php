<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvPesonaTire extends Model
{
    public const TYPES = [
        'ORI' => 'Ori',
        'VULKANISIR_DINGIN' => 'Vulkanisir Dingin',
        'VULKANISIR_PANAS' => 'Vulkanisir Panas',
        'VELG_BAN_SET' => 'Velg + Ban (Set)',
    ];

    public const CONDITIONS = ['BARU' => 'Baru', 'BEKAS' => 'Bekas'];

    public const LOCATION_WAREHOUSE = 'GUDANG';

    public const LOCATION_VEHICLE = 'ARMADA';

    protected $table = 'inv_pesona_tires';

    protected $fillable = [
        'serial_number', 'tire_type', 'brand', 'size', 'tire_condition', 'is_usable', 'received_on',
        'supplier_name', 'document_number', 'location_status', 'inv_pesona_vehicle_id', 'position_number',
        'is_active', 'notes', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_usable' => 'boolean',
            'is_active' => 'boolean',
            'received_on' => 'date:Y-m-d',
            'position_number' => 'integer',
        ];
    }

    public function vehicle()
    {
        return $this->belongsTo(InvPesonaVehicle::class, 'inv_pesona_vehicle_id');
    }

    public function histories()
    {
        return $this->hasMany(InvPesonaTireHistory::class, 'inv_pesona_tire_id');
    }

    /** Stok siap pakai: aktif + gudang + layak + nomor seri tersedia. */
    public function scopeReady(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('location_status', self::LOCATION_WAREHOUSE)
            ->where('is_usable', true)
            ->whereNotNull('serial_number');
    }

    /** Label pilihan ban: NOMOR SERI — TIPE — MEREK UKURAN (KONDISI). Hanya tampilan, bukan nilai transaksi. */
    public function pickerLabel(): string
    {
        return $this->serial_number.' — '.(self::TYPES[$this->tire_type] ?? $this->tire_type)
            .($this->brand || $this->size ? ' — '.trim($this->brand.' '.$this->size) : '')
            .' ('.(self::CONDITIONS[$this->tire_condition] ?? $this->tire_condition).')';
    }

    public function usableLabel(): string
    {
        return $this->is_usable === null ? 'Belum diketahui' : ($this->is_usable ? 'Layak' : 'Tidak layak');
    }
}
