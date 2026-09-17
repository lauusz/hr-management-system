<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Models\User;
use App\Services\LeaveRequestDayService;

describe('LeaveRequestDayService', function () {
    it('membentuk pilihan default cuti untuk hari kerja sabtu dan minggu', function () {
        $employee = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'leave_balance' => 12,
        ]);

        $leave = LeaveRequest::factory()->forUser($employee)->create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-09-04', // Jumat
            'end_date' => '2026-09-06',   // Minggu
        ]);

        app(LeaveRequestDayService::class)->syncDateRange($leave);

        $days = $leave->days()->orderBy('leave_date')->get();

        expect($days)->toHaveCount(3)
            ->and($days[0]->leave_date->toDateString())->toBe('2026-09-04')
            ->and($days[0]->treatment)->toBe(LeaveRequestDay::LEAVE_BALANCE)
            ->and($days[0]->deduction_amount)->toBe(1.0)
            ->and($days[1]->leave_date->toDateString())->toBe('2026-09-05')
            ->and($days[1]->treatment)->toBe(LeaveRequestDay::LEAVE_BALANCE)
            ->and($days[1]->deduction_amount)->toBe(0.5)
            ->and($days[2]->leave_date->toDateString())->toBe('2026-09-06')
            ->and($days[2]->treatment)->toBe(LeaveRequestDay::NONE)
            ->and($days[2]->deduction_amount)->toBe(0.0);
    });

    it('mempertahankan keputusan tanggal yang tetap dan membuang tanggal di luar rentang baru', function () {
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $leave = LeaveRequest::factory()->forUser($employee)->create([
            'type' => LeaveType::CUTI,
            'start_date' => '2026-09-04',
            'end_date' => '2026-09-06',
        ]);
        $service = app(LeaveRequestDayService::class);
        $service->syncDateRange($leave);

        $leave->days()->whereDate('leave_date', '2026-09-05')->update([
            'treatment' => LeaveRequestDay::MEAL_ALLOWANCE,
            'deduction_amount' => 0,
        ]);

        $leave->update([
            'start_date' => '2026-09-05',
            'end_date' => '2026-09-07',
        ]);
        $days = $service->syncDateRange($leave->fresh())->keyBy(
            fn (LeaveRequestDay $day) => $day->leave_date->toDateString(),
        );

        expect($days->keys()->all())->toBe(['2026-09-05', '2026-09-06', '2026-09-07'])
            ->and($days['2026-09-05']->treatment)->toBe(LeaveRequestDay::MEAL_ALLOWANCE)
            ->and($days['2026-09-05']->deduction_amount)->toBe(0.0)
            ->and($days['2026-09-07']->treatment)->toBe(LeaveRequestDay::LEAVE_BALANCE)
            ->and($days['2026-09-07']->deduction_amount)->toBe(1.0)
            ->and(LeaveRequestDay::where('leave_request_id', $leave->id)->whereDate('leave_date', '2026-09-04')->exists())->toBeFalse();
    });

    it('menyimpan keputusan radio per tanggal dan mengembalikan total potong cuti', function () {
        $hr = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $leave = LeaveRequest::factory()->forUser($employee)->create([
            'type' => LeaveType::IZIN,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-04',
        ]);
        $service = app(LeaveRequestDayService::class);
        $service->syncDateRange($leave);

        $total = $service->saveDecisions($leave, [
            '2026-09-01' => 'MEAL_ALLOWANCE',
            '2026-09-02' => 'LEAVE_BALANCE_1',
            '2026-09-03' => 'LEAVE_BALANCE_0_5',
            '2026-09-04' => 'NONE',
        ], $hr->id);

        $days = $leave->days()->orderBy('leave_date')->get();

        expect($total)->toBe(1.5)
            ->and($days->pluck('treatment')->all())->toBe([
                LeaveRequestDay::MEAL_ALLOWANCE,
                LeaveRequestDay::LEAVE_BALANCE,
                LeaveRequestDay::LEAVE_BALANCE,
                LeaveRequestDay::NONE,
            ])
            ->and($days->pluck('deduction_amount')->all())->toBe([0.0, 1.0, 0.5, 0.0])
            ->and($days->every(fn (LeaveRequestDay $day) => $day->decided_by === $hr->id))->toBeTrue()
            ->and($days->every(fn (LeaveRequestDay $day) => $day->decided_at !== null))->toBeTrue();
    });

    it('menolak keputusan yang tidak mencakup seluruh tanggal pengajuan', function () {
        $hr = User::factory()->create(['role' => UserRole::HRD]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $leave = LeaveRequest::factory()->forUser($employee)->create([
            'type' => LeaveType::IZIN,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-02',
        ]);
        $service = app(LeaveRequestDayService::class);
        $service->syncDateRange($leave);

        expect(fn () => $service->saveDecisions($leave, [
            '2026-09-01' => 'NONE',
        ], $hr->id))->toThrow(\Illuminate\Validation\ValidationException::class);
    });
});
