<?php

namespace App\Models;

// PENTING: Import Enum UserRole
use App\Enums\UserRole;
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
        'can_manage_payroll', // <--- [BARU] Hak akses kelola slip gaji 
        'leave_balance',        // <--- [BARU] Tambahkan ini agar sisa cuti bisa di-update
        'division_id',
        'position_id',
        'direct_supervisor_id', 
        'manager_id',           
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
            'password'      => 'hashed',
            'role'          => UserRole::class,
            'can_manage_payroll' => 'boolean',
        ];
    }

    // =========================================================================
    // HELPER METHODS (Logic Role & Hak Akses)
    // =========================================================================

    public function isHrManager(): bool
    {
        return $this->role === UserRole::HRD;
    }

    public function isHrStaff(): bool
    {
        return $this->role === UserRole::HR_STAFF;
    }

    public function canManagePayroll(): bool
    {
        // Akses diberikan jika user adalah HR Manager atau punya flag can_manage_payroll = true
        return $this->isHrManager() || $this->can_manage_payroll;
    }

    public function isHR(): bool
    {
        return in_array($this->role, [
            UserRole::HRD,
            UserRole::HR_STAFF
        ]);
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::MANAGER;
    }

    public function isSupervisor(): bool
    {
        return $this->role === UserRole::SUPERVISOR;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::EMPLOYEE;
    }

    public function canApprove(): bool
    {
        return in_array($this->role, [
            UserRole::MANAGER,
            UserRole::SUPERVISOR
        ]);
    }

    // =========================================================================
    // RELATIONSHIPS (Relasi Database)
    // =========================================================================

    // Relasi ke Atasan Langsung (Observer / SPV)
    public function directSupervisor()
    {
        return $this->belongsTo(User::class, 'direct_supervisor_id');
    }

    // Relasi ke Manager (Approver)
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Relasi ke Bawahan (Berdasarkan direct supervisor)
    public function subordinates()
    {
        return $this->hasMany(User::class, 'direct_supervisor_id');
    }

    public function profile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function pt()
    {
        return $this->hasOneThrough(
            Pt::class,              
            EmployeeProfile::class, 
            'user_id',              
            'id',                   
            'id',                   
            'pt_id'                 
        );
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

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'user_id');
    }
}