<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = AtkCategory::withCount('items')
            ->orderBy('name')
            ->paginate(20);

        return view('atk.admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:atk_categories,name'],
        ]);

        AtkCategory::create($validated + ['is_active' => true]);

        return redirect()->route('v2.atk.admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, AtkCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('atk_categories', 'name')->ignore($category->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('v2.atk.admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }
}
