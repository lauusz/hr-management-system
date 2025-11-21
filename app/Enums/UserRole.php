<?php

namespace App\Enums;

enum UserRole: string
{
    case HRD = 'HRD';
    case SUPERVISOR = 'SUPERVISOR';
    case EMPLOYEE = 'EMPLOYEE';
    
}
