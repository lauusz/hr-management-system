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

    it('uses compact labels for attendance proof buttons', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'clock_in_photo' => 'attendance/foto.jpg',
            'clock_out_photo' => 'attendance/foto-out.jpg',
            'clock_in_lat' => '-6.200000',
            'clock_in_lng' => '106.816666',
            'clock_out_lat' => '-6.210000',
            'clock_out_lng' => '106.826666',
        ]);

        actingAs($hrd, 'web');

        $this->get(route('hr.approval_attendance.index'))
            ->assertOk()
            ->assertSee('Foto', false)
            ->assertSee('Lokasi', false)
            ->assertDontSee('Lihat Foto')
            ->assertDontSee('Cek Lokasi')
            ->assertDontSee('Foto In')
            ->assertDontSee('Foto Out')
            ->assertDontSee('Lokasi In')
            ->assertDontSee('Lokasi Out');
    });

    it('shows the Clock In location in an approval modal', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'clock_in_lat' => '-6.200000',
            'clock_in_lng' => '106.816666',
        ]);

        actingAs($hrd, 'web');

        $this->get(route('hr.approval_attendance.index'))
            ->assertOk()
            ->assertSee('data-approval-attendance-location', false)
            ->assertSee('id="approval-attendance-location-map"', false)
            ->assertSee('id="approval-attendance-location-frame"', false)
            ->assertDontSee('google.com/maps/search/', false);
    });

    it('shows Clock Out details for pending attendance without Clock In', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $clockOutAt = now()->setTime(17, 15);

        Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'type' => 'WFO',
            'status' => 'TIDAK CLOCK IN',
            'completion_status' => Attendance::COMPLETION_MISSED_CLOCK_IN,
            'clock_in_at' => null,
            'clock_out_at' => $clockOutAt,
            'clock_out_photo' => 'attendance/clockout.jpg',
            'clock_out_lat' => '-6.210000',
            'clock_out_lng' => '106.826666',
        ]);

        actingAs($hrd, 'web');

        $this->get(route('hr.approval_attendance.index'))
            ->assertOk()
            ->assertSee('<th>Clock In</th>', false)
            ->assertSee('<th>Clock Out</th>', false)
            ->assertDontSee('<th>Aksi Absensi</th>', false)
            ->assertDontSee('WFO', false)
            ->assertSee($clockOutAt->format('H:i'))
            ->assertSee('storage/attendance/clockout.jpg', false)
            ->assertSee('data-approval-attendance-location-label="Clock Out"', false)
            ->assertDontSee('google.com/maps/search/', false);
    });

    it('shows Clock In and Clock Out in separate columns when both are recorded', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'type' => 'WFO',
            'clock_in_at' => now()->setTime(8, 0),
            'clock_out_at' => now()->setTime(17, 15),
            'clock_in_photo' => 'attendance/clockin.jpg',
            'clock_out_photo' => 'attendance/clockout.jpg',
            'clock_in_lat' => '-6.200000',
            'clock_in_lng' => '106.816666',
            'clock_out_lat' => '-6.210000',
            'clock_out_lng' => '106.826666',
        ]);

        actingAs($hrd, 'web');

        $this->get(route('hr.approval_attendance.index'))
            ->assertOk()
            ->assertSee('<th>Clock In</th>', false)
            ->assertSee('<th>Clock Out</th>', false)
            ->assertDontSee('<th>Aksi Absensi</th>', false)
            ->assertSeeInOrder(['08:00', '17:15'], false)
            ->assertSee('storage/attendance/clockin.jpg', false)
            ->assertSee('storage/attendance/clockout.jpg', false)
            ->assertSee('data-approval-attendance-location-label="Clock In"', false)
            ->assertSee('data-approval-attendance-location-label="Clock Out"', false);
    });

    it('shows a work-date range only for pending overnight attendance', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $normalEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $overnightEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $workDate = now()->setDate(2026, 10, 12)->startOfDay();

        $normalAttendance = Attendance::factory()->for($normalEmployee)->create([
            'approval_status' => 'PENDING',
            'date' => $workDate,
            'normal_start_time' => '08:00:00',
            'normal_end_time' => '17:00:00',
            'clock_in_at' => $workDate->copy()->setTime(8, 30),
            'clock_out_at' => $workDate->copy()->setTime(17, 0),
        ]);
        $overnightAttendance = Attendance::factory()->for($overnightEmployee)->create([
            'approval_status' => 'PENDING',
            'date' => $workDate,
            'normal_start_time' => '20:00:00',
            'normal_end_time' => '06:00:00',
            'clock_in_at' => $workDate->copy()->setTime(20, 0),
            'clock_out_at' => $workDate->copy()->addDay()->setTime(6, 0),
        ]);

        actingAs($hrd, 'web');

        expect($normalAttendance->fresh()->work_date_label)->toBe('12 Okt 2026')
            ->and($overnightAttendance->fresh()->work_date_label)->toBe('12 Okt - 13 Okt 2026');

        $this->get(route('hr.approval_attendance.index'))
            ->assertOk()
            ->assertSee('12 Okt 2026')
            ->assertSee('12 Okt - 13 Okt 2026')
            ->assertSee('08:30')
            ->assertSee('17:00')
            ->assertSee('20:00')
            ->assertSee('06:00');
    });

    it('shows bulk approval controls for pending attendances', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
        ]);

        actingAs($hrd, 'web');

        $this->get(route('hr.approval_attendance.index'))
            ->assertOk()
            ->assertSee('name="attendance_ids[]"', false)
            ->assertSee('Terima Semua')
            ->assertSee('id="bulkApproveBar"', false)
            ->assertSee('id="bulkApproveModal"', false)
            ->assertSee('max-height: 136px; overflow-y: auto;', false);
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

    it('approves only selected pending attendances in bulk', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $anotherEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $unselectedEmployee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $firstSelected = Attendance::factory()->for($employee)->create([
            'approval_status' => 'PENDING',
            'status' => 'TERLAMBAT',
        ]);
        $secondSelected = Attendance::factory()->for($anotherEmployee)->create([
            'approval_status' => 'PENDING',
            'status' => 'HADIR',
        ]);
        $unselected = Attendance::factory()->for($unselectedEmployee)->create([
            'approval_status' => 'PENDING',
        ]);

        actingAs($hrd, 'web');

        $this->post('/hr/approval-attendance/bulk-approve', [
            'attendance_ids' => [$firstSelected->id, $secondSelected->id],
        ])->assertRedirect()
            ->assertSessionHas('success', '2 absensi berhasil disetujui.');

        expect($firstSelected->fresh()->approval_status)->toBe('APPROVED')
            ->and($firstSelected->fresh()->status)->toBe('TERLAMBAT')
            ->and((int) $firstSelected->fresh()->approved_by)->toBe($hrd->id)
            ->and($secondSelected->fresh()->approval_status)->toBe('APPROVED')
            ->and($unselected->fresh()->approval_status)->toBe('PENDING');
    });

    it('does not approve an attendance that is no longer pending in bulk', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $attendance = Attendance::factory()->for($employee)->create([
            'approval_status' => 'REJECTED',
            'status' => 'REJECTED',
            'rejection_note' => 'Lokasi tidak sesuai',
        ]);

        actingAs($hrd, 'web');

        $this->post('/hr/approval-attendance/bulk-approve', [
            'attendance_ids' => [$attendance->id],
        ])->assertRedirect()
            ->assertSessionHas('error', 'Absensi yang dipilih tidak lagi dalam status menunggu.');

        expect($attendance->fresh()->approval_status)->toBe('REJECTED')
            ->and($attendance->fresh()->status)->toBe('REJECTED')
            ->and($attendance->fresh()->rejection_note)->toBe('Lokasi tidak sesuai');
    });

    it('requires at least one attendance for bulk approval', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);

        actingAs($hrd, 'web');

        $this->from(route('hr.approval_attendance.index'))
            ->post('/hr/approval-attendance/bulk-approve', [])
            ->assertRedirect(route('hr.approval_attendance.index'))
            ->assertSessionHasErrors('attendance_ids');
    });

    it('lists pending attendances by latest upload without attendance type labels', function () {
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
            ->assertDontSee('data-attendance-type=', false)
            ->assertDontSee('class="badge-type', false);
    });

    it('filters pending attendances by submission date range, name, Clock In, and Clock Out', function () {
        $hrd = User::factory()->create(['role' => UserRole::HRD]);
        $targetUser = User::factory()->create(['role' => UserRole::EMPLOYEE, 'name' => 'Nadia Pratama']);
        $hasClockOutUser = User::factory()->create(['role' => UserRole::EMPLOYEE, 'name' => 'Nadia Pratama Kedua']);
        $otherUser = User::factory()->create(['role' => UserRole::EMPLOYEE, 'name' => 'Nadia Lestari']);
        $date = now()->startOfDay();

        $target = Attendance::factory()->for($targetUser)->create([
            'approval_status' => 'PENDING',
            'date' => $date,
            'clock_in_at' => $date->copy()->setTime(8, 0),
            'clock_out_at' => null,
        ]);
        $hasClockOut = Attendance::factory()->for($hasClockOutUser)->create([
            'approval_status' => 'PENDING',
            'date' => $date,
            'clock_in_at' => $date->copy()->setTime(8, 0),
            'clock_out_at' => $date->copy()->setTime(17, 0),
        ]);
        $otherName = Attendance::factory()->for($otherUser)->create([
            'approval_status' => 'PENDING',
            'date' => $date,
            'clock_in_at' => $date->copy()->setTime(8, 0),
            'clock_out_at' => null,
        ]);
        $otherDate = Attendance::factory()->for($targetUser)->create([
            'approval_status' => 'PENDING',
            'date' => $date->copy()->subDay(),
            'clock_in_at' => $date->copy()->subDay()->setTime(8, 0),
            'clock_out_at' => null,
            'created_at' => $date->copy()->subDay()->setTime(8, 0),
            'updated_at' => $date->copy()->subDay()->setTime(8, 0),
        ]);

        actingAs($hrd, 'web');

        $this->get(route('hr.approval_attendance.index', [
            'submitted_range' => $date->toDateString().' sampai '.$date->toDateString(),
            'name' => 'Nadia Pratama',
            'clock_in' => 'ada',
            'clock_out' => 'tidak_ada',
        ]))
            ->assertOk()
            ->assertSee('id="approval-attendance-filter-panel"', false)
            ->assertSee('data-approval-attendance-filter-toggle', false)
            ->assertSee('id="attendance-filter-date-range"', false)
            ->assertSee('grid-template-columns: repeat(4, minmax(0, 1fr))', false)
            ->assertSee('data-approval-attendance-live-search', false)
            ->assertSee('data-approval-attendance-results', false)
            ->assertSee("headers: { 'X-Requested-With': 'XMLHttpRequest' }", false)
            ->assertSee('request?.abort()', false)
            ->assertDontSee('setTimeout(() => form.submit(), 350)', false)
            ->assertSee('data-attendance-id="'.$target->id.'"', false)
            ->assertDontSee('data-attendance-id="'.$hasClockOut->id.'"', false)
            ->assertDontSee('data-attendance-id="'.$otherName->id.'"', false)
            ->assertDontSee('data-attendance-id="'.$otherDate->id.'"', false);
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
