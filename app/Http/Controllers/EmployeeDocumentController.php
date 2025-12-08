<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentController extends Controller
{
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', EmployeeDocument::types())],
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'effective_date' => ['nullable', 'date'],
            'expired_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $path = $request->file('file')->store('employee_documents', 'public');

        EmployeeDocument::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'title' => $validated['title'] ?? null,
            'file_path' => $path,
            'effective_date' => $validated['effective_date'] ?? null,
            'expired_date' => $validated['expired_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Dokumen karyawan berhasil ditambahkan.');
    }

    public function destroy(EmployeeDocument $employeeDocument)
    {
        if ($employeeDocument->file_path && Storage::disk('public')->exists($employeeDocument->file_path)) {
            Storage::disk('public')->delete($employeeDocument->file_path);
        }

        $employeeDocument->delete();

        return back()->with('success', 'Dokumen karyawan berhasil dihapus.');
    }

}
