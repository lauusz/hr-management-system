<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkCategory extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(AtkItem::class);
    }
}
