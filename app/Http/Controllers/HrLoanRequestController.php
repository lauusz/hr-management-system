<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use App\Models\LoanRepayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrLoanRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LoanRequest::orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $loans = $query->get();

        return view('hr.loan_requests.index', compact('loans'));
    }

    public function show($id)
    {
        $loan = LoanRequest::with('repayments')->findOrFail($id);

        return view('hr.loan_requests.show', compact('loan'));
    }

    public function approve(Request $request, $id)
    {
        $loan = LoanRequest::findOrFail($id);

        $loan->update([
            'status' => 'APPROVED',
            'hrd_id' => Auth::id(),
            'hrd_decided_at' => now(),
            'hrd_note' => $request->hrd_note,
        ]);

        return redirect()->back()->with('success', 'Pengajuan hutang telah disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'hrd_note' => ['required', 'string']
        ]);

        $loan = LoanRequest::findOrFail($id);

        $loan->update([
            'status' => 'REJECTED',
            'hrd_id' => Auth::id(),
            'hrd_decided_at' => now(),
            'hrd_note' => $request->hrd_note,
        ]);

        return redirect()->back()->with('success', 'Pengajuan hutang telah ditolak.');
    }

    public function storeRepayment(Request $request, $id)
    {
        $request->validate([
            'paid_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'in:TUNAI,TRANSFER,POTONG_GAJI'],
            'note' => ['nullable', 'string']
        ]);

        $loan = LoanRequest::with('repayments')->findOrFail($id);

        $cleanAmount = intval($request->amount);

        $totalPaid = $loan->repayments->sum('amount');
        $remaining = max(0, $loan->amount - $totalPaid);

        if ($cleanAmount > $remaining) {
            return redirect()->back()->withErrors('Nominal cicilan melebihi sisa hutang.');
        }

        LoanRepayment::create([
            'loan_request_id' => $id,
            'user_id' => Auth::id(),
            'paid_at' => $request->paid_at,
            'amount' => $cleanAmount,
            'method' => $request->method,
            'note' => $request->note,
        ]);

        $totalPaidAfter = $totalPaid + $cleanAmount;

        if ($totalPaidAfter >= $loan->amount) {
            $loan->update([
                'status' => 'Lunas',
            ]);
        }

        return redirect()->back()->with('success', 'Cicilan berhasil dicatat.');
    }
}
