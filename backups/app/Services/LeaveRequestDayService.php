<?php

namespace App\Services;

use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class LeaveRequestDayService
{
    public function __construct(
        protected LeaveBalanceService $leaveBalanceService,
    ) {}

    /**
     * Sinkronkan baris detail dengan rentang parent tanpa menimpa keputusan yang sudah ada.
     *
     * @return Collection<int, LeaveRequestDay>
     */
    public function syncDateRange(LeaveRequest $leave): Collection
    {
        if (! Schema::hasTable('leave_request_days')) {
            return collect();
        }

        $leave->loadMissing('user');

        $startDate = $leave->start_date->toDateString();
        $endDate = $leave->end_date->toDateString();
        $hadDetails = $leave->days()->exists();

        $leave->days()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('leave_date', '<', $startDate)
                    ->orWhereDate('leave_date', '>', $endDate);
            })
            ->delete();

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $dateString = $date->toDateString();
            [$treatment, $amount] = $this->defaultDecision($leave, $dateString);

            $exists = LeaveRequestDay::query()
                ->where('leave_request_id', $leave->id)
                ->whereDate('leave_date', $dateString)
                ->exists();

            if (! $exists) {
                LeaveRequestDay::create([
                    'leave_request_id' => $leave->id,
                    'leave_date' => $dateString,
                    'treatment' => $treatment,
                    'deduction_amount' => $amount,
                ]);
            }
        }

        $days = $leave->days()->orderBy('leave_date')->get();

        if (! $hadDetails) {
            $this->applyLegacyDecision($leave, $days);
            $days = $leave->days()->orderBy('leave_date')->get();
        }

        return $days;
    }

    /**
     * Simpan keputusan final HR dan kembalikan target total potong cuti.
     *
     * @param  array<string, string>  $decisions
     */
    public function saveDecisions(LeaveRequest $leave, array $decisions, int $actorId): float
    {
        if (! Schema::hasTable('leave_request_days')) {
            throw new RuntimeException('Penyimpanan perlakuan per tanggal belum tersedia.');
        }

        return DB::transaction(function () use ($leave, $decisions, $actorId) {
            $days = $this->syncDateRange($leave)->keyBy(
                fn (LeaveRequestDay $day): string => $day->leave_date->toDateString(),
            );

            $expectedDates = $days->keys()->sort()->values()->all();
            $submittedDates = collect(array_keys($decisions))->sort()->values()->all();

            if ($submittedDates !== $expectedDates) {
                throw ValidationException::withMessages([
                    'daily_treatments' => 'Pilihan perlakuan harus diisi untuk seluruh tanggal pengajuan.',
                ]);
            }

            $total = 0.0;
            foreach ($days as $date => $day) {
                [$treatment, $amount] = $this->parseDecision($decisions[$date]);

                $day->update([
                    'treatment' => $treatment,
                    'deduction_amount' => $amount,
                    'decided_by' => $actorId,
                    'decided_at' => now(),
                ]);

                if ($treatment === LeaveRequestDay::LEAVE_BALANCE) {
                    $total += $amount;
                }
            }

            return round($total, 2);
        });
    }

    /**
     * @return array{0: string, 1: float}
     */
    private function defaultDecision(LeaveRequest $leave, string $date): array
    {
        $type = $leave->type instanceof LeaveType ? $leave->type->value : (string) $leave->type;

        if ($type !== LeaveType::CUTI->value) {
            return [LeaveRequestDay::NONE, 0.0];
        }

        $amount = $this->leaveBalanceService->calculateEffectiveDaysForUser(
            $leave->user,
            $date,
            $date,
        );

        return $amount > 0
            ? [LeaveRequestDay::LEAVE_BALANCE, $amount]
            : [LeaveRequestDay::NONE, 0.0];
    }

    /**
     * Petakan keputusan lama tingkat-pengajuan ke detail tanggal saat pertama kali dibuka.
     * Hasil ini hanya jembatan kompatibilitas; setelah HR menyimpan pilihan, detail tanggal
     * menjadi sumber keputusan yang dipertahankan oleh sync berikutnya.
     *
     * @param  Collection<int, LeaveRequestDay>  $days
     */
    private function applyLegacyDecision(LeaveRequest $leave, Collection $days): void
    {
        $remainingDeduction = $this->leaveBalanceService->currentNetDeductionForLeave($leave);
        $hasMealAllowance = (bool) $leave->deduct_um;

        if ($remainingDeduction <= 0 && ! $hasMealAllowance) {
            return;
        }

        foreach ($days as $day) {
            $day->update([
                'treatment' => LeaveRequestDay::NONE,
                'deduction_amount' => 0,
            ]);
        }

        foreach ($days as $day) {
            if ($remainingDeduction <= 0) {
                break;
            }

            $amount = min(1.0, $remainingDeduction);
            $day->update([
                'treatment' => LeaveRequestDay::LEAVE_BALANCE,
                'deduction_amount' => $amount,
            ]);
            $remainingDeduction = round($remainingDeduction - $amount, 2);
        }

        if ($hasMealAllowance) {
            foreach ($days->where('treatment', LeaveRequestDay::NONE) as $day) {
                $day->update([
                    'treatment' => LeaveRequestDay::MEAL_ALLOWANCE,
                    'deduction_amount' => 0,
                ]);
            }
        }
    }

    /**
     * @return array{0: string, 1: float}
     */
    private function parseDecision(string $decision): array
    {
        return match ($decision) {
            'NONE' => [LeaveRequestDay::NONE, 0.0],
            'MEAL_ALLOWANCE' => [LeaveRequestDay::MEAL_ALLOWANCE, 0.0],
            'LEAVE_BALANCE_1' => [LeaveRequestDay::LEAVE_BALANCE, 1.0],
            'LEAVE_BALANCE_0_5' => [LeaveRequestDay::LEAVE_BALANCE, 0.5],
            default => throw ValidationException::withMessages([
                'daily_treatments' => 'Pilihan perlakuan tanggal tidak valid.',
            ]),
        };
    }
}
