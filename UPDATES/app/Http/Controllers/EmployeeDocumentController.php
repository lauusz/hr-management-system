<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\Image\ImageCompressor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EmployeeDocumentController extends Controller
{
    public function __construct(protected ImageCompressor $imageCompressor) {}

    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', EmployeeDocument::types())],
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,heic,heif,gif,bmp,tif,tiff,avif,doc,docx', 'max:5120'],
            'effective_date' => ['nullable', 'date'],
            'expired_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $path = $this->storeDocumentFile($request->file('file'));

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
        if (! Auth::user()?->isHR()) {
            return redirect()->back()->with('error', 'Anda tidak berhak menghapus dokumen karyawan.');
        }

        if ($employeeDocument->file_path && Storage::disk('public')->exists($employeeDocument->file_path)) {
            Storage::disk('public')->delete($employeeDocument->file_path);
        }

        $employeeDocument->delete();

        return back()->with('success', 'Dokumen karyawan berhasil dihapus.');
    }

    private function storeDocumentFile(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'gif', 'bmp', 'tif', 'tiff', 'avif'], true)) {
            return $file->store('employee_documents', 'public');
        }

        try {
            return $this->imageCompressor->compressAndStore($file, 'photo', 'employee_documents', 'employee_doc_');
        } catch (ValidationException $exception) {
            if (! in_array($extension, ['heic', 'heif'], true)) {
                throw $exception;
            }

            return $file->store('employee_documents', 'public');
        }
    }
}
