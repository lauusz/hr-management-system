<?php

namespace App\Http\Controllers\Ops\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAccessRole;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function index(Request $request)
    {
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

        return view('ops.admin.access.index', compact('users'));
    }

    public function grantUser(User $user)
    {
        $this->grant($user, UserRole::OPS);

        return $this->back('Akses OPS berhasil diberikan.');
    }

    public function grantAdmin(User $user)
    {
        $this->grant($user, UserRole::ADMIN_OPS);

        return $this->back('Akses Admin OPS berhasil diberikan.');
    }

    public function revokeUser(User $user)
    {
        $this->revoke($user, UserRole::OPS);

        return $this->back('Akses OPS berhasil dicabut.');
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
