<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkMksAccessPt extends Model
{
    protected $fillable = ['pt_id', 'created_by'];

    public function pt()
    {
        return $this->belongsTo(Pt::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
