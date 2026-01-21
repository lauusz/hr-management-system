<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'shift_id',
        'employee_shift_id',
        'normal_start_time',
        'normal_end_time',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'location_id',
        
        // Data Clock In
        'clock_in_at',
        'clock_in_photo',
        'clock_in_photo_deleted_at',
        'clock_in_lat',
        'clock_in_lng',
        'clock_in_distance_m',
        
        // Data Clock Out
        'clock_out_at',
        'clock_out_photo',
        'clock_out_photo_deleted_at',
        'clock_out_lat',
        'clock_out_lng',
        'clock_out_distance_m',
        
        'status',
        'notes',

        // [PENTING] Kolom Dinas Luar & Approval (Wajib ada di fillable)
        'type',              // Values: 'WFO', 'DINAS_LUAR'
        'approval_status',   // Values: 'PENDING', 'APPROVED', 'REJECTED'
        'rejection_note',
        'approved_by'
    ];

    protected $casts = [
        'date'                      => 'date',
        'clock_in_at'               => 'datetime',
        'clock_out_at'              => 'datetime',
        'clock_in_photo_deleted_at' => 'datetime',
        'clock_out_photo_deleted_at'=> 'datetime',
        // Casting ke datetime memudahkan manipulasi jam di Controller
        'normal_start_time'         => 'datetime:H:i:s', 
        'normal_end_time'           => 'datetime:H:i:s',
    ];

    // --- RELATIONS ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function employeeShift()
    {
        return $this->belongsTo(EmployeeShift::class, 'employee_shift_id');
    }

    public function location()
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id');
    }

    // Relasi ke User HRD yang melakukan approval
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // --- ACCESSORS / HELPERS ---

    public function getNormalStartLabelAttribute(): string
    {
        return $this->normal_start_time ? $this->normal_start_time->format('H:i') : '-';
    }

    public function getNormalEndLabelAttribute(): string
    {
        return $this->normal_end_time ? $this->normal_end_time->format('H:i') : '-';
    }

    public function getClockInLabelAttribute(): string
    {
        return $this->clock_in_at ? $this->clock_in_at->format('H:i') : '-';
    }

    public function getClockOutLabelAttribute(): string
    {
        return $this->clock_out_at ? $this->clock_out_at->format('H:i') : '-';
    }
}