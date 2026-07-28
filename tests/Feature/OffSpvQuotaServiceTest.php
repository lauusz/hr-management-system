<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\OffSpvChange;
use App\Models\OffSpvPeriod;
use App\Models\User;
use App\Services\OffSpvQuotaService;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

describe('OFF SPV quota service', function () {
    it('uses the 26 to 25 cutoff and subtracts two Saturdays', function () {
        $service = app(OffSpvQuotaService::class);

        $january = $service->periodForDate(Carbon::parse('2026-01-10'));
        $august = $service->periodForDate(Carbon::parse('2026-08-10'));

        expect($january['label'])->toBe('2026-01')
            ->and($january['start']->toDateString())->toBe('2025-12-26')
            ->and($january['end']->toDateString())->toBe('2026-01-25')
            ->and($january['saturday_count'])->toBe(5)
            ->and($january['base_quota'])->toBe(3)
            ->and($august['start']->toDateString())->toBe('2026-07-26')
            ->and($august['end']->toDateString())->toBe('2026-08-25')
            ->and($august['saturday_count'])->toBe(4)
            ->and($august['base_quota'])->toBe(2);
    });

    it('creates one automatic period and one initialization audit row', function () {
        $supervisor = User::factory()->create([
            'role' => UserRole::SUPERVISOR,
            'status' => User::STATUS_ACTIVE,
        ]);
        $service = app(OffSpvQuotaService::class);

        $first = $service->getOrCreatePeriod($supervisor, Carbon::parse('2026-08-10'));
        $second = $service->getOrCreatePeriod($supervisor, Carbon::parse('2026-08-20'));

        expect($first->is($second))->toBeTrue()
            ->and($first->base_quota)->toBe(2)
            ->and($first->effective_quota)->toBe(2)
            ->and(OffSpvPeriod::count())->toBe(1)
            ->and(OffSpvChange::count())->toBe(1)
            ->and(OffSpvChange::first()->change_type)->toBe(OffSpvChange::TYPE_INITIALIZED);
    });

    it('calculates approved pending remaining and expired without negatives', function () {
        Carbon::setTestNow('2026-09-10');
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
        $period = app(OffSpvQuotaService::class)
            ->getOrCreatePeriod($supervisor, Carbon::parse('2026-08-10'));

        LeaveRequest::factory()->create([
            'user_id' => $supervisor->id,
            'off_spv_period_id' => $period->id,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-01',
            'status' => LeaveRequest::STATUS_APPROVED,
        ]);
        LeaveRequest::factory()->create([
            'user_id' => $supervisor->id,
            'off_spv_period_id' => $period->id,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-08-08',
            'end_date' => '2026-08-08',
            'status' => LeaveRequest::STATUS_APPROVED,
        ]);
        LeaveRequest::factory()->create([
            'user_id' => $supervisor->id,
            'off_spv_period_id' => $period->id,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-08-15',
            'end_date' => '2026-08-15',
            'status' => LeaveRequest::PENDING_HR,
        ]);

        $period->update(['effective_quota' => 1]);
        $stats = app(OffSpvQuotaService::class)->stats($period);

        expect($stats)->toMatchArray([
            'approved' => 2,
            'pending' => 1,
            'remaining' => 0,
            'expired' => 0,
            'excess' => 1,
        ]);
    });

    it('initializes a full year for active supervisors without overwriting manual quota', function () {
        Carbon::setTestNow('2026-07-26 00:05:00');
        $active = User::factory()->create(['role' => UserRole::SUPERVISOR, 'status' => User::STATUS_ACTIVE]);
        User::factory()->create(['role' => UserRole::SUPERVISOR, 'status' => 'INACTIVE']);
        User::factory()->create(['role' => UserRole::MANAGER, 'status' => User::STATUS_ACTIVE]);

        $service = app(OffSpvQuotaService::class);
        $created = $service->initializeYearPeriods(2026);
        $august = OffSpvPeriod::where('user_id', $active->id)
            ->where('period_year', 2026)
            ->where('period_month', 8)
            ->sole();
        $august->update(['effective_quota' => 0]);
        $createdAgain = $service->initializeYearPeriods(2026);

        expect($created)->toBe(12)
            ->and($createdAgain)->toBe(0)
            ->and(OffSpvPeriod::where('user_id', $active->id)->count())->toBe(12)
            ->and(OffSpvChange::where('change_type', OffSpvChange::TYPE_INITIALIZED)->count())->toBe(12)
            ->and($august->fresh()->effective_quota)->toBe(0);
    });

    it('exposes an idempotent initializer command for the scheduler', function () {
        Carbon::setTestNow('2026-07-26 00:05:00');
        User::factory()->create(['role' => UserRole::SUPERVISOR, 'status' => User::STATUS_ACTIVE]);

        $this->artisan('off-spv:initialize-periods 2026')
            ->expectsOutput('12 periode OFF SPV tahun 2026 dibuat.')
            ->assertSuccessful();

        $this->artisan('off-spv:initialize-periods')
            ->expectsOutput('0 periode OFF SPV tahun 2026 dibuat.')
            ->assertSuccessful();
    });

    it('rejects an invalid initializer year', function (string $year) {
        $this->artisan("off-spv:initialize-periods {$year}")
            ->expectsOutput('Tahun periode harus antara 2026 dan 2100.')
            ->assertFailed();
    })->with(['2025', '2101', 'invalid']);

    it('rechecks quota availability against the stored period', function () {
        Carbon::setTestNow('2026-07-30 09:00:00');
        $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
        $period = app(OffSpvQuotaService::class)->getOrCreatePeriod($supervisor, '2026-08-01');
        $period->update(['effective_quota' => 1]);
        LeaveRequest::factory()->create([
            'user_id' => $supervisor->id,
            'off_spv_period_id' => $period->id,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-01',
            'status' => LeaveRequest::PENDING_HR,
        ]);

        expect(fn () => app(OffSpvQuotaService::class)
            ->assertRequestAvailable($supervisor, $period, '2026-08-08'))
            ->toThrow(RuntimeException::class, 'Kuota OFF Supervisor habis.');
    });
});
