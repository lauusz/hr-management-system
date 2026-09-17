<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HRApproverMappingController extends Controller
{
    public function index()
    {
        $baseRelations = ['division', 'position', 'profile.pt'];

        $managers = User::query()
            ->with($baseRelations)
            ->withCount([
                'leaveRequests as pending_initial_count' => fn (Builder $query) => $query->where('status', LeaveRequest::PENDING_SUPERVISOR),
            ])
            ->where('role', UserRole::MANAGER->value)
            ->whereHas('managedEmployees')
            ->orderBy('name')
            ->get();

        $supervisors = User::query()
            ->with($baseRelations)
            ->withCount([
                'leaveRequests as pending_initial_count' => fn (Builder $query) => $query->where('status', LeaveRequest::PENDING_SUPERVISOR),
            ])
            ->where('role', UserRole::SUPERVISOR->value)
            ->whereHas('subordinates')
            ->orderBy('name')
            ->get();

        $approvers = User::query()
            ->with($baseRelations)
            ->withCount([
                'leaveRequests as pending_initial_count' => fn (Builder $query) => $query->where('status', LeaveRequest::PENDING_SUPERVISOR),
            ])
            ->where('role', UserRole::EMPLOYEE->value)
            ->where(function (Builder $query) {
                $query->where('can_approve_leave', true)
                    ->orWhereHas('approvalAssignees');
            })
            ->orderBy('name')
            ->get();

        $managerEmployees = $this->employeesByRelation('manager_id');
        $supervisorEmployees = $this->employeesByRelation('direct_supervisor_id');
        $approverEmployees = $this->employeesByRelation('approver_id');

        return view('hr.approvers.index', compact(
            'managers',
            'supervisors',
            'approvers',
            'managerEmployees',
            'supervisorEmployees',
            'approverEmployees',
        ));
    }

    public function create()
    {
        $candidates = $this->approverCandidates();

        return view('hr.approvers.create', compact('candidates'));
    }

    public function searchApprovers(Request $request)
    {
        $query = trim((string) $request->query('q'));

        if ($query === '') {
            return response()->json([]);
        }

        $users = $this->approverCandidates($query)
            ->take(10)
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'meta' => collect([
                        $user->position->name ?? 'Tanpa jabatan',
                        $user->division->name ?? 'Tanpa divisi',
                        $user->profile?->pt?->name,
                    ])->filter()->implode(' · '),
                ];
            })
            ->values();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', UserRole::EMPLOYEE->value)
                    ->where('status', User::STATUS_ACTIVE)),
            ],
        ]);

        $approver = User::query()->findOrFail($validated['user_id']);
        $approver->forceFill(['can_approve_leave' => true])->save();

        return redirect()
            ->route('hr.approvers.show', $approver)
            ->with('success', 'Akses Approver berhasil ditambahkan.');
    }

    public function show(User $approver)
    {
        $approver->load(['division', 'position', 'profile.pt']);

        [$assignmentColumn, $roleLabel, $roleClass] = $this->assignmentMappingFor($approver);

        $employees = $this->employeesByRelation($assignmentColumn)->get($approver->id, collect());

        return view('hr.approvers.show', compact(
            'approver',
            'employees',
            'roleLabel',
            'roleClass',
        ));
    }

    public function createUser(User $approver)
    {
        $approver->load(['division', 'position', 'profile.pt']);

        [$assignmentColumn, $roleLabel, $roleClass] = $this->assignmentMappingFor($approver);

        $candidates = $this->assignableUsersFor($assignmentColumn, $approver);

        return view('hr.approvers.add-user', compact(
            'approver',
            'assignmentColumn',
            'roleLabel',
            'roleClass',
            'candidates',
        ));
    }

    public function searchUsers(Request $request, User $approver)
    {
        $query = trim((string) $request->query('q'));

        if ($query === '') {
            return response()->json([]);
        }

        [$assignmentColumn] = $this->assignmentMappingFor($approver);

        $users = $this->assignableUsersFor($assignmentColumn, $approver, $query)
            ->take(10)
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'meta' => collect([
                        $user->position->name ?? 'Tanpa jabatan',
                        $user->division->name ?? 'Tanpa divisi',
                        $user->profile?->pt?->name,
                    ])->filter()->implode(' · '),
                ];
            })
            ->values();

        return response()->json($users);
    }

    public function storeUser(Request $request, User $approver)
    {
        [$assignmentColumn] = $this->assignmentMappingFor($approver);

        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('status', User::STATUS_ACTIVE)
                    ->where('id', '!=', $approver->id)),
            ],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $user->forceFill([$assignmentColumn => $approver->id])->save();

        return redirect()
            ->route('hr.approvers.show', $approver)
            ->with('success', 'User berhasil ditambahkan ke mapping approval.');
    }

    public function destroyUser(User $approver, User $user)
    {
        [$assignmentColumn] = $this->assignmentMappingFor($approver);

        if ((int) $user->{$assignmentColumn} === (int) $approver->id) {
            $user->forceFill([$assignmentColumn => null])->save();
        }

        return redirect()
            ->route('hr.approvers.show', $approver)
            ->with('success', 'User berhasil dihapus dari mapping approval.');
    }

    public function revoke(User $approver)
    {
        if ($approver->role === UserRole::EMPLOYEE) {
            $approver->forceFill(['can_approve_leave' => false])->save();
        }

        return redirect()
            ->route('hr.approvers.index')
            ->with('success', 'Hak Approver berhasil dicabut.');
    }

    private function employeesByRelation(string $column)
    {
        return User::query()
            ->with(['division', 'position', 'profile.pt'])
            ->withCount([
                'leaveRequests as pending_initial_count' => fn (Builder $query) => $query->where('status', LeaveRequest::PENDING_SUPERVISOR),
                'leaveRequests as pending_hr_count' => fn (Builder $query) => $query->where('status', LeaveRequest::PENDING_HR),
            ])
            ->whereNotNull($column)
            ->orderBy('name')
            ->get()
            ->groupBy($column);
    }

    private function approverCandidates(?string $search = null)
    {
        return User::query()
            ->with(['division', 'position', 'profile.pt'])
            ->where('role', UserRole::EMPLOYEE->value)
            ->where('status', User::STATUS_ACTIVE)
            ->where('can_approve_leave', false)
            ->whereDoesntHave('approvalAssignees')
            ->when($search, fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->get();
    }

    private function assignableUsersFor(string $assignmentColumn, User $approver, ?string $search = null)
    {
        return User::query()
            ->with(['division', 'position', 'profile.pt'])
            ->where('status', User::STATUS_ACTIVE)
            ->whereKeyNot($approver->id)
            ->where(function (Builder $query) use ($assignmentColumn, $approver) {
                $query->whereNull($assignmentColumn)
                    ->orWhere($assignmentColumn, '!=', $approver->id);
            })
            ->when($search, fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->get();
    }

    private function assignmentMappingFor(User $approver): array
    {
        return match ($approver->role) {
            UserRole::MANAGER => ['manager_id', 'MANAGER', 'approval-map-badge--manager'],
            UserRole::SUPERVISOR => ['direct_supervisor_id', 'SUPERVISOR', 'approval-map-badge--supervisor'],
            default => ['approver_id', 'APPROVER', 'approval-map-badge--approver'],
        };
    }
}
