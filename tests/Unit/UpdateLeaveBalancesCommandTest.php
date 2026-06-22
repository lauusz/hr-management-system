<?php

uses(Tests\TestCase::class);

use App\Enums\UserRole;
use App\Models\EmployeeProfile;
use App\Models\LeaveBalanceTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    LeaveBalanceTransaction::query()->delete();
    EmployeeProfile::query()->delete();
    User::query()->delete();
});

afterEach(function () {
    Carbon::setTestNow();
});

function createEmployeeWithJoinDate(string $joinDate, string $status = User::STATUS_ACTIVE): User
{
    $user = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'status' => $status,
        'leave_balance' => 0,
    ]);

    EmployeeProfile::create([
        'user_id' => $user->id,
        'tgl_bergabung' => $joinDate,
        'kategori' => 'KONTRAK',
    ]);

    return $user;
}

it('grants eight days when employee reaches first anniversary in April', function () {
    Carbon::setTestNow('2026-04-15 00:01:00');
    $employee = createEmployeeWithJoinDate('2025-04-15');

    expect(Artisan::call('leave:update-balances'))->toBe(0)
        ->and((float) $employee->fresh()->leave_balance)->toBe(8.0);

    $transaction = LeaveBalanceTransaction::where(
        'idempotency_key',
        "FIRST_YEAR_PRORATA:USER:{$employee->id}:ANNIVERSARY:2026"
    )->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->transaction_type)->toBe(LeaveBalanceTransaction::ADJUSTMENT)
        ->and((float) $transaction->balance_before)->toBe(0.0)
        ->and((float) $transaction->balance_after)->toBe(8.0)
        ->and($transaction->created_by)->toBeNull();
});

it('does not grant first-year balance twice when command is rerun', function () {
    Carbon::setTestNow('2026-04-15 00:01:00');
    $employee = createEmployeeWithJoinDate('2025-04-15');
    $key = "FIRST_YEAR_PRORATA:USER:{$employee->id}:ANNIVERSARY:2026";

    Artisan::call('leave:update-balances');
    Artisan::call('leave:update-balances');

    expect((float) $employee->fresh()->leave_balance)->toBe(8.0)
        ->and(LeaveBalanceTransaction::where('idempotency_key', $key)->count())->toBe(1);
});

it('skips inactive employees and employees not reaching anniversary today', function () {
    Carbon::setTestNow('2026-04-15 00:01:00');
    $inactive = createEmployeeWithJoinDate('2025-04-15', 'INACTIVE');
    $notAnniversary = createEmployeeWithJoinDate('2025-04-16');

    Artisan::call('leave:update-balances');

    expect((float) $inactive->fresh()->leave_balance)->toBe(0.0)
        ->and((float) $notAnniversary->fresh()->leave_balance)->toBe(0.0)
        ->and(LeaveBalanceTransaction::query()->count())->toBe(0);
});

it('keeps first anniversary prorata separate from annual reset on January first', function () {
    Carbon::setTestNow('2026-01-01 00:01:00');
    $firstAnniversary = createEmployeeWithJoinDate('2025-01-01');
    $existingEmployee = createEmployeeWithJoinDate('2024-06-10');
    $existingEmployee->update(['leave_balance' => 4]);

    Artisan::call('leave:update-balances');

    expect((float) $firstAnniversary->fresh()->leave_balance)->toBe(11.0)
        ->and((float) $existingEmployee->fresh()->leave_balance)->toBe(12.0)
        ->and(LeaveBalanceTransaction::where(
            'idempotency_key',
            "FIRST_YEAR_PRORATA:USER:{$firstAnniversary->id}:ANNIVERSARY:2026"
        )->count())->toBe(1)
        ->and(LeaveBalanceTransaction::where(
            'idempotency_key',
            "ANNUAL_RESET:USER:{$existingEmployee->id}:YEAR:2026"
        )->count())->toBe(1);
});
