<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeApprovalAction extends Model
{
    public $timestamps = false;
    protected $fillable = ['overtime_request_id', 'approval_round', 'decision_slot', 'action', 'actor_id', 'actor_name', 'actor_role', 'notes', 'acted_at'];
    protected $casts = ['acted_at' => 'datetime'];
}
