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

        if ($request->filled('q')) {
            $query->where('snapshot_name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('submitted_at')) {
            $query->whereDate('submitted_at', $request->submitted_at);
        }

        $loans = $query->get();

        return view('hr.loan_requests.index', compact('loans'));
    }

    public function show($id)
    {
        $loan = LoanRequest::with('repayments')->findOrFail($id);

        return view('hr.loan_requests.show', compact('loan'));
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->isHrManager(), 403);

        $loan = LoanRequest::findOrFail($id);

        return view('hr.loan_requests.edit', compact('loan'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->isHrManager(), 403);

        $loan = LoanRequest::findOrFail($id);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'monthly_installment' => ['required', 'numeric', 'min:1'],
            'repayment_term' => ['required', 'integer', 'min:1'],
            'disbursement_date' => ['nullable', 'date'],
            'payment_method' => ['required', 'in:TUNAI,CICILAN,POTONG_GAJI'],
            'purpose' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $loan->update($validated);

        return redirect()
            ->route('hr.loan_requests.show', $loan->id)
            ->with('success', 'Detail pinjaman berhasil diupdate.');
    }

    public function approve(Request $request, $id)
    {
        abort_unless(auth()->user()->isHrManager(), 403);

        $loan = LoanRequest::findOrFail($id);

        if ($loan->status !== 'PENDING_HRD') {
            return redirect()->back()->with('error', 'Pengajuan tidak bisa diapprove (status saat ini: ' . $loan->status . ')');
        }

        $loan->update([
            'status' => 'APPROVED',
            'hrd_id' => Auth::id(),
            'hrd_decided_at' => now(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Pengajuan hutang telah disetujui.');
    }

    public function reject(Request $request, $id)
    {
        abort_unless(auth()->user()->isHrManager(), 403);

        $loan = LoanRequest::findOrFail($id);

        if (!in_array($loan->status, ['PENDING_HRD', 'APPROVED'])) {
            return redirect()->back()->with('error', 'Pengajuan tidak bisa ditolak (status saat ini: ' . $loan->status . ')');
        }

        $loan->update([
            'status' => 'REJECTED',
            'hrd_id' => Auth::id(),
            'hrd_decided_at' => now(),
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Pengajuan hutang telah ditolak.');
    }

    public function saveInternalNote(Request $request, $id)
    {
        abort_unless(auth()->user()->isHrManager(), 403);

        $request->validate([
            'hrd_note' => ['nullable', 'string', 'max:1000']
        ]);

        $loan = LoanRequest::findOrFail($id);

        $newNote = trim($request->hrd_note);
        if (empty($newNote)) {
            return redirect()->back()->with('error', 'Catatan tidak boleh kosong.');
        }

        $existingNote = $loan->hrd_note;
        $timestamp = now()->format('d M Y, H:i');
        $formattedNew = "[{$timestamp}] {$newNote}";

        if ($existingNote) {
            $loan->update(['hrd_note' => $existingNote . "\n" . $formattedNew]);
        } else {
            $loan->update(['hrd_note' => $formattedNew]);
        }

        return redirect()->back()->with('success', 'Catatan internal berhasil disimpan.');
    }

    public function storeRepayment(Request $request, $id)
    {
        abort_unless(auth()->user()->isHrManager(), 403);

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

        // If amount exceeds remaining and not confirmed, show warning with remainder info
        if ($cleanAmount > $remaining && !$request->boolean('force_submit')) {
            $remainder = $cleanAmount - $remaining;
            return redirect()->back()
                ->withInput()
                ->with('repayment_warning', [
                    'amount' => $cleanAmount,
                    'remaining' => $remaining,
                    'remainder' => $remainder,
                ]);
        }

        // If force_submit is true, we allow the excess but it becomes "extra" payment
        // The remainder is recorded but doesn't change loan status to LUNAS automatically
        // HRD has discretion - we cap at remaining for LUNAS calculation
        $remainder = $cleanAmount > $remaining ? $cleanAmount - $remaining : 0;
        $effectiveAmount = min($cleanAmount, $remaining + $remainder);

        LoanRepayment::create([
            'loan_request_id' => $id,
            'user_id' => Auth::id(),
            'paid_at' => $request->paid_at,
            'amount' => $cleanAmount,
            'method' => $request->method,
            'note' => $request->note,
        ]);

        // Recalculate total paid with new repayment
        $totalPaidAfter = $totalPaid + $cleanAmount;

        if ($totalPaidAfter >= $loan->amount) {
            $loan->update([
                'status' => 'LUNAS',
            ]);
        }

        return redirect()->back()->with('success', 'Cicilan berhasil dicatat.');
    }

    public function updateRepayment(Request $request, $id, $repaymentId)
    {
        abort_unless(auth()->user()->isHrManager(), 403);

        $request->validate([
            'paid_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'in:TUNAI,TRANSFER,POTONG_GAJI'],
            'note' => ['nullable', 'string']
        ]);

        $loan = LoanRequest::with('repayments')->findOrFail($id);
        $repayment = $loan->repayments()->where('id', $repaymentId)->firstOrFail();

        $oldAmount = $repayment->amount;
        $newAmount = intval($request->amount);

        $repayment->update([
            'paid_at' => $request->paid_at,
            'amount' => $newAmount,
            'method' => $request->method,
            'note' => $request->note,
        ]);

        // Recalculate total paid after update
        $totalPaidAfter = $loan->repayments()->sum('amount');

        // Update loan status based on new total
        if ($totalPaidAfter >= $loan->amount) {
            $loan->update(['status' => 'LUNAS']);
        } else {
            // If was LUNAS but now not fully paid, revert to APPROVED
            if ($loan->status === 'LUNAS') {
                $loan->update(['status' => 'APPROVED']);
            }
        }

        return redirect()->back()->with('success', 'Cicilan berhasil diupdate.');
    }

    public function destroyRepayment($id, $repaymentId)
    {
        abort_unless(auth()->user()->isHrManager(), 403);

        $loan = LoanRequest::with('repayments')->findOrFail($id);
        $repayment = $loan->repayments()->where('id', $repaymentId)->firstOrFail();

        $repayment->delete();

        // Recalculate total paid after delete
        $totalPaidAfter = $loan->repayments()->sum('amount');

        // Update loan status based on remaining
        if ($totalPaidAfter >= $loan->amount) {
            $loan->update(['status' => 'LUNAS']);
        } else {
            if ($loan->status === 'LUNAS') {
                $loan->update(['status' => 'APPROVED']);
            }
        }

        return redirect()->back()->with('success', 'Cicilan berhasil dihapus.');
    }
}