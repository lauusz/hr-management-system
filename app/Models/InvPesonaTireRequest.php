<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvPesonaTireRequest extends Model
{
    public const TYPE_INSTALL = 'PASANG';

    public const TYPE_REPLACE = 'GANTI';

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    public const TYPES = ['PASANG' => 'Pasang Ban', 'GANTI' => 'Ganti Ban'];

    public const STATUSES = ['PENDING' => 'Menunggu', 'APPROVED' => 'Disetujui', 'REJECTED' => 'Ditolak'];

    protected $table = 'inv_pesona_tire_requests';

    protected $fillable = [
        'request_number', 'requested_by', 'requested_by_name_snapshot', 'inv_pesona_vehicle_id', 'nopol_snapshot',
        'position_number', 'request_type', 'need_notes', 'old_tire_id', 'old_tire_snapshot', 'status',
        'assigned_tire_id', 'assigned_tire_snapshot', 'reviewed_by', 'reviewed_by_name_snapshot', 'reviewed_at',
        'rejection_reason', 'install_history_id', 'return_history_id',
    ];

    protected function casts(): array
    {
        return [
            'position_number' => 'integer',
            'old_tire_snapshot' => 'array',
            'assigned_tire_snapshot' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function vehicle()
    {
        return $this->belongsTo(InvPesonaVehicle::class, 'inv_pesona_vehicle_id');
    }

    public function oldTire()
    {
        return $this->belongsTo(InvPesonaTire::class, 'old_tire_id');
    }

    public function assignedTire()
    {
        return $this->belongsTo(InvPesonaTire::class, 'assigned_tire_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
