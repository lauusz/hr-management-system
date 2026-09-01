<?php

use App\Enums\UserRole;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalanceTransaction;
use App\Models\User;

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
