<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Enums\UserRole;
use App\Enums\LeaveType;
use App\Services\Image\ImageCompressor;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    // Inject ImageCompressor untuk fitur upload foto saat revisi
    public function __construct(
        protected ImageCompressor $imageCompressor,
        protected LeaveBalanceService $leaveBalanceService,
    )
    {
    }

    /**
     * Inbox Approval.
     * Menampilkan pengajuan yang SEDANG MENUNGGU persetujuan user ini (Pending).
     */
    public function index(Request $request)
    {
        $me = auth()->user();
        
        // 1. Cek Hak Akses
        if (!in_array($me->role, [UserRole::MANAGER, UserRole::SUPERVISOR, UserRole::HRD])) {
            abort(403, 'Anda tidak memiliki akses approval.');
        }

        $query = LeaveRequest::with(['user.profile.pt', 'user.division', 'user.position'])
            ->orderByDesc('created_at');

        // 2. Filter Status Pending (Hanya Inbox)
        $query->where('status', LeaveRequest::PENDING_SUPERVISOR);

        // 3. Logic Berdasarkan Profile ID (Supervisor / Manager)
        // Supervisor: lihat jika applicant punya direct_supervisor_id = me
        // Manager: lihat jika applicant HANYA punya manager_id = me (tanpa direct_supervisor_id)
        $query->whereHas('user', function (Builder $q) use ($me) {
            $q->where(function ($subQ) use ($me) {
                $subQ->where('direct_supervisor_id', $me->id)
                     ->orWhere(function ($q2) use ($me) {
                         $q2->where('manager_id', $me->id)
                            ->whereNull('direct_supervisor_id');
                     });
            });
        });

        $leaves = $query->paginate(20);
        $isApprover = true; 

        return view('supervisor.leave_requests.index', compact('leaves', 'isApprover'));
    }

    /**
     * Master Data Cuti Bawahan (Rekap).
     */
    public function master(Request $request)
    {
        $me = auth()->user();

        // 1. Cek Hak Akses
        if (!in_array($me->role, [UserRole::MANAGER, UserRole::SUPERVISOR, UserRole::HRD])) {
            abort(403, 'Anda tidak memiliki akses ini.');
        }

        // 2. Base Query
        $query = LeaveRequest::with(['user.profile.pt', 'user.division', 'user.position'])
            ->orderByDesc('created_at');

        // 3. Hierarchy Logic (View All Subordinates)
        $query->whereHas('user', function (Builder $q) use ($me) {
            $q->where(function ($subQ) use ($me) {
                $subQ->where('direct_supervisor_id', $me->id)
                     ->orWhere('manager_id', $me->id);
            });
        });

        // 4. Filter Logic
        $submittedRange = $request->input('submitted_range');
        if ($submittedRange) {
            $dates = explode(' sampai ', $submittedRange);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', [
                    $dates[0] . ' 00:00:00',
                    $dates[1] . ' 23:59:59'
                ]);
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
            'items',
            'typeOptions',
            'statusOptions',
            'submittedRange',
            'typeFilter',
            'status',
            'q'
        ));
    }

    /**
     * Tampilkan Detail Pengajuan
     */
    public function show(LeaveRequest $leave)
    {
        $me = auth()->user();
        $leave->load(['user.profile.pt', 'user.division', 'approver']); 

        // Cek Hak Lihat
        if (!$this->checkCanView($leave->user, $me) && !$me->isHR() && $leave->user_id !== $me->id) {
            abort(403, 'Anda tidak memiliki akses melihat data ini.');
        }

        // Cek Hak Edit/Approve (Atasan Langsung)
        $isDirectApprover = $this->checkIsAuthorizedApprover($leave->user, $me);
        
        // Tombol Approve HANYA muncul jika status PENDING_SUPERVISOR
        $canApprove = $isDirectApprover && ($leave->status === LeaveRequest::PENDING_SUPERVISOR);

        return view('supervisor.leave_requests.show', [
            'item' => $leave,
            'canApprove' => $canApprove,
            'isApprover' => $isDirectApprover, // Variable baru untuk logic tombol Edit (muncul kapanpun)
        ]);
    }

    /**
     * Form Edit untuk Supervisor (Revisi Data Bawahan)
     */
    public function edit(LeaveRequest $leave)
    {
        $me = auth()->user();
        
        // Cek Hak Akses (Hanya Atasan Langsung)
        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Hanya atasan langsung yang dapat mengubah data pengajuan ini.');
        }
        
        return view('supervisor.leave_requests.edit', compact('leave'));
    }

    /**
     * Update Data oleh Supervisor
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        $me = auth()->user();

        // 1. Validasi Akses
        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Validasi Input (Sama seperti Create/Edit Staff)
        $validated = $request->validate([
            'type'       => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i'],
            'reason'     => ['required', 'string', 'max:5000'],
            'photo'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            
            // Helper Cuti Khusus
            'special_leave_detail' => [
                'nullable',
                'string',
                Rule::requiredIf(fn() => $request->type === LeaveType::CUTI_KHUSUS->value)
            ],
            // PIC
            'substitute_pic'   => ['nullable', 'string', 'max:255'],
            'substitute_phone' => ['nullable', 'string', 'max:50'],
        ]);

        // 3. Logic Notes (Audit Trail)
        $currentNotes = $leave->notes;
        $systemNote = "[System] Data direvisi oleh Supervisor (" . $me->name . ") pada " . now()->format('d M Y H:i');
        $newNotes = $currentNotes ? $currentNotes . "\n" . $systemNote : $systemNote;

        // 4. Siapkan Data Update
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
            
            // [PENTING] Reset status ke PENDING_HR (Atasan Mengetahui)
            // Agar HRD tahu ada perubahan dan memverifikasi ulang
            'status'      => LeaveRequest::PENDING_HR,
            'approved_by' => $me->id, // SPV dianggap otomatis menyetujui hasil revisinya
            'approved_at' => now(),
        ];

        // Handle Cuti Khusus Category
        if ($validated['type'] === LeaveType::CUTI_KHUSUS->value) {
            $dataToUpdate['special_leave_category'] = $validated['special_leave_detail'];
        } else {
            $dataToUpdate['special_leave_category'] = null;
        }

        // Handle Upload Foto Baru
        if ($request->hasFile('photo')) {
            if ($leave->photo) {
                Storage::disk('public')->delete('leave_photos/' . $leave->photo);
            }
            $fullPath = $this->imageCompressor->compressAndStore(
                $request->file('photo'), 
                'photo', 
                'leave_photos', 
                'leave_'
            );
            $dataToUpdate['photo'] = basename($fullPath);
        }

        $leave->update($dataToUpdate);

        return redirect()->route('approval.show', $leave->id)
            ->with('success', 'Data berhasil direvisi dan status dikembalikan ke HRD untuk verifikasi ulang.');
    }

    /**
     * [AJUKAN PEMBATALAN]
     * Mengubah status langsung menjadi BATAL. Tidak perlu persetujuan HRD.
     */
    public function destroy(LeaveRequest $leave)
    {
        $me = auth()->user();

        // 1. Validasi Akses
        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Update Notes (Audit Trail)
        $currentNotes = $leave->notes;
        $systemNote = "[System] Dibatalkan oleh Supervisor/Atasan (" . $me->name . ") pada " . now()->format('d M Y H:i');
        $newNotes = $currentNotes ? $currentNotes . "\n" . $systemNote : $systemNote;

        // 3. Langsung set ke BATAL (tanpa perlu approval HRD)
        $leave->update([
            'status' => 'BATAL',
            'notes'  => $newNotes
        ]);

        return redirect()->route('approval.index')
            ->with('success', 'Pengajuan telah dibatalkan.');
    }

    /**
     * Action: Setujui (Approve)
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        $me = auth()->user();

        if (!$this->checkIsAuthorizedApprover($leave->user, $me)) {
            abort(403, 'Anda bukan atasan langsung yang berhak menyetujui level ini.');
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return back()->with('error', 'Status pengajuan sudah berubah.');
        }

        // 2. Logic Approval
        // Cek Role Applicant
        $applicantRole = $leave->user->role instanceof \App\Enums\UserRole ? $leave->user->role->value : $leave->user->role;
        $isHRD = in_array(strtoupper($applicantRole), ['HRD', 'HR MANAGER']);

        if ($isHRD) {
            // [CASE KHUSUS HRD] 
            // HRD Manager -> Manager Approve -> LANGSUNG APPROVED (Skip Pending HR)
            // Sistem Otomatis Potong Cuti memakai flow yang sama dengan approval HR biasa.

            DB::transaction(function () use ($leave, $me, $request) {
                $this->leaveBalanceService->deductLeaveBalanceForLeave($leave);

                $leave->update([
                    'status'      => LeaveRequest::STATUS_APPROVED, // Langsung FINAL
                    'approved_by' => $me->id,
                    'approved_at' => now(),
                    'notes'       => ($request->notes ? $request->notes . "\n" : '') . "[System] Disetujui oleh {$me->name}",
                ]);

                // [AUTO DELETE DUPLIKAT] Hapus pengajuan duplikat yang masih pending
                $this->deleteDuplicateLeaveRequests($leave);
            });

            return redirect()->route('approval.index')->with('success', 'Pengajuan HRD telah disetujui sepenuhnya (Auto-Approved).');

        } else {
            // [CASE COMMMON STAFF/SPV]
            // Masuk ke PENDING_HR dulu untuk verifikasi HRD
            $leave->update([
                'status'      => LeaveRequest::PENDING_HR,
                'approved_by' => $me->id,
                'approved_at' => now(),
                'notes'       => $request->notes, // Optional notes from superior
            ]);
            
            return redirect()->route('approval.index')->with('success', 'Pengajuan disetujui. Menunggu verifikasi HRD.');
        }
    }

    /**
     * Action: Tolak (Reject)
     */
    public function reject(LeaveRequest $leave)
    {
        if (!$this->checkIsAuthorizedApprover($leave->user, auth()->user())) {
            abort(403, 'Anda bukan atasan langsung yang berhak menolak level ini.');
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
             return back()->with('error', 'Status pengajuan sudah berubah.');
        }

        $leave->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }

    /**
     * [HELPER] Hapus pengajuan duplikat yang masih pending di tanggal yang sama
     */
    private function deleteDuplicateLeaveRequests(LeaveRequest $approvedLeave)
    {
        // Cari pengajuan lain dari user yang sama, di tanggal yang overlap, masih pending
        $duplicates = LeaveRequest::where('user_id', $approvedLeave->user_id)
            ->where('id', '!=', $approvedLeave->id)
            ->whereIn('status', [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR])
            ->where(function ($query) use ($approvedLeave) {
                // Cek overlap tanggal: start_date atau end_date berada dalam range
                $query->whereBetween('start_date', [$approvedLeave->start_date, $approvedLeave->end_date])
                    ->orWhereBetween('end_date', [$approvedLeave->start_date, $approvedLeave->end_date])
                    ->orWhere(function ($q) use ($approvedLeave) {
                        // Atau pengajuan duplikat yang range-nya "meliputi" pengajuan approved
                        $q->where('start_date', '<=', $approvedLeave->start_date)
                          ->where('end_date', '>=', $approvedLeave->end_date);
                    });
            })
            ->get();

        // Delete semua duplikat yang ditemukan
        foreach ($duplicates as $duplicate) {
            $duplicate->delete();
        }
    }

    /**
     * PRIVATE HELPER: Logika Penentuan Hak Approve (STRICT)
     *
     * Aturan:
     * - Jika applicant punya direct_supervisor_id → HANYA direct supervisor yang bisa approve
     * - Jika applicant HANYA punya manager_id (tanpa direct_supervisor_id) → manager bisa approve
     */
    private function checkIsAuthorizedApprover($applicant, $me)
    {
        // 1. Jika saya adalah Direct Supervisor-nya → SELALU bisa approve
        if ((int) $applicant->direct_supervisor_id === (int) $me->id) {
            return true;
        }

        // 2. Jika saya adalah Manager-nya → HANYA bisa approve JIKA applicant TIDAK punya direct_supervisor_id
        if ((int) $applicant->manager_id === (int) $me->id && empty($applicant->direct_supervisor_id)) {
            return true;
        }

        return false;
    }

    /**
     * PRIVATE HELPER: Logika Penentuan Hak LIHAT (LOOSE)
     */
    private function checkCanView($applicant, $me)
    {
        // 1. Jika saya Atasan Langsung
        if ($this->checkIsAuthorizedApprover($applicant, $me)) {
            return true;
        }

        // 2. Jika saya Manager dari Staff tersebut (Grand-boss view)
        if ($applicant->manager_id === $me->id) {
            return true;
        }

        return false;
    }
}