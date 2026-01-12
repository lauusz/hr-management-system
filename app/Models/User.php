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
        'role', // Kolom ini akan otomatis dikonversi jadi Enum oleh 'casts'
        'division_id',
        'position_id',
        'direct_supervisor_id', // [BARU] Tambahkan ini agar bisa di-save
        'shift_id',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting tipe data otomatis.
     * PENTING: 'role' di-cast ke Enum UserRole agar sistem membaca object Enum, bukan string biasa.
     */
    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password'      => 'hashed',
            'role'          => UserRole::class,
        ];
    }

    // =========================================================================
    // HELPER METHODS (Logic Role & Hak Akses)
    // =========================================================================

    /**
     * Cek apakah user adalah MASTER HRD (Manager Level).
     * Berhak melakukan Approval akhir.
     */
    public function isHrManager(): bool
    {
        return $this->role === UserRole::HRD;
    }

    /**
     * Cek apakah user adalah STAFF HRD (Admin).
     * Bisa kelola data karyawan, tapi tidak bisa Approve cuti (harus izin Master).
     */
    public function isHrStaff(): bool
    {
        return $this->role === UserRole::HR_STAFF;
    }

    /**
     * Helper Gabungan: Apakah dia "Orang HR" (Bisa Staff / Manager)?
     * Digunakan untuk akses menu Sidebar (Data Karyawan, Presensi, dll) yang sifatnya administratif.
     */
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

    /**
     * Cek apakah user berhak melakukan Approval Bawahan
     * (Hanya Manager & Supervisor yang punya wewenang ini)
     */
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

    // [BARU] Relasi ke Atasan Langsung (Self-Join)
    // Mengambil data user lain yang menjadi atasan user ini
    public function directSupervisor()
    {
        return $this->belongsTo(User::class, 'direct_supervisor_id');
    }

    // [BARU] Relasi ke Bawahan (Self-Join)
    // Mengambil daftar user yang menjadikan user ini sebagai atasan
    public function subordinates()
    {
        return $this->hasMany(User::class, 'direct_supervisor_id');
    }

    public function profile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    /**
     * Relasi Pintas (Shortcut) ke PT.
     * Memungkinkan akses: $user->pt->name 
     * (Melewati tabel employee_profiles)
     */
    public function pt()
    {
        return $this->hasOneThrough(
            Pt::class,              // Model Tujuan (PT)
            EmployeeProfile::class, // Model Perantara (Profile)
            'user_id',              // FK di table profiles (menuju user)
            'id',                   // FK di table pts (menuju profile - teknik reverse join)
            'id',                   // PK di table users
            'pt_id'                 // PK di table profiles (yang menyimpan ID PT)
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

    // Accessor untuk nama shift yang aman (jika null)
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