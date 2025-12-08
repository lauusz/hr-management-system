<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeLoanRequestController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $loans = LoanRequest::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return view('loan_requests.index', compact('loans'));
    }

    public function create()
    {
        $user = Auth::user();

        return view('loan_requests.create', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'purpose' => ['nullable', 'string'],
            'installment_months' => ['nullable', 'integer', 'min:1', 'max:12'],
            'disbursement_date' => ['nullable', 'date'],
            'payment_method' => ['required', 'in:TUNAI,CICILAN,POTONG_GAJI'],
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $user = Auth::user();

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('loan_documents', 'public');
        }

        $installmentMonths = $request->installment_months;

        LoanRequest::create([
            'user_id' => $user->id,
            'snapshot_name' => $user->name,
            'snapshot_nik' => $user->employee_profile?->nik,
            'snapshot_position' => $user->employee_profile?->position?->name,
            'snapshot_division' => $user->employee_profile?->division?->name,
            'snapshot_company' => $user->employee_profile?->pt?->name,
            'submitted_at' => now()->toDateString(),
            'document_path' => $documentPath,
            'amount' => $request->amount,
            'purpose' => $request->purpose,
            'repayment_term' => $installmentMonths ? (string) $installmentMonths : null,
            'disbursement_date' => $request->disbursement_date,
            'payment_method' => $request->payment_method,
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
