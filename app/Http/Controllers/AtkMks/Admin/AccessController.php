<?php

namespace App\Http\Controllers\AtkMks\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AtkMksAccessPt;
use App\Models\Pt;
use App\Models\User;
use App\Models\UserAccessRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    public function index(Request $request)
    {
        $pts = Pt::withCount([
            'profiles as active_users_count' => fn ($query) => $query
                ->whereHas('user', fn ($userQuery) => $userQuery->active()),
        ])->orderBy('name')->get();
        $selectedPtIds = AtkMksAccessPt::pluck('pt_id')->all();
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

        return view('atk-mks.admin.access.index', compact('pts', 'selectedPtIds', 'users'));
    }

    public function syncPts(Request $request)
    {
        $validated = $request->validate([
            'pt_ids' => ['sometimes', 'array'],
            'pt_ids.*' => ['integer', 'distinct', 'exists:pts,id'],
        ]);
        $ptIds = array_map('intval', $validated['pt_ids'] ?? []);

        DB::transaction(function () use ($ptIds, $request): void {
            if ($ptIds === []) {
                AtkMksAccessPt::query()->delete();
            } else {
                AtkMksAccessPt::whereNotIn('pt_id', $ptIds)->delete();
            }

            foreach ($ptIds as $ptId) {
                AtkMksAccessPt::firstOrCreate(
                    ['pt_id' => $ptId],
                    ['created_by' => $request->user()->id],
                );
            }
        });

        return $this->back('Akses pengguna ATK MKS berhasil disimpan.');
    }

    public function grantAdmin(User $user)
    {
        $this->grant($user, UserRole::ADMIN_ATK_MKS);

        return $this->back('Akses Admin ATK MKS berhasil diberikan.');
    }

    public function revokeAdmin(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return redirect()->route('v2.atk-mks.admin.access.index')->with('warning', 'Akses admin diri sendiri tidak bisa dicabut.');
        }

        $this->revoke($user, UserRole::ADMIN_ATK_MKS);

        return $this->back('Akses Admin ATK MKS berhasil dicabut.');
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
        return redirect()->route('v2.atk-mks.admin.access.index')->with('success', $message);
    }
}

