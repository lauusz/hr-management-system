<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\Division;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

pest()->extend(Tests\TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

/**
 * Scenario-Based Tests for ApprovalController
 *
 * Positive Scenarios    - Valid supervisor actions that SHOULD succeed
 * Negative Scenarios    - Invalid actions that SHOULD fail (auth, authorization, validation)
 * Boundary/Edge Cases  - Status transitions, edge conditions, double-action prevention
 */
describe('ApprovalController', function () {

    // =====================================================================
    // POSITIVE SCENARIOS
    // =====================================================================
    describe('POSITIVE: Supervisor can approve their subordinates', function () {

        test('supervisor can view pending leaves in inbox', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->get(route('approval.index'));

            $response->assertStatus(200);
            expect($response->viewData('leaves'))->toBeTruthy();
        });

        test('supervisor can ACK pending leave and forward to HRD', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->post(route('approval.ack', $leave));

            $response->assertRedirect(route('approval.index'));
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR)
                ->and($leave->supervisor_ack_at)->toBeTruthy();
        });

        test('supervisor can REJECT pending leave', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->post(route('approval.reject', $leave));

            $response->assertRedirect(route('approval.index'));
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_REJECTED);
        });

        test('supervisor can APPROVE non-HRD leave -> goes to PENDING_HR', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'type'   => LeaveType::IZIN,
            ]);

            actingAs($supervisor, 'web');

            $response = $this->post(route('approval.approve', $leave));

            $response->assertRedirect(route('approval.index'));
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR);
        });

        test('supervisor can edit subordinate leave data', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'reason' => 'Original reason',
            ]);

            actingAs($supervisor, 'web');

            $response = $this->get(route('approval.edit', $leave));

            $response->assertStatus(200);
            expect($response->viewData('leave')->id)->toBe($leave->id);
        });

        test('supervisor can update subordinate leave data', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'reason' => 'Original reason',
            ]);

            actingAs($supervisor, 'web');

            $response = $this->put(route('approval.update', $leave), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Updated reason by supervisor',
            ]);

            $response->assertRedirect(route('approval.show', $leave->id));
            $leave->refresh();
            expect($leave->reason)->toBe('Updated reason by supervisor')
                ->and($leave->status)->toBe(LeaveRequest::PENDING_HR);
        });

        test('supervisor can cancel (BATAL) subordinate leave', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->delete(route('approval.destroy', $leave));

            $response->assertRedirect(route('approval.index'));
            $leave->refresh();
            expect($leave->status)->toBe('BATAL');
        });

        test('manager can approve staff without direct_supervisor_id', function () {
            $division = Division::factory()->create();
            $manager = User::factory()->create(['role' => UserRole::MANAGER, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'manager_id' => $manager->id,
                'direct_supervisor_id' => null,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($manager, 'web');

            $response = $this->post(route('approval.approve', $leave));

            $response->assertRedirect(route('approval.index'));
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR);
        });

        test('supervisor can view leave detail', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->get(route('approval.show', $leave));

            $response->assertStatus(200);
            expect($response->viewData('item')->id)->toBe($leave->id);
        });

        test('supervisor can access master page', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            LeaveRequest::factory()->forUser($employee)->create();

            actingAs($supervisor, 'web');

            $response = $this->get(route('supervisor.leave.master'));

            $response->assertStatus(200);
        });
    });

    // =====================================================================
    // NEGATIVE SCENARIOS
    // =====================================================================
    describe('NEGATIVE: Unauthorized actions should fail', function () {

        test('employee cannot access approval index', function () {
            $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

            actingAs($employee, 'web');

            $response = $this->get(route('approval.index'));

            $response->assertStatus(403);
        });

        test('supervisor cannot access another division employee leave', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $otherDivision = Division::factory()->create();
            $otherEmployee = User::factory()->create(['division_id' => $otherDivision->id]);

            $leave = LeaveRequest::factory()->forUser($otherEmployee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
            ]);

            actingAs($supervisor, 'web');

            $response = $this->get(route('approval.show', $leave));

            $response->assertStatus(403);
        });

        test('supervisor cannot approve another supervisor subordinate', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $otherSupervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
            $otherEmployee = User::factory()->create(['direct_supervisor_id' => $otherSupervisor->id]);

            $leave = LeaveRequest::factory()->forUser($otherEmployee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
            ]);

            actingAs($supervisor, 'web');

            $response = $this->post(route('approval.approve', $leave));

            $response->assertStatus(403);
        });

        test('supervisor cannot ACK leave not in PENDING_SUPERVISOR status', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_HR]);

            actingAs($supervisor, 'web');

            $response = $this->post(route('approval.ack', $leave));

            $response->assertRedirect(route('approval.index'));
            expect(session('error'))->toBeTruthy();
        });

        test('supervisor cannot REJECT leave not in PENDING_SUPERVISOR status', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_HR]);

            actingAs($supervisor, 'web');

            $response = $this->post(route('approval.reject', $leave));

            $response->assertRedirect(route('approval.index'));
            expect(session('error'))->toBeTruthy();
        });

        test('supervisor cannot APPROVE leave not in PENDING_SUPERVISOR status', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_HR]);

            actingAs($supervisor, 'web');

            $response = $this->post(route('approval.approve', $leave));

            $response->assertRedirect(route('approval.index'));
            expect(session('error'))->toBeTruthy();
        });

        test('employee cannot view approval pages', function () {
            $employee = User::factory()->create();

            actingAs($employee, 'web');

            $response = $this->get(route('approval.show', 1));

            $response->assertStatus(403);
        });

        test('unauthenticated user is redirected to login', function () {
            $response = $this->get(route('approval.index'));

            $response->assertRedirect('/login');
        });

        test('HRD applicant supervisor approval goes directly to APPROVED (auto-approved)', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::HRD,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'type'   => LeaveType::IZIN,
            ]);

            actingAs($supervisor, 'web');

            $response = $this->post(route('approval.approve', $leave));

            $response->assertRedirect(route('approval.index'));
            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED);
        });

        test('supervisor cannot edit leave in non-PENDING_SUPERVISOR status', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_HR]);

            actingAs($supervisor, 'web');

            $response = $this->get(route('approval.edit', $leave));

            $response->assertStatus(403);
        });

        test('supervisor cannot cancel leave they do not supervise', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $otherEmployee = User::factory()->create([
                'direct_supervisor_id' => User::factory()->create()->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($otherEmployee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
            ]);

            actingAs($supervisor, 'web');

            $response = $this->delete(route('approval.destroy', $leave));

            $response->assertStatus(403);
        });
    });

    // =====================================================================
    // BOUNDARY / EDGE CASES
    // =====================================================================
    describe('BOUNDARY: Status transition edge cases', function () {

        test('ACK sets supervisor_ack_at timestamp', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $this->post(route('approval.ack', $leave));

            $leave->refresh();
            expect($leave->supervisor_ack_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        });

        test('reject records approved_by with current user', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $this->post(route('approval.reject', $leave));

            $leave->refresh();
            expect($leave->approved_by)->toBe($supervisor->id)
                ->and($leave->approved_at)->toBeTruthy();
        });

        test('approve records approved_by with current user', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'type'   => LeaveType::IZIN,
            ]);

            actingAs($supervisor, 'web');

            $this->post(route('approval.approve', $leave));

            $leave->refresh();
            expect($leave->approved_by)->toBe($supervisor->id)
                ->and($leave->approved_at)->toBeTruthy();
        });

        test('reject adds system note to existing notes', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'notes'  => 'Existing note',
            ]);

            actingAs($supervisor, 'web');

            $this->post(route('approval.reject', $leave));

            $leave->refresh();
            expect($leave->notes)->toContain('Ditolak oleh Atasan')
                ->and($leave->notes)->toContain('Existing note');
        });

        test('update by supervisor adds system note', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $this->put(route('approval.update', $leave), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Updated reason',
            ]);

            $leave->refresh();
            expect($leave->notes)->toContain('direvisi oleh Supervisor');
        });

        test('cancel (BATAL) adds system note', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $this->delete(route('approval.destroy', $leave));

            $leave->refresh();
            expect($leave->notes)->toContain('Dibatalkan oleh Supervisor/Atasan');
        });

        test('update validates end_date after start_date', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->put(route('approval.update', $leave), [
                'type'       => LeaveType::IZIN,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Invalid dates',
            ]);

            $response->assertSessionHasErrors(['end_date']);
        });

        test('update validates type is valid LeaveType', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->put(route('approval.update', $leave), [
                'type'       => 'INVALID_TYPE',
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Test',
            ]);

            $response->assertSessionHasErrors(['type']);
        });

        test('master filters by status correctly', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);
            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_HR]);
            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::STATUS_APPROVED]);

            actingAs($supervisor, 'web');

            $response = $this->get(route('supervisor.leave.master', ['status' => 'APPROVED']));

            $response->assertStatus(200);
            expect($response->viewData('items')->total())->toBe(1);
        });

        test('master filters by search query', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            LeaveRequest::factory()->forUser($employee)->create();

            actingAs($supervisor, 'web');

            $response = $this->get(route('supervisor.leave.master', ['q' => substr($employee->name, 0, 3)]));

            $response->assertStatus(200);
            expect($response->viewData('items')->total())->toBeGreaterThanOrEqual(1);
        });

        test('employee cannot approve their own leave', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($employee, 'web');

            $response = $this->post(route('approval.approve', $leave));

            $response->assertStatus(403);
        });

        test('HRD role can access approval index via role middleware', function () {
            $hrd = User::factory()->create(['role' => UserRole::HRD]);

            actingAs($hrd, 'web');

            $response = $this->get(route('approval.index'));

            $response->assertStatus(200);
        });

        test('update with CUTI_KHUSUS requires special_leave_detail', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->put(route('approval.update', $leave), [
                'type'       => LeaveType::CUTI_KHUSUS->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Special leave without detail',
            ]);

            $response->assertSessionHasErrors(['special_leave_detail']);
        });

        test('update with CUTI_KHUSUS and special_leave_detail passes validation', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->put(route('approval.update', $leave), [
                'type'       => LeaveType::CUTI_KHUSUS->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date'   => now()->addDays(1)->toDateString(),
                'reason'     => 'Special leave',
                'special_leave_detail' => 'Keperluan keluarga',
            ]);

            $response->assertRedirect(route('approval.show', $leave->id));
        });

        test('show returns 404 for non-existent leave request', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);

            actingAs($supervisor, 'web');

            $response = $this->get(route('approval.show', 99999));

            $response->assertStatus(404);
        });

        test('supervisor index shows only direct subordinates', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);
            $otherEmployee = User::factory()->create([
                'direct_supervisor_id' => User::factory()->create()->id,
            ]);

            LeaveRequest::factory()->forUser($employee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);
            LeaveRequest::factory()->forUser($otherEmployee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

            actingAs($supervisor, 'web');

            $response = $this->get(route('approval.index'));

            $response->assertStatus(200);
            expect($response->viewData('leaves')->total())->toBe(1);
        });
    });

    // =====================================================================
    // EDGE CASE: HRD Applicant Auto-Approve
    // =====================================================================
    describe('EDGE: HRD Applicant Auto-Approve flow', function () {

        test('HRD applicant APPROVED directly without HRD verification step', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $hrdEmployee = User::factory()->create([
                'role' => UserRole::HRD,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($hrdEmployee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'type'   => LeaveType::IZIN,
            ]);

            actingAs($supervisor, 'web');

            $response = $this->post(route('approval.approve', $leave));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::STATUS_APPROVED)
                ->and($leave->approved_by)->toBe($supervisor->id);
        });

        test('non-HRD applicant goes to PENDING_HR after supervisor approval', function () {
            $division = Division::factory()->create();
            $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR, 'division_id' => $division->id]);
            $employee = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'division_id' => $division->id,
                'direct_supervisor_id' => $supervisor->id,
            ]);

            $leave = LeaveRequest::factory()->forUser($employee)->create([
                'status' => LeaveRequest::PENDING_SUPERVISOR,
                'type'   => LeaveType::CUTI,
            ]);

            actingAs($supervisor, 'web');

            $this->post(route('approval.approve', $leave));

            $leave->refresh();
            expect($leave->status)->toBe(LeaveRequest::PENDING_HR);
        });
    });
});
