<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvPesonaTireHistory extends Model
{
    public const EVENTS = [
        'AWAL' => 'Pencatatan awal',
        'MASUK' => 'Penerimaan baru',
        'PASANG' => 'Dipasang',
        'KEMBALI' => 'Dikembalikan ke gudang',
        'UBAH' => 'Ubah data',
        'NONAKTIF' => 'Dinonaktifkan',
        'AKTIF' => 'Diaktifkan kembali',
    ];

    private const FIELD_LABELS = [
        'serial_number' => 'Nomor seri', 'tire_type' => 'Tipe', 'brand' => 'Merek', 'size' => 'Ukuran',
        'tire_condition' => 'Kondisi', 'is_usable' => 'Kelayakan', 'received_on' => 'Tgl penerimaan',
        'supplier_name' => 'Pemasok', 'document_number' => 'No. dokumen', 'notes' => 'Catatan ban',
    ];

    public $timestamps = false;

    protected $table = 'inv_pesona_tire_histories';

    protected $fillable = [
        'inv_pesona_tire_id', 'event_type', 'occurred_at', 'from_vehicle_id', 'from_position_number',
        'to_vehicle_id', 'to_position_number', 'before_snapshot', 'after_snapshot', 'notes',
        'created_by', 'created_by_name_snapshot', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
            'before_snapshot' => 'array',
            'after_snapshot' => 'array',
        ];
    }

    // Riwayat hanya ditambah; koreksi dicatat sebagai baris baru.
    protected static function booted(): void
    {
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    public function tire()
    {
        return $this->belongsTo(InvPesonaTire::class, 'inv_pesona_tire_id');
    }

    /** Perubahan field metadata dari snapshot (bukan dari data master terkini). */
    public function changedFields(): array
    {
        $before = $this->before_snapshot;
        if (! is_array($before)) {
            return [];
        }
        $changes = [];
        foreach (self::FIELD_LABELS as $key => $label) {
            $old = $before[$key] ?? null;
            $new = $this->after_snapshot[$key] ?? null;
            if ($old !== $new) {
                $changes[] = $label.': '.$this->display($key, $old).' → '.$this->display($key, $new);
            }
        }

        return $changes;
    }

    private function display(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        if ($key === 'is_usable') {
            return $value ? 'Layak' : 'Tidak layak';
        }

        return (string) match ($key) {
            'tire_type' => InvPesonaTire::TYPES[$value] ?? $value,
            'tire_condition' => InvPesonaTire::CONDITIONS[$value] ?? $value,
            default => $value,
        };
    }
}
