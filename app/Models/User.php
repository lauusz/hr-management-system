<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'phone',
        'role',
        'division_id',
        'position_id',
        'shift_id',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isHR()
    {
        return $this->role === 'HRD';
    }

    public function isSupervisor()
    {
        return $this->role === 'SUPERVISOR';
    }

    public function isEmployee()
    {
        return $this->role === 'EMPLOYEE';
    }

    public function profile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function supervisedDivisions()
    {
        return $this->hasMany(Division::class, 'supervisor_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function getShiftNameAttribute()
    {
        return $this->shift?->name ?? 'No Shift assigned';
    }

    public function employeeShift()
    {
        return $this->hasOne(EmployeeShift::class, 'user_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }
}
