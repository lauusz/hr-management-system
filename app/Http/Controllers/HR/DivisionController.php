<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisionController extends Controller
{
    public function index(Request $request)
    {
        $query = Division::query()
            ->with('supervisor')
            ->orderBy('name');

        if ($search = $request->get('q')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->paginate(10)->withQueryString();

        return view('hr.divisions.index', compact('items', 'search'));
    }

    public function create()
    {
        $supervisors = User::query()
            ->where('role', UserRole::SUPERVISOR->value)
            ->orderBy('name')
            ->get();

        return view('hr.divisions.create', compact('supervisors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255', 'unique:divisions,name'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
        ]);

        Division::create($data);

        return redirect()
            ->route('hr.divisions.index')
            ->with('success', 'Divisi baru berhasil ditambahkan.');
    }

    public function edit(Division $division)
    {
        $supervisors = User::query()
            ->where('role', UserRole::SUPERVISOR->value)
            ->orderBy('name')
            ->get();

        return view('hr.divisions.edit', [
            'item'        => $division,
            'supervisors' => $supervisors,
        ]);
    }

    public function update(Request $request, Division $division)
    {
        $data = $request->validate([
            'name'          => [
                'required',
                'string',
                'max:255',
                Rule::unique('divisions', 'name')->ignore($division->id),
            ],
            'supervisor_id' => ['nullable', 'exists:users,id'],
        ]);

        $division->update($data);

        return redirect()
            ->route('hr.divisions.index')
            ->with('success', 'Data divisi berhasil diperbarui.');
    }

    public function destroy(Division $division)
    {
        if ($division->users()->exists()) {
            return redirect()
                ->route('hr.divisions.index')
                ->with('error', 'Divisi masih memiliki karyawan dan tidak dapat dihapus.');
        }

        $division->delete();

        return redirect()
            ->route('hr.divisions.index')
            ->with('success', 'Divisi berhasil dihapus.');
    }
}
