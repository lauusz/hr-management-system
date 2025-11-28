<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('division')
            ->orderBy('division_id')
            ->orderBy('name')
            ->paginate(100);

        return view('hr.positions.index', [
            'positions' => $positions,
        ]);
    }

    public function create()
    {
        $divisions = Division::orderBy('name')->get();

        return view('hr.positions.create', [
            'divisions' => $divisions,
        ]);
    }

    public function store(Request $request)
    {
        $divisionId = $request->input('division_id');

        $data = $request->validate([
            'division_id' => ['nullable', 'exists:divisions,id'],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('positions')->where(function ($query) use ($divisionId) {
                    if ($divisionId) {
                        $query->where('division_id', $divisionId);
                    } else {
                        $query->whereNull('division_id');
                    }
                }),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        Position::create($data);

        return redirect()
            ->route('hr.positions.index')
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Position $position)
    {
        $divisions = Division::orderBy('name')->get();

        return view('hr.positions.edit', [
            'item' => $position,
            'divisions' => $divisions,
        ]);
    }

    public function update(Request $request, Position $position)
    {
        $divisionId = $request->input('division_id');

        $data = $request->validate([
            'division_id' => ['nullable', 'exists:divisions,id'],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('positions')
                    ->ignore($position->id)
                    ->where(function ($query) use ($divisionId) {
                        if ($divisionId) {
                            $query->where('division_id', $divisionId);
                        } else {
                            $query->whereNull('division_id');
                        }
                    }),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        }

        $position->update($data);

        return redirect()
            ->route('hr.positions.index')
            ->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()
            ->route('hr.positions.index')
            ->with('success', 'Jabatan berhasil dihapus.');
    }
}
