<?php

use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

it('shows HR approval mapping as clickable approver names without assignee details', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $manager = User::factory()->create(['name' => 'Manager Pusat', 'role' => UserRole::MANAGER]);
    $supervisor = User::factory()->create(['name' => 'Supervisor Barat', 'role' => UserRole::SUPERVISOR]);
    $approver = User::factory()->create([
        'name' => 'Approver Cuti',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);
    $managerEmployee = User::factory()->create([
        'name' => 'Staff Manager',
        'role' => UserRole::EMPLOYEE,
        'manager_id' => $manager->id,
    ]);
    $supervisorEmployee = User::factory()->create([
        'name' => 'Staff Supervisor',
        'role' => UserRole::EMPLOYEE,
        'direct_supervisor_id' => $supervisor->id,
    ]);
    $approverEmployee = User::factory()->create([
        'name' => 'Staff Approver',
        'role' => UserRole::EMPLOYEE,
        'approver_id' => $approver->id,
    ]);

    LeaveRequest::factory()->forUser($managerEmployee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);
    LeaveRequest::factory()->forUser($supervisorEmployee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);
    LeaveRequest::factory()->forUser($approverEmployee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.approvers.index'));

    $response->assertOk()
        ->assertSee('Daftar Approver')
        ->assertSee('Managers')
        ->assertSee('Supervisors')
        ->assertSee('Approvers')
        ->assertSee('Manager Pusat')
        ->assertSee('Supervisor Barat')
        ->assertSee('Approver Cuti')
        ->assertSee('Tambah')
        ->assertSee('data-modal-target="approval-create-approver-modal"', false)
        ->assertSee('data-approver-create-form', false)
        ->assertSee('data-approver-create-search', false)
        ->assertSee('data-search-url="'.route('hr.approvers.search').'"', false)
        ->assertSee(route('hr.approvers.store'), false)
        ->assertDontSee('href="'.route('hr.approvers.create').'"', false)
        ->assertSee(route('hr.approvers.show', $manager), false)
        ->assertSee(route('hr.approvers.show', $supervisor), false)
        ->assertSee(route('hr.approvers.show', $approver), false)
        ->assertDontSee('Staff Manager')
        ->assertDontSee('Staff Supervisor')
        ->assertDontSee('Staff Approver')
        ->assertSee('data-manager-layout', false)
        ->assertSee('data-supervisor-layout', false)
        ->assertSee('data-approver-layout', false);
});

it('searches users that can become approvers from the index modal', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $candidate = User::factory()->create([
        'name' => 'Calon Approver Modal',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'can_approve_leave' => false,
    ]);
    $alreadyApprover = User::factory()->create([
        'name' => 'Approver Lama Modal',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'can_approve_leave' => true,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->getJson(route('hr.approvers.search').'?q=Modal');

    $response->assertOk()
        ->assertJsonFragment([
            'id' => $candidate->id,
            'name' => 'Calon Approver Modal',
        ])
        ->assertJsonMissing([
            'id' => $alreadyApprover->id,
            'name' => 'Approver Lama Modal',
        ]);
});

it('shows create form for adding employee approver access', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $candidate = User::factory()->create([
        'name' => 'Calon Approver',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.approvers.create'));

    $response->assertOk()
        ->assertSee('Tambah Approver')
        ->assertSee('Cari nama user')
        ->assertSee('Calon Approver')
        ->assertSee('Simpan')
        ->assertSee('jquery-3.7.1.min.js', false)
        ->assertSee('$(function()', false)
        ->assertSee('approver-suggestions', false)
        ->assertSee('name="user_id"', false)
        ->assertSee('.approver-suggestion-item[hidden]', false)
        ->assertDontSee('<select', false);
});

it('grants employee approver access from create form', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $candidate = User::factory()->create([
        'name' => 'Calon Approver',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->post(route('hr.approvers.store'), [
            'user_id' => $candidate->id,
        ]);

    $response->assertRedirect(route('hr.approvers.show', $candidate));
    expect($candidate->fresh()->can_approve_leave)->toBeTrue();

    $this->actingAs($hrd, 'web')
        ->get(route('hr.approvers.index'))
        ->assertOk()
        ->assertSee('Calon Approver')
        ->assertSee('0 users');
});

it('shows approver detail with currently assigned users', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $approver = User::factory()->create([
        'name' => 'Approver Cuti',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);
    $assignedEmployee = User::factory()->create([
        'name' => 'Staff Approver',
        'role' => UserRole::EMPLOYEE,
        'approver_id' => $approver->id,
    ]);
    $otherEmployee = User::factory()->create([
        'name' => 'Staff Lain',
        'role' => UserRole::EMPLOYEE,
    ]);

    LeaveRequest::factory()->forUser($assignedEmployee)->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.approvers.show', $approver));

    $response->assertOk()
        ->assertSee('Detail Approver')
        ->assertSee('class="back-btn"', false)
        ->assertSee("window.location.href='".route('hr.approvers.index')."';", false)
        ->assertSee('class="back-btn-text"', false)
        ->assertSee('M10 19l-7-7m0 0l7-7m-7 7h18', false)
        ->assertDontSee('approval-detail-back', false)
        ->assertSee('Approver Cuti')
        ->assertSee('APPROVER')
        ->assertSee('Tambah User')
        ->assertSee('data-modal-target="approval-add-user-modal"', false)
        ->assertSee('data-approval-add-modal', false)
        ->assertSee('data-approver-user-form', false)
        ->assertSee('data-approver-user-search', false)
        ->assertSee('data-search-url="'.route('hr.approvers.users.search', $approver).'"', false)
        ->assertSee('id="approver-modal-user-suggestions"', false)
        ->assertSee('jquery-3.7.1.min.js', false)
        ->assertSee(route('hr.approvers.users.store', $approver), false)
        ->assertDontSee('<select id="approval-add-user-id"', false)
        ->assertDontSee(route('hr.approvers.users.create', $approver), false)
        ->assertSee(route('hr.approvers.users.destroy', [$approver, $assignedEmployee]), false)
        ->assertSee('data-approval-delete-modal', false)
        ->assertSee('data-approval-delete-open', false)
        ->assertDontSee("confirm('Hapus user ini dari mapping approval?')", false)
        ->assertSee('Hapus')
        ->assertSee('Cabut Hak Approver')
        ->assertSee(route('hr.approvers.revoke', $approver), false)
        ->assertSee('Staff Approver')
        ->assertSee('1 pending');
});

it('searches assignable users for approver detail modal', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $approver = User::factory()->create([
        'name' => 'Approver Cuti',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);
    $candidate = User::factory()->create([
        'name' => 'Staff Bisa Dipilih',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);
    $assignedEmployee = User::factory()->create([
        'name' => 'Staff Sudah Assigned',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'approver_id' => $approver->id,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->getJson(route('hr.approvers.users.search', $approver).'?q=Bisa');

    $response->assertOk()
        ->assertJsonFragment([
            'id' => $candidate->id,
            'name' => 'Staff Bisa Dipilih',
        ])
        ->assertJsonMissing([
            'id' => $assignedEmployee->id,
            'name' => 'Staff Sudah Assigned',
        ]);
});

it('shows manager detail using manager mapping', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $manager = User::factory()->create(['name' => 'Manager Pusat', 'role' => UserRole::MANAGER]);
    $assignedEmployee = User::factory()->create([
        'name' => 'Staff Manager',
        'role' => UserRole::EMPLOYEE,
        'manager_id' => $manager->id,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.approvers.show', $manager));

    $response->assertOk()
        ->assertSee('Manager Pusat')
        ->assertSee('MANAGER')
        ->assertSee('Staff Manager')
        ->assertSee('Hapus')
        ->assertDontSee('Tidak ada pending')
        ->assertSee('1 user');
});

it('shows add user form from approver detail', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $approver = User::factory()->create([
        'name' => 'Approver Cuti',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);
    $candidate = User::factory()->create([
        'name' => 'Staff Baru',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.approvers.users.create', $approver));

    $response->assertOk()
        ->assertSee('Tambah User Approval')
        ->assertSee('Approver Cuti')
        ->assertSee('Staff Baru')
        ->assertSee('approver-user-suggestions', false)
        ->assertSee('name="user_id"', false);
});

it('adds user to manager mapping', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $manager = User::factory()->create(['role' => UserRole::MANAGER]);
    $candidate = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'manager_id' => null,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->post(route('hr.approvers.users.store', $manager), [
            'user_id' => $candidate->id,
        ]);

    $response->assertRedirect(route('hr.approvers.show', $manager));
    expect($candidate->fresh()->manager_id)->toBe($manager->id);
});

it('adds user to supervisor mapping', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $candidate = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'direct_supervisor_id' => null,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->post(route('hr.approvers.users.store', $supervisor), [
            'user_id' => $candidate->id,
        ]);

    $response->assertRedirect(route('hr.approvers.show', $supervisor));
    expect($candidate->fresh()->direct_supervisor_id)->toBe($supervisor->id);
});

it('adds user to employee approver mapping', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $approver = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'can_approve_leave' => true,
    ]);
    $candidate = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'approver_id' => null,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->post(route('hr.approvers.users.store', $approver), [
            'user_id' => $candidate->id,
        ]);

    $response->assertRedirect(route('hr.approvers.show', $approver));
    expect($candidate->fresh()->approver_id)->toBe($approver->id);
});

it('removes user from manager mapping by clearing manager column', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $manager = User::factory()->create(['role' => UserRole::MANAGER]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'manager_id' => $manager->id,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->delete(route('hr.approvers.users.destroy', [$manager, $employee]));

    $response->assertRedirect(route('hr.approvers.show', $manager));
    expect($employee->fresh()->manager_id)->toBeNull();
});

it('removes user from supervisor mapping by clearing supervisor column', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'direct_supervisor_id' => $supervisor->id,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->delete(route('hr.approvers.users.destroy', [$supervisor, $employee]));

    $response->assertRedirect(route('hr.approvers.show', $supervisor));
    expect($employee->fresh()->direct_supervisor_id)->toBeNull();
});

it('removes user from employee approver mapping by clearing approver column', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $approver = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'can_approve_leave' => true,
    ]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'approver_id' => $approver->id,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->delete(route('hr.approvers.users.destroy', [$approver, $employee]));

    $response->assertRedirect(route('hr.approvers.show', $approver));
    expect($employee->fresh()->approver_id)->toBeNull();
});

it('revokes employee approver access from detail action', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $approver = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'can_approve_leave' => true,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->patch(route('hr.approvers.revoke', $approver));

    $response->assertRedirect(route('hr.approvers.index'));
    expect($approver->fresh()->can_approve_leave)->toBeFalse();
});

it('adds Daftar Approver under the HR employee menu', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.approvers.index'));

    $response->assertOk()
        ->assertSee(route('hr.approvers.index'), false)
        ->assertSee('Daftar Approver');
});
