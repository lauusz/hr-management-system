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
        return $user->id === $lr->user_id || $user->isHR() || $user->isSupervisor();
    }

    public function create(User $user): bool
    {
        return $user->isEmployee() || $user->isSupervisor() || $user->isHR();
    }

    public function update(User $user, LeaveRequest $lr): bool
    {
        return $user->id === $lr->user_id
            && in_array($lr->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true);
    }

    public function approve(User $user, LeaveRequest $lr): bool
    {
        return ($user->isHR() || $user->isSupervisor())
            && in_array($lr->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true);
    }

    public function delete(User $user, LeaveRequest $lr): bool
    {
        return $user->id === $lr->user_id
            && in_array($lr->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true);
    }
}
