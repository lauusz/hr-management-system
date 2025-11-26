<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
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

    public function master(Request $request)
    {
        $this->authorizeAccess();

        $query = LeaveRequest::with(['user', 'approver'])
            ->orderByDesc('created_at');

        $statusOptions = [
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
        ];

        $status = $request->query('status');
        if ($status && in_array($status, $statusOptions, true)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        $typeFilter = $request->query('type');
        if ($typeFilter && in_array($typeFilter, LeaveType::values(), true)) {
            $query->where('type', $typeFilter);
        } else {
            $typeFilter = null;
        }

        $submittedDate = $request->query('submitted_date');
        if ($submittedDate) {
            try {
                $start = \Carbon\Carbon::parse($submittedDate)->startOfDay();
                $end = (clone $start)->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            } catch (\Exception $e) {
                $submittedDate = null;
            }
        }

        $q = $request->query('q');
        if ($q) {
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%');
            });
        }

        $items = $query->paginate(20)->appends([
            'status'         => $status,
            'type'           => $typeFilter,
            'submitted_date' => $submittedDate,
            'q'              => $q,
        ]);

        return view('hr.leave_requests.master', [
            'items'         => $items,
            'status'        => $status,
            'statusOptions' => $statusOptions,
            'typeFilter'    => $typeFilter,
            'typeOptions'   => LeaveType::cases(),
            'submittedDate' => $submittedDate,
            'q'             => $q,
        ]);
    }

    public function show(LeaveRequest $leave)
    {
        $this->authorizeAccess();

        return view('hr.leave_requests.show', [
            'item' => $leave->load('user', 'approver'),
        ]);
    }

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
