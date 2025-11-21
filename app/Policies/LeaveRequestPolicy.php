<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LeaveRequest;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool {
        return true;
    }

    public function view(User $user, LeaveRequest $lr): bool {
        return $user->id === $lr->user_id || $user->isHR() || $user->isSupervisor();
    }

    public function create(User $user): bool {
        return $user->isEmployee() || $user->isSupervisor() || $user->isHR();
    }

    public function update(User $user, LeaveRequest $lr): bool {
        return $lr->status === 'PENDING' && $user->id === $lr->user_id;
    }

    public function approve(User $user, LeaveRequest $lr): bool {
        return ($user->isHR() || $user->isSupervisor()) && $lr->status === 'PENDING';
    }

    public function delete(User $user, LeaveRequest $lr): bool {
        return $user->id === $lr->user_id && $lr->status === 'PENDING';
    }
}
