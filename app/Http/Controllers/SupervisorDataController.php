<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;

class SupervisorDataController extends Controller
{
    public function index()
    {
        $supervisors = User::with(['division', 'position'])
            ->whereIn('role', [UserRole::SUPERVISOR, UserRole::MANAGER])
            ->orderBy('name')
            ->paginate(20);

        // View tetap plural sesuai folder
        return view('hr.supervisors.index', compact('supervisors'));
    }

    public function create()
    {
        $candidates = User::with(['division', 'position'])
            ->where('role', UserRole::EMPLOYEE)
            ->orderBy('name')
            ->get();

        return view('hr.supervisors.create', compact('candidates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|in:SUPERVISOR,MANAGER'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update(['role' => $request->role]);

        // FIX: Route jadi plural
        return redirect()->route('hr.supervisors.index')
            ->with('success', "{$user->name} berhasil diangkat menjadi {$request->role}.");
    }

    public function edit(User $user)
    {
        if (!in_array($user->role, [UserRole::SUPERVISOR, UserRole::MANAGER])) {
            return back()->with('error', 'User ini bukan Supervisor/Manager.');
        }
        return view('hr.supervisors.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:SUPERVISOR,MANAGER']);
        $user->update(['role' => $request->role]);

        // FIX: Route jadi plural
        return redirect()->route('hr.supervisors.index')
            ->with('success', "Jabatan {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menurunkan jabatan diri sendiri.');
        }
        $user->update(['role' => UserRole::EMPLOYEE]);

        return back()->with('success', "{$user->name} telah dikembalikan menjadi Staff biasa.");
    }
}