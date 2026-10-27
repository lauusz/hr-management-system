<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAccessRole;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function index(Request $request)
    {
        $adminRole = UserRole::ADMIN_ATK->value;
        $roleFilter = $request->string('role')->toString();

        $isAdminCondition = function ($query) use ($adminRole): void {
            $query->where('role', $adminRole)
                ->orWhereHas('accessRoles', fn ($sub) => $sub->where('role', $adminRole));
        };

        $users = User::with(['accessRoles', 'pt'])
            ->when($roleFilter === 'admin', $isAdminCondition)
            ->when($roleFilter === 'user', function ($query) use ($adminRole): void {
                $query->where(function ($sub) use ($adminRole): void {
                    $sub->whereNull('role')->orWhere('role', '!=', $adminRole);
                })->whereDoesntHave('accessRoles', fn ($sub) => $sub->where('role', $adminRole));
            })
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('q')->toString().'%';
                $query->where(function ($query) use ($keyword): void {
                    $query->where('name', 'like', $keyword)
                        ->orWhere('username', 'like', $keyword)
                        ->orWhere('email', 'like', $keyword)
                        ->orWhereHas('pt', function ($query) use ($keyword): void {
                            $query->where('name', 'like', $keyword);
                        });
                });
            })
            ->orderByRaw(
                'CASE WHEN role = ? OR EXISTS (SELECT 1 FROM user_access_roles WHERE user_access_roles.user_id = users.id AND user_access_roles.role = ?) THEN 0 ELSE 1 END',
                [$adminRole, $adminRole]
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('atk.admin.access.index', compact('users'));
    }

    public function grant(User $user)
    {
        UserAccessRole::firstOrCreate([
            'user_id' => $user->id,
            'role' => UserRole::ADMIN_ATK->value,
        ]);

        return redirect()->route('v2.atk.admin.access.index')->with('success', 'Akses Admin ATK berhasil diberikan.');
    }

    public function revoke(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return redirect()->route('v2.atk.admin.access.index')->with('warning', 'Akses diri sendiri tidak bisa dicabut dari panel ini.');
        }

        $user->accessRoles()
            ->where('role', UserRole::ADMIN_ATK->value)
            ->delete();

        return redirect()->route('v2.atk.admin.access.index')->with('success', 'Akses Admin ATK berhasil dicabut.');
    }
}
