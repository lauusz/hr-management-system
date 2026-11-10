<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkScheduleGroup extends Model
{
    protected $table = 'employee_work_schedule_groups';

    protected $fillable = ['name', 'created_by', 'updated_by'];
}
