<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\HrLeaveController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\EmployeeShiftController;
use App\Http\Controllers\HR\AttendanceLocationController;
use App\Http\Controllers\HR\DivisionController;
use App\Http\Controllers\HR\PositionController;
use App\Http\Controllers\HR\ScheduleController;
use App\Http\Controllers\HRAttendanceController;
use App\Http\Controllers\ApprovalAttendanceController;
use App\Http\Controllers\HREmployeeController;
use App\Http\Controllers\PtController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\HR\OrganizationController;
use App\Http\Controllers\EmployeeLoanRequestController;
use App\Http\Controllers\HrLoanRequestController;
use App\Http\Controllers\SupervisorDataController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\SupervisorOvertimeController;
use App\Http\Controllers\HrOvertimeController;
use App\Http\Controllers\Hr\PayslipController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    Route::resource('leave-requests', LeaveRequestController::class)
        ->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
    Route::post('/leave-requests/{leave_request}/upload-photo', [LeaveRequestController::class, 'uploadPhoto'])
        ->name('leave-requests.upload-photo');
    
    // [BARU] Endpoint untuk cek duplikat pengajuan
    Route::post('/leave-requests/check-duplicate', [LeaveRequestController::class, 'checkDuplicate'])->name('leave-requests.checkDuplicate');

    Route::resource('overtime-requests', OvertimeRequestController::class)
        ->only(['index', 'create', 'store', 'update', 'destroy']);

    Route::get('/attendance', [AttendanceController::class, 'dashboard'])->name('attendance.dashboard');
    Route::get('/attendance/clock-in', [AttendanceController::class, 'showClockInForm'])->name('attendance.clockIn.form');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::get('/attendance/clock-out', [AttendanceController::class, 'showClockOutForm'])->name('attendance.clockOut.form');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    // Remote Attendance (Karyawan)
    Route::prefix('remote-attendance')->name('remote-attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'remoteIndex'])->name('index');
        Route::post('/clock-in', [AttendanceController::class, 'remoteClockIn'])->name('clockIn');
        Route::post('/clock-out', [AttendanceController::class, 'remoteClockOut'])->name('clockOut');
    });

    Route::get('/loan-requests', [EmployeeLoanRequestController::class, 'index'])->name('employee.loan_requests.index');
    Route::get('/loan-requests/create', [EmployeeLoanRequestController::class, 'create'])->name('employee.loan_requests.create');
    Route::post('/loan-requests', [EmployeeLoanRequestController::class, 'store'])->name('employee.loan_requests.store');
    Route::get('/loan-requests/{loan}', [EmployeeLoanRequestController::class, 'show'])->name('employee.loan_requests.show');
    Route::delete('/loan-requests/{loan}', [EmployeeLoanRequestController::class, 'destroy'])->name('employee.loan_requests.destroy');

    Route::get('/settings/password', [AuthController::class, 'showChangePasswordForm'])->name('settings.password');
    Route::put('/settings/password', [AuthController::class, 'updatePassword'])->name('settings.password.update');

    // --- ROUTE HRD & HR STAFF ---
    Route::middleware('role:HRD,HR STAFF')->group(function () {

        Route::get('/hr/leave-requests', [HrLeaveController::class, 'index'])->name('hr.leave.index');
        Route::get('/hr/leave-requests/{leave}', [HrLeaveController::class, 'show'])->name('hr.leave.show');
        Route::put('/hr/leave-requests/{leave}', [HrLeaveController::class, 'update'])->name('hr.leave.update');
        Route::post('/hr/leave-requests/{leave}/approve', [HrLeaveController::class, 'approve'])->name('hr.leave.approve');
        Route::post('/hr/leave-requests/{leave}/reject', [HrLeaveController::class, 'reject'])->name('hr.leave.reject');

        Route::get('/hr/leave/master', [HrLeaveController::class, 'master'])->name('hr.leave.master');
        Route::get('/hr/leave/master/export', [HrLeaveController::class, 'exportMaster'])->name('hr.leave.master.export');
        Route::get('/hr/leave/master/create', [HrLeaveController::class, 'createManual'])->name('hr.leave.manual.create');
        Route::post('/hr/leave/master/create', [HrLeaveController::class, 'storeManual'])->name('hr.leave.manual.store');

        Route::get('/hr/shifts', [ShiftController::class, 'index'])->name('hr.shifts.index');
        Route::get('/hr/shifts/create', [ShiftController::class, 'create'])->name('hr.shifts.create');
        Route::post('/hr/shifts', [ShiftController::class, 'store'])->name('hr.shifts.store');
        Route::get('/hr/shifts/{shift}/edit', [ShiftController::class, 'edit'])->name('hr.shifts.edit');
        Route::put('/hr/shifts/{shift}', [ShiftController::class, 'update'])->name('hr.shifts.update');
        Route::delete('/hr/shifts/{shift}', [ShiftController::class, 'destroy'])->name('hr.shifts.destroy');

        // --- EMPLOYEE MANAGEMENT ---
        Route::get('/hr/employees', [HREmployeeController::class, 'index'])->name('hr.employees.index');
        Route::get('/hr/employees/create', [HREmployeeController::class, 'create'])->name('hr.employees.create');
        Route::post('/hr/employees', [HREmployeeController::class, 'store'])->name('hr.employees.store');
        Route::get('/hr/employees/{employee}', [HREmployeeController::class, 'show'])->name('hr.employees.show');
        Route::get('/hr/employees/{employee}/edit', [HREmployeeController::class, 'edit'])->name('hr.employees.edit');
        Route::put('/hr/employees/{employee}', [HREmployeeController::class, 'update'])->name('hr.employees.update');
        Route::patch('/hr/employees/{employee}/reset-password', [HREmployeeController::class, 'resetPassword'])->name('hr.employees.reset-password');
        Route::put('/hr/employees/{employee}/exit', [HREmployeeController::class, 'exit'])->name('hr.employees.exit');
        Route::get('/hr/employees/{employee}/exit-detail', [HREmployeeController::class, 'exitDetail'])->name('hr.employees.exit_detail');
        Route::delete('/hr/employees/{employee}', [HREmployeeController::class, 'destroy'])->name('hr.employees.destroy');

        Route::get('/hr/employees/{user}/shift', [EmployeeShiftController::class, 'edit'])->name('hr.employees.shift.edit');
        Route::put('/hr/employees/{user}/shift', [EmployeeShiftController::class, 'update'])->name('hr.employees.shift.update');
        Route::patch('/hr/employees/{user}/shift-inline', [EmployeeShiftController::class, 'updateInline'])->name('hr.employees.shift.inline');
        Route::post('/hr/employees/{user}/documents', [EmployeeDocumentController::class, 'store'])->name('hr.employees.documents.store');
        Route::delete('/hr/employee-documents/{employeeDocument}', [EmployeeDocumentController::class, 'destroy'])->name('hr.employee_documents.destroy');

        Route::prefix('hr/supervisors')->name('hr.supervisors.')->group(function () {
            Route::get('/', [SupervisorDataController::class, 'index'])->name('index');
            Route::get('/create', [SupervisorDataController::class, 'create'])->name('create');
            Route::post('/', [SupervisorDataController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [SupervisorDataController::class, 'edit'])->name('edit');
            Route::put('/{user}', [SupervisorDataController::class, 'update'])->name('update');
            Route::delete('/{user}', [SupervisorDataController::class, 'destroy'])->name('destroy');
        });

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

        // [UBAH DISINI] MASTER ATTENDANCE (Hanya List)
        Route::get('/hr/attendances', [HRAttendanceController::class, 'index'])->name('hr.attendances.index');

        // [BARU] APPROVAL ATTENDANCE (Khusus Dinas Luar)
        Route::get('/hr/approval-attendance', [ApprovalAttendanceController::class, 'index'])->name('hr.approval_attendance.index');
        Route::post('/hr/approval-attendance/{attendance}/approve', [ApprovalAttendanceController::class, 'approve'])->name('hr.approval_attendance.approve');
        Route::post('/hr/approval-attendance/{attendance}/reject', [ApprovalAttendanceController::class, 'reject'])->name('hr.approval_attendance.reject');

        // LOAN REQUESTS (HRD & HR STAFF)
        Route::middleware('role:HRD,HR STAFF')->group(function () {
            Route::get('/hr/loan-requests', [HrLoanRequestController::class, 'index'])->name('hr.loan_requests.index');
            Route::get('/hr/loan-requests/{id}', [HrLoanRequestController::class, 'show'])->name('hr.loan_requests.show');
            Route::get('/hr/loan-requests/{id}/edit', [HrLoanRequestController::class, 'edit'])->name('hr.loan_requests.edit');
            Route::put('/hr/loan-requests/{id}', [HrLoanRequestController::class, 'update'])->name('hr.loan_requests.update');
            Route::post('/hr/loan-requests/{id}/approve', [HrLoanRequestController::class, 'approve'])->name('hr.loan_requests.approve');
            Route::post('/hr/loan-requests/{id}/reject', [HrLoanRequestController::class, 'reject'])->name('hr.loan_requests.reject');
            Route::put('/hr/loan-requests/{id}/internal-note', [HrLoanRequestController::class, 'saveInternalNote'])->name('hr.loan_requests.saveInternalNote');
            Route::post('/hr/loan-requests/{id}/repayments', [HrLoanRequestController::class, 'storeRepayment'])->name('hr.loan_requests.repayments.store');
            Route::put('/hr/loan-requests/{id}/repayments/{repaymentId}', [HrLoanRequestController::class, 'updateRepayment'])->name('hr.loan_requests.repayments.update');
            Route::delete('/hr/loan-requests/{id}/repayments/{repaymentId}', [HrLoanRequestController::class, 'destroyRepayment'])->name('hr.loan_requests.repayments.destroy');
        });

        // Overtime Requests (HR) - Routes Manual untuk Action
        Route::get('/hr/overtime-requests/master', [HrOvertimeController::class, 'master'])->name('hr.overtime-requests.master');
        Route::get('/hr/overtime-requests', [HrOvertimeController::class, 'index'])->name('hr.overtime-requests.index');
        Route::get('/hr/overtime-requests/{overtimeRequest}', [HrOvertimeController::class, 'show'])->name('hr.overtime-requests.show');
        Route::post('/hr/overtime-requests/{overtimeRequest}/approve', [HrOvertimeController::class, 'approve'])->name('hr.overtime-requests.approve');
        Route::post('/hr/overtime-requests/{overtimeRequest}/reject', [HrOvertimeController::class, 'reject'])->name('hr.overtime-requests.reject');

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

        // Debug
        Route::get('/hr/phpinfo', fn() => phpinfo())->name('hr.phpinfo');
        Route::get('/hr/pts/create', [PtController::class, 'create'])->name('hr.pts.create');
        Route::post('/hr/pts', [PtController::class, 'store'])->name('hr.pts.store');
        Route::get('/hr/pts/{pt}/edit', [PtController::class, 'edit'])->name('hr.pts.edit');
        Route::put('/hr/pts/{pt}', [PtController::class, 'update'])->name('hr.pts.update');
        Route::delete('/hr/pts/{pt}', [PtController::class, 'destroy'])->name('hr.pts.destroy');
    });

    // PAYSLIP ROUTES (Accessible by HR Manager or any user with can_manage_payroll = true)
    Route::middleware('can:manage-payroll')->group(function () {
        Route::get('/hr/payroll/settings', [PayslipController::class, 'settings'])->name('hr.payroll.settings');
        Route::post('/hr/payroll/settings', [PayslipController::class, 'updateSettings'])->name('hr.payroll.settings.update');

        Route::post('/hr/payroll/import/preview', [PayslipController::class, 'previewImport'])->name('hr.payroll.import.preview');
        Route::post('/hr/payroll/import/store', [PayslipController::class, 'storeBulkImport'])->name('hr.payroll.import.store');
        Route::get('/hr/payroll/export', [PayslipController::class, 'exportExcel'])->name('hr.payroll.export');
        Route::post('/hr/payroll/send-email', [PayslipController::class, 'sendSelectedEmails'])->name('hr.payroll.send-email');
        Route::delete('/hr/payroll/clear-selected', [PayslipController::class, 'destroySelected'])->name('hr.payroll.destroy-selected');

        Route::resource('/hr/payroll', PayslipController::class)
            ->names('hr.payroll')
            ->parameters(['payroll' => 'payslip']);
    });
    Route::middleware('has.subordinates')->prefix('supervisor')->name('supervisor.')->group(function () {
        // Overtime Requests (Supervisor)
        Route::get('/overtime-requests/master', [SupervisorOvertimeController::class, 'master'])->name('overtime-requests.master');
        Route::get('/overtime-requests', [SupervisorOvertimeController::class, 'index'])->name('overtime-requests.index');
        Route::get('/overtime-requests/{overtimeRequest}', [SupervisorOvertimeController::class, 'show'])->name('overtime-requests.show');
        Route::post('/overtime-requests/{overtimeRequest}/approve', [SupervisorOvertimeController::class, 'approve'])->name('overtime-requests.approve');
        Route::post('/overtime-requests/{overtimeRequest}/reject', [SupervisorOvertimeController::class, 'reject'])->name('overtime-requests.reject');
    });

    Route::middleware('has.subordinates')->group(function () {
        Route::get('/approval/requests', [ApprovalController::class, 'index'])->name('approval.index');
        Route::get('/supervisor/leave/master', [ApprovalController::class, 'master'])->name('supervisor.leave.master');
        Route::get('/approval/requests/{leave}', [ApprovalController::class, 'show'])->name('approval.show');
        Route::get('/approval/requests/{leave}/edit', [ApprovalController::class, 'edit'])->name('approval.edit');
        Route::put('/approval/requests/{leave}', [ApprovalController::class, 'update'])->name('approval.update');
        Route::delete('/approval/requests/{leave}', [ApprovalController::class, 'destroy'])->name('approval.destroy');
        Route::post('/approval/requests/{leave}/ack', [ApprovalController::class, 'ack'])->name('approval.ack');
        Route::post('/approval/requests/{leave}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/requests/{leave}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


Route::get('/', fn() => redirect()->route('login'));
