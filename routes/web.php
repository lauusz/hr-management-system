<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\SupervisorLeaveController;
use App\Http\Controllers\HrLeaveController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\EmployeeShiftController;
use App\Http\Controllers\HR\AttendanceLocationController;
use App\Http\Controllers\HR\DivisionController;
use App\Http\Controllers\HR\PositionController;
use App\Http\Controllers\HR\ScheduleController;
use App\Http\Controllers\HRAttendanceController;
use App\Http\Controllers\HREmployeeController;
use App\Http\Controllers\PtController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\HR\OrganizationController;
use App\Http\Controllers\EmployeeLoanRequestController;
use App\Http\Controllers\HrLoanRequestController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    Route::resource('leave-requests', LeaveRequestController::class)
        ->only(['index', 'create', 'store', 'show']);

    Route::get('/attendance', [AttendanceController::class, 'dashboard'])->name('attendance.dashboard');
    Route::get('/attendance/clock-in', [AttendanceController::class, 'showClockInForm'])->name('attendance.clockIn.form');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::get('/attendance/clock-out', [AttendanceController::class, 'showClockOutForm'])->name('attendance.clockOut.form');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    Route::get('/loan-requests', [EmployeeLoanRequestController::class, 'index'])->name('employee.loan_requests.index');
    Route::get('/loan-requests/create', [EmployeeLoanRequestController::class, 'create'])->name('employee.loan_requests.create');
    Route::post('/loan-requests', [EmployeeLoanRequestController::class, 'store'])->name('employee.loan_requests.store');
    Route::get('/loan-requests/{loan}', [EmployeeLoanRequestController::class, 'show'])->name('employee.loan_requests.show');

    Route::get('/settings/password', [AuthController::class, 'showChangePasswordForm'])->name('settings.password');
    Route::put('/settings/password', [AuthController::class, 'updatePassword'])->name('settings.password.update');

    Route::middleware('role:HRD')->group(function () {

        Route::get('/hr/leave-requests', [HrLeaveController::class, 'index'])->name('hr.leave.index');
        Route::get('/hr/leave-requests/{leave}', [HrLeaveController::class, 'show'])->name('hr.leave.show');
        Route::post('/hr/leave-requests/{leave}/approve', [HrLeaveController::class, 'approve'])->name('hr.leave.approve');
        Route::post('/hr/leave-requests/{leave}/reject', [HrLeaveController::class, 'reject'])->name('hr.leave.reject');

        Route::get('/hr/leave/master', [HrLeaveController::class, 'master'])->name('hr.leave.master');

        Route::get('/hr/shifts', [ShiftController::class, 'index'])->name('hr.shifts.index');
        Route::get('/hr/shifts/create', [ShiftController::class, 'create'])->name('hr.shifts.create');
        Route::post('/hr/shifts', [ShiftController::class, 'store'])->name('hr.shifts.store');
        Route::get('/hr/shifts/{shift}/edit', [ShiftController::class, 'edit'])->name('hr.shifts.edit');
        Route::put('/hr/shifts/{shift}', [ShiftController::class, 'update'])->name('hr.shifts.update');
        Route::delete('/hr/shifts/{shift}', [ShiftController::class, 'destroy'])->name('hr.shifts.destroy');

        Route::get('/hr/employees', [HREmployeeController::class, 'index'])->name('hr.employees.index');
        Route::get('/hr/employees/create', [HREmployeeController::class, 'create'])->name('hr.employees.create');
        Route::post('/hr/employees', [HREmployeeController::class, 'store'])->name('hr.employees.store');

        Route::get('/hr/employees/{employee}', [HREmployeeController::class, 'show'])->name('hr.employees.show');
        Route::get('/hr/employees/{employee}/edit', [HREmployeeController::class, 'edit'])->name('hr.employees.edit');
        Route::put('/hr/employees/{employee}', [HREmployeeController::class, 'update'])->name('hr.employees.update');
        Route::put('/hr/employees/{employee}/exit', [HREmployeeController::class, 'exit'])->name('hr.employees.exit');
        Route::get('/hr/employees/{employee}/exit-detail', [HREmployeeController::class, 'exitDetail'])->name('hr.employees.exit_detail');
        Route::delete('/hr/employees/{employee}', [HREmployeeController::class, 'destroy'])->name('hr.employees.destroy');

        Route::get('/hr/employees/{user}/shift', [EmployeeShiftController::class, 'edit'])->name('hr.employees.shift.edit');
        Route::put('/hr/employees/{user}/shift', [EmployeeShiftController::class, 'update'])->name('hr.employees.shift.update');

        Route::post('/hr/employees/{user}/documents', [EmployeeDocumentController::class, 'store'])->name('hr.employees.documents.store');
        Route::delete('/hr/employee-documents/{employeeDocument}', [EmployeeDocumentController::class, 'destroy'])->name('hr.employee_documents.destroy');

        Route::get('/hr/locations', [AttendanceLocationController::class, 'index'])->name('hr.locations.index');
        Route::get('/hr/locations/create', [AttendanceLocationController::class, 'create'])->name('hr.locations.create');
        Route::post('/hr/locations', [AttendanceLocationController::class, 'store'])->name('hr.locations.store');
        Route::get('/hr/locations/{location}/edit', [AttendanceLocationController::class, 'edit'])->name('hr.locations.edit');
        Route::put('/hr/locations/{location}', [AttendanceLocationController::class, 'update'])->name('hr.locations.update');
        Route::delete('/hr/locations/{location}', [AttendanceLocationController::class, 'destroy'])->name('hr.locations.destroy');

        Route::get('/hr/schedules', [ScheduleController::class, 'index'])->name('hr.schedules.index');
        Route::get('/hr/schedules/create', [ScheduleController::class, 'create'])->name('hr.schedules.create');
        Route::post('/hr/schedules', [ScheduleController::class, 'store'])->name('hr.schedules.store');
        Route::get('/hr/schedules/{schedule}/edit', [ScheduleController::class, 'edit'])->name('hr.schedules.edit');
        Route::put('/hr/schedules/{schedule}', [ScheduleController::class, 'update'])->name('hr.schedules.update');
        Route::delete('/hr/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('hr.schedules.destroy');

        Route::get('/hr/attendances', [HRAttendanceController::class, 'index'])->name('hr.attendances.index');

        Route::get('/hr/loan-requests', [HrLoanRequestController::class, 'index'])->name('hr.loan_requests.index');
        Route::get('/hr/loan-requests/{id}', [HrLoanRequestController::class, 'show'])->name('hr.loan_requests.show');
        Route::post('/hr/loan-requests/{id}/approve', [HrLoanRequestController::class, 'approve'])->name('hr.loan_requests.approve');
        Route::post('/hr/loan-requests/{id}/reject', [HrLoanRequestController::class, 'reject'])->name('hr.loan_requests.reject');
        Route::post('/hr/loan-requests/{id}/repayments', [HrLoanRequestController::class, 'storeRepayment'])->name('hr.loan_requests.repayments.store');

        Route::get('/hr/organization', [OrganizationController::class, 'index'])->name('hr.organization');

        Route::get('/hr/positions/create', [PositionController::class, 'create'])->name('hr.positions.create');
        Route::post('/hr/positions', [PositionController::class, 'store'])->name('hr.positions.store');
        Route::get('/hr/positions/{position}/edit', [PositionController::class, 'edit'])->name('hr.positions.edit');
        Route::put('/hr/positions/{position}', [PositionController::class, 'update'])->name('hr.positions.update');
        Route::delete('/hr/positions/{position}', [PositionController::class, 'destroy'])->name('hr.positions.destroy');

        Route::get('/hr/divisions/create', [DivisionController::class, 'create'])->name('hr.divisions.create');
        Route::post('/hr/divisions', [DivisionController::class, 'store'])->name('hr.divisions.store');
        Route::get('/hr/divisions/{division}/edit', [DivisionController::class, 'edit'])->name('hr.divisions.edit');
        Route::put('/hr/divisions/{division}', [DivisionController::class, 'update'])->name('hr.divisions.update');
        Route::delete('/hr/divisions/{division}', [DivisionController::class, 'destroy'])->name('hr.divisions.destroy');

        Route::get('/hr/pts', [PtController::class, 'index'])->name('hr.pts.index');
        Route::get('/hr/pts/create', [PtController::class, 'create'])->name('hr.pts.create');
        Route::post('/hr/pts', [PtController::class, 'store'])->name('hr.pts.store');
        Route::get('/hr/pts/{pt}/edit', [PtController::class, 'edit'])->name('hr.pts.edit');
        Route::put('/hr/pts/{pt}', [PtController::class, 'update'])->name('hr.pts.update');
        Route::delete('/hr/pts/{pt}', [PtController::class, 'destroy'])->name('hr.pts.destroy');
    });

    Route::middleware('role:SUPERVISOR')->group(function () {
        Route::get('/supervisor/leave-requests', [SupervisorLeaveController::class, 'index'])->name('supervisor.leave.index');
        Route::get('/supervisor/leave-requests/{leave}', [SupervisorLeaveController::class, 'show'])->name('supervisor.leave.show');
        Route::post('/leave-requests/{leave}/supervisor/ack', [SupervisorLeaveController::class, 'ack'])->name('supervisor.leave.ack');
        Route::post('/leave-requests/{leave}/supervisor/reject', [SupervisorLeaveController::class, 'reject'])->name('supervisor.leave.reject');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/', fn() => redirect()->route('login'));
