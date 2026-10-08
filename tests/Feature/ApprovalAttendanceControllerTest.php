<?php

use App\Enums\UserRole;
use App\Http\Controllers\ApprovalAttendanceController;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

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

    it('keeps late attendance status when HRD approves a pending WFO attendance', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $attendance = Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'type' => 'WFO',
            'status' => 'TERLAMBAT',
        ]);

        actingAs($hrd, 'web');

        $response = $this->post(route('hr.approval_attendance.approve', $attendance));

        $response->assertSessionHas('success');
        $attendance->refresh();
        expect($attendance->approval_status)->toBe('APPROVED')
            ->and($attendance->status)->toBe('TERLAMBAT')
            ->and((int) $attendance->approved_by)->toBe($hrd->id);
    });

    it('lists pending WFO and DINAS_LUAR attendances by latest upload', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $anotherEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $older = Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'type' => 'WFO',
            'created_at' => now()->subMinute(),
        ]);
        $newer = Attendance::factory()->for($anotherEmployee)->create([
            'approval_status' => 'PENDING',
            'type' => 'DINAS_LUAR',
            'created_at' => now(),
        ]);

        actingAs($hrd, 'web');

        $this->get(route('hr.approval_attendance.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'data-attendance-id="'.$newer->id.'"',
                'data-attendance-id="'.$older->id.'"',
            ], false)
            ->assertSee('data-attendance-type="WFO"', false)
            ->assertSee('data-attendance-type="DINAS_LUAR"', false);
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
            ->and($attendance->status)->toBe('REJECTED')
            ->and((int) $attendance->approved_by)->toBe($hrStaff->id);
    });

    it('does not overwrite a decision made after the attendance was loaded', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $attendance = Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'status' => 'HADIR',
        ]);

        Attendance::query()->whereKey($attendance->id)->update([
            'approval_status' => 'REJECTED',
            'status' => 'REJECTED',
        ]);

        actingAs($hrd, 'web');

        $response = app(ApprovalAttendanceController::class)->approve(new Request, $attendance);

        expect($response->getSession()->get('error'))->toBe('Absensi ini tidak dalam status menunggu.');

        expect($attendance->fresh()->approval_status)->toBe('REJECTED')
            ->and($attendance->fresh()->status)->toBe('REJECTED');
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
