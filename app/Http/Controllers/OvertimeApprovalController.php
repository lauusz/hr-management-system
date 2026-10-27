<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Services\OvertimeApprovalService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeApprovalController extends Controller
{
    public function index()
    {
        $id = Auth::id();
        $overtimes = OvertimeRequest::with(['user.overtimeApproverAssignment.supervisor', 'user.overtimeApproverAssignment.manager', 'approvalActions'])
            ->where(function ($q) use ($id) {
                $q->where(function ($q) use ($id) {
                    $q->where('status', OvertimeRequest::STATUS_PENDING_SUPERVISOR)
                        ->whereHas('user.overtimeApproverAssignment', fn ($q) => $q->where('supervisor_id', $id));
                })->orWhereHas('approvalActions', fn ($q) => $q->where('actor_id', $id)->whereIn('action', ['APPROVE', 'REJECT']));
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [OvertimeRequest::STATUS_PENDING_SUPERVISOR])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('overtime_approvals.index', compact('overtimes'));
    }

    public function show(OvertimeRequest $overtimeRequest)
    {
        abort_unless($overtimeRequest->canViewEvidence(Auth::user()), 403);
        $overtimeRequest->load(['user.position', 'user.division', 'user.overtimeApproverAssignment.supervisor', 'user.overtimeApproverAssignment.manager', 'approvalActions', 'attachments']);

        return view('overtime_approvals.show', compact('overtimeRequest'));
    }

    public function approve(OvertimeRequest $overtimeRequest, OvertimeApprovalService $service)
    {
        return $this->decide($overtimeRequest, $service, 'APPROVE');
    }

    public function reject(Request $request, OvertimeRequest $overtimeRequest, OvertimeApprovalService $service)
    {
        $data = $request->validate(['rejection_note' => 'required|string|max:255']);

        return $this->decide($overtimeRequest, $service, 'REJECT', $data['rejection_note']);
    }

    private function decide(OvertimeRequest $overtime, OvertimeApprovalService $service, string $decision, ?string $note = null)
    {
        try {
            $service->decide($overtime, Auth::user(), $decision, $note);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('overtime-approvals.index')->with('success', $decision === 'APPROVE' ? 'Persetujuan lembur berhasil dicatat.' : 'Pengajuan lembur ditolak.');
    }
}
