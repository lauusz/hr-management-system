<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Image\ImageCompressor;
use App\Services\LeaveApprovalAssignmentService;
use App\Services\LeaveBalanceService;
use App\Services\LeaveRequestDayService;
use App\Services\LeaveRequestDuplicateCleanupService;
use App\Services\LeaveRequestStateMachine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApprovalController extends Controller
{
    public function __construct(
        protected ImageCompressor $imageCompressor,
        protected LeaveBalanceService $leaveBalanceService,
        protected LeaveRequestDayService $leaveRequestDayService,
        protected LeaveRequestStateMachine $stateMachine,
        protected LeaveRequestDuplicateCleanupService $duplicateCleanupService,
        protected LeaveApprovalAssignmentService $approvalAssignmentService,
    ) {}

    // =====================================================================
    // INBOX / LIST
    // =====================================================================

    /**
     * Inbox Approval - PENDING_SUPERVISOR berdasarkan hierarki dan role user login.
     *
     * - Supervisor: lihat bawahan langsung (direct_supervisor_id = me)
     * - Manager   : lihat bawahan tanpa SPV (manager_id = me AND ds IS NULL)
     * - HRD+Manager: lihat bawahan yang mg = me (untuk kasus HRD applicant)
     */
    public function index(Request $request)
    {
        $me = auth()->user();

        $query = $this->approvalAssignmentService->queryPendingFor($me)
            ->with(['user.profile.pt', 'user.division', 'user.position'])
            ->orderByDesc('created_at');

        $leaves = $query->paginate(20);

        return view('supervisor.leave_requests.index', [
            'leaves' => $leaves,
            'isApprover' => true,
            'approvalCapacityLabel' => 'Approver',
        ]);
    }

    /**
     * [SUPERVISOR ONLY] List bawahan - same Divisi & PT, PENDING_SUPERVISOR.
     * Supervisor tidak bisa melihat bawahan di divisi/PT berbeda.
     */
    public function indexBySupervisor(Request $request)
    {
        $me = auth()->user();
        $me->load('profile');
        $myPtId = $me->profile?->pt_id;

        $leaves = LeaveRequest::with(['user.profile.pt', 'user.division'])
            ->where('status', LeaveRequest::PENDING_SUPERVISOR)
            ->whereHas('user', function (Builder $q) use ($me) {
                $q->where('division_id', $me->division_id);
            })
            ->when($myPtId, function ($query) use ($myPtId) {
                $query->whereHas('user.profile', function (Builder $q) use ($myPtId) {
                    $q->where('pt_id', $myPtId);
                });
            })
            ->orderByDesc('id')
            ->paginate(20);

        return view('supervisor.leave_requests.index', compact('leaves'));
    }

    /**
     * Master Data Cuti Bawahan (Rekap - semua status).
     */
    public function master(Request $request)
    {
        $me = auth()->user();

        $query = $this->approvalAssignmentService->queryVisibleTo($me)
            ->with(['user.profile.pt', 'user.division', 'user.position'])
            ->where('user_id', '!=', $me->id)
            ->orderByDesc('created_at')
            ->whereHas('user');

        $submittedRange = $request->input('submitted_range');
        if ($submittedRange) {
            $dates = explode(' sampai ', $submittedRange);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', [$dates[0].' 00:00:00', $dates[1].' 23:59:59']);
            } else {
                $query->whereDate('created_at', $dates[0]);
            }
        }

        $typeFilter = $request->input('type');
        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $status = $request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        $q = $request->input('q');
        if ($q) {
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%');
            });
        }

        $items = $query->paginate(20);
        $typeOptions = LeaveType::cases();
        $statusOptions = [
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
        ];

        return view('supervisor.leave_requests.master', compact(
            'items', 'typeOptions', 'statusOptions', 'submittedRange',
            'typeFilter', 'status', 'q'
        ));
    }

    // =====================================================================
    // DETAIL
    // =====================================================================

    /**
     * Detail Pengajuan - untuk Supervisor/Manager/HRD.
     */
    public function show(LeaveRequest $leave)
    {
        $me = auth()->user();
        $leave->load(['user.profile.pt', 'user.division', 'approver', 'user.assignedApprover']);

        if (! $this->canUseApprovalScreen($me, $leave)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses melihat data ini.');
        }

        $canProcessInitial = $this->approvalAssignmentService->canProcessInitialStage($me, $leave);
        $canRevise = $this->canReviseApprovalRequest($me, $leave);
        $canCancel = $canProcessInitial;
        $canReject = $canProcessInitial;
        $approvalCapacityLabel = $this->approvalCapacityLabel($this->approvalAssignmentService->initialApprovalCapacity($leave->user));
        $isMonitoringOnly = $this->approvalAssignmentService->canView($me, $leave)
            && ! $canProcessInitial
            && ! $canRevise
            && ! $canCancel
            && ! $canReject;

        return view('supervisor.leave_requests.show', [
            'item' => $leave,
            'canApprove' => $canProcessInitial,
            'canReject' => $canReject,
            'canRevise' => $canRevise,
            'canCancel' => $canCancel,
            'isApprover' => $canProcessInitial || $canRevise,
            'approvalCapacityLabel' => $approvalCapacityLabel,
            'isMonitoringOnly' => $isMonitoringOnly,
        ]);
    }

    // =====================================================================
    // ACK - Supervisor ketahui & teruskan ke HRD
    // =====================================================================

    /**
     * [Supervisor/Manager] ACK - Mengetahui & teruskan ke HRD.
     * Hanya untuk status PENDING_SUPERVISOR.
     */
    public function ack(Request $request, LeaveRequest $leave)
    {
        $me = auth()->user();

        if (! $this->isCurrentInitialApprover($me, $leave)) {
            abort(403);
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan tidak valid atau sudah berubah.');
        }

        $leave->loadMissing('user.assignedApprover');
        $capacity = $this->approvalAssignmentService->initialApprovalCapacity($leave->user);
        $isHRD = $this->isHrdApplicant($leave->user);

        // [ADJUSTMENT] HRD Applicant: ACK = Final Approval (langsung APPROVED, tidak ke HR inbox)
        if ($isHRD) {
            $approved = $this->stateMachine->perform(
                $leave,
                LeaveRequestStateMachine::APPROVE,
                function (LeaveRequest $lockedLeave) use ($me, $request, $capacity) {
                    $fromStatus = $lockedLeave->status;
                    $toStatus = $this->stateMachine->getTargetStatus($fromStatus, LeaveRequestStateMachine::APPROVE);
                    $this->leaveBalanceService->deductLeaveBalanceForLeave($lockedLeave);
                    $this->approvalAssignmentService->recordAction($lockedLeave, $me, $capacity, 'ACK', $fromStatus, $toStatus);

                    $systemNote = '[System] Disetujui oleh Atasan ('.$me->name.') pada '.now()->format('d M Y H:i');

                    return [
                        'supervisor_ack_at' => now(),
                        'approved_by' => $me->id,
                        'approved_at' => now(),
                        'notes' => ($request->notes ? $request->notes."\n" : '').$systemNote,
                    ];
                },
                [],
                LeaveRequest::PENDING_SUPERVISOR,
                function (LeaveRequest $lockedLeave) {
                    $this->duplicateCleanupService->deleteDuplicatePendingLeaveRequests($lockedLeave);
                }
            );

            if (! $approved) {
                return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
            }

            return redirect()->route('approval.index')->with('success', 'Pengajuan HRD telah disetujui sepenuhnya.');
        }

        $acknowledged = $this->stateMachine->perform(
            $leave,
            LeaveRequestStateMachine::FORWARD_TO_HR,
            function (LeaveRequest $lockedLeave) use ($me, $capacity) {
                $fromStatus = $lockedLeave->status;
                $toStatus = $this->stateMachine->getTargetStatus($fromStatus, LeaveRequestStateMachine::FORWARD_TO_HR);
                $currentNotes = $lockedLeave->notes;
                $systemNote = '[System] Diketahui oleh Atasan ('.$me->name.') pada '.now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;
                $this->approvalAssignmentService->recordAction($lockedLeave, $me, $capacity, 'ACK', $fromStatus, $toStatus);

                return [
                    'supervisor_ack_at' => now(),
                    'approved_by' => $me->id,
                    'approved_at' => now(),
                    'notes' => $newNotes,
                ];
            }
        );

        if (! $acknowledged) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
        }

        return redirect()->route('approval.index')->with('success', 'Pengajuan telah diketahui dan diteruskan ke HR.');
    }

    // =====================================================================
    // REJECT
    // =====================================================================

    /**
     * [Supervisor/Manager] Reject - dengan audit trail di notes.
     */
    public function reject(LeaveRequest $leave)
    {
        $me = auth()->user();

        if (! $this->isCurrentInitialApprover($me, $leave)) {
            abort(403);
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
        }

        $leave->loadMissing('user.assignedApprover');
        $capacity = $this->approvalAssignmentService->initialApprovalCapacity($leave->user);

        $rejected = $this->stateMachine->perform(
            $leave,
            LeaveRequestStateMachine::REJECT,
            function (LeaveRequest $lockedLeave) use ($me, $capacity) {
                $fromStatus = $lockedLeave->status;
                $toStatus = $this->stateMachine->getTargetStatus($fromStatus, LeaveRequestStateMachine::REJECT);
                $currentNotes = $lockedLeave->notes;
                $systemNote = '[System] Ditolak oleh Atasan ('.$me->name.') pada '.now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;
                $this->approvalAssignmentService->recordAction($lockedLeave, $me, $capacity, 'REJECT', $fromStatus, $toStatus);

                return [
                    'approved_by' => $me->id,
                    'approved_at' => now(),
                    'notes' => $newNotes,
                ];
            },
            [],
            LeaveRequest::PENDING_SUPERVISOR
        );

        if (! $rejected) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
        }

        return redirect()->route('approval.index')->with('success', 'Pengajuan ditolak.');
    }

    // =====================================================================
    // APPROVE
    // =====================================================================

    /**
     * [Supervisor/Manager] Approve - HRD applicant langsung APPROVED,
     * staff lain → PENDING_HR.
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        $me = auth()->user();

        if (! $this->isCurrentInitialApprover($me, $leave)) {
            abort(403);
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
        }

        $leave->loadMissing('user.assignedApprover');
        $capacity = $this->approvalAssignmentService->initialApprovalCapacity($leave->user);
        $isHRD = $this->isHrdApplicant($leave->user);

        if ($isHRD) {
            $approved = $this->stateMachine->perform(
                $leave,
                LeaveRequestStateMachine::APPROVE,
                function (LeaveRequest $lockedLeave) use ($me, $request, $capacity) {
                    $fromStatus = $lockedLeave->status;
                    $toStatus = $this->stateMachine->getTargetStatus($fromStatus, LeaveRequestStateMachine::APPROVE);
                    $this->leaveBalanceService->deductLeaveBalanceForLeave($lockedLeave);
                    $this->approvalAssignmentService->recordAction($lockedLeave, $me, $capacity, 'APPROVE', $fromStatus, $toStatus);

                    return [
                        'approved_by' => $me->id,
                        'approved_at' => now(),
                        'notes' => ($request->notes ? $request->notes."\n" : '')."[System] Disetujui oleh {$me->name}",
                    ];
                },
                [],
                LeaveRequest::PENDING_SUPERVISOR,
                function (LeaveRequest $lockedLeave) {
                    $this->duplicateCleanupService->deleteDuplicatePendingLeaveRequests($lockedLeave);
                }
            );

            if (! $approved) {
                return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
            }

            return redirect()->route('approval.index')->with('success', 'Pengajuan HRD telah disetujui secara otomatis.');
        }

        $approved = $this->stateMachine->perform(
            $leave,
            LeaveRequestStateMachine::FORWARD_TO_HR,
            function (LeaveRequest $lockedLeave) use ($me, $request, $capacity) {
                $fromStatus = $lockedLeave->status;
                $toStatus = $this->stateMachine->getTargetStatus($fromStatus, LeaveRequestStateMachine::FORWARD_TO_HR);
                $currentNotes = $lockedLeave->notes;
                $systemNote = '[System] Disetujui oleh Atasan ('.$me->name.') pada '.now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;
                $newNotes = $request->notes ? $request->notes."\n".$newNotes : $newNotes;
                $this->approvalAssignmentService->recordAction($lockedLeave, $me, $capacity, 'APPROVE', $fromStatus, $toStatus);

                return [
                    'approved_by' => $me->id,
                    'approved_at' => now(),
                    'notes' => $newNotes,
                ];
            }
        );

        if (! $approved) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
        }

        return redirect()->route('approval.index')->with('success', 'Pengajuan disetujui. Menunggu verifikasi HRD.');
    }

    // =====================================================================
    // EDIT / UPDATE (Supervisor revisi data bawahan)
    // =====================================================================

    /**
     * Form Edit untuk Supervisor (Revisi Data Bawahan).
     * Hanya dapat mengedit pengajuan dengan status pending.
     */
    public function edit(LeaveRequest $leave)
    {
        $me = auth()->user();

        if (! $this->isCurrentInitialApprover($me, $leave)) {
            abort(403);
        }

        if (! in_array($leave->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
            return redirect()->back()->with('error', 'Pengajuan sudah diproses, tidak dapat direvisi.');
        }

        $leave->loadMissing('user.assignedApprover');
        $approvalCapacityLabel = $this->approvalCapacityLabel($this->approvalAssignmentService->initialApprovalCapacity($leave->user));

        return view('supervisor.leave_requests.edit', compact('leave', 'approvalCapacityLabel'));
    }

    /**
     * Update Data oleh Supervisor - reset status ke PENDING_HR.
     * Hanya dapat mengedit pengajuan dengan status pending.
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        $me = auth()->user();

        if (! $this->isCurrentInitialApprover($me, $leave)) {
            abort(403);
        }

        if (! in_array($leave->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
            return redirect()->back()->with('error', 'Pengajuan sudah diproses, tidak dapat direvisi.');
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:5000'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            'special_leave_detail' => [
                'nullable', 'string',
                Rule::requiredIf(fn () => $request->type === LeaveType::CUTI_KHUSUS->value),
            ],
            'substitute_pic' => ['nullable', 'string', 'max:255'],
            'substitute_phone' => ['nullable', 'string', 'max:50'],
        ], [
            'photo.max' => 'Ukuran file bukti pendukung tidak boleh lebih dari 8 MB.',
            'photo.uploaded' => 'File gagal diunggah. Pastikan ukurannya tidak lebih dari 8 MB.',
        ]);

        $leave->loadMissing('user.assignedApprover');
        $capacity = $this->approvalAssignmentService->initialApprovalCapacity($leave->user);

        $updated = $this->stateMachine->perform(
            $leave,
            LeaveRequestStateMachine::REVISE_FOR_HR,
            function (LeaveRequest $lockedLeave) use ($me, $request, $validated, $capacity) {
                $fromStatus = $lockedLeave->status;
                $toStatus = $this->stateMachine->getTargetStatus($fromStatus, LeaveRequestStateMachine::REVISE_FOR_HR);
                $currentNotes = $lockedLeave->notes;
                $systemNote = '[System] Data direvisi oleh Supervisor ('.$me->name.') pada '.now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;
                $this->approvalAssignmentService->recordAction($lockedLeave, $me, $capacity, 'REVISE', $fromStatus, $toStatus);

                $dataToUpdate = [
                    'type' => $validated['type'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'start_time' => $validated['start_time'] ?? null,
                    'end_time' => $validated['end_time'] ?? null,
                    'reason' => $validated['reason'],
                    'notes' => $newNotes,
                    'substitute_pic' => $validated['substitute_pic'] ?? $lockedLeave->substitute_pic,
                    'substitute_phone' => $validated['substitute_phone'] ?? $lockedLeave->substitute_phone,
                    'approved_by' => $me->id,
                    'approved_at' => now(),
                ];

                if ($validated['type'] === LeaveType::CUTI_KHUSUS->value) {
                    $dataToUpdate['special_leave_category'] = $validated['special_leave_detail'];
                } else {
                    $dataToUpdate['special_leave_category'] = null;
                }

                if ($request->hasFile('photo')) {
                    $fullPath = $this->imageCompressor->compressAndStore(
                        $request->file('photo'), 'photo', 'leave_photos', 'leave_'
                    );

                    if ($lockedLeave->photo) {
                        Storage::disk('public')->delete('leave_photos/'.$lockedLeave->photo);
                    }

                    $dataToUpdate['photo'] = basename($fullPath);
                }

                return $dataToUpdate;
            },
            [],
            null,
            function (LeaveRequest $updatedLeave): void {
                $this->leaveRequestDayService->syncDateRange($updatedLeave);
            },
        );

        if (! $updated) {
            return redirect()->back()->with('error', 'Pengajuan sudah berubah status, tidak dapat direvisi.');
        }

        return redirect()->route('approval.show', $leave->id)
            ->with('success', 'Data berhasil direvisi dan status dikembalikan ke HRD untuk verifikasi ulang.');
    }

    /**
     * [AJUKAN PEMBATALAN] - Supervisor/Atasan dapat membatalkan pengajuan bawahan
     * dengan status pending atau APPROVED. Refund saldo (jika ada) dilakukan
     * secara atomik melalui LeaveRequestWorkflowService.
     */
    public function destroy(LeaveRequest $leave)
    {
        $me = auth()->user();

        if (! $this->isCurrentInitialApprover($me, $leave)) {
            abort(403);
        }

        // Supervisor/Atasan hanya dapat membatalkan pengajuan yang masih
        // menunggu acknowledgment-nya. Pengajuan PENDING_HR atau APPROVED
        // harus dibatalkan melalui workflow HR agar saldo cuti tetap konsisten.
        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')
                ->with('error', 'Pengajuan ini tidak dapat dibatalkan oleh atasan. Hubungi HR untuk pembatalan.');
        }

        $leave->loadMissing('user.assignedApprover');
        $capacity = $this->approvalAssignmentService->initialApprovalCapacity($leave->user);

        $cancelled = $this->stateMachine->perform(
            $leave,
            LeaveRequestStateMachine::CANCEL,
            function (LeaveRequest $lockedLeave) use ($me, $capacity) {
                $fromStatus = $lockedLeave->status;
                $toStatus = $this->stateMachine->getTargetStatus($fromStatus, LeaveRequestStateMachine::CANCEL);
                $currentNotes = $lockedLeave->notes;
                $systemNote = '[System] Dibatalkan oleh Supervisor/Atasan ('.$me->name.') pada '.now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;
                $this->approvalAssignmentService->recordAction($lockedLeave, $me, $capacity, 'CANCEL', $fromStatus, $toStatus);

                return ['notes' => $newNotes];
            },
            [],
            LeaveRequest::PENDING_SUPERVISOR,
        );

        if (! $cancelled) {
            return redirect()->route('approval.index')->with('error', 'Pengajuan ini tidak dapat dibatalkan.');
        }

        return redirect()->route('approval.index')->with('success', 'Pengajuan telah dibatalkan.');
    }

    // =====================================================================
    // PRIVATE HELPERS
    // =====================================================================

    private function canUseApprovalScreen(User $viewer, LeaveRequest $leave): bool
    {
        return (int) $leave->user_id !== (int) $viewer->id
            && $this->approvalAssignmentService->canView($viewer, $leave);
    }

    private function canReviseApprovalRequest(User $actor, LeaveRequest $leave): bool
    {
        if (! in_array($leave->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
            return false;
        }

        return $this->isCurrentInitialApprover($actor, $leave);
    }

    private function isCurrentInitialApprover(User $actor, LeaveRequest $leave): bool
    {
        $leave->loadMissing('user.assignedApprover');
        $applicant = $leave->user;

        if ($applicant === null || (int) $applicant->id === (int) $actor->id) {
            return false;
        }

        $initialApprover = $this->approvalAssignmentService->initialApproverFor($applicant);

        return $initialApprover !== null
            && (int) $initialApprover->id === (int) $actor->id;
    }

    private function approvalCapacityLabel(string $capacity): string
    {
        return match ($capacity) {
            LeaveApprovalAssignmentService::CAPACITY_APPROVER => 'Approver',
            LeaveApprovalAssignmentService::CAPACITY_SUPERVISOR => 'Supervisor',
            LeaveApprovalAssignmentService::CAPACITY_MANAGER => 'Manager',
            LeaveApprovalAssignmentService::CAPACITY_HR => 'HRD',
            LeaveApprovalAssignmentService::CAPACITY_FINAL_FOR_HRD => 'Final Approval',
            default => 'Approver',
        };
    }

    private function isHrdApplicant(User $applicant): bool
    {
        $role = $applicant->role instanceof UserRole
            ? $applicant->role->value
            : (string) $applicant->role;

        return in_array(strtoupper($role), ['HRD', 'HR MANAGER'], true);
    }

}
