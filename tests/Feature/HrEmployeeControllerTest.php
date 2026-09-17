<?php

use App\Enums\UserRole;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveApprovalAssignmentService;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

it('renders live employee search and a three column desktop grid', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.employees.index'))
        ->assertOk()
        ->assertSee('data-employee-live-search', false)
        ->assertSee('data-employee-results aria-live="polite" aria-busy="false"', false)
        ->assertSee('data-employee-list', false)
        ->assertSee('data-employee-pagination', false)
        ->assertSee('grid-template-columns: repeat(3, minmax(0, 1fr))', false)
        ->assertDontSee('emp-btn-search', false);
});

it('employee search ignores dots and spaces in employee names', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $matchingEmployee = User::factory()->create(['name' => 'MOH. AINUL YAQIN']);
    User::factory()->create(['name' => 'MOHAMMAD FAJAR']);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.employees.index', ['q' => 'mohainul']));

    $response->assertOk();
    expect($response->viewData('items')->total())->toBe(1)
        ->and($response->viewData('items')->first()->id)->toBe($matchingEmployee->id);
});

it('lists active employees before inactive employees', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD, 'name' => 'HRD']);
    $inactiveA = User::factory()->create(['name' => 'A Inactive', 'status' => 'INACTIVE']);
    $activeB = User::factory()->create(['name' => 'B Active', 'status' => User::STATUS_ACTIVE]);
    $activeC = User::factory()->create(['name' => 'C Active', 'status' => User::STATUS_ACTIVE]);
    $inactiveD = User::factory()->create(['name' => 'D Inactive', 'status' => 'INACTIVE']);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.employees.index'));

    expect($response->viewData('items')->pluck('id')->all())->toBe([
        $activeB->id,
        $activeC->id,
        $hrd->id,
        $inactiveA->id,
        $inactiveD->id,
    ]);
});

it('renders compact Indonesian dates on employee cards', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-08-06'));
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create(['status' => 'ACTIVE']);
    EmployeeProfile::create([
        'user_id' => $employee->id,
        'tgl_bergabung' => '2025-12-01',
        'tgl_akhir_percobaan' => '2026-12-01',
    ]);

    $this->actingAs($hrd, 'web')
        ->get(route('hr.employees.index', ['near_expiry' => 1]))
        ->assertOk()
        ->assertSee('1 Des 2025')
        ->assertSee('Berakhir: 1 Des 2026')
        ->assertDontSee('1 Desember 2025');
});

it('uses global image viewer for stored employee documents', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    EmployeeProfile::create([
        'user_id' => $employee->id,
        'path_kartu_keluarga' => 'employee-documents/kk.jpg',
        'path_ktp' => 'employee-documents/ktp.jpg',
    ]);

    $this->actingAs($hrd, 'web');

    $response = $this->get(route('hr.employees.edit', $employee));

    $response->assertOk()
        ->assertSee('data-image-viewer-alt="Kartu Keluarga"', false)
        ->assertSee('data-image-viewer-alt="KTP"', false);
});

it('manual leave_balance update writes adjustment ledger', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 10,
    ]);

    $this->actingAs($hrd, 'web');

    $response = $this->put(route('hr.employees.update', $employee), [
        'name' => $employee->name,
        'role' => $employee->role->value,
        'leave_balance' => 12,
    ]);

    $response->assertRedirect(route('hr.employees.index'));

    $employee->refresh();

    expect((float) $employee->leave_balance)->toBe(12.0)
        ->and(LeaveBalanceTransaction::where('user_id', $employee->id)
            ->where('transaction_type', LeaveBalanceTransaction::ADJUSTMENT)
            ->count())->toBe(1)
        ->and(LeaveBalanceTransaction::where('user_id', $employee->id)
            ->where('transaction_type', LeaveBalanceTransaction::OPENING_BALANCE)
            ->count())->toBe(1);
});

it('manual leave_balance update with same value only creates opening ledger', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 10,
    ]);

    $this->actingAs($hrd, 'web');

    $response = $this->put(route('hr.employees.update', $employee), [
        'name' => $employee->name,
        'role' => $employee->role->value,
        'leave_balance' => 10,
    ]);

    $response->assertRedirect(route('hr.employees.index'));

    $employee->refresh();

    expect((float) $employee->leave_balance)->toBe(10.0)
        ->and(LeaveBalanceTransaction::where('user_id', $employee->id)
            ->where('transaction_type', LeaveBalanceTransaction::ADJUSTMENT)
            ->count())->toBe(0)
        ->and(LeaveBalanceTransaction::where('user_id', $employee->id)
            ->where('transaction_type', LeaveBalanceTransaction::OPENING_BALANCE)
            ->count())->toBe(1);
});

it('stores a separate active employee approver for an employee account', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $manager = User::factory()->create(['role' => UserRole::MANAGER]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $approver = User::factory()->create(['role' => UserRole::EMPLOYEE, 'status' => User::STATUS_ACTIVE]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'manager_id' => $manager->id,
        'direct_supervisor_id' => $supervisor->id,
    ]);

    $this->actingAs($hrd, 'web');

    $response = $this->put(route('hr.employees.update', $employee), [
        'name' => $employee->name,
        'role' => $employee->role->value,
        'status' => $employee->status,
        'manager_id' => $manager->id,
        'direct_supervisor_id' => $supervisor->id,
        'approver_id' => $approver->id,
    ]);

    $response->assertRedirect(route('hr.employees.index'));

    $employee->refresh();

    expect((int) $employee->manager_id)->toBe($manager->id)
        ->and((int) $employee->direct_supervisor_id)->toBe($supervisor->id)
        ->and((int) $employee->approver_id)->toBe($approver->id);
});

it('shows only active employee users as approver candidates and excludes the edited employee', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $employee = User::factory()->create([
        'name' => 'Target Employee',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);
    $activeEmployeeApprover = User::factory()->create([
        'name' => 'Active Employee Approver',
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
    ]);
    $inactiveEmployee = User::factory()->create([
        'name' => 'Inactive Employee Candidate',
        'role' => UserRole::EMPLOYEE,
        'status' => 'INACTIVE',
    ]);
    $manager = User::factory()->create([
        'name' => 'Manager Candidate',
        'role' => UserRole::MANAGER,
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this->actingAs($hrd, 'web')
        ->get(route('hr.employees.edit', $employee));

    $approverIds = $response->viewData('approvers')->pluck('id')->all();

    $response->assertOk()
        ->assertSee('name="approver_id"', false);

    expect($approverIds)->toContain($activeEmployeeApprover->id)
        ->not->toContain($employee->id)
        ->not->toContain($inactiveEmployee->id)
        ->not->toContain($manager->id);
});

it('rejects invalid approver assignments without changing the previous approver', function (array $candidateAttributes) {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $currentApprover = User::factory()->create(['role' => UserRole::EMPLOYEE, 'status' => User::STATUS_ACTIVE]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'approver_id' => $currentApprover->id,
    ]);
    $candidate = User::factory()->create($candidateAttributes);

    $this->actingAs($hrd, 'web');

    $response = $this->from(route('hr.employees.edit', $employee))
        ->put(route('hr.employees.update', $employee), [
            'name' => $employee->name,
            'role' => $employee->role->value,
            'status' => $employee->status,
            'approver_id' => $candidate->id,
        ]);

    $response->assertRedirect(route('hr.employees.edit', $employee))
        ->assertSessionHasErrors('approver_id');

    $employee->refresh();

    expect((int) $employee->approver_id)->toBe($currentApprover->id);
})->with([
    'non employee role' => [[
        'role' => UserRole::MANAGER,
        'status' => User::STATUS_ACTIVE,
    ]],
    'inactive employee' => [[
        'role' => UserRole::EMPLOYEE,
        'status' => 'INACTIVE',
    ]],
]);

it('rejects assigning an employee as their own approver', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $currentApprover = User::factory()->create(['role' => UserRole::EMPLOYEE, 'status' => User::STATUS_ACTIVE]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'approver_id' => $currentApprover->id,
    ]);

    $this->actingAs($hrd, 'web');

    $response = $this->from(route('hr.employees.edit', $employee))
        ->put(route('hr.employees.update', $employee), [
            'name' => $employee->name,
            'role' => $employee->role->value,
            'status' => $employee->status,
            'approver_id' => $employee->id,
        ]);

    $response->assertRedirect(route('hr.employees.edit', $employee))
        ->assertSessionHasErrors('approver_id');

    $employee->refresh();

    expect((int) $employee->approver_id)->toBe($currentApprover->id);
});

it('moves pending initial leave processing to a newly assigned approver when employee data changes', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $oldSupervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $newApprover = User::factory()->create(['role' => UserRole::EMPLOYEE, 'status' => User::STATUS_ACTIVE]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'approver_id' => null,
        'direct_supervisor_id' => $oldSupervisor->id,
        'manager_id' => null,
    ]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $this->actingAs($hrd, 'web');

    $response = $this->put(route('hr.employees.update', $employee), [
        'name' => $employee->name,
        'role' => $employee->role->value,
        'status' => $employee->status,
        'direct_supervisor_id' => $oldSupervisor->id,
        'approver_id' => $newApprover->id,
    ]);

    $response->assertRedirect(route('hr.employees.index'));

    $service = app(LeaveApprovalAssignmentService::class);

    expect($leave->fresh()->status)->toBe(LeaveRequest::PENDING_SUPERVISOR)
        ->and($service->queryPendingFor($oldSupervisor)->pluck('id')->all())->toBe([])
        ->and($service->queryPendingFor($newApprover)->pluck('id')->all())->toBe([$leave->id]);
});

it('moves pending initial leave to HR when employee update removes every initial approver', function () {
    $hrd = User::factory()->create(['role' => UserRole::HRD]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $employee = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => User::STATUS_ACTIVE,
        'approver_id' => null,
        'direct_supervisor_id' => $supervisor->id,
        'manager_id' => null,
    ]);
    $leave = LeaveRequest::factory()->forUser($employee)->create([
        'status' => LeaveRequest::PENDING_SUPERVISOR,
    ]);

    $this->actingAs($hrd, 'web');

    $response = $this->put(route('hr.employees.update', $employee), [
        'name' => $employee->name,
        'role' => $employee->role->value,
        'status' => $employee->status,
        'direct_supervisor_id' => null,
        'manager_id' => null,
        'approver_id' => null,
    ]);

    $response->assertRedirect(route('hr.employees.index'));

    expect($leave->fresh()->status)->toBe(LeaveRequest::PENDING_HR)
        ->and($leave->fresh()->notes)->toContain('dialihkan otomatis ke HR');
});
