<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\Pt;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

// âš ï¸ PERINGATAN: JANGAN gunakan LazilyRefreshDatabase / RefreshDatabase
// karena akan men-trigger migrate:fresh yang menghapus SEMUA data.

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

            EmployeeProfile::create(['user_id' => $emp1->id, 'pt_id' => $pt1->id, 'kategori' => 'KONTRAK']);
            EmployeeProfile::create(['user_id' => $emp2->id, 'pt_id' => $pt2->id, 'kategori' => 'KONTRAK']);

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
                'type' => LeaveType::IZIN->value,
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
            EmployeeProfile::create(['user_id' => $employee->id, 'tgl_bergabung' => now()->subYears(2)->toDateString(), 'kategori' => 'KONTRAK']);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
            ]);

            actingAs($hrd, 'web');

            $this->post(route('hr.leave.approve', $leave->id), ['deduct_leave' => '1']);

            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and($employee->leave_balance)->toBeLessThan(12);
        });

        it('HRD final approve deletes duplicate pending overlaps', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-06-10',
                'end_date' => '2026-06-12',
            ]);

            $duplicate = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'type' => LeaveType::IZIN->value,
                'start_date' => '2026-06-11',
                'end_date' => '2026-06-13',
            ]);

            $differentType = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-11',
                'end_date' => '2026-06-12',
            ]);

            actingAs($hrd, 'web');

            $this->post(route('hr.leave.approve', $leave->id));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and(LeaveRequest::find($duplicate->id))->toBeNull()
                ->and(LeaveRequest::find($differentType->id))->not->toBeNull();
        });

        it('HRD cannot approve own leave request', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $leave = LeaveRequest::factory()->forUser($hrd)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.approve', $leave->id));

            $response->assertSessionHas('error', 'Etika Profesi: Anda tidak dapat menyetujui pengajuan Anda sendiri.');
        });

        it('HR Staff can approve PENDING_HR CUTI for employee', function () {
            $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
            ]);

            actingAs($hrStaff, 'web');

            $this->post(route('hr.leave.approve', $leave->id));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('HR Staff CAN approve non-CUTI PENDING_HR', function () {
            $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($hrStaff, 'web');

            $this->post(route('hr.leave.approve', $leave->id));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        // --- MANAGER tests ---
        it('HR Staff CANNOT approve MANAGER CUTI even when HR Staff flag is true', function () {
            $hrStaff = User::factory()->create([
                'role' => UserRole::HR_STAFF,
                'hr_staff_can_approve_non_cuti' => true,
            ]);
            $manager = User::factory()->create([
                'role' => UserRole::MANAGER,
            ]);
            $leave = LeaveRequest::factory()->forUser($manager)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
            ]);

            actingAs($hrStaff, 'web');

            $response = $this->post(route('hr.leave.approve', $leave->id));
            $response->assertStatus(403);
        });

        it('HR Staff CAN approve MANAGER non-CUTI when HR Staff flag is true', function () {
            $hrStaff = User::factory()->create([
                'role' => UserRole::HR_STAFF,
                'hr_staff_can_approve_non_cuti' => true,
            ]);
            $manager = User::factory()->create([
                'role' => UserRole::MANAGER,
                'hr_staff_can_approve_non_cuti' => false,
            ]);
            $leave = LeaveRequest::factory()->forUser($manager)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::SAKIT->value,
            ]);

            actingAs($hrStaff, 'web');

            $this->post(route('hr.leave.approve', $leave->id));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('HR Staff CANNOT approve MANAGER non-CUTI when HR Staff flag is false', function () {
            $hrStaff = User::factory()->create([
                'role' => UserRole::HR_STAFF,
                'hr_staff_can_approve_non_cuti' => false,
            ]);
            $manager = User::factory()->create([
                'role' => UserRole::MANAGER,
                'hr_staff_can_approve_non_cuti' => true,
            ]);
            $leave = LeaveRequest::factory()->forUser($manager)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($hrStaff, 'web');

            $response = $this->post(route('hr.leave.approve', $leave->id));
            $response->assertStatus(403);
        });

        // --- SUPERVISOR tests ---
        it('Manager acknowledgment allows HR Staff to approve SUPERVISOR CUTI without special flag', function () {
            $manager = User::factory()->create([
                'role' => UserRole::MANAGER,
            ]);
            $hrStaff = User::factory()->create([
                'role' => UserRole::HR_STAFF,
                'hr_staff_can_approve_non_cuti' => false,
            ]);
            $supervisor = User::factory()->create([
                'role' => UserRole::SUPERVISOR,
                'direct_supervisor_id' => null,
                'manager_id' => $manager->id,
            ]);
            $leave = LeaveRequest::factory()->forUser($supervisor)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'type' => LeaveType::CUTI->value,
            ]);

            actingAs($manager, 'web');
            $this->post(route('approval.ack', $leave->id));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR)
                ->and($leave->supervisor_ack_at)->not->toBeNull();

            actingAs($hrStaff, 'web');

            $this->post(route('hr.leave.approve', $leave->id));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('HR Staff CAN approve SUPERVISOR non-CUTI without special flag', function () {
            $hrStaff = User::factory()->create([
                'role' => UserRole::HR_STAFF,
                'hr_staff_can_approve_non_cuti' => false,
            ]);
            $supervisor = User::factory()->create([
                'role' => UserRole::SUPERVISOR,
                'hr_staff_can_approve_non_cuti' => false,
            ]);
            $leave = LeaveRequest::factory()->forUser($supervisor)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($hrStaff, 'web');

            $this->post(route('hr.leave.approve', $leave->id));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('HR Staff CAN reject SUPERVISOR leave after Manager acknowledgment', function () {
            $hrStaff = User::factory()->create([
                'role' => UserRole::HR_STAFF,
                'hr_staff_can_approve_non_cuti' => false,
            ]);
            $supervisor = User::factory()->create([
                'role' => UserRole::SUPERVISOR,
                'hr_staff_can_approve_non_cuti' => true,
            ]);
            $leave = LeaveRequest::factory()->forUser($supervisor)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::SAKIT->value,
            ]);

            actingAs($hrStaff, 'web');

            $this->post(route('hr.leave.reject', $leave->id), [
                'notes_hrd' => 'Pengajuan belum sesuai kebijakan.',
            ]);

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_REJECTED);
        });

        it('HRD can still approve MANAGER and SUPERVISOR CUTI', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $manager = User::factory()->create([
                'role' => UserRole::MANAGER,
                'hr_staff_can_approve_non_cuti' => false,
            ]);
            $supervisor = User::factory()->create([
                'role' => UserRole::SUPERVISOR,
                'hr_staff_can_approve_non_cuti' => false,
            ]);
            $leaveManager = LeaveRequest::factory()->forUser($manager)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
            ]);
            $leaveSupervisor = LeaveRequest::factory()->forUser($supervisor)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
            ]);

            actingAs($hrd, 'web');

            $this->post(route('hr.leave.approve', $leaveManager->id));
            $leaveManager->refresh();
            expect($leaveManager->status)->toBe(LeaveRequest::STATUS_APPROVED);

            $this->post(route('hr.leave.approve', $leaveSupervisor->id));
            $leaveSupervisor->refresh();
            expect($leaveSupervisor->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('EMPLOYEE approval behavior unchanged for HR Staff', function () {
            $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);

            $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
            $leaveEmployeeCuti = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI_KHUSUS->value,
            ]);
            $leaveEmployeeIzin = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($hrStaff, 'web');

            $this->post(route('hr.leave.approve', $leaveEmployeeCuti->id));
            $leaveEmployeeCuti->refresh();
            expect($leaveEmployeeCuti->status)->toBe(LeaveRequest::STATUS_APPROVED);

            $this->post(route('hr.leave.approve', $leaveEmployeeIzin->id));
            $leaveEmployeeIzin->refresh();
            expect($leaveEmployeeIzin->status)->toBe(LeaveRequest::STATUS_APPROVED);
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

        it('final HR approval of CUTI sent twice only deducts balance once', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
            ]);

            $this->actingAs($hrd, 'web');

            // Request pertama: approve & potong saldo
            $response1 = $this->post(route('hr.leave.approve', $leave->id));
            $response1->assertRedirect();
            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and((float) $employee->leave_balance)->toBe(10.0);

            // Request kedua (kalah race): tidak boleh memotong saldo lagi
            $this->post(route('hr.leave.approve', $leave->id));
            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and((float) $employee->leave_balance)->toBe(10.0)
                ->and(LeaveBalanceTransaction::where('user_id', $employee->id)
                    ->where('transaction_type', LeaveBalanceTransaction::OPENING_BALANCE)
                    ->count())->toBe(1)
                ->and(LeaveBalanceTransaction::where('user_id', $employee->id)
                    ->where('transaction_type', LeaveBalanceTransaction::DEDUCT)
                    ->count())->toBe(1);
        });

        it('stale PENDING_HR instance does not approve when DB moved back to PENDING_SUPERVISOR', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
            ]);

            // Race: instance $leave masih PENDING_HR, tapi DB sudah PENDING_SUPERVISOR.
            LeaveRequest::where('id', $leave->id)->update(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            $this->actingAs($hrd, 'web');

            $controller = app(\App\Http\Controllers\HrLeaveController::class);
            $request = new \Illuminate\Http\Request;
            $response = $controller->approve($request, $leave);

            expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
                ->and($response->getTargetUrl())->toBe(route('hr.leave.index'))
                ->and(session('error'))->toBe('Status pengajuan sudah berubah.');

            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_SUPERVISOR)
                ->and((float) $employee->leave_balance)->toBe(12.0)
                ->and(LeaveBalanceTransaction::where('user_id', $employee->id)->count())->toBe(0);
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
                'type' => LeaveType::CUTI->value,
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

        it('stale leave model inside reject does not overwrite APPROVED status', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create(['leave_balance' => 12]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
            ]);

            // Race: instance $leave masih PENDING_HR, tapi DB sudah APPROVED & saldo dipotong.
            LeaveRequest::where('id', $leave->id)->update(['status' => LeaveRequest::STATUS_APPROVED]);

            actingAs($hrd, 'web');

            $controller = app(\App\Http\Controllers\HrLeaveController::class);
            $request = new \Illuminate\Http\Request(['notes_hrd' => 'Ditolak karena alasan']);
            $response = $controller->reject($request, $leave);

            expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
                ->and($response->getTargetUrl())->toBe(route('hr.leave.index'))
                ->and(session('error'))->toBe('Status pengajuan sudah berubah.');

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        it('rejecting PENDING_HR CUTI does not change balance or ledger', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create(['leave_balance' => 12]);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.reject', $leave->id), [
                'notes_hrd' => 'Ditolak karena alasan',
            ]);

            $response->assertRedirect(route('hr.leave.index'));
            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_REJECTED)
                ->and((float) $employee->leave_balance)->toBe(12.0)
                ->and(LeaveBalanceTransaction::where('user_id', $employee->id)->count())->toBe(0);
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
                'user_id' => $employee->id,
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'reason' => 'Manual entry by HR',
                'status' => LeaveRequest::STATUS_APPROVED,
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
                'user_id' => $employee->id,
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'reason' => 'Manual',
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            expect($leave->supervisor_ack_at)->toBeTruthy();
        });

        it('manual create with PENDING_SUPERVISOR does not set supervisor_ack_at', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create(['direct_supervisor_id' => User::factory()->create()->id]);

            actingAs($hrd, 'web');

            $this->post(route('hr.leave.manual.store'), [
                'user_id' => $employee->id,
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'reason' => 'Manual',
                'status' => LeaveRequest::PENDING_SUPERVISOR,
            ]);

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            expect($leave->supervisor_ack_at)->toBeNull();
        });

        it('manual APPROVED CUTI deducts balance and writes ledger', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.manual.store'), [
                'user_id' => $employee->id,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
                'reason' => 'Manual CUTI',
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            $response->assertRedirect(route('hr.leave.master'));

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            $employee->refresh();
            expect($leave)->toBeTruthy()
                ->and($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and((float) $employee->leave_balance)->toBe(10.0);

            $deduct = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::DEDUCT)
                ->where('leave_request_id', $leave->id)
                ->first();
            expect($deduct)->not->toBeNull()
                ->and((float) $deduct->amount)->toBe(2.0);
        });

        it('manual APPROVED non-CUTI does not deduct balance', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);

            actingAs($hrd, 'web');

            $this->post(route('hr.leave.manual.store'), [
                'user_id' => $employee->id,
                'type' => LeaveType::SAKIT->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
                'reason' => 'Manual SAKIT',
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            $leave = LeaveRequest::where('user_id', $employee->id)->first();
            $employee->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and((float) $employee->leave_balance)->toBe(12.0)
                ->and(LeaveBalanceTransaction::where('leave_request_id', $leave->id)->count())->toBe(0);
        });

        it('manual APPROVED CUTI with insufficient balance rolls back leave and ledger', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 1,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.manual.store'), [
                'user_id' => $employee->id,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
                'reason' => 'Manual CUTI insufficient',
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            $response->assertRedirect()
                ->assertSessionHas('error');

            $employee->refresh();
            expect((float) $employee->leave_balance)->toBe(1.0)
                ->and(LeaveRequest::where('user_id', $employee->id)->count())->toBe(0)
                ->and(LeaveBalanceTransaction::where('user_id', $employee->id)->count())->toBe(0);
        });

        it('manual APPROVED CUTI with insufficient balance cleans up uploaded photo', function () {
            Storage::fake('public');

            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 1,
            ]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.manual.store'), [
                'user_id' => $employee->id,
                'type' => LeaveType::CUTI->value,
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-16',
                'reason' => 'Manual CUTI insufficient with photo',
                'status' => LeaveRequest::STATUS_APPROVED,
                'photo' => UploadedFile::fake()->image('bukti.jpg'),
            ]);

            $response->assertRedirect()
                ->assertSessionHas('error');

            $files = Storage::disk('public')->allFiles('leave_photos');
            expect($files)->toBeEmpty();
        });

        it('validates required user_id', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.manual.store'), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
            ]);

            $response->assertSessionHasErrors(['user_id']);
        });

        it('validates end_date after start_date', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();

            actingAs($hrd, 'web');

            $response = $this->post(route('hr.leave.manual.store'), [
                'user_id' => $employee->id,
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
            ]);

            $response->assertSessionHasErrors(['end_date']);
        });
    });

    // =====================================================================
    // UPDATE
    // =====================================================================
    describe('update', function () {
        it('HRD can update pending leave request metadata', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'reason' => 'Original reason',
            ]);

            actingAs($hrd, 'web');

            $response = $this->put(route('hr.leave.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Updated reason',
            ]);

            $response->assertRedirect();
            $leave->refresh();
            expect($leave->reason)->toBe('Updated reason')
                ->and($leave->status)->toBe(LeaveRequest::PENDING_HR);
        });

        it('HR Staff cannot self-approve through update', function () {
            $hrStaff = User::factory()->create(['role' => UserRole::HR_STAFF]);
            $leave = LeaveRequest::factory()->forUser($hrStaff)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($hrStaff, 'web');

            $response = $this->put(route('hr.leave.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'status' => LeaveRequest::STATUS_APPROVED,
                'reason' => 'Trying to self-approve',
            ]);

            $response->assertRedirect();
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR)
                ->and($leave->approved_by)->toBeNull()
                ->and($leave->approved_at)->toBeNull();
        });

        it('status PENDING_HR does not become APPROVED through update payload', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::IZIN->value,
            ]);

            actingAs($hrd, 'web');

            $this->put(route('hr.leave.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'status' => LeaveRequest::STATUS_APPROVED,
                'approved_by' => $hrd->id,
                'approved_at' => now()->toDateTimeString(),
            ]);

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR)
                ->and($leave->approved_by)->toBeNull()
                ->and($leave->approved_at)->toBeNull();
        });

        it('balance does not change when update payload sends status APPROVED', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);
            EmployeeProfile::create(['user_id' => $employee->id, 'tgl_bergabung' => now()->subYears(2)->toDateString(), 'kategori' => 'KONTRAK']);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
            ]);

            actingAs($hrd, 'web');

            $this->put(route('hr.leave.update', $leave->id), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);

            $leave->refresh();
            $employee->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR)
                ->and((float) $employee->leave_balance)->toBe(12.0);
        });

        it('cannot update APPROVED leave request', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create();
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::STATUS_APPROVED,
                'reason' => 'Original reason',
            ]);

            actingAs($hrd, 'web');

            $response = $this->put(route('hr.leave.update', $leave->id), [
                'type' => LeaveType::IZIN->value,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Updated reason',
            ]);

            $response->assertRedirect();
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and($leave->reason)->toBe('Original reason');
        });

        // [P0-03] HR update CUTI pending harus validasi saldo secara atomik.
        it('rejects HR update when CUTI balance insufficient', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 1,
            ]);
            EmployeeProfile::create(['user_id' => $employee->id, 'tgl_bergabung' => now()->subYears(2)->toDateString(), 'kategori' => 'KONTRAK']);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'reason' => 'Original reason',
            ]);

            actingAs($hrd, 'web');

            $response = $this->put(route('hr.leave.update', $leave->id), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'reason' => 'Extended reason',
            ]);

            $response->assertSessionHas('error');
            $leave->refresh();
            expect($leave->reason)->toBe('Original reason')
                ->and($leave->start_date->format('Y-m-d'))->toBe(now()->addDays(5)->toDateString());
        });

        it('allows HR update when CUTI balance sufficient', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'leave_balance' => 12,
            ]);
            EmployeeProfile::create(['user_id' => $employee->id, 'tgl_bergabung' => now()->subYears(2)->toDateString(), 'kategori' => 'KONTRAK']);
            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_HR,
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'reason' => 'Original reason',
            ]);

            actingAs($hrd, 'web');

            $response = $this->put(route('hr.leave.update', $leave->id), [
                'type' => LeaveType::CUTI->value,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
                'reason' => 'Extended reason',
            ]);

            $response->assertSessionHas('success');
            $leave->refresh();
            expect($leave->reason)->toBe('Extended reason')
                ->and($leave->end_date->format('Y-m-d'))->toBe(now()->addDays(6)->toDateString());
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
