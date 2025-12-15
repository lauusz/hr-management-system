<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeLoanRequestController extends Controller
{
    public function index()
    {
        $loans = LoanRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('loan_requests.index', compact('loans'));
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
            'installment_months' => ['nullable', 'integer', 'min:1', 'max:12'],
            'disbursement_date' => ['nullable', 'date'],
            'payment_method' => ['required', 'in:TUNAI,CICILAN,POTONG_GAJI'],
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $user = Auth::user()->load([
            'profile.pt',
            'division',
            'position',
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('loan_documents', 'public');
        }

        LoanRequest::create([
            'user_id' => $user->id,
            'snapshot_name' => $user->name,
            'snapshot_nik' => $user->profile?->nik,
            'snapshot_position' => $user->position?->name,
            'snapshot_division' => $user->division?->name,
            'snapshot_pt' => $user->profile?->pt?->name,
            'submitted_at' => now()->toDateString(),
            'document_path' => $documentPath,
            'amount' => $validated['amount'],
            'purpose' => $validated['purpose'] ?? null,
            'repayment_term' => $validated['installment_months'] ?? null,
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
}
