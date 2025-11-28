<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'phone',
        'role',
        'division_id',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    public function setPasswordAttribute($value)
    {
        if ($value && !str_starts_with($value, '$2y$')) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }


    // Role Helper
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
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

    public function EmployeeShift()
    {
        return $this->hasOne(EmployeeShift::class, 'user_id');
    }
}
