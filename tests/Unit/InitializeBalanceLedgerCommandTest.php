<?php

uses(Tests\TestCase::class);

use App\Models\LeaveBalanceTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    LeaveBalanceTransaction::query()->delete();
});

it('dry-run does not write opening balance', function () {
    $email = 'test-cmd-dry-'.uniqid().'@example.com';
    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Command Dry',
            'email' => $email,
            'password' => 'password',
            'role' => \App\Enums\UserRole::EMPLOYEE,
            'leave_balance' => 7,
        ]);
    });

    $exitCode = Artisan::call('leave:initialize-balance-ledger');

    expect($exitCode)->toBe(0)
        ->and(LeaveBalanceTransaction::where('user_id', $user->id)->count())->toBe(0);
});

it('execute creates opening balance and rerun does not duplicate', function () {
    $email = 'test-cmd-exec-'.uniqid().'@example.com';
    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Command Execute',
            'email' => $email,
            'password' => 'password',
            'role' => \App\Enums\UserRole::EMPLOYEE,
            'leave_balance' => 7,
        ]);
    });

    $exitCode1 = Artisan::call('leave:initialize-balance-ledger', ['--execute' => true]);

    expect($exitCode1)->toBe(0)
        ->and(LeaveBalanceTransaction::where('user_id', $user->id)
            ->where('transaction_type', LeaveBalanceTransaction::OPENING_BALANCE)
            ->count())->toBe(1);

    $exitCode2 = Artisan::call('leave:initialize-balance-ledger', ['--execute' => true]);

    expect($exitCode2)->toBe(0)
        ->and(LeaveBalanceTransaction::where('user_id', $user->id)
            ->where('transaction_type', LeaveBalanceTransaction::OPENING_BALANCE)
            ->count())->toBe(1);
});
