<?php

uses(Tests\TestCase::class);

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveBalanceService;

beforeEach(function () {
    LeaveBalanceTransaction::query()->delete();
});

it('counts effective leave days consistently for every role', function (UserRole $role, float $expectedDays) {
    $service = new LeaveBalanceService;
    $user = new User([
        'role' => $role,
        'leave_balance' => 12,
    ]);

    // 2026-03-27 (Fri), 2026-03-28 (Sat), 2026-03-29 (Sun), 2026-03-30 (Mon)
    // MANAGER (5-day): Fri=1, Sat=0, Sun=0, Mon=1 -> 2
    // Non-MANAGER (6-day): Fri=1, Sat=0.5, Sun=0, Mon=1 -> 2.5
    $days = $service->calculateEffectiveDaysForUser($user, '2026-03-27', '2026-03-30');

    expect($days)->toBe($expectedDays);
})->with([
    'hrd uses 5 day work week' => [UserRole::HRD, 2.0],
    'hr staff uses 6 day work week' => [UserRole::HR_STAFF, 2.5],
    'manager uses 5 day work week' => [UserRole::MANAGER, 2.0],
    'supervisor uses 6 day work week' => [UserRole::SUPERVISOR, 2.5],
    'employee uses 6 day work week' => [UserRole::EMPLOYEE, 2.5],
]);

it('treats enum cast cuti as annual leave for shared balance logic', function () {
    $service = new LeaveBalanceService;
    $leave = new LeaveRequest([
        'type' => LeaveType::CUTI,
        'start_date' => '2026-03-27',
        'end_date' => '2026-03-30',
    ]);

    $leave->setRelation('user', new User([
        'role' => UserRole::HRD,
        'leave_balance' => 12,
    ]));

    expect($service->isAnnualLeave($leave))->toBeTrue()
        ->and($service->calculateEffectiveDaysForLeave($leave))->toBe(2.0);
});

it('refunds and deducts annual leave with the same shared rule for all cuti flows', function () {
    $service = new LeaveBalanceService;
    $email = 'test-hrd-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test HRD',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::HRD,
            'leave_balance' => 12,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-03-27',
            'end_date' => '2026-03-30',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    $leave->setRelation('user', $user);

    // HRD (5-day): 2026-03-27(Fri)=1, 2026-03-28(Sat)=0, 2026-03-29(Sun)=0, 2026-03-30(Mon)=1 -> 2 days
    expect($service->deductLeaveBalanceForLeave($leave))->toBe(2.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(10.0);

    $deduct = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::DEDUCT)
        ->where('user_id', $user->id)
        ->first();

    expect($deduct)->not->toBeNull()
        ->and((float) $deduct->amount)->toBe(2.0)
        ->and((float) $deduct->balance_before)->toBe(12.0)
        ->and((float) $deduct->balance_after)->toBe(10.0);

    $leave->setRelation('user', $user->fresh());

    expect($service->refundLeaveBalanceForLeave($leave))->toBe(2.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0);

    expect(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
        ->where('user_id', $user->id)
        ->count())->toBe(1);
});

it('does not change balance when balance is insufficient and does not write ledger', function () {
    $service = new LeaveBalanceService;
    $email = 'test-insufficient-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Insufficient',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 1,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-16',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    $leave->setRelation('user', $user);

    // EMPLOYEE (6-day): 2026-06-15(Mon)=1, 2026-06-16(Tue)=1 -> 2 days
    expect((float) $user->fresh()->leave_balance)->toBe(1.0)
        ->and(fn () => $service->deductLeaveBalanceForLeave($leave))->toThrow(RuntimeException::class)
        ->and((float) $user->fresh()->leave_balance)->toBe(1.0)
        ->and(LeaveBalanceTransaction::where('user_id', $user->id)->count())->toBe(0);
});

it('creates opening balance and deduct ledger on first deduction', function () {
    $service = new LeaveBalanceService;
    $email = 'test-deduct-ledger-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Deduct Ledger',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 10,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-16',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    $amount = $service->deductLeaveBalanceForLeave($leave);

    expect($amount)->toBe(2.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(8.0);

    $opening = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::OPENING_BALANCE)
        ->where('user_id', $user->id)
        ->first();

    $deduct = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::DEDUCT)
        ->where('user_id', $user->id)
        ->first();

    expect($opening)->not->toBeNull()
        ->and((float) $opening->amount)->toBe(10.0)
        ->and($deduct)->not->toBeNull()
        ->and((float) $deduct->amount)->toBe(2.0)
        ->and((float) $deduct->balance_before)->toBe(10.0)
        ->and((float) $deduct->balance_after)->toBe(8.0)
        ->and($deduct->leave_request_id)->toBe($leave->id);
});

it('duplicate deduct is idempotent', function () {
    $service = new LeaveBalanceService;
    $email = 'test-deduct-idempotent-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Deduct Idempotent',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 10,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-16',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    $first = $service->deductLeaveBalanceForLeave($leave);
    $second = $service->deductLeaveBalanceForLeave($leave);

    expect($first)->toBe($second)
        ->and((float) $user->fresh()->leave_balance)->toBe(8.0)
        ->and(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::DEDUCT)
            ->where('user_id', $user->id)
            ->count())->toBe(1);
});

it('duplicate refund is idempotent', function () {
    $service = new LeaveBalanceService;
    $email = 'test-refund-idempotent-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Refund Idempotent',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 10,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-16',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    $service->deductLeaveBalanceForLeave($leave);
    $first = $service->refundLeaveBalanceForLeave($leave);
    $second = $service->refundLeaveBalanceForLeave($leave);

    expect($first)->toBe($second)
        ->and((float) $user->fresh()->leave_balance)->toBe(10.0)
        ->and(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
            ->where('user_id', $user->id)
            ->count())->toBe(1);
});

it('refunds annual leave using original deduct amount even if role changes', function () {
    $service = new LeaveBalanceService;
    $email = 'test-refund-role-change-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Refund Role Change',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 12,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-03-27',
            'end_date' => '2026-03-30',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    // EMPLOYEE (6-day): Fri(1) + Sat(0.5) + Sun(0) + Mon(1) = 2.5 days.
    $service->deductLeaveBalanceForLeave($leave);
    expect((float) $user->fresh()->leave_balance)->toBe(9.5);

    // Ubah role menjadi MANAGER (5-day) setelah deduction. Jika refund
    // menghitung ulang dari tanggal, hasilnya menjadi 2 hari, tetapi refund
    // harus tetap mengikuti ledger DEDUCT asli sebesar 2.5 hari.
    $user->update(['role' => UserRole::MANAGER]);
    $refunded = $service->refundLeaveBalanceForLeave($leave);

    expect($refunded)->toBe(2.5)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0);

    $refund = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
        ->where('user_id', $user->id)
        ->first();

    expect((float) $refund->amount)->toBe(2.5)
        ->and((float) $refund->balance_before)->toBe(9.5)
        ->and((float) $refund->balance_after)->toBe(12.0);
});

it('refunds annual leave using original deduct amount even if dates change', function () {
    $service = new LeaveBalanceService;
    $email = 'test-refund-date-change-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Refund Date Change',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 12,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-16',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    // Deduct 2 days.
    $service->deductLeaveBalanceForLeave($leave);
    expect((float) $user->fresh()->leave_balance)->toBe(10.0);

    // Perpanjang rentang cuti setelah deduction; refund tetap mengikuti DEDUCT.
    $leave->update([
        'start_date' => '2026-06-15',
        'end_date' => '2026-06-19',
    ]);
    $refunded = $service->refundLeaveBalanceForLeave($leave);

    // EMPLOYEE untuk rentang baru: Mon(1)+Tue(1)+Wed(1)+Thu(1)+Fri(1) = 5,
    // tetapi refund harus tetap 2 hari sesuai DEDUCT asli.
    expect($refunded)->toBe(2.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0);
});

it('adjusts approved leave balance from saturday to monday and refunds the latest amount', function () {
    $service = new LeaveBalanceService;
    $user = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 10,
    ]);
    $leave = LeaveRequest::factory()->forUser($user)->cuti()->approved()->create([
        'start_date' => '2026-07-18',
        'end_date' => '2026-07-18',
    ]);

    expect($service->deductLeaveBalanceForLeave($leave))->toBe(0.5)
        ->and((float) $user->fresh()->leave_balance)->toBe(9.5);

    $result = $service->adjustApprovedLeaveDateBalance(
        $leave,
        '2026-07-20',
        'Permintaan karyawan',
        $user->id,
    );

    expect($result)->toBe([
        'old_amount' => 0.5,
        'new_amount' => 1.0,
        'adjustment' => 0.5,
    ])->and((float) $user->fresh()->leave_balance)->toBe(9.0);

    $adjustment = LeaveBalanceTransaction::query()
        ->where('leave_request_id', $leave->id)
        ->where('transaction_type', LeaveBalanceTransaction::ADJUSTMENT)
        ->first();

    expect($adjustment)->not->toBeNull()
        ->and((float) $adjustment->amount)->toBe(0.5)
        ->and($adjustment->idempotency_key)->toStartWith("ADJUST_DATE:LEAVE:{$leave->id}:");

    expect($service->refundLeaveBalanceForLeave($leave))->toBe(1.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(10.0);
});

it('rejects approved date adjustment when deduct ledger is missing', function () {
    $service = new LeaveBalanceService;
    $user = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 9.5,
    ]);
    $leave = LeaveRequest::factory()->forUser($user)->cuti()->approved()->create([
        'start_date' => '2026-07-18',
        'end_date' => '2026-07-18',
    ]);

    expect(fn () => $service->adjustApprovedLeaveDateBalance(
        $leave,
        '2026-07-20',
        'Permintaan karyawan',
        $user->id,
    ))->toThrow(RuntimeException::class, 'Ledger pemotongan cuti belum tersedia')
        ->and((float) $user->fresh()->leave_balance)->toBe(9.5);
});

it('refunds non-CUTI leave using original deduct amount without explicit refund amount', function () {
    $service = new LeaveBalanceService;
    $email = 'test-refund-sakit-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Refund Sakit',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 12,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::SAKIT,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-16',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    // HRD dapat memutuskan potong saldo cuti untuk SAKIT sebesar 3 hari.
    $service->deductLeaveBalanceForLeave($leave, 3.0);
    expect((float) $user->fresh()->leave_balance)->toBe(9.0);

    // Refund otomatis mengikuti DEDUCT asli tanpa perlu explicit amount.
    $refunded = $service->refundLeaveBalanceForLeave($leave);

    expect($refunded)->toBe(3.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0);

    expect(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
        ->where('user_id', $user->id)
        ->count())->toBe(1);
});

it('falls back to effective days for legacy CUTI without deduct ledger', function () {
    $service = new LeaveBalanceService;
    $email = 'test-refund-legacy-cuti-'.uniqid().'@example.com';

    // Modelkan saldo snapshot user yang sudah pernah dipotong 2 hari sebelum
    // era ledger, sehingga refund fallback mengembalikan saldo ke angka semula.
    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Refund Legacy Cuti',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 10,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-16',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    $refunded = $service->refundLeaveBalanceForLeave($leave);

    expect($refunded)->toBe(2.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0);

    expect(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::DEDUCT)
        ->where('user_id', $user->id)
        ->count())->toBe(0)
        ->and(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
            ->where('user_id', $user->id)
            ->count())->toBe(1);
});

it('does not refund non-CUTI legacy without deduct ledger even with explicit amount', function () {
    $service = new LeaveBalanceService;
    $email = 'test-refund-legacy-sakit-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Refund Legacy Sakit',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 12,
        ]);
    });

    $leave = LeaveRequest::unguarded(function () use ($user) {
        return LeaveRequest::create([
            'type' => LeaveType::SAKIT,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-16',
            'status' => LeaveRequest::PENDING_HR,
            'user_id' => $user->id,
        ]);
    });

    // Meskipin explicit amount diberikan, non-CUTI tanpa DEDUCT ledger tidak boleh
    // mengubah saldo atau menulis REFUND.
    $refunded = $service->refundLeaveBalanceForLeave($leave, 5.0);

    expect($refunded)->toBe(0.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0)
        ->and(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::REFUND)
            ->where('user_id', $user->id)
            ->count())->toBe(0);
});

it('adjustment creates opening and adjustment ledger', function () {
    $service = new LeaveBalanceService;
    $email = 'test-adjust-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Adjustment',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 10,
        ]);
    });

    $adjusted = $service->adjustBalanceToTarget(
        $user,
        12,
        'Penyesuaian manual oleh HR',
        'ADJUST:MANUAL:'.$user->id,
        $user->id,
    );

    expect($adjusted)->toBe(2.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0);

    $opening = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::OPENING_BALANCE)
        ->where('user_id', $user->id)
        ->first();
    $adjustment = LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::ADJUSTMENT)
        ->where('user_id', $user->id)
        ->first();

    expect($opening)->not->toBeNull()
        ->and((float) $opening->amount)->toBe(10.0)
        ->and($adjustment)->not->toBeNull()
        ->and((float) $adjustment->amount)->toBe(2.0)
        ->and((float) $adjustment->balance_before)->toBe(10.0)
        ->and((float) $adjustment->balance_after)->toBe(12.0);
});

it('adjustment is no-op when target equals current balance', function () {
    $service = new LeaveBalanceService;
    $email = 'test-adjust-noop-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Adjustment Noop',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 12,
        ]);
    });

    $adjusted = $service->adjustBalanceToTarget($user, 12);

    expect($adjusted)->toBe(0.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0)
        ->and(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::ADJUSTMENT)
            ->where('user_id', $user->id)
            ->count())->toBe(0);
});

it('adjustment with duplicate key is idempotent', function () {
    $service = new LeaveBalanceService;
    $email = 'test-adjust-dup-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Adjustment Duplicate',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 10,
        ]);
    });

    $key = 'ADJUST:DUP:'.$user->id;

    $first = $service->adjustBalanceToTarget($user, 12, 'Dup', $key);
    $second = $service->adjustBalanceToTarget($user, 15, 'Dup', $key);

    expect($first)->toBe($second)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0)
        ->and(LeaveBalanceTransaction::where('transaction_type', LeaveBalanceTransaction::ADJUSTMENT)
            ->where('user_id', $user->id)
            ->count())->toBe(1);
});

it('annual reset uses deterministic idempotency key', function () {
    $service = new LeaveBalanceService;
    $email = 'test-annual-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Annual Reset',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 5,
        ]);
    });

    $key = "ANNUAL_RESET:USER:{$user->id}:YEAR:2026";

    $first = $service->adjustBalanceToTarget($user, 12, 'Reset tahun 2026', $key);
    $second = $service->adjustBalanceToTarget($user, 12, 'Reset tahun 2026', $key);

    expect($first)->toBe(7.0)
        ->and($second)->toBe(7.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0)
        ->and(LeaveBalanceTransaction::where('idempotency_key', $key)->count())->toBe(1);
});

it('annual reset with explicit key stores zero marker when target already matches current', function () {
    $service = new LeaveBalanceService;
    $email = 'test-annual-marker-'.uniqid().'@example.com';

    $user = User::unguarded(function () use ($email) {
        return User::create([
            'name' => 'Test Annual Marker',
            'email' => $email,
            'password' => 'password',
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 12,
        ]);
    });

    $annualKey = "ANNUAL_RESET:USER:{$user->id}:YEAR:2026";

    // Run pertama: target == saldo saat ini. Harus membuat marker ADJUSTMENT 0.
    $first = $service->adjustBalanceToTarget($user, 12, 'Reset tahun 2026', $annualKey);

    expect($first)->toBe(0.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(12.0)
        ->and(LeaveBalanceTransaction::where('idempotency_key', $annualKey)->count())->toBe(1)
        ->and((float) LeaveBalanceTransaction::where('idempotency_key', $annualKey)->value('amount'))->toBe(0.0);

    // Saldo berubah karena transaksi lain (misalnya potong cuti) dengan key berbeda.
    $deductKey = "DEDUCT:MANUAL:{$user->id}";
    $service->adjustBalanceToTarget($user, 11, 'Potong 1 hari', $deductKey);

    // Run kedua dengan annual key lama tidak boleh mengubah saldo lagi.
    $second = $service->adjustBalanceToTarget($user, 12, 'Reset tahun 2026', $annualKey);

    expect($second)->toBe(0.0)
        ->and((float) $user->fresh()->leave_balance)->toBe(11.0)
        ->and(LeaveBalanceTransaction::where('idempotency_key', $annualKey)->count())->toBe(1)
        ->and(LeaveBalanceTransaction::where('user_id', $user->id)
            ->where('transaction_type', LeaveBalanceTransaction::ADJUSTMENT)
            ->count())->toBe(2);
});
