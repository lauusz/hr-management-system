<?php

namespace App\Http\Controllers;

use App\Models\Pt;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PtController extends Controller
{
     public function index()
    {
        $items = Pt::orderBy('name')->paginate(50);

        return view('hr.pts.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('hr.pts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:pts,name'],
        ]);

        Pt::create([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('hr.pts.index')
            ->with('success', 'PT baru berhasil ditambahkan.');
    }

    public function edit(Pt $pt)
    {
        return view('hr.pts.edit', [
            'item' => $pt,
        ]);
    }

    public function update(Request $request, Pt $pt)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('pts', 'name')->ignore($pt->id),
            ],
        ]);

        $pt->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('hr.pts.index')
            ->with('success', 'Data PT berhasil diperbarui.');
    }

    public function destroy(Pt $pt)
    {
        $pt->delete();

        return redirect()
            ->route('hr.pts.index')
            ->with('success', 'PT berhasil dihapus.');
    }
}
