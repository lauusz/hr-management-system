<?php

namespace App\Services;

use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use RuntimeException;

class LeaveBalanceService
{
    private const FIVE_DAY_WORK_WEEK_ROLES = ['MANAGER'];

    public function isFiveDayWorkWeekForUser(User $user): bool
    {
        return in_array($this->getRoleString($user), self::FIVE_DAY_WORK_WEEK_ROLES, true);
    }

    public function calculateEffectiveDaysForUser(User $user, Carbon|string $startDate, Carbon|string $endDate): int
    {
        $start = $startDate instanceof Carbon ? $startDate->copy() : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate->copy() : Carbon::parse($endDate);
        $period = CarbonPeriod::create($start, $end);
        $isFiveDayWorkWeek = $this->isFiveDayWorkWeekForUser($user);

        $days = 0;

        foreach ($period as $date) {
            if ($isFiveDayWorkWeek) {
                if ($date->isSaturday() || $date->isSunday()) {
                    continue;
                }
            } elseif ($date->isSunday()) {
                continue;
            }

            $days++;
        }

        return $days;
    }

    public function calculateEffectiveDaysForLeave(LeaveRequest $leave): int
    {
        return $this->calculateEffectiveDaysForUser($leave->user, $leave->start_date, $leave->end_date);
    }

    public function isAnnualLeave(LeaveRequest $leave): bool
    {
        $leaveType = $leave->type instanceof LeaveType ? $leave->type->value : (string) $leave->type;

        return strtoupper($leaveType) === LeaveType::CUTI->value;
    }

    public function deductLeaveBalanceForLeave(LeaveRequest $leave): int
    {
        if (!$this->isAnnualLeave($leave)) {
            return 0;
        }

        $daysToDeduct = $this->calculateEffectiveDaysForLeave($leave);
        $currentBalance = (int) $leave->user->leave_balance;

        if ($currentBalance < $daysToDeduct) {
            throw new RuntimeException("Gagal Approve: Saldo cuti tidak cukup. User punya: {$currentBalance}, Butuh (Efektif): {$daysToDeduct} hari.");
        }

        if ($daysToDeduct > 0) {
            $leave->user->decrement('leave_balance', $daysToDeduct);
        }

        return $daysToDeduct;
    }

    public function refundLeaveBalanceForLeave(LeaveRequest $leave): int
    {
        if (!$this->isAnnualLeave($leave)) {
            return 0;
        }

        $daysToRefund = $this->calculateEffectiveDaysForLeave($leave);

        if ($daysToRefund > 0) {
            $leave->user->increment('leave_balance', $daysToRefund);
        }

        return $daysToRefund;
    }

    private function getRoleString(User $user): string
    {
        return strtoupper((string) ($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role));
    }
}