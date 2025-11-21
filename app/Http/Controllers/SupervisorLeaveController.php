<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class SupervisorLeaveController extends Controller
{
    public function index(Request $request)
    {
        $me = auth()->user();

        $leaves = LeaveRequest::with('user')
            ->pendingSupervisor()
            ->whereHas('user', fn($q) => $q->where('division_id', $me->division_id))
            ->orderByDesc('id')
            ->paginate(20);

        return view('supervisor.leave_requests.index', compact('leaves'));
    }

    // show detail
    public function show(LeaveRequest $leave)
    {
        $me = auth()->user();
        abort_unless($me->division_id === optional($leave->user)->division_id, 403);

        return view('supervisor.leave_requests.show', [
            'item' => $leave->load('user', 'approver'),
        ]);
    }

    // acknowledge
    public function ack(LeaveRequest $leave)
    {
        $me = auth()->user();

        abort_unless($me->division_id === optional($leave->user)->division_id, 403);
        abort_unless($leave->status === LeaveRequest::PENDING_SUPERVISOR, 400);

        $leave->update([
            'status'             => LeaveRequest::PENDING_HR,
            'supervisor_ack_at'  => now(),
        ]);

        return back()->with('success', 'Pengajuan telah diketahui dan diteruskan ke HR.');
    }

    // Supervisor Reject
    public function reject(LeaveRequest $leave)
    {
        $me = auth()->user();

        abort_unless($me->division_id === optional($leave->user)->division_id, 403);
        abort_unless($leave->status === LeaveRequest::PENDING_SUPERVISOR, 400);

        $leave->update(['status' => LeaveRequest::STATUS_REJECTED]);

        return back()->with('success', 'Pengajuan ditolak oleh Supervisor.');
    }
}
