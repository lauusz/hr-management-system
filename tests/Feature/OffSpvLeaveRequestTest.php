<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\OffSpvQuotaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Laravel\actingAs;

afterEach(function () {
    Carbon::setTestNow();
    RateLimiter::clear('submit_izin_'.(auth()->id() ?? 'guest'));
});

function offSpvPayload(string $date): array
{
    return [
        'type' => LeaveType::OFF_SPV->value,
        'start_date' => $date,
        'end_date' => $date,
        'reason' => 'Jadwal OFF supervisor',
    ];
}

describe('OFF SPV leave requests', function () {
    beforeEach(function () {
        Carbon::setTestNow('2026-07-30 09:00:00');
        $this->supervisor = User::factory()->create([
            'role' => UserRole::SUPERVISOR,
            'status' => User::STATUS_ACTIVE,
        ]);
        actingAs($this->supervisor, 'web');
    });

    it('does not create a managed period before the August rollout', function () {
        Carbon::setTestNow('2026-07-24 09:00:00');

        $this->get(route('leave-requests.create'))->assertOk();

        expect(\App\Models\OffSpvPeriod::where('user_id', $this->supervisor->id)->exists())->toBeFalse();
    });

    it('links a Saturday request to the active cutoff period', function () {
        $response = $this->post(route('leave-requests.store'), offSpvPayload('2026-08-01'));

        $response->assertRedirect(route('leave-requests.index'));
        $leave = LeaveRequest::where('user_id', $this->supervisor->id)->sole();

        expect($leave->status)->toBe(LeaveRequest::PENDING_HR)
            ->and($leave->off_spv_period_id)->not->toBeNull()
            ->and($leave->offSpvPeriod->period_month)->toBe(8)
            ->and($leave->offSpvPeriod->period_start->toDateString())->toBe('2026-07-26')
            ->and($leave->offSpvPeriod->period_end->toDateString())->toBe('2026-08-25');
    });

    it('rejects OFF SPV outside Saturday', function () {
        $response = $this->from(route('leave-requests.create'))
            ->post(route('leave-requests.store'), offSpvPayload('2026-08-07'));

        $response->assertRedirect(route('leave-requests.create'))
            ->assertSessionHas('error', 'OFF SPV hanya dapat diajukan untuk hari Sabtu.');
        expect(LeaveRequest::where('user_id', $this->supervisor->id)->count())->toBe(0);
    });

    it('rejects OFF SPV outside the active cutoff period', function () {
        $response = $this->from(route('leave-requests.create'))
            ->post(route('leave-requests.store'), offSpvPayload('2026-09-05'));

        $response->assertRedirect(route('leave-requests.create'))
            ->assertSessionHas('error', 'Pengajuan OFF SPV hanya dapat dilakukan untuk periode aktif.');
        expect(LeaveRequest::where('user_id', $this->supervisor->id)->count())->toBe(0);
    });

    it('counts pending requests against available quota', function () {
        $period = app(OffSpvQuotaService::class)
            ->getOrCreatePeriod($this->supervisor, Carbon::parse('2026-08-01'));
        $period->update(['effective_quota' => 1]);

        LeaveRequest::factory()->create([
            'user_id' => $this->supervisor->id,
            'off_spv_period_id' => $period->id,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-01',
            'status' => LeaveRequest::PENDING_HR,
        ]);

        $response = $this->from(route('leave-requests.create'))
            ->post(route('leave-requests.store'), offSpvPayload('2026-08-08'));

        $response->assertRedirect(route('leave-requests.create'))
            ->assertSessionHas('error', 'Kuota OFF Supervisor habis.');
        expect(LeaveRequest::where('user_id', $this->supervisor->id)->count())->toBe(1);
    });
});
