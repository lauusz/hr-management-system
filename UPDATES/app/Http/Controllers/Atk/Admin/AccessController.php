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
        $users = User::with(['accessRoles', 'pt'])
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
