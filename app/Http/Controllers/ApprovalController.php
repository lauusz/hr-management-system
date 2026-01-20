<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Enums\UserRole;
use App\Enums\LeaveType;
use App\Services\Image\ImageCompressor;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApprovalController extends Controller
{
    // Inject ImageCompressor untuk fitur upload foto saat revisi
    public function __construct(protected ImageCompressor $imageCompressor)
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

        // 3. Strict Hierarchy Logic (Hanya bawahan langsung)
        $query->whereHas('user', function (Builder $q) use ($me) {
            $q->where(function ($subQ) use ($me) {
                // Skenario 1: Staff -> Supervisor Approve
                $subQ->where(function ($karyawan) use ($me) {
                    $karyawan->where('role', UserRole::EMPLOYEE)
                             ->where('direct_supervisor_id', $me->id);
                });

                // Skenario 2: Supervisor -> Manager Approve
                $subQ->orWhere(function ($spv) use ($me) {
                    $spv->whereIn('role', [UserRole::SUPERVISOR, 'SUPERVISOR'])
                        ->where('manager_id', $me->id);
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
     * Mengubah status menjadi CANCEL_REQ agar HRD yang menghapus.
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
        $systemNote = "[System] Supervisor (" . $me->name . ") mengajukan permohonan pembatalan pada " . now()->format('d M Y H:i');
        $newNotes = $currentNotes ? $currentNotes . "\n" . $systemNote : $systemNote;

        // 3. Update Status jadi 'CANCEL_REQ'
        // Kita pakai string 'CANCEL_REQ' sebagai penanda request batal
        $leave->update([
            'status' => 'CANCEL_REQ', 
            'notes'  => $newNotes
        ]);

        return redirect()->route('approval.index')
            ->with('success', 'Permohonan pembatalan telah dikirim ke HRD.');
    }

    /**
     * Action: Setujui (Approve)
     */
    public function approve(LeaveRequest $leave)
    {
        if (!$this->checkIsAuthorizedApprover($leave->user, auth()->user())) {
            abort(403, 'Anda bukan atasan langsung yang berhak menyetujui level ini.');
        }

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return back()->with('error', 'Status pengajuan sudah berubah.');
        }

        $leave->update([
            'status'      => LeaveRequest::PENDING_HR, // Lanjut ke HRD
            'approved_by' => auth()->id(), 
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan disetujui dan diteruskan ke HRD.');
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
     * PRIVATE HELPER: Logika Penentuan Hak Approve (STRICT)
     */
    private function checkIsAuthorizedApprover($applicant, $me)
    {
        $roleValue = $applicant->role instanceof UserRole ? $applicant->role->value : $applicant->role;
        $roleStr = strtoupper((string) $roleValue);

        // Rule 1: Jika Staff -> Cek direct_supervisor_id
        if ($roleStr === 'EMPLOYEE' && $applicant->direct_supervisor_id === $me->id) {
            return true;
        }

        // Rule 2: Jika SPV -> Cek manager_id
        if (($roleStr === 'SUPERVISOR' || $roleStr === 'SPV') && $applicant->manager_id === $me->id) {
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