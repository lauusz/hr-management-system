<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pt extends Model
{
     use HasFactory;

    protected $table = 'pts';

    protected $fillable = [
        'name',
    ];

    public function profiles()
    {
        return $this->hasMany(EmployeeProfile::class, 'pt_id');
    }
}
