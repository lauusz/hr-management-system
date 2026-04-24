<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Image\ImageCompressor;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApprovalController extends Controller
{
    public function __construct(
        protected ImageCompressor $imageCompressor,
        protected LeaveBalanceService $leaveBalanceService,
    ) {}

    // =====================================================================
    // INBOX / LIST
    // =====================================================================

    /**
     * Inbox Approval — PENDING_SUPERVISOR berdasarkan hierarki
     * (direct_supervisor_id = me OR manager_id = me AND direct_supervisor_id = NULL).
     */
    public function index(Request $request)
    {
        $me = auth()->user();

        if (!in_array($me->role, [UserRole::MANAGER, UserRole::SUPERVISOR, UserRole::HRD])) {
            abort(403, 'Anda tidak memiliki akses approval.');
        }

        $query = LeaveRequest::with(['user.profile.pt', 'user.division', 'user.position'])
            ->orderByDesc('created_at')
            ->where('status', LeaveRequest::PENDING_SUPERVISOR)
            ->whereHas('user', function (Builder $q) use ($me) {
                $q->where(function ($subQ) use ($me) {
                    $subQ->where('direct_supervisor_id', $me->id)
                         ->orWhere(function ($q2) use ($me) {
                             $q2->where('manager_id', $me->id)
                                ->whereNull('direct_supervisor_id');
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
     * [SUPERVISOR ONLY] List bawahan — same Divisi & PT, PENDING_SUPERVISOR.
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
            ->paginate(100);

        return view('supervisor.leave_requests.index', compact('leaves'));
    }

    /**
     * Master Data Cuti Bawahan (Rekap — semua status).
     */
    public function master(Request $request)
    {
        $me = auth()->user();

        if (!in_array($me->role, [UserRole::MANAGER, UserRole::SUPERVISOR, UserRole::HRD])) {
            abort(403, 'Anda tidak memiliki akses ini.');
        }

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
                $query->whereBetween('created_at', [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
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
                $sub->where('name', 'like', '%' . $q . '%');
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
     * Detail Pengajuan — untuk Supervisor/Manager/HRD.
     */
    public function show(LeaveRequest $leave)
    {
        $me = auth()->user();
        $leave->load(['user.profile.pt', 'user.division', 'approver']);

        if (!$this->checkCanView($leave->user, $me) && !$me->isHR() && $leave->user_id !== $me->id) {
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
    // ACK — Supervisor ketahui & teruskan ke HRD
    // =====================================================================

    /**
     * [Supervisor/Manager] ACK — set status ke PENDING_HR + supervisor_ack_at.
     */
    public function ack(Request $request, LeaveRequest $leave)
    {
        $me = auth()->user();

        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Anda bukan atasan langsung yang berhak menyetujui.');
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan tidak valid atau sudah berubah.');
        }

        $leave->update([
            'status'             => LeaveRequest::PENDING_HR,
            'supervisor_ack_at'  => now(),
        ]);

        return redirect()->route('approval.index')->with('success', 'Pengajuan telah diketahui dan diteruskan ke HR.');
    }

    // =====================================================================
    // REJECT
    // =====================================================================

    /**
     * [Supervisor/Manager] Reject — dengan audit trail di notes.
     */
    public function reject(LeaveRequest $leave)
    {
        $me = auth()->user();

        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Anda bukan atasan langsung yang berhak menolak.');
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
        }

        $currentNotes = $leave->notes;
        $systemNote = "[System] Ditolak oleh Atasan (" . $me->name . ") pada " . now()->format('d M Y H:i');
        $newNotes = $currentNotes ? $currentNotes . "\n" . $systemNote : $systemNote;

        $leave->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approved_by' => $me->id,
            'approved_at' => now(),
            'notes'       => $newNotes,
        ]);

        return redirect()->route('approval.index')->with('success', 'Pengajuan ditolak.');
    }

    // =====================================================================
    // APPROVE
    // =====================================================================

    /**
     * [Supervisor/Manager] Approve — HRD applicant langsung APPROVED,
     * staff lain → PENDING_HR.
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        $me = auth()->user();

        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Anda bukan atasan langsung yang berhak menyetujui level ini.');
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return redirect()->route('approval.index')->with('error', 'Status pengajuan sudah berubah.');
        }

        $applicantRole = $leave->user->role instanceof UserRole ? $leave->user->role->value : $leave->user->role;
        $isHRD = in_array(strtoupper($applicantRole), ['HRD', 'HR MANAGER']);

        if ($isHRD) {
            DB::transaction(function () use ($leave, $me, $request) {
                $this->leaveBalanceService->deductLeaveBalanceForLeave($leave);

                $leave->update([
                    'status'      => LeaveRequest::STATUS_APPROVED,
                    'approved_by' => $me->id,
                    'approved_at' => now(),
                    'notes'       => ($request->notes ? $request->notes . "\n" : '') . "[System] Disetujui oleh {$me->name}",
                ]);

                $this->deleteDuplicateLeaveRequests($leave);
            });

            return redirect()->route('approval.index')->with('success', 'Pengajuan HRD telah disetujui sepenuhnya (Auto-Approved).');
        }

        $leave->update([
            'status'      => LeaveRequest::PENDING_HR,
            'approved_by' => $me->id,
            'approved_at' => now(),
            'notes'       => $request->notes,
        ]);

        return redirect()->route('approval.index')->with('success', 'Pengajuan disetujui. Menunggu verifikasi HRD.');
    }

    // =====================================================================
    // EDIT / UPDATE (Supervisor revisi data bawahan)
    // =====================================================================

    /**
     * Form Edit untuk Supervisor (Revisi Data Bawahan).
     */
    public function edit(LeaveRequest $leave)
    {
        $me = auth()->user();

        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Hanya atasan langsung yang dapat mengubah data pengajuan ini.');
        }

        return view('supervisor.leave_requests.edit', compact('leave'));
    }

    /**
     * Update Data oleh Supervisor — reset status ke PENDING_HR.
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        $me = auth()->user();

        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'type'       => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i'],
            'reason'     => ['required', 'string', 'max:5000'],
            'photo'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            'special_leave_detail' => [
                'nullable', 'string',
                Rule::requiredIf(fn() => $request->type === LeaveType::CUTI_KHUSUS->value),
            ],
            'substitute_pic'   => ['nullable', 'string', 'max:255'],
            'substitute_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $currentNotes = $leave->notes;
        $systemNote = "[System] Data direvisi oleh Supervisor (" . $me->name . ") pada " . now()->format('d M Y H:i');
        $newNotes = $currentNotes ? $currentNotes . "\n" . $systemNote : $systemNote;

        $dataToUpdate = [
            'type'       => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'start_time' => $validated['start_time'],
            'end_time'   => $validated['end_time'],
            'reason'     => $validated['reason'],
            'notes'      => $newNotes,
            'substitute_pic'   => $validated['substitute_pic'] ?? $leave->substitute_pic,
            'substitute_phone' => $validated['substitute_phone'] ?? $leave->substitute_phone,
            'status'      => LeaveRequest::PENDING_HR,
            'approved_by' => $me->id,
            'approved_at' => now(),
        ];

        if ($validated['type'] === LeaveType::CUTI_KHUSUS->value) {
            $dataToUpdate['special_leave_category'] = $validated['special_leave_detail'];
        } else {
            $dataToUpdate['special_leave_category'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($leave->photo) {
                Storage::disk('public')->delete('leave_photos/' . $leave->photo);
            }
            $fullPath = $this->imageCompressor->compressAndStore(
                $request->file('photo'), 'photo', 'leave_photos', 'leave_'
            );
            $dataToUpdate['photo'] = basename($fullPath);
        }

        $leave->update($dataToUpdate);

        return redirect()->route('approval.show', $leave->id)
            ->with('success', 'Data berhasil direvisi dan status dikembalikan ke HRD untuk verifikasi ulang.');
    }

    /**
     * [AJUKAN PEMBATALAN] — Langsung BATAL, tanpa perlu persetujuan HRD.
     */
    public function destroy(LeaveRequest $leave)
    {
        $me = auth()->user();

        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Akses ditolak.');
        }

        $currentNotes = $leave->notes;
        $systemNote = "[System] Dibatalkan oleh Supervisor/Atasan (" . $me->name . ") pada " . now()->format('d M Y H:i');
        $newNotes = $currentNotes ? $currentNotes . "\n" . $systemNote : $systemNote;

        $leave->update([
            'status' => 'BATAL',
            'notes'  => $newNotes,
        ]);

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

        $myPtId   = optional($me->profile)->pt_id;
        $userPtId = optional($leave->user->profile)->pt_id;

        $isSamePt = ($myPtId && $userPtId) ? ($myPtId === $userPtId) : false;

        abort_unless($isSameDivision && $isSamePt, 403,
            'Akses Ditolak: Karyawan berbeda Divisi atau PT.');
    }

    /**
     * Hak Approve (STRICT):
     * - direct_supervisor_id = me → SELALU bisa approve
     * - manager_id = me AND direct_supervisor_id = NULL → bisa approve
     */
    private function checkIsAuthorizedApprover($applicant, $me): bool
    {
        if ((int) $applicant->direct_supervisor_id === (int) $me->id) {
            return true;
        }

        if ((int) $applicant->manager_id === (int) $me->id && empty($applicant->direct_supervisor_id)) {
            return true;
        }

        return false;
    }

    /**
     * Hak Lihat (LOOSE):
     * - atasan langsung
     * - manager dari staff tersebut
     */
    private function checkCanView($applicant, $me): bool
    {
        if ($this->checkIsAuthorizedApprover($applicant, $me)) {
            return true;
        }

        if ($applicant->manager_id === $me->id) {
            return true;
        }

        return false;
    }

    /**
     * Hapus pengajuan duplikat (overlap tanggal) yang masih pending.
     */
    private function deleteDuplicateLeaveRequests(LeaveRequest $approvedLeave)
    {
        $duplicates = LeaveRequest::where('user_id', $approvedLeave->user_id)
            ->where('id', '!=', $approvedLeave->id)
            ->whereIn('status', [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR])
            ->where(function ($query) use ($approvedLeave) {
                $query->whereBetween('start_date', [$approvedLeave->start_date, $approvedLeave->end_date])
                    ->orWhereBetween('end_date', [$approvedLeave->start_date, $approvedLeave->end_date])
                    ->orWhere(function ($q) use ($approvedLeave) {
                        $q->where('start_date', '<=', $approvedLeave->start_date)
                          ->where('end_date', '>=', $approvedLeave->end_date);
                    });
            })
            ->get();

        foreach ($duplicates as $duplicate) {
            $duplicate->delete();
        }
    }
}