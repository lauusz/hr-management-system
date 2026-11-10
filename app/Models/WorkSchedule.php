<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class WorkSchedule extends Model
{
    protected $table = 'employee_work_schedules';

    protected $fillable = [
        'user_id', 'work_date', 'shift_id', 'shift_name', 'start_time', 'end_time',
        'is_holiday', 'is_overnight', 'is_wita', 'created_by', 'updated_by', 'work_schedule_group_id',
    ];

    public static function scheduleSchemaReady(): bool
    {
        return Schema::hasTable('employee_work_schedules') && Schema::hasColumns('employee_work_schedules', [
            'id', 'user_id', 'work_date', 'shift_id', 'shift_name', 'start_time', 'end_time',
            'is_holiday', 'is_overnight', 'is_wita', 'created_by', 'updated_by', 'created_at', 'updated_at',
        ]);
    }

    public static function scheduleReadable(): bool
    {
        return Schema::hasTable('employee_work_schedules') && Schema::hasColumns('employee_work_schedules', [
            'id', 'user_id', 'work_date', 'shift_id', 'shift_name', 'start_time', 'end_time',
            'is_holiday', 'is_overnight', 'is_wita',
        ]);
    }

    public static function schemaReady(): bool
    {
        return self::scheduleSchemaReady()
            && Schema::hasColumn('employee_work_schedules', 'work_schedule_group_id')
            && Schema::hasTable('employee_work_schedule_groups')
            && Schema::hasColumns('employee_work_schedule_groups', ['id', 'name', 'created_by', 'updated_by', 'created_at', 'updated_at'])
            && Schema::hasTable('attendance_events')
            && Schema::hasColumns('attendance_events', [
                'work_date', 'attendance_shift_id', 'shift_start_time', 'shift_end_time', 'is_overnight',
                'attendance_location_scope', 'is_wita', 'is_flexible', 'is_outside_reference_location',
                'shift_name_snapshot', 'schedule_is_holiday',
            ]);
    }

    protected $casts = [
        'work_date' => 'date',
        'is_holiday' => 'boolean',
        'is_overnight' => 'boolean',
        'is_wita' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(WorkScheduleGroup::class, 'work_schedule_group_id');
    }
}
