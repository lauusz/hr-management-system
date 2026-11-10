<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvPesonaRequest extends Model
{
    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_PARTIAL = 'PARTIAL';

    protected $table = 'inv_pesona_requests';

    protected $fillable = [
        'request_number', 'user_id', 'user_name_snapshot', 'pt_id',
        'pt_name_snapshot', 'inv_pesona_vehicle_id', 'nopol_snapshot', 'status', 'notes', 'approved_by', 'approved_at',
        'rejected_by', 'rejected_at', 'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pt()
    {
        return $this->belongsTo(Pt::class, 'pt_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(InvPesonaVehicle::class, 'inv_pesona_vehicle_id');
    }

    public function items()
    {
        return $this->hasMany(InvPesonaRequestItem::class, 'inv_pesona_request_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
