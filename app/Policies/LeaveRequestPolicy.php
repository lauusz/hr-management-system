<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeaveRequest $lr): bool
    {
        if ($user->id === $lr->user_id) {
            return true;
        }

        if ($user->isHR()) {
            return true;
        }

        return $this->isDirectApproverOf($user, $lr->user);
    }

    public function create(User $user): bool
    {
        return $user->isEmployee() || $user->isSupervisor() || $user->isHR() || $user->isManager();
    }

    /**
     * Update hanya diizinkan untuk pemilik atau HR.
     * MANAGER/Supervisor tidak boleh mengubah pengajuan user lain melalui
     * endpoint umum leave-requests (hak revisi ada di ApprovalController
     * untuk pengajuan bawahan yang masih pending).
     */
    public function update(User $user, LeaveRequest $lr): bool
    {
        return ($user->id === $lr->user_id || $user->isHR())
            && in_array($lr->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true);
    }

    /**
     * Pembatalan umum hanya untuk pemilik atau HR.
     * Pembatasan status spesifik (pending/approved) ditangani oleh controller
     * agar dapat memberikan flash message yang sesuai.
     */
    public function delete(User $user, LeaveRequest $lr): bool
    {
        if ($user->isHR()) {
            return true;
        }

        return $user->id === $lr->user_id;
    }

    /**
     * Approval hanya untuk HR atau atasan langsung dari pemohon.
     */
    public function approve(User $user, LeaveRequest $lr): bool
    {
        if ($user->isHR()) {
            return in_array($lr->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true);
        }

        return $this->isDirectApproverOf($user, $lr->user)
            && $lr->status === LeaveRequest::PENDING_SUPERVISOR;
    }

    /**
     * Cek apakah user adalah atasan langsung (supervisor atau manager) dari applicant.
     */
    private function isDirectApproverOf(User $approver, User $applicant): bool
    {
        if ((int) $applicant->direct_supervisor_id === (int) $approver->id) {
            return true;
        }

        return (int) $applicant->manager_id === (int) $approver->id;
    }
}
