<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class HrLeaveController extends Controller
{
    public function index()
    {
        $this->authorizeAccess();

        $leaves = LeaveRequest::pendingHr()
            ->with('user')
            ->orderByDesc('id')
            ->paginate(20);

        return view('hr.leave_requests.index', compact('leaves'));
    }

    public function show(LeaveRequest $leave)
    {
        $this->authorizeAccess();

        return view('hr.leave_requests.show', [
            'item' => $leave->load('user', 'approver'),
        ]);
    }

    // approve leave request
    public function approve(LeaveRequest $leave)
    {
        $this->authorizeAccess();

        abort_unless($leave->status === LeaveRequest::PENDING_HR, 400);

        $leave->update([
            'status'      => LeaveRequest::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan disetujui HR.');
    }

    // reject leave request
    public function reject(LeaveRequest $leave)
    {
        $this->authorizeAccess();

        abort_unless($leave->status === LeaveRequest::PENDING_HR, 400);

        $leave->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan ditolak HR.');
    }

    private function authorizeAccess()
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'HRD', 403, 'Akses khusus HRD');
    }
}
