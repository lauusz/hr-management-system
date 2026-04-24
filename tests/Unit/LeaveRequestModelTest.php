<?php

use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Enums\UserRole;

describe('LeaveRequest Model', function () {

    // =====================================================================
    // Status Constants
    // =====================================================================
    it('has correct PENDING_SUPERVISOR constant', function () {
        expect(LeaveRequest::PENDING_SUPERVISOR)->toBe('PENDING_SUPERVISOR');
    });

    it('has correct PENDING_HR constant', function () {
        expect(LeaveRequest::PENDING_HR)->toBe('PENDING_HR');
    });

    it('has correct STATUS_APPROVED constant', function () {
        expect(LeaveRequest::STATUS_APPROVED)->toBe('APPROVED');
    });

    it('has correct STATUS_REJECTED constant', function () {
        expect(LeaveRequest::STATUS_REJECTED)->toBe('REJECTED');
    });

    it('STATUS_OPTIONS contains all expected statuses', function () {
        $options = LeaveRequest::STATUS_OPTIONS;

        expect($options)->toHaveKeys([
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
            'CANCEL_REQ',
            'BATAL',
        ]);
    });

    // =====================================================================
    // Type Cast
    // =====================================================================
    it('type is cast to LeaveType enum', function () {
        $leave = LeaveRequest::factory()->create(['type' => LeaveType::CUTI]);

        expect($leave->type)->toBeInstanceOf(LeaveType::class)
            ->and($leave->type)->toBe(LeaveType::CUTI);
    });

    it('type_label returns label for CUTI', function () {
        $leave = LeaveRequest::factory()->create(['type' => LeaveType::CUTI]);

        expect($leave->type_label)->toBe('Cuti');
    });

    it('type_label returns label for SAKIT', function () {
        $leave = LeaveRequest::factory()->create(['type' => LeaveType::SAKIT]);

        expect($leave->type_label)->toBe('Sakit');
    });

    it('type_label returns Tidak ditemukan for invalid type', function () {
        $leave = LeaveRequest::factory()->make(['type' => 'INVALID_TYPE']);

        expect($leave->type_label)->toBe('Tidak diketahui');
    });

    // =====================================================================
    // setTypeAttribute Mutation
    // =====================================================================
    it('setTypeAttribute uppercases string values', function () {
        $leave = LeaveRequest::factory()->make(['type' => 'cuti']);

        // After mutator runs, type should be uppercased when stored
        expect($leave->type)->toBe('CUTI');
    });

    it('setTypeAttribute passes through enum values', function () {
        $leave = LeaveRequest::factory()->make(['type' => LeaveType::CUTI]);

        expect($leave->type)->toBe(LeaveType::CUTI);
    });

    // =====================================================================
    // Date Casts
    // =====================================================================
    it('start_date is cast to date', function () {
        $leave = LeaveRequest::factory()->create([
            'start_date' => '2026-04-15',
        ]);

        expect($leave->start_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
            ->and($leave->start_date->format('Y-m-d'))->toBe('2026-04-15');
    });

    it('end_date is cast to date', function () {
        $leave = LeaveRequest::factory()->create([
            'end_date' => '2026-04-20',
        ]);

        expect($leave->end_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
            ->and($leave->end_date->format('Y-m-d'))->toBe('2026-04-20');
    });

    it('supervisor_ack_at is cast to datetime', function () {
        $leave = LeaveRequest::factory()->create([
            'supervisor_ack_at' => now(),
        ]);

        expect($leave->supervisor_ack_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    // =====================================================================
    // Relationships
    // =====================================================================
    it('belongs to user', function () {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->create(['user_id' => $user->id]);

        expect($leave->user->id)->toBe($user->id);
    });

    it('belongs to approver via approved_by', function () {
        $approver = User::factory()->create(['role' => UserRole::HRD]);
        $leave = LeaveRequest::factory()->create(['approved_by' => $approver->id]);

        expect($leave->approver->id)->toBe($approver->id);
    });

    // =====================================================================
    // Scopes
    // =====================================================================
    it('pendingSupervisor scope filters correctly', function () {
        LeaveRequest::factory()->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);
        LeaveRequest::factory()->create(['status' => LeaveRequest::PENDING_HR]);
        LeaveRequest::factory()->create(['status' => LeaveRequest::STATUS_APPROVED]);

        $pendingSpv = LeaveRequest::pendingSupervisor()->get();

        expect($pendingSpv)->toHaveCount(1)
            ->and($pendingSpv->first()->status)->toBe(LeaveRequest::PENDING_SUPERVISOR);
    });

    it('pendingHr scope filters correctly', function () {
        LeaveRequest::factory()->create(['status' => LeaveRequest::PENDING_SUPERVISOR]);
        LeaveRequest::factory()->create(['status' => LeaveRequest::PENDING_HR]);
        LeaveRequest::factory()->create(['status' => LeaveRequest::STATUS_APPROVED]);

        $pendingHr = LeaveRequest::pendingHr()->get();

        expect($pendingHr)->toHaveCount(1)
            ->and($pendingHr->first()->status)->toBe(LeaveRequest::PENDING_HR);
    });

    // =====================================================================
    // Status Label
    // =====================================================================
    it('getStatusLabelAttribute returns correct label for PENDING_SUPERVISOR', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $leave = LeaveRequest::factory()->make([
            'user_id' => $user->id,
            'status' => LeaveRequest::PENDING_SUPERVISOR,
        ]);

        expect($leave->status_label)->toBe('Menunggu Atasan');
    });

    it('getStatusLabelAttribute returns correct label for PENDING_HR', function () {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $leave = LeaveRequest::factory()->make([
            'user_id' => $user->id,
            'status' => LeaveRequest::PENDING_HR,
        ]);

        expect($leave->status_label)->toBe('Menunggu HRD');
    });

    it('getStatusLabelAttribute returns correct label for BATAL', function () {
        $leave = LeaveRequest::factory()->make(['status' => 'BATAL']);

        expect($leave->status_label)->toBe('Dibatalkan');
    });

    it('getStatusLabelAttribute returns status itself when not in OPTIONS', function () {
        $leave = LeaveRequest::factory()->make(['status' => 'UNKNOWN_STATUS']);

        expect($leave->status_label)->toBe('UNKNOWN_STATUS');
    });
});
