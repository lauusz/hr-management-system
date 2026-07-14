<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use App\Services\Image\ImageCompressor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EmployeeLoanRequestController extends Controller
{
    public function __construct(protected ImageCompressor $imageCompressor) {}

    public function index()
    {
        $loans = LoanRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        $hasActiveLoan = $loans->whereIn('status', ['PENDING_HRD', 'APPROVED'])->isNotEmpty();

        return view('loan_requests.index', compact('loans', 'hasActiveLoan'));
    }

    public function create()
    {
        $user = Auth::user()->load([
            'profile.pt',
            'division',
            'position',
        ]);

        $snapshot = [
            'name' => $user->name,
            'nik' => $user->profile?->nik,
            'position' => $user->position?->name,
            'division' => $user->division?->name,
            'pt' => $user->profile?->pt?->name,
        ];

        return view('loan_requests.create', compact('user', 'snapshot'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'purpose' => ['nullable', 'string'],
            'monthly_installment' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string'],
            'disbursement_date' => ['nullable', 'date'],
            'payment_method' => ['required', 'in:TUNAI,CICILAN,POTONG_GAJI'],
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,gif,bmp,tif,tiff,avif,pdf,doc,docx,xls,xlsx,txt', 'max:8192'],
        ]);

        $user = Auth::user()->load([
            'profile.pt',
            'division',
            'position',
        ]);

        // Calculate tenor based on amount and monthly installment
        $tenor = (int) ceil($validated['amount'] / $validated['monthly_installment']);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $this->storeLoanDocument($request->file('document'));
        }

        LoanRequest::create([
            'user_id' => $user->id,
            'snapshot_name' => $user->name,
            'snapshot_nik' => $user->profile?->nik,
            'snapshot_position' => $user->position?->name,
            'snapshot_division' => $user->division?->name,
            'snapshot_company' => $user->profile?->pt?->name,
            'submitted_at' => now()->toDateString(),
            'document_path' => $documentPath,
            'amount' => $validated['amount'],
            'purpose' => $validated['purpose'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'monthly_installment' => $validated['monthly_installment'],
            'repayment_term' => $tenor,
            'disbursement_date' => $validated['disbursement_date'] ?? null,
            'payment_method' => $validated['payment_method'],
            'status' => 'PENDING_HRD',
        ]);

        return redirect()
            ->route('employee.loan_requests.index')
            ->with('success', 'Pengajuan hutang berhasil diajukan.');
    }

    public function show($id)
    {
        $loan = LoanRequest::with('repayments')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        return view('loan_requests.show', compact('loan'));
    }

    public function destroy($id)
    {
        $loan = LoanRequest::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        if ($loan->status !== 'PENDING_HRD') {
            return redirect()->back()->with('error', 'Pengajuan yang sudah diproses tidak bisa dibatalkan.');
        }

        $loan->update(['status' => 'CANCELED']);

        return redirect()->route('employee.loan_requests.index')->with('success', 'Pengajuan hutang berhasil dibatalkan.');
    }

    private function storeLoanDocument(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'gif', 'bmp', 'tif', 'tiff', 'avif'], true)) {
            return $file->store('loan_documents', 'public');
        }

        try {
            return $this->imageCompressor->compressAndStore($file, 'photo', 'loan_documents', 'loan_');
        } catch (ValidationException $exception) {
            if (! in_array($extension, ['heic', 'heif'], true)) {
                throw $exception;
            }

            return $file->store('loan_documents', 'public');
        }
    }
}
