<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LeaveApprovalAssignmentService
{
    public const CAPACITY_APPROVER = 'APPROVER';

    public const CAPACITY_SUPERVISOR = 'SUPERVISOR';

    public const CAPACITY_MANAGER = 'MANAGER';

    public const CAPACITY_HR = 'HR';

    public const CAPACITY_FINAL_FOR_HRD = 'FINAL_FOR_HRD';

    public function initialApprovalCapacity(User $applicant): string
    {
        if ($this->validAssignedApprover($applicant) !== null) {
            return self::CAPACITY_APPROVER;
        }

        if ($this->hasAssignedApproverReference($applicant)) {
            return $this->isHrdApplicant($applicant)
                ? self::CAPACITY_FINAL_FOR_HRD
                : self::CAPACITY_HR;
        }

        if ($this->validDirectSupervisor($applicant) !== null) {
            return self::CAPACITY_SUPERVISOR;
        }

        if ($this->validManager($applicant) !== null) {
            return self::CAPACITY_MANAGER;
        }

        return $this->isHrdApplicant($applicant)
            ? self::CAPACITY_FINAL_FOR_HRD
            : self::CAPACITY_HR;
    }

    public function initialApproverFor(User $applicant): ?User
    {
        $assignedApprover = $this->validAssignedApprover($applicant);

        if ($assignedApprover !== null) {
            return $assignedApprover;
        }

        if ($this->hasAssignedApproverReference($applicant)) {
            return null;
        }

        return $this->validDirectSupervisor($applicant)
            ?? $this->validManager($applicant);
    }

    public function canView(User $viewer, LeaveRequest $leave): bool
    {
        $applicant = $this->applicantFor($leave);

        if ($applicant === null) {
            return false;
        }

        if ((int) $applicant->id === (int) $viewer->id || $viewer->isHR()) {
            return true;
        }

        return (int) $applicant->approver_id === (int) $viewer->id
            || (int) $applicant->direct_supervisor_id === (int) $viewer->id
            || (int) $applicant->manager_id === (int) $viewer->id;
    }

    public function canProcessInitialStage(User $actor, LeaveRequest $leave): bool
    {
        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return false;
        }

        $applicant = $this->applicantFor($leave);

        if ($applicant === null || (int) $applicant->id === (int) $actor->id) {
            return false;
        }

        $initialApprover = $this->initialApproverFor($applicant);

        return $initialApprover !== null
            && (int) $initialApprover->id === (int) $actor->id;
    }

    public function queryVisibleTo(User $viewer): Builder
    {
        $query = LeaveRequest::query();

        if ($viewer->isHR()) {
            return $query;
        }

        return $query->where(function (Builder $leaveQuery) use ($viewer) {
            $leaveQuery->where('user_id', $viewer->id)
                ->orWhereHas('user', function (Builder $userQuery) use ($viewer) {
                    $userQuery->where('approver_id', $viewer->id)
                        ->orWhere('direct_supervisor_id', $viewer->id)
                        ->orWhere('manager_id', $viewer->id);
                });
        });
    }

    public function queryPendingFor(User $viewer): Builder
    {
        $query = LeaveRequest::query();

        if ($viewer->isHR()) {
            return $query->where('status', LeaveRequest::PENDING_HR);
        }

        return $query->where('status', LeaveRequest::PENDING_SUPERVISOR)
            ->whereHas('user', function (Builder $userQuery) use ($viewer) {
                $userQuery->where(function (Builder $candidateQuery) use ($viewer) {
                    if ($viewer->isActive() && $viewer->isEmployee()) {
                        $candidateQuery->where(function (Builder $assignedApproverQuery) use ($viewer) {
                            $assignedApproverQuery->where('approver_id', $viewer->id)
                                ->whereColumn('approver_id', '!=', 'users.id');
                        });
                    } else {
                        $candidateQuery->whereRaw('1 = 0');
                    }

                    $candidateQuery
                        ->orWhere(function (Builder $supervisorQuery) use ($viewer) {
                            $supervisorQuery->whereNull('approver_id')
                                ->where('direct_supervisor_id', $viewer->id)
                                ->whereColumn('direct_supervisor_id', '!=', 'users.id');
                        })
                        ->orWhere(function (Builder $managerQuery) use ($viewer) {
                            $managerQuery->whereNull('approver_id')
                                ->whereNull('direct_supervisor_id')
                                ->where('manager_id', $viewer->id)
                                ->whereColumn('manager_id', '!=', 'users.id');
                        });
                });
            });
    }

    public function reconcilePendingInitialRequests(User $applicant): int
    {
        return DB::transaction(function () use ($applicant) {
            $freshApplicant = User::query()->find($applicant->id);

            if ($freshApplicant === null || $this->initialApproverFor($freshApplicant) !== null) {
                return 0;
            }

            $systemNote = '[System] Pengajuan dialihkan otomatis ke HR karena pihak tahap awal tidak tersedia pada '
                .now()->format('d M Y H:i');

            $updated = 0;

            LeaveRequest::query()
                ->where('user_id', $freshApplicant->id)
                ->where('status', LeaveRequest::PENDING_SUPERVISOR)
                ->lockForUpdate()
                ->get()
                ->each(function (LeaveRequest $leave) use (&$updated, $systemNote) {
                    $currentNotes = trim((string) $leave->notes);

                    $leave->update([
                        'status' => LeaveRequest::PENDING_HR,
                        'notes' => $currentNotes !== ''
                            ? $currentNotes."\n".$systemNote
                            : $systemNote,
                    ]);

                    $updated++;
                });

            return $updated;
        });
    }

    public function recordAction(
        LeaveRequest $leave,
        User $actor,
        string $capacity,
        string $action,
        string $fromStatus,
        string $toStatus,
        ?string $notes = null,
    ): void {
        $leave->approvalActions()->create([
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'actor_role' => $this->roleValue($actor),
            'capacity' => $capacity,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'acted_at' => now(),
        ]);
    }

    private function applicantFor(LeaveRequest $leave): ?User
    {
        return $leave->relationLoaded('user')
            ? $leave->user
            : $leave->user()->first();
    }

    private function validAssignedApprover(User $applicant): ?User
    {
        if (empty($applicant->approver_id) || (int) $applicant->approver_id === (int) $applicant->id) {
            return null;
        }

        $approver = $applicant->relationLoaded('assignedApprover')
            ? $applicant->assignedApprover
            : User::find($applicant->approver_id);

        if ($approver === null || ! $approver->isActive() || ! $approver->isEmployee()) {
            return null;
        }

        return $approver;
    }

    private function hasAssignedApproverReference(User $applicant): bool
    {
        return ! empty($applicant->approver_id)
            && (int) $applicant->approver_id !== (int) $applicant->id;
    }

    private function validDirectSupervisor(User $applicant): ?User
    {
        if (empty($applicant->direct_supervisor_id) || (int) $applicant->direct_supervisor_id === (int) $applicant->id) {
            return null;
        }

        return User::find($applicant->direct_supervisor_id);
    }

    private function validManager(User $applicant): ?User
    {
        if (empty($applicant->manager_id) || (int) $applicant->manager_id === (int) $applicant->id) {
            return null;
        }

        return User::find($applicant->manager_id);
    }

    private function isHrdApplicant(User $applicant): bool
    {
        return in_array($this->roleValue($applicant), [
            UserRole::HRD->value,
            'HR MANAGER',
        ], true);
    }

    private function roleValue(User $user): string
    {
        return $user->role instanceof UserRole
            ? $user->role->value
            : (string) $user->role;
    }
}
