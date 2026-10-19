<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeApproverAssignment extends Model
{
    protected $primaryKey = 'employee_id';
    public $incrementing = false;
    protected $fillable = ['employee_id', 'supervisor_id', 'manager_id'];

    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
}
