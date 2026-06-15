<?php

use App\Enums\UserRole;
use App\Models\LeaveBalanceTransaction;
use App\Models\User;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

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
