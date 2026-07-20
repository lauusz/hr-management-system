<?php

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\User;

use function Pest\Laravel\actingAs;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

describe('ApprovalAttendanceController', function () {
    it('uses global image viewer for attendance photos', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'type' => 'DINAS_LUAR',
            'clock_in_photo' => 'attendance/foto.jpg',
        ]);

        actingAs($hrd, 'web');

        $response = $this->get(route('hr.approval_attendance.index'));

        $response->assertOk()
            ->assertSee('data-image-viewer-src=', false)
            ->assertDontSee('id="simple-viewer"', false);
    });

    it('allows HRD to approve pending attendance', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $attendance = Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'type' => 'DINAS_LUAR',
        ]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.approval_attendance.approve', $attendance));

        $response->assertSessionHas('success');
        $attendance->refresh();
        expect($attendance->approval_status)->toBe('APPROVED')
            ->and($attendance->status)->toBe('HADIR')
            ->and((int) $attendance->approved_by)->toBe($hrd->id);
    });

    it('allows HR STAFF to reject pending attendance', function () {
        $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $attendance = Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'type' => 'DINAS_LUAR',
        ]);

        actingAs($hrStaff, 'web');

        $response = $this->post(route('hr.approval_attendance.reject', $attendance), [
            'rejection_note' => 'Dokumen tidak lengkap',
        ]);

        $response->assertSessionHas('success');
        $attendance->refresh();
        expect($attendance->approval_status)->toBe('REJECTED')
            ->and($attendance->rejection_note)->toBe('Dokumen tidak lengkap')
            ->and((int) $attendance->approved_by)->toBe($hrStaff->id);
    });

    it('prevents non-HR employee from approving attendance', function () {
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $otherEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $attendance = Attendance::factory()->for($otherEmployee)->create([
            'approval_status' => 'PENDING',
            'type' => 'DINAS_LUAR',
        ]);

        actingAs($employee, 'web');

        $this->post(route('hr.approval_attendance.approve', $attendance))
            ->assertForbidden();

        $attendance->refresh();
        expect($attendance->approval_status)->toBe('PENDING');
    });

    it('prevents approving already approved attendance', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $attendance = Attendance::factory()->for($employee)->create([
            'approval_status' => 'APPROVED',
            'type' => 'DINAS_LUAR',
        ]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.approval_attendance.approve', $attendance));

        $response->assertSessionHas('error');
    });

    it('prevents rejecting already rejected attendance', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $attendance = Attendance::factory()->for($employee)->create([
            'approval_status' => 'REJECTED',
            'type' => 'DINAS_LUAR',
        ]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.approval_attendance.reject', $attendance), [
            'rejection_note' => 'Alasan lain',
        ]);

        $response->assertSessionHas('error');
    });
});
