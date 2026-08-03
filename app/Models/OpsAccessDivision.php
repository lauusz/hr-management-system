<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpsAccessDivision extends Model
{
    protected $fillable = ['division_id', 'created_by'];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
