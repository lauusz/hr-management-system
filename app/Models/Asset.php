<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    public const STATUS_AVAILABLE = 'AVAILABLE';

    public const STATUS_ASSIGNED = 'ASSIGNED';

    public const STATUS_SERVICE = 'SERVICE';

    public const STATUS_LOST = 'LOST';

    public const STATUS_DISPOSAL = 'DISPOSAL';

    protected $fillable = [
        'asset_code',
        'asset_category_id',
        'name',
        'brand',
        'model',
        'photo_path',
        'serial_number',
        'hostname',
        'email_laptop',
        'condition_status',
        'asset_status',
        'current_user_id',
        'current_pt_id',
        'purchase_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
        ];
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function currentPt()
    {
        return $this->belongsTo(Pt::class, 'current_pt_id');
    }

    public function movements()
    {
        return $this->hasMany(AssetMovement::class);
    }
}
