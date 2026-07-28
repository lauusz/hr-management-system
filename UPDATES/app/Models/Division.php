<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'supervisor_id',
    ];

    public function supervisor(){
        return $this->belongsTo(User::class,'supervisor_id');
    }

    public function users(){
        return $this->hasMany(User::class, 'division_id');
    }
}
