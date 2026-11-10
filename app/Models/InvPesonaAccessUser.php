<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvPesonaAccessUser extends Model
{
    protected $table = 'inv_pesona_access_users';

    protected $fillable = [
        'user_id', 'is_admin', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
