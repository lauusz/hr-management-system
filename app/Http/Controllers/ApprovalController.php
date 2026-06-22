<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Image\ImageCompressor;
use App\Services\LeaveBalanceService;
use App\Services\LeaveRequestDuplicateCleanupService;
use App\Services\LeaveRequestStateMachine;
use App\Services\LeaveRequestWorkflowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApprovalController extends Controller
{
    public function __construct(
        protected ImageCompressor $imageCompressor,
        protected LeaveBalanceService $leaveBalanceService,
        protected LeaveRequestStateMachine $stateMachine,
        protected LeaveRequestWorkflowService $workflowService,
        protected LeaveRequestDuplicateCleanupService $duplicateCleanupService,
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
        $myRole = strtoupper((string) ($me->role instanceof UserRole ? $me->role->value : $me->role));

        $query = LeaveRequest::with(['user.profile.pt', 'user.division', 'user.position'])
            ->orderByDesc('created_at')
            ->where('status', LeaveRequest::PENDING_SUPERVISOR)
            ->whereHas('user', function (Builder $q) use ($me, $myRole) {
                $q->where(function ($subQ) use ($me, $myRole) {
                    // Supervisor: ack untuk bawahan langsung
                    if ($myRole === 'SUPERVISOR') {
                        $subQ->where('direct_supervisor_id', $me->id);

                        return;
                    }

                    // Manager: ack hanya jika tidak ada SPV (ds null)
                    if ($myRole === 'MANAGER') {
                        $subQ->whereNull('direct_supervisor_id')
                            ->where('manager_id', $me->id);

                        return;
                    }

                    // HRD yang juga punya bawahan sebagai manager
                    if ($myRole === 'HRD') {
                        $isManagerForSomeone = User::where('manager_id', $me->id)->exists();
                        if ($isManagerForSomeone) {
                            $subQ->whereNull('direct_supervisor_id')
                                ->where('manager_id', $me->id);

                            return;
                        }
                    }

                    // Fallback: tampilkan semua yang relevan
                    $subQ->where('direct_supervisor_id', $me->id)
                        ->orWhere(function ($q2) use ($me) {
                            $q2->whereNull('direct_supervisor_id')
                                ->where('manager_id', $me->id);
                        });
                });
            });

        $leaves = $query->paginate(20);

        return view('supervisor.leave_requests.index', [
            'leaves' => $leaves,
            'isApprover' => true,
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

        $query = LeaveRequest::with(['user.profile.pt', 'user.division', 'user.position'])
            ->orderByDesc('created_at')
            ->whereHas('user', function (Builder $q) use ($me) {
                $q->where(function ($subQ) use ($me) {
                    $subQ->where('direct_supervisor_id', $me->id)
                        ->orWhere('manager_id', $me->id);
                });
            });

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
        $leave->load(['user.profile.pt', 'user.division', 'approver']);

        if (! $this->checkCanView($leave->user, $me) && ! $me->isHR() && $leave->user_id !== $me->id) {
            abort(403, 'Anda tidak memiliki akses melihat data ini.');
        }

        $isDirectApprover = $this->checkIsAuthorizedApprover($leave->user, $me);
        $canApprove = $isDirectApprover && ($leave->status === LeaveRequest::PENDING_SUPERVISOR);

        return view('supervisor.leave_requests.show', [
            'item' => $leave,
            'canApprove' => $canApprove,
            'isApprover' => $isDirectApprover,
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

        if (! $this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Anda bukan atasan langsung yang berhak menyetujui.');
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan tidak valid atau sudah berubah.');
        }

        $applicantRole = $leave->user->role instanceof UserRole ? $leave->user->role->value : $leave->user->role;
        $isHRD = in_array(strtoupper((string) $applicantRole), ['HRD', 'HR MANAGER']);

        // [ADJUSTMENT] HRD Applicant: ACK = Final Approval (langsung APPROVED, tidak ke HR inbox)
        if ($isHRD) {
            $approved = $this->stateMachine->perform(
                $leave,
                LeaveRequestStateMachine::APPROVE,
                function (LeaveRequest $lockedLeave) use ($me, $request) {
                    $this->leaveBalanceService->deductLeaveBalanceForLeave($lockedLeave);

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
            function (LeaveRequest $lockedLeave) use ($me) {
                $currentNotes = $lockedLeave->notes;
                $systemNote = '[System] Diketahui oleh Atasan ('.$me->name.') pada '.now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;

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

        if (! $this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Anda bukan atasan langsung yang berhak menolak.');
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
        }

        $rejected = $this->stateMachine->perform(
            $leave,
            LeaveRequestStateMachine::REJECT,
            function (LeaveRequest $lockedLeave) use ($me) {
                $currentNotes = $lockedLeave->notes;
                $systemNote = '[System] Ditolak oleh Atasan ('.$me->name.') pada '.now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;

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

        if (! $this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Anda bukan atasan langsung yang berhak menyetujui level ini.');
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
        }

        $applicantRole = $leave->user->role instanceof UserRole ? $leave->user->role->value : $leave->user->role;
        $isHRD = in_array(strtoupper($applicantRole), ['HRD', 'HR MANAGER']);

        if ($isHRD) {
            $approved = $this->stateMachine->perform(
                $leave,
                LeaveRequestStateMachine::APPROVE,
                function (LeaveRequest $lockedLeave) use ($me, $request) {
                    $this->leaveBalanceService->deductLeaveBalanceForLeave($lockedLeave);

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

            return redirect()->route('approval.index')->with('success', 'Pengajuan HRD telah disetujui sepenuhnya (Auto-Approved).');
        }

        $approved = $this->stateMachine->perform(
            $leave,
            LeaveRequestStateMachine::FORWARD_TO_HR,
            function (LeaveRequest $lockedLeave) use ($me, $request) {
                $currentNotes = $lockedLeave->notes;
                $systemNote = '[System] Disetujui oleh Atasan ('.$me->name.') pada '.now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;

                return [
                    'approved_by' => $me->id,
                    'approved_at' => now(),
                    'notes' => ($request->notes ? $request->notes."\n" : '').$systemNote,
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

        if (! $this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Hanya atasan langsung yang dapat mengubah data pengajuan ini.');
        }

        if (! in_array($leave->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
            abort(403, 'Pengajuan sudah diproses, tidak dapat direvisi.');
        }

        return view('supervisor.leave_requests.edit', compact('leave'));
    }

    /**
     * Update Data oleh Supervisor - reset status ke PENDING_HR.
     * Hanya dapat mengedit pengajuan dengan status pending.
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        $me = auth()->user();

        if (! $this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Akses ditolak.');
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

        $updated = $this->stateMachine->perform(
            $leave,
            LeaveRequestStateMachine::REVISE_FOR_HR,
            function (LeaveRequest $lockedLeave) use ($me, $request, $validated) {
                $currentNotes = $lockedLeave->notes;
                $systemNote = '[System] Data direvisi oleh Supervisor ('.$me->name.') pada '.now()->format('d M Y H:i');
                $newNotes = $currentNotes ? $currentNotes."\n".$systemNote : $systemNote;

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
            }
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

        if (! $this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Akses ditolak.');
        }

        // Supervisor/Atasan hanya dapat membatalkan pengajuan yang masih
        // menunggu acknowledgment-nya. Pengajuan PENDING_HR atau APPROVED
        // harus dibatalkan melalui workflow HR agar saldo cuti tetap konsisten.
        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')
                ->with('error', 'Pengajuan ini tidak dapat dibatalkan oleh atasan. Hubungi HR untuk pembatalan.');
        }

        $cancelled = $this->workflowService->cancelLeaveRequest($leave, $me);

        if (! $cancelled) {
            return redirect()->route('approval.index')->with('error', 'Pengajuan ini tidak dapat dibatalkan.');
        }

        return redirect()->route('approval.index')->with('success', 'Pengajuan telah dibatalkan.');
    }

    // =====================================================================
    // PRIVATE HELPERS
    // =====================================================================

    /**
     * [SUPERVISOR ONLY] Otorisasi: hanya bisa akses karyawan satu Divisi & PT.
     */
    private function authorizeSupervisor(LeaveRequest $leave)
    {
        $me = auth()->user();

        $isSameDivision = $me->division_id === optional($leave->user)->division_id;

        $myPtId = optional($me->profile)->pt_id;
        $userPtId = optional($leave->user->profile)->pt_id;

        $isSamePt = ($myPtId && $userPtId) ? ($myPtId === $userPtId) : false;

        abort_unless($isSameDivision && $isSamePt, 403,
            'Akses Ditolak: Karyawan berbeda Divisi atau PT.');
    }

    /**
     * Hak Acknowledge (STRICT):
     * - direct_supervisor_id = me → SELALU bisa ack
     * - direct_supervisor_id is null AND manager_id = me AND current user role is MANAGER → bisa ack
     * - Jika keduanya ada, hanya direct_supervisor_id yang boleh ack
     */
    private function checkIsAuthorizedApprover($applicant, $me): bool
    {
        if ((int) $applicant->direct_supervisor_id === (int) $me->id) {
            return true;
        }

        if (empty($applicant->direct_supervisor_id) && (int) $applicant->manager_id === (int) $me->id) {
            return true;
        }

        return false;
    }

    /**
     * Hak Lihat (LOOSE):
     * - atasan langsung (direct_supervisor_id = me)
     * - manager dari staff tersebut (manager_id = me)
     * Manager boleh melihat meskipun direct_supervisor_id exists,
     * tetapi tidak boleh melakukan ack.
     */
    private function checkCanView($applicant, $me): bool
    {
        if ($this->checkIsAuthorizedApprover($applicant, $me)) {
            return true;
        }

        if ((int) $applicant->manager_id === (int) $me->id) {
            return true;
        }

        return false;
    }
}
