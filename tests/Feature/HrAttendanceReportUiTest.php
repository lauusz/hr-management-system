<?php

use App\Enums\UserRole;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\Pt;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\InstallsAttendanceEventSchema;

uses(InstallsAttendanceEventSchema::class);

function ensureAttendanceReportTestSchema(object $testCase): void
{
    $ref = new ReflectionMethod($testCase, 'installAttendanceEventSchema');
    $ref->invoke($testCase);

    if (! Schema::hasColumn('attendance_events', 'is_wita')) {
        Schema::table('attendance_events', function (Blueprint $table): void {
            $table->boolean('is_wita')->nullable();
        });
    }

}

it('allows HR roles to view /hr/reports/attendance and denies non-HR roles', function () {
    ensureAttendanceReportTestSchema($this);

    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($hrd)
        ->get(route('hr.reports.attendance'))
        ->assertOk()
        ->assertSee('Rekap Kehadiran Bulanan');

    $this->actingAs($hrStaff)
        ->get(route('hr.reports.attendance'))
        ->assertOk();

    $this->actingAs($employee)
        ->get(route('hr.reports.attendance'))
        ->assertForbidden();

    $this->actingAs($employee)
        ->getJson(route('hr.reports.attendance.detail', [
            'employee' => $employee->id,
            'month' => 11,
            'year' => 2026,
            'category' => 'CUTI',
        ]))
        ->assertForbidden();
});

it('renders redesigned user-friendly UI, exact 12-column order, KPI stats, sticky table, and z-index 9999 modal', function () {
    ensureAttendanceReportTestSchema($this);

    $pt = Pt::create(['name' => 'PT Samudra Nusantara']);
    $position = Position::create(['name' => 'Staff Operasional']);

    $hrd = User::factory()->create([
        'name' => 'Admin HRD Utama',
        'role' => UserRole::HRD,
    ]);

    $lateEmployee = User::factory()->create([
        'name' => 'Budi Santoso',
        'role' => UserRole::EMPLOYEE,
        'position_id' => $position->id,
        'status' => User::STATUS_ACTIVE,
    ]);
    EmployeeProfile::create([
        'user_id' => $lateEmployee->id,
        'pt_id' => $pt->id,
    ]);

    $inactiveEmployee = User::factory()->create([
        'name' => 'Citra Lestari',
        'role' => UserRole::EMPLOYEE,
        'status' => 'INACTIVE',
    ]);

    // Insert 1 late event on 2026-10-28 (within Nov 2026 cut-off: 26 Oct 2026 - 25 Nov 2026)
    DB::table('attendance_events')->insert([
        'user_id' => $lateEmployee->id,
        'event_type' => 'CLOCK_IN',
        'type' => 'WFO',
        'occurred_at' => '2026-10-28 08:17:00',
        'work_date' => '2026-10-28',
        'shift_start_time' => '08:00:00',
        'is_overnight' => false,
        'is_wita' => false,
        'approval_status' => 'APPROVED',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Insert 2 approved CUTI days within period
    $leave = LeaveRequest::create([
        'user_id' => $lateEmployee->id,
        'type' => 'CUTI',
        'start_date' => '2026-11-10',
        'end_date' => '2026-11-11',
        'reason' => 'Keperluan keluarga',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);
    DB::table('leave_request_days')->insert([
        ['leave_request_id' => $leave->id, 'leave_date' => '2026-11-10', 'created_at' => now(), 'updated_at' => now()],
        ['leave_request_id' => $leave->id, 'leave_date' => '2026-11-11', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = $this->actingAs($hrd)->get(route('hr.reports.attendance', [
        'month' => 11,
        'year' => 2026,
    ]));

    $response->assertOk()
        ->assertSee('26 Oktober 2026')
        ->assertSee('25 November 2026')
        ->assertSee('Total Hari Periode')
        ->assertSee('PT Samudra Nusantara')
        ->assertSee('Staff Operasional')
        ->assertSee('Nonaktif')
        ->assertSeeInOrder([
            'NAMA',
            'JABATAN',
            'TELAT',
            'POT UM',
            'IZIN TELAT',
            'SET HARI',
            'CUTI',
            'SAKIT',
            'IZIN',
            'CUTI KHUSUS',
            'ALPHA',
            'LIBUR SPV',
        ])
        ->assertSee('aria-label="Rincian TELAT Budi Santoso"', false)
        ->assertSee('aria-label="Rincian CUTI Budi Santoso"', false)
        ->assertSee('data-report-live-search', false)
        ->assertSee('data-report-results', false)
        ->assertSee('z-index: 9999;', false);

    // Verify detail JSON endpoint for TELAT and CUTI
    $this->actingAs($hrd)
        ->getJson(route('hr.reports.attendance.detail', [
            'employee' => $lateEmployee->id,
            'month' => 11,
            'year' => 2026,
            'category' => 'TELAT',
        ]))
        ->assertOk()
        ->assertJson([
            'employee' => 'Budi Santoso',
            'category' => 'Telat',
            'start' => '2026-10-26',
            'end' => '2026-11-25',
            'total' => 1,
            'incomplete' => false,
        ]);

    $this->actingAs($hrd)
        ->getJson(route('hr.reports.attendance.detail', [
            'employee' => $lateEmployee->id,
            'month' => 11,
            'year' => 2026,
            'category' => 'CUTI',
        ]))
        ->assertOk()
        ->assertJson([
            'employee' => 'Budi Santoso',
            'category' => 'Cuti',
            'total' => 2,
        ]);
});

it('renders active filter chips, empty state when no employees match, and handles invalid parameters safely', function () {
    ensureAttendanceReportTestSchema($this);

    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $this->actingAs($hrd)
        ->get(route('hr.reports.attendance', [
            'q' => 'NamaTidakPernahAda999',
            'month' => 1,
            'year' => 2027,
        ]))
        ->assertOk()
        ->assertSee('26 Desember 2026')
        ->assertSee('25 Januari 2027')
        ->assertSee('Filter Aktif')
        ->assertSee('Belum Ada Data Karyawan');

    $this->actingAs($hrd)
        ->from(route('hr.reports.attendance'))
        ->get(route('hr.reports.attendance', ['month' => 99]))
        ->assertRedirect(route('hr.reports.attendance'));
});
