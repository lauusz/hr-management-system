<?php

namespace App\Http\Controllers\Ops\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\OpsAccessDivision;
use App\Models\User;
use App\Models\UserAccessRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    public function index(Request $request)
    {
        $divisions = Division::withCount([
            'users as active_users_count' => fn ($query) => $query->active(),
        ])->orderBy('name')->get();
        $selectedDivisionIds = OpsAccessDivision::pluck('division_id')->all();
        $users = User::with(['accessRoles', 'pt'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('q')->toString().'%';
                $query->where(function ($query) use ($keyword): void {
                    $query->where('name', 'like', $keyword)
                        ->orWhere('username', 'like', $keyword)
                        ->orWhere('email', 'like', $keyword)
                        ->orWhereHas('pt', fn ($query) => $query->where('name', 'like', $keyword));
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('ops.admin.access.index', compact('divisions', 'selectedDivisionIds', 'users'));
    }

    public function syncDivisions(Request $request)
    {
        $validated = $request->validate([
            'division_ids' => ['sometimes', 'array'],
            'division_ids.*' => ['integer', 'distinct', 'exists:divisions,id'],
        ]);
        $divisionIds = array_map('intval', $validated['division_ids'] ?? []);

        DB::transaction(function () use ($divisionIds, $request): void {
            if ($divisionIds === []) {
                OpsAccessDivision::query()->delete();
            } else {
                OpsAccessDivision::whereNotIn('division_id', $divisionIds)->delete();
            }

            foreach ($divisionIds as $divisionId) {
                OpsAccessDivision::firstOrCreate(
                    ['division_id' => $divisionId],
                    ['created_by' => $request->user()->id],
                );
            }
        });

        return $this->back('Akses pengguna OPS berhasil disimpan.');
    }

    public function grantAdmin(User $user)
    {
        $this->grant($user, UserRole::ADMIN_OPS);

        return $this->back('Akses Admin OPS berhasil diberikan.');
    }

    public function revokeAdmin(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return redirect()->route('v2.ops.admin.access.index')->with('warning', 'Akses admin diri sendiri tidak bisa dicabut.');
        }

        $this->revoke($user, UserRole::ADMIN_OPS);

        return $this->back('Akses Admin OPS berhasil dicabut.');
    }

    private function grant(User $user, UserRole $role): void
    {
        UserAccessRole::firstOrCreate(['user_id' => $user->id, 'role' => $role->value]);
    }

    private function revoke(User $user, UserRole $role): void
    {
        $user->accessRoles()->where('role', $role->value)->delete();
    }

    private function back(string $message)
    {
        return redirect()->route('v2.ops.admin.access.index')->with('success', $message);
    }
}
