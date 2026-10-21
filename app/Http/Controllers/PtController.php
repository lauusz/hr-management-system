<?php

namespace App\Http\Controllers;

use App\Models\Pt;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PtController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));

        $totalCount = Pt::count();
        $activeCount = Pt::has('profiles')->count();
        $emptyCount = $totalCount - $activeCount;

        $query = Pt::withCount('profiles')->orderBy('name');

        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }

        if ($status === 'with_employees') {
            $query->has('profiles');
        } elseif ($status === 'without_employees') {
            $query->doesntHave('profiles');
        }

        $items = $query->paginate(20)->withQueryString();

        return view('hr.pts.index', [
            'items' => $items,
            'q' => $q,
            'status' => $status,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'emptyCount' => $emptyCount,
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
        $pt->loadCount('profiles');

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
