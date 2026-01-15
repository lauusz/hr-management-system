<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Enums\UserRole;
use App\Enums\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ApprovalController extends Controller
{
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

        // 3. Strict Hierarchy Logic (Hanya bawahan langsung untuk approval)
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
     * Menampilkan SEMUA riwayat pengajuan (Pending, Approved, Rejected) dari bawahan.
     * * UPDATE: Manager bisa melihat Staff (Grand-subordinate) juga.
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
        // Logic ini lebih luas daripada 'index'.
        // Kita izinkan User melihat data JIKA:
        // A. Dia adalah Supervisor langsungnya (direct_supervisor_id)
        // B. ATAU Dia adalah Managernya (manager_id) -> Ini mencakup Staff & SPV
        $query->whereHas('user', function (Builder $q) use ($me) {
            $q->where(function ($subQ) use ($me) {
                $subQ->where('direct_supervisor_id', $me->id)
                     ->orWhere('manager_id', $me->id);
            });
        });

        // 4. Filter Logic
        
        // Filter Tanggal Pengajuan
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

        // Filter Jenis Cuti
        $typeFilter = $request->input('type');
        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        // Filter Status
        $status = $request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        // Filter Pencarian Nama
        $q = $request->input('q');
        if ($q) {
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%');
            });
        }

        // 5. Pagination
        $items = $query->paginate(20);

        // 6. Data Pendukung View
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

        // Cek apakah saya berhak melihat (Approver Sah, Manager Grand-boss, atau HR)
        $canView = $this->checkCanView($leave->user, $me);

        if (!$canView && !$me->isHR() && $leave->user_id !== $me->id) {
            abort(403, 'Anda tidak memiliki akses melihat data ini.');
        }

        // Tombol Approve hanya muncul jika saya Approver LANGSUNG & Status masih Pending
        // Gunakan fungsi checkIsAuthorizedApprover untuk strict approval
        $isDirectApprover = $this->checkIsAuthorizedApprover($leave->user, $me);
        $canApprove = $isDirectApprover && ($leave->status === LeaveRequest::PENDING_SUPERVISOR);

        return view('supervisor.leave_requests.show', [
            'item' => $leave,
            'canApprove' => $canApprove,
        ]);
    }

    /**
     * Action: Setujui (Approve)
     */
    public function approve(LeaveRequest $leave)
    {
        // Strict Check: Hanya atasan langsung yg boleh approve
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
        // Strict Check: Hanya atasan langsung yg boleh reject
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
     * Hanya mengembalikan TRUE jika user adalah atasan LANGSUNG (untuk tombol aksi).
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
     * Mengembalikan TRUE jika user adalah Atasan Langsung ATAU Manager di atasnya.
     */
    private function checkCanView($applicant, $me)
    {
        // 1. Jika saya Atasan Langsung (SPV nya Staff, atau Manager nya SPV)
        if ($this->checkIsAuthorizedApprover($applicant, $me)) {
            return true;
        }

        // 2. Jika saya Manager dari Staff tersebut (Grand-boss view)
        // Ini agar Manager bisa lihat detail pengajuan Staff meskipun yang approve SPV.
        if ($applicant->manager_id === $me->id) {
            return true;
        }

        return false;
    }
}