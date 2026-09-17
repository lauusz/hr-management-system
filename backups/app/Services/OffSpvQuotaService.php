<?php

namespace App\Services;

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\OffSpvChange;
use App\Models\OffSpvPeriod;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OffSpvQuotaService
{
    public const ROLLOUT_DATE = '2026-07-26';

    /**
     * @return array{label:string,year:int,month:int,start:Carbon,end:Carbon,saturdays:array<int,string>,saturday_count:int,base_quota:int}
     */
    public function periodForDate(CarbonInterface|string $date): array
    {
        $date = Carbon::parse($date)->startOfDay();
        $label = $date->day >= 26
            ? $date->copy()->addMonthNoOverflow()->startOfMonth()
            : $date->copy()->startOfMonth();
        $start = $label->copy()->subMonthNoOverflow()->day(26);
        $end = $label->copy()->day(25);
        $saturdays = [];
        $saturdayCount = 0;

        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addDay()) {
            if ($cursor->isSaturday()) {
                $saturdays[] = $cursor->toDateString();
                $saturdayCount++;
            }
        }

        return [
            'label' => $label->format('Y-m'),
            'year' => (int) $label->year,
            'month' => (int) $label->month,
            'start' => $start,
            'end' => $end,
            'saturdays' => $saturdays,
            'saturday_count' => $saturdayCount,
            'base_quota' => max(0, $saturdayCount - 2),
        ];
    }

    public function getOrCreatePeriod(User $supervisor, CarbonInterface|string $date): OffSpvPeriod
    {
        $period = $this->periodForDate($date);

        return DB::transaction(function () use ($supervisor, $period) {
            $model = OffSpvPeriod::firstOrCreate(
                [
                    'user_id' => $supervisor->id,
                    'period_year' => $period['year'],
                    'period_month' => $period['month'],
                ],
                [
                    'period_start' => $period['start']->toDateString(),
                    'period_end' => $period['end']->toDateString(),
                    'saturday_count' => $period['saturday_count'],
                    'base_quota' => $period['base_quota'],
                    'effective_quota' => $period['base_quota'],
                ],
            );

            if ($model->wasRecentlyCreated) {
                $model->changes()->create([
                    'change_type' => OffSpvChange::TYPE_INITIALIZED,
                    'quota_before' => null,
                    'quota_after' => $model->effective_quota,
                    'reason' => 'Jatah otomatis berdasarkan cutoff 26-25',
                    'changed_by' => null,
                    'created_at' => now(),
                ]);
            }

            return $model;
        });
    }

    public function currentPeriod(User $supervisor, CarbonInterface|string|null $date = null): OffSpvPeriod
    {
        return $this->getOrCreatePeriod($supervisor, $date ?? now());
    }

    public function rolloutStarted(CarbonInterface|string|null $date = null): bool
    {
        return ! Carbon::parse($date ?? now())->startOfDay()->isBefore(self::ROLLOUT_DATE);
    }

    public function periodForHrEntry(User $supervisor, CarbonInterface|string $requestedDate): OffSpvPeriod
    {
        $requestedDate = Carbon::parse($requestedDate)->startOfDay();

        if (! $supervisor->isSupervisor()) {
            throw new RuntimeException('Tipe OFF hanya untuk Supervisor.');
        }

        if ($requestedDate->isBefore(self::ROLLOUT_DATE)) {
            throw new RuntimeException('Perhitungan OFF SPV baru dimulai dari periode Agustus 2026.');
        }

        if (! $requestedDate->isSaturday()) {
            throw new RuntimeException('OFF SPV hanya dapat dicatat untuk hari Sabtu.');
        }

        return $this->getOrCreatePeriod($supervisor, $requestedDate);
    }

    public function periodForHrIntervention(User $supervisor, CarbonInterface|string $requestedDate): OffSpvPeriod
    {
        $requestedDate = Carbon::parse($requestedDate)->startOfDay();

        if (! $supervisor->isSupervisor()) {
            throw new RuntimeException('Tipe OFF hanya untuk Supervisor.');
        }

        if (! $requestedDate->isSaturday()) {
            throw new RuntimeException('OFF SPV hanya dapat dicatat untuk hari Sabtu.');
        }

        return $this->getOrCreatePeriod($supervisor, $requestedDate);
    }

    public function initializeYearPeriods(?int $year = null, ?User $supervisor = null): int
    {
        $year ??= $this->periodForDate(now())['year'];
        $supervisors = $supervisor
            ? collect([$supervisor])->filter(fn (User $user) => $user->isSupervisor() && $user->isActive())
            : User::query()->active()->where('role', UserRole::SUPERVISOR->value)->get();
        $created = 0;

        foreach ($supervisors as $user) {
            foreach (range(1, 12) as $month) {
                $period = $this->getOrCreatePeriod($user, Carbon::create($year, $month, 1));
                $created += $period->wasRecentlyCreated ? 1 : 0;
            }
        }

        return $created;
    }

    public function periodForRequest(
        User $supervisor,
        CarbonInterface|string $requestedDate,
        ?int $excludeLeaveId = null,
    ): OffSpvPeriod {
        $requestedDate = Carbon::parse($requestedDate)->startOfDay();
        $current = $this->periodForDate(now());
        $requested = $this->periodForDate($requestedDate);

        if (now()->isBefore(self::ROLLOUT_DATE)
            || $requestedDate->isBefore(self::ROLLOUT_DATE)
            || $requested['label'] !== $current['label']) {
            throw new RuntimeException('Pengajuan OFF SPV hanya dapat dilakukan untuk periode aktif.');
        }

        if (! $requestedDate->isSaturday()) {
            throw new RuntimeException('OFF SPV hanya dapat diajukan untuk hari Sabtu.');
        }

        $period = $this->getOrCreatePeriod($supervisor, $requestedDate);
        $this->assertRequestAvailable($supervisor, $period, $requestedDate, $excludeLeaveId);

        return $period;
    }

    public function assertRequestAvailable(
        User $supervisor,
        OffSpvPeriod $period,
        CarbonInterface|string $requestedDate,
        ?int $excludeLeaveId = null,
    ): void {
        if ($this->stats($period, $excludeLeaveId)['remaining'] <= 0) {
            throw new RuntimeException('Kuota OFF Supervisor habis.');
        }

        $requestedDate = Carbon::parse($requestedDate)->startOfDay();
        $weekStart = $requestedDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);
        $alreadyInWeek = LeaveRequest::query()
            ->where('user_id', $supervisor->id)
            ->where('type', LeaveType::OFF_SPV->value)
            ->whereBetween('start_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, LeaveRequest::STATUS_CANCELLED])
            ->when($excludeLeaveId, fn (Builder $query) => $query->whereKeyNot($excludeLeaveId))
            ->exists();

        if ($alreadyInWeek) {
            throw new RuntimeException('Pengajuan OFF SPV maksimal 1x seminggu.');
        }
    }

    /**
     * @return array{approved:int,pending:int,remaining:int,expired:int,excess:int}
     */
    public function stats(OffSpvPeriod $period, ?int $excludeLeaveId = null): array
    {
        $query = LeaveRequest::query()
            ->where('off_spv_period_id', $period->id)
            ->where('type', LeaveType::OFF_SPV->value)
            ->when($excludeLeaveId, fn ($builder) => $builder->whereKeyNot($excludeLeaveId));

        $approved = (clone $query)->where('status', LeaveRequest::STATUS_APPROVED)->count();
        $pending = (clone $query)->whereIn('status', [
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
        ])->count();
        $remaining = max(0, $period->effective_quota - $approved - $pending);

        return [
            'approved' => $approved,
            'pending' => $pending,
            'remaining' => $remaining,
            'expired' => $period->period_end->isBefore(today()) ? $remaining : 0,
            'excess' => max(0, $approved - $period->effective_quota),
        ];
    }
}
