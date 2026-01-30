<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\Pt;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HrLeaveController extends Controller
{
    /**
     * Menampilkan daftar pengajuan yang statusnya:
     * 1. PENDING_HR (Tugas Utama HR)
     * 2. PENDING_SUPERVISOR tapi user-nya bawahan saya (Saya merangkap SPV)
     * 3. PENDING_SUPERVISOR tapi user-nya TIDAK PUNYA SPV (Orphan/Bypass)
     */
    public function index()
    {
        $this->authorizeAccess();

        $userId = Auth::id();

        $leaves = LeaveRequest::withoutGlobalScopes()
            ->with([
                'user.division', 
                'user.position', 
                'user.profile.pt' 
            ])
            ->where(function ($query) use ($userId) {
                // 1. Ambil yang statusnya PENDING_HR (Tugas Global HR Manager)
                $query->where('status', LeaveRequest::PENDING_HR)
                
                // 2. ATAU Ambil yang statusnya PENDING_SUPERVISOR...
                ->orWhere(function ($subQuery) use ($userId) {
                    $subQuery->where('status', LeaveRequest::PENDING_SUPERVISOR)
                        ->where(function ($q) use ($userId) {
                            // A. ...Dimana Saya adalah Supervisor-nya
                            $q->whereHas('user', function ($u) use ($userId) {
                                $u->where('direct_supervisor_id', $userId);
                            })
                            // B. ...ATAU User tersebut TIDAK PUNYA Supervisor (Orphan Data)
                            ->orWhereHas('user', function ($u) {
                                $u->whereNull('direct_supervisor_id');
                            });
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(100);

        return view('hr.leave_requests.index', compact('leaves'));
    }

    /**
     * Halaman Master / Riwayat Pengajuan (Semua Data)
     */
    public function master(Request $request)
    {
        $this->authorizeAccess();

        // Base Query
        $query = LeaveRequest::withoutGlobalScopes()
            ->with([
                'user.division', 
                'user.position',
                'user.profile.pt', 
                'approver'
            ])
            ->orderByDesc('created_at');
        
        // --- 1. Filter Status ---
        $statusOptions = [
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
            'BATAL', 
            'CANCEL_REQ'
        ];

        $status = $request->query('status');
        if ($status && in_array($status, $statusOptions, true)) {
            $query->where('status', $status);
        }

        // --- 2. Filter Tipe Cuti ---
        $typeFilter = $request->query('type');
        if ($typeFilter && in_array($typeFilter, LeaveType::values(), true)) {
            $query->where('type', $typeFilter);
        }

        // --- 3. Filter Range Tanggal ---
        $submittedRange = trim((string) $request->query('submitted_range'));
        if ($submittedRange !== '') {
            try {
                $parts = preg_split('/\s+(to|sampai)\s+/i', $submittedRange);
                if (count($parts) === 1) {
                    $from = Carbon::parse(trim($parts[0]))->startOfDay();
                    $to = (clone $from)->endOfDay();
                    $query->whereBetween('created_at', [$from, $to]);
                } elseif (count($parts) >= 2) {
                    $from = Carbon::parse(trim($parts[0]))->startOfDay();
                    $to = Carbon::parse(trim($parts[1]))->endOfDay();
                    if ($from->gt($to)) { $temp = $from; $from = $to; $to = $temp; }
                    $query->whereBetween('created_at', [$from, $to]);
                }
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        // --- 4. Filter PT ---
        $ptId = $request->query('pt_id');
        if ($ptId) {
            $query->whereHas('user.profile', function (Builder $q) use ($ptId) {
                $q->where('pt_id', $ptId);
            });
        }

        // --- 5. Search ---
        $q = $request->query('q');
        if ($q) {
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%');
            });
        }

        $items = $query->paginate(100)->appends([
            'status'          => $status,
            'type'            => $typeFilter,
            'submitted_range' => $submittedRange,
            'pt_id'           => $ptId,
            'q'               => $q,
        ]);

        $pts = Pt::orderBy('name', 'asc')->get();

        return view('hr.leave_requests.master', [
            'items'          => $items,
            'status'         => $status,
            'statusOptions'  => $statusOptions,
            'typeFilter'     => $typeFilter,
            'typeOptions'    => LeaveType::cases(),
            'submittedRange' => $submittedRange,
            'pt_id'          => $ptId,
            'q'              => $q,
            'pts'            => $pts,
        ]);
    }

    public function show(LeaveRequest $leave)
    {
        $this->authorizeAccess();

        // Load relasi yang diperlukan
        $leave->load(['user.profile.pt', 'user.division', 'user.position', 'approver']);

        // [LOGIC TOMBOL APPROVE]
        $canApprove = false;
        $userId = Auth::id();

        if ($leave->user_id === $userId) {
            $canApprove = false;
        } 
        else {
            if ($leave->status === LeaveRequest::PENDING_HR) {
                $canApprove = true;
            }
            elseif ($leave->status === LeaveRequest::PENDING_SUPERVISOR) {
                if ($leave->user->direct_supervisor_id === $userId) {
                    $canApprove = true;
                }
                elseif (empty($leave->user->direct_supervisor_id)) {
                    $canApprove = true;
                }
            }
            elseif ($leave->status === 'CANCEL_REQ') {
                $canApprove = true;
            }
        }

        return view('hr.leave_requests.show', [
            'item' => $leave,
            'canApprove' => $canApprove,
        ]);
    }

    /**
     * [UPDATE] APPROVE BERDASARKAN CHECKBOX DARI FORM
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        $this->authorizeAccess();

        // 1. Validasi
        $request->validate([
            'notes_hrd'    => 'nullable|string|max:1000',
            'deduct_leave' => 'nullable|in:1', // Validasi checkbox
        ]);

        // Pastikan status valid
        $allowedStatus = [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR, 'CANCEL_REQ'];
        abort_unless(in_array($leave->status, $allowedStatus), 400, 'Status pengajuan tidak valid untuk disetujui.');

        if ($leave->user_id === auth()->id()) {
             return back()->with('error', 'Etika Profesi: Anda tidak dapat menyetujui pengajuan Anda sendiri.');
        }

        // =====================================================================
        // [LOGIC BARU] PEMOTONGAN SALDO BERDASARKAN CHECKBOX
        // =====================================================================
        // Kita cek apakah HRD mencentang "Potong Saldo Cuti" (value="1")
        $shouldDeduct = $request->input('deduct_leave') == '1';

        // Hanya potong jika checkbox dicentang DAN status sebelumnya belum approved
        if ($shouldDeduct && $leave->status !== LeaveRequest::STATUS_APPROVED) {
            $user = $leave->user;
            
            // Hitung durasi (hari)
            $start = Carbon::parse($leave->start_date);
            $end   = Carbon::parse($leave->end_date);
            $days  = $start->diffInDays($end) + 1;

            // Optional: Double Check Saldo
            if ($user->leave_balance < $days) {
                 return back()->with('error', "Gagal Approve: Saldo cuti karyawan tidak mencukupi untuk dipotong (Sisa: {$user->leave_balance}, Butuh: {$days}).");
            }

            // Eksekusi Potong Saldo
            $user->decrement('leave_balance', $days);
        }

        // Update status pengajuan jadi APPROVED
        $leave->update([
            'status'      => LeaveRequest::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes_hrd'   => $request->notes_hrd,
        ]);

        return back()->with('success', 'Pengajuan disetujui' . ($shouldDeduct ? ' & Saldo cuti dipotong.' : '.'));
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $this->authorizeAccess();

        $request->validate([
            'notes_hrd' => 'required|string|max:1000',
        ]);

        $allowedStatus = [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR];
        abort_unless(in_array($leave->status, $allowedStatus), 400, 'Status pengajuan tidak valid untuk ditolak.');

        if ($leave->user_id === auth()->id()) {
             return back()->with('error', 'Etika Profesi: Anda tidak dapat menolak pengajuan Anda sendiri.');
        }

        $leave->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes_hrd'   => $request->notes_hrd, 
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }

    private function authorizeAccess()
    {
        $user = auth()->user();
        
        if (!$user || !method_exists($user, 'isHR') || !$user->isHR()) {
            if (!in_array($user->role, ['HRD', 'HR STAFF', 'MANAGER HR'])) {
                 abort(403, 'Akses khusus HRD');
            }
        }
    }
}