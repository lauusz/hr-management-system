<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class HREmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with('shift')
            ->orderBy('name');

        if ($search = $request->get('q')) {
            $query->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(10)->withQueryString();

        return view('hr.employees.index', compact('items', 'search'));
    }

    public function create()
    {
        $divisions = Division::orderBy('name')->get();
        $roles = UserRole::cases();

        return view('hr.employees.create', compact('divisions', 'roles'));
    }

    public function store(Request $request)
    {
        $roleValues = array_map(fn (UserRole $r) => $r->value, UserRole::cases());

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'username'    => ['required', 'string', 'max:255', 'unique:users,username'],
            'phone'       => ['required', 'string', 'max:255'],
            'role'        => ['required', Rule::in($roleValues)],
            'division_id' => ['nullable', 'exists:divisions,id'],
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->phone = $data['phone'];
        $user->role = $data['role'];
        $user->division_id = $data['division_id'] ?? null;
        $user->status = 'ACTIVE';
        $user->password = Hash::make('123456');
        $user->save();

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Karyawan baru berhasil ditambahkan.');
    }

    public function edit(User $employee)
    {
        $divisions = Division::orderBy('name')->get();
        $roles = UserRole::cases();

        return view('hr.employees.edit', [
            'item'      => $employee,
            'divisions' => $divisions,
            'roles'     => $roles,
        ]);
    }

    public function update(Request $request, User $employee)
    {
        $roleValues = array_map(fn (UserRole $r) => $r->value, UserRole::cases());

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'username'    => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($employee->id)],
            'phone'       => ['required', 'string', 'max:255'],
            'role'        => ['required', Rule::in($roleValues)],
            'division_id' => ['nullable', 'exists:divisions,id'],
        ]);

        $employee->update($data);

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $employee)
    {
        $employee->update([
            'status' => 'INACTIVE',
        ]);

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Karyawan berhasil dinonaktifkan.');
    }
}
