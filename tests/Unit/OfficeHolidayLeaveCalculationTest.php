<?php

use App\Enums\UserRole;
use App\Models\OfficeHoliday;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
    if (! Schema::hasTable('office_holidays')) {
        Schema::create('office_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date')->unique();
            $table->string('name');
            $table->string('type')->default('NATIONAL');
            $table->boolean('deducts_leave')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    DB::table('office_holidays')->delete();
});

it('excludes an active office holiday from employee annual leave days', function () {
    DB::table('office_holidays')->insert([
        'holiday_date' => '2026-06-16',
        'name' => 'Libur Kantor',
        'type' => 'COMPANY',
        'deducts_leave' => false,
        'is_active' => true,
    ]);

    $user = new User([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 12,
    ]);

    $days = app(LeaveBalanceService::class)
        ->calculateEffectiveDaysForUser($user, '2026-06-03', '2026-06-18');

    expect($days)->toBe(12.0);
});

it('keeps the normal day weight when a calendar entry deducts leave', function () {
    DB::table('office_holidays')->insert([
        'holiday_date' => '2026-06-16',
        'name' => 'Cuti Bersama Potong Saldo',
        'type' => 'COLLECTIVE',
        'deducts_leave' => true,
        'is_active' => true,
    ]);

    $user = new User([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 12,
    ]);

    $days = app(LeaveBalanceService::class)
        ->calculateEffectiveDaysForUser($user, '2026-06-03', '2026-06-18');

    expect($days)->toBe(13.0);
});

it('excludes an office holiday created on the period end date', function () {
    OfficeHoliday::create([
        'holiday_date' => '2026-06-18',
        'name' => 'Libur di Akhir Periode',
        'type' => OfficeHoliday::TYPE_COMPANY,
        'deducts_leave' => false,
        'is_active' => true,
    ]);

    $user = new User([
        'role' => UserRole::EMPLOYEE,
        'leave_balance' => 12,
    ]);

    $days = app(LeaveBalanceService::class)
        ->calculateEffectiveDaysForUser($user, '2026-06-18', '2026-06-18');

    expect($days)->toBe(0.0);
});
