<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\Division;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\Pt;
use App\Models\User;
use Carbon\Carbon;
// ⚠️ PERINGATAN: JANGAN gunakan LazilyRefreshDatabase / RefreshDatabase
// karena akan men-trigger migrate:fresh yang menghapus SEMUA data.

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

describe('HrLeaveController', function () {
    // =====================================================================
    // INDEX
    // =====================================================================
    describe('index', function () {
        it('HRD can access index', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.index'));

            $response->assertStatus(200);
        });

        it('HR Staff can access index', function () {
            $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);

            actingAs($hrStaff, 'web');

            $response = $this->get(route('hr.leave.index'));

            $response->assertStatus(200);
        });

        it('employee cannot access index', function () {
            $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            actingAs($employee, 'web');

            $response = $this->get(route('hr.leave.index'));

            $response->assertStatus(403);
        });

        it('supervisor cannot access index', function () {
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->get(route('hr.leave.index'));

            $response->assertStatus(403);
        });

        it('shows PENDING_HR leaves', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_HR]);
            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::STATUS_APPROVED]);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.index'));

            $response->assertStatus(200);
            expect($response->viewData('leaves')->total())->toBeGreaterThanOrEqual(1);
        });

        it('index filters by submitted_today', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            // Yesterday's leave
            LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'created_at' => now()->subDay(),
            ]);
            // Today's leave
            LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'created_at' => now(),
            ]);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.index', ['submitted_today' => true]));

            $response->assertStatus(200);
            expect($response->viewData('leaves')->total())->toBe(1);
        });

        it('index filters by period_today', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            // Leave that includes today
            LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'start_date' => now()->subDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
            ]);
            // Leave that doesn't include today
            LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
            ]);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.index', ['period_today' => true]));

            $response->assertStatus(200);
            expect($response->viewData('leaves')->total())->toBe(1);
        });

        it('unauthenticated redirected to login', function () {
            $response = $this->get(route('hr.leave.index'));

            $response->assertRedirect('/login');
        });
    });

    // =====================================================================
    // MASTER
    // =====================================================================
    describe('master', function () {
        it('HRD can access master page', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.master'));

            $response->assertStatus(200);
        });

        it('master shows all statuses', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_HR]);
            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::STATUS_APPROVED]);
            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::STATUS_REJECTED]);
            LeaveRequest::factory()->forUser($employee)->create(['status' => 'BATAL']);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.master'));

            $response->assertStatus(200);
            expect($response->viewData('items')->total())->toBe(4);
        });

        it('master filters by status', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_HR]);
            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::STATUS_APPROVED]);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.master', ['status' => 'APPROVED']));

            $response->assertStatus(200);
            expect($response->viewData('items')->total())->toBe(1);
        });

        it('master filters by type', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            LeaveRequest::factory()->forUser($employee)->create(['type' => LeaveType::CUTI]);
            LeaveRequest::factory()->forUser($employee)->create(['type' => LeaveType::SAKIT]);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.master', ['type' => 'CUTI']));

            $response->assertStatus(200);
            expect($response->viewData('items')->total())->toBe(1);
        });

        it('master filters by search query', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee1 = User::factory()->create(['name' => 'John Doe']);
            $employee2 = User::factory()->create(['name' => 'Jane Smith']);

            LeaveRequest::factory()->forUser($employee1)->create();
            LeaveRequest::factory()->forUser($employee2)->create();

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.master', ['q' => 'John']));

            $response->assertStatus(200);
            expect($response->viewData('items')->total())->toBe(1);
        });

        it('master filters by PT', function () {
            $pt1 = Pt::factory()->create(['name' => 'PT Alpha']);
            $pt2 = Pt::factory()->create(['name' => 'PT Beta']);
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $emp1 = User::factory()->create();
            $emp2 = User::factory()->create();

            EmployeeProfile::factory()->forUser($emp1)->create(['pt_id' => $pt1->id]);
            EmployeeProfile::factory()->forUser($emp2)->create(['pt_id' => $pt2->id]);

            LeaveRequest::factory()->forUser($emp1)->create();
            LeaveRequest::factory()->forUser($emp2)->create();

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.master', ['pt_id' => $pt1->id]));

            $response->assertStatus(200);
            expect($response->viewData('items')->total())->toBe(1);
        });
    });

    // =====================================================================
    // SHOW
    // =====================================================================
    describe('show', function () {
        it('HRD can view leave details', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create();

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.show', $leave->id));

            $response->assertStatus(200);
            expect($response->viewData('item')->id)->toBe($leave->id);
        });

        it('employee cannot view leave in HR page', function () {
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create();

            actingAs($employee, 'web');

            $response = $this->get(route('hr.leave.show', $leave->id));

            $response->assertStatus(403);
        });

        it('returns 404 for non-existent leave', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.show', 99999));

            $response->assertStatus(404);
        });
    });

    // =====================================================================
    // APPROVE
    // =====================================================================
    describe('approve', function () {
        it('HRD can approve PENDING_HR leave', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type'   => LeaveType::IZIN,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.approve', $leave->id));

            $response->assertRedirect();
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and($leave->approved_by)->toBe($hrd->id)
                ->and($leave->approved_at)->toBeTruthy();
        });

        it('HRD can approve PENDING_HR CUTI with balance deduction', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);
            EmployeeProfile::factory()->forUser($employee)->joinedYearsAgo(2)->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type'   => LeaveType::CUTI,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(6)->toDateString(),
            ]);

            actingAs($hrd, 'web');

            $this->post(route('hr.leave.approve', $leave->id), ['deduct_leave' => '1']);

            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and($employee->leave_balance)->toBeLessThan(12);
        });

        it('HRD cannot approve own leave request', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $leave = LeaveRequest::factory()->forUser($hrd)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type'   => LeaveType::IZIN,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.approve', $leave->id));

            $response->assertSessionHas('error', 'Etika Profesi: Anda tidak dapat menyetujui pengajuan Anda sendiri.');
        });

        it('HR Staff cannot approve PENDING_HR CUTI (only HRD)', function () {
            $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type'   => LeaveType::CUTI,
            ]);

            actingAs($hrStaff, 'web');

            $response = $this->post(route('hr.leave.approve', $leave->id));

            $response->assertStatus(403);
        });

        it('HR Staff CAN approve non-CUTI PENDING_HR', function () {
            $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type'   => LeaveType::IZIN,
            ]);

            actingAs($hrStaff, 'web');

            $this->post(route('hr.leave.approve', $leave->id));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('cannot approve already approved leave', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.approve', $leave->id));

            $response->assertStatus(400);
        });

        it('cannot approve rejected leave', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::STATUS_REJECTED,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.approve', $leave->id));

            $response->assertStatus(400);
        });

        it('approve saves notes_hrd', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($hrd, 'web');

            $this->post(route('hr.leave.approve', $leave->id), [
                'notes_hrd' => 'Disetujui dengan catatan khusus',
            ]);

            $leave->refresh();
            expect($leave->notes_hrd)->toBe('Disetujui dengan catatan khusus');
        });

        it('employee cannot approve', function () {
            $employee = User::factory()->create();
            $other = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($other)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('hr.leave.approve', $leave->id));

            $response->assertStatus(403);
        });
    });

    // =====================================================================
    // REJECT
    // =====================================================================
    describe('reject', function () {
        it('HRD can reject PENDING_HR leave', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create(['leave_balance' => 12]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type'   => LeaveType::CUTI,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.reject', $leave->id), [
                'notes_hrd' => 'Ditolak karena alasan',
            ]);

            $response->assertRedirect();
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_REJECTED)
                ->and($leave->notes_hrd)->toBe('Ditolak karena alasan');
        });

        it('HRD cannot reject own leave', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $leave = LeaveRequest::factory()->forUser($hrd)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.reject', $leave->id), [
                'notes_hrd' => 'Tolak sendiri',
            ]);

            $response->assertSessionHas('error');
        });

        it('rejects without notes_hrd returns error', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.reject', $leave->id), []);

            $response->assertSessionHasErrors(['notes_hrd']);
        });

        it('cannot reject already rejected leave', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::STATUS_REJECTED,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.reject', $leave->id), [
                'notes_hrd' => 'Tolak',
            ]);

            $response->assertStatus(400);
        });

        it('employee cannot reject', function () {
            $employee = User::factory()->create();
            $other = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($other)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($employee, 'web');

            $response = $this->post(route('hr.leave.reject', $leave->id), [
                'notes_hrd' => 'Tolak',
            ]);

            $response->assertStatus(403);
        });
    });

    // =====================================================================
    // MANUAL CREATE (create_manual / store_manual)
    // =====================================================================
    describe('create_manual', function () {
        it('HRD can access manual create page', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);

            actingAs($hrd, 'web');

            $response = $this->get(route('hr.leave.manual.create'));

            $response->assertStatus(200);
            expect($response->viewData('employees'))->toBeTruthy();
        });

        it('employee cannot access manual create', function () {
            $employee = User::factory()->create();

            actingAs($employee, 'web');

            $response = $this->get(route('hr.leave.manual.create'));

            $response->assertStatus(403);
        });
    });

    describe('store_manual', function () {
        it('HRD can create manual leave request', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.manual.store'), [
                'user_id'    => $employee->id,
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Manual entry by HR',
                'status'     => LeaveRequest::STATUS_APPROVED,
            ]);

            $response->assertRedirect(route('hr.leave.master'));

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            expect($leave)->toBeTruthy()
                ->and($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('manual create sets supervisor_ack_at when not PENDING_SUPERVISOR', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            actingAs($hrd, 'web');

            $this->post(route('hr.leave.manual.store'), [
                'user_id'    => $employee->id,
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Manual',
                'status'     => LeaveRequest::STATUS_APPROVED,
            ]);

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            expect($leave->supervisor_ack_at)->toBeTruthy();
        });

        it('manual create with PENDING_SUPERVISOR does not set supervisor_ack_at', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create(['direct_supervisor_id' => User::factory()->create()->id]);

            actingAs($hrd, 'web');

            $this->post(route('hr.leave.manual.store'), [
                'user_id'    => $employee->id,
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Manual',
                'status'     => LeaveRequest::PENDING_SUPERVISOR,
            ]);

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            expect($leave->supervisor_ack_at)->toBeNull();
        });

        it('validates required user_id', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.manual.store'), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
            ]);

            $response->assertSessionHasErrors(['user_id']);
        });

        it('validates end_date after start_date', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.manual.store'), [
                'user_id'    => $employee->id,
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
            ]);

            $response->assertSessionHasErrors(['end_date']);
        });
    });

    // =====================================================================
    // UPDATE
    // =====================================================================
    describe('update', function () {
        it('HRD can update leave request', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'reason' => 'Original reason',
            ]);

            actingAs($hrd, 'web');

            $response = $this->put(route('hr.leave.update', $leave->id), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date'   => now()->addDays(2)->toDateString(),
                'reason'     => 'Updated reason',
            ]);

            $response->assertRedirect();
            $leave->refresh();
            expect($leave->reason)->toBe('Updated reason');
        });

        it('HRD can change status during update', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
            ]);

            actingAs($hrd, 'web');

            $this->put(route('hr.leave.update', $leave->id), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date'   => now()->addDays(2)->toDateString(),
                'status'     => LeaveRequest::STATUS_APPROVED,
            ]);

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('status change from APPROVED to REJECTED triggers refund', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create(['leave_balance' => 10]);
            EmployeeProfile::factory()->forUser($employee)->joinedYearsAgo(2)->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status'     => LeaveRequest::STATUS_APPROVED,
                'type'       => LeaveType::CUTI,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(6)->toDateString(),
            ]);

            // Deduct first (simulate approval)
            $service = new \App\Services\LeaveBalanceService();
            $service->deductLeaveBalanceForLeave($leave);
            $employee->refresh();

            actingAs($hrd, 'web');

            $this->put(route('hr.leave.update', $leave->id), [
                'type'       => LeaveType::CUTI,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(6)->toDateString(),
                'status'     => LeaveRequest::STATUS_REJECTED,
            ]);

            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_REJECTED)
                ->and($employee->leave_balance)->toBe(12); // Refunded back
        });
    });

    // =====================================================================
    // UNAUTHENTICATED
    // =====================================================================
    describe('auth', function () {
        it('unauthenticated for show', function () {
            $response = $this->get(route('hr.leave.show', 1));

            $response->assertRedirect('/login');
        });

        it('unauthenticated for approve', function () {
            $response = $this->post(route('hr.leave.approve', 1));

            $response->assertRedirect('/login');
        });

        it('unauthenticated for reject', function () {
            $response = $this->post(route('hr.leave.reject', 1));

            $response->assertRedirect('/login');
        });

        it('unauthenticated for manual create', function () {
            $response = $this->get(route('hr.leave.manual.create'));

            $response->assertRedirect('/login');
        });

        it('unauthenticated for manual store', function () {
            $response = $this->post(route('hr.leave.manual.store'), []);

            $response->assertRedirect('/login');
        });
    });
});
