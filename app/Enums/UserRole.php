<?php

namespace App\Enums;

enum UserRole: string
{
    case HRD = 'HRD';             // Master HRD (Approver Akhir)
    case HR_STAFF = 'HR STAFF';   // Admin HRD (Requester Biasa)
    case MANAGER = 'MANAGER';     // Manager Divisi Lain
    case SUPERVISOR = 'SUPERVISOR';
    case EMPLOYEE = 'EMPLOYEE';
    
    // Helper untuk label tampilan (Opsional, biar rapi di UI nanti)
    public function label(): string
    {
        return match($this) {
            self::HRD => 'HR Manager (Master)',
            self::HR_STAFF => 'HR Staff',
            self::MANAGER => 'Manager',
            self::SUPERVISOR => 'Supervisor',
            self::EMPLOYEE => 'Karyawan',
        };
    }
}