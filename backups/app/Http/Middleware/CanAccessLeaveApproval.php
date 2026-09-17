<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\LeaveApprovalAssignmentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanAccessLeaveApproval
{
    public function __construct(
        private readonly LeaveApprovalAssignmentService $approvalAssignmentService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        if ($this->canAccessLeaveApproval($user)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki pengajuan cuti/izin yang perlu dipantau atau di-approve.');
    }

    private function canAccessLeaveApproval(User $user): bool
    {
        if ($user->isHR()) {
            return true;
        }

        if ($user->hasLeaveApprovalAssignments()) {
            return true;
        }

        return $this->approvalAssignmentService
            ->queryVisibleTo($user)
            ->where('user_id', '!=', $user->id)
            ->exists();
    }
}
