<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvPesonaVehicle extends Model
{
    protected $table = 'inv_pesona_vehicles';

    protected $fillable = ['nopol', 'is_active', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function requests()
    {
        return $this->hasMany(InvPesonaRequest::class, 'inv_pesona_vehicle_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
