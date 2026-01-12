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
                            // Ini menangani kasus Renaldy yang "Langsung ke HRD" tapi statusnya "Menunggu Atasan"
                            ->orWhereHas('user', function ($u) {
                                $u->whereNull('direct_supervisor_id');
                            });
                        });
                });
            })
            ->orderByDesc('created_at') // Disarankan created_at agar urutan waktu lebih jelas
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

        // 1. Boleh jika status PENDING_HR (Rida sebagai HR Manager)
        if ($leave->status === LeaveRequest::PENDING_HR) {
            $canApprove = true;
        }
        // 2. Boleh jika status PENDING_SUPERVISOR...
        elseif ($leave->status === LeaveRequest::PENDING_SUPERVISOR) {
            // ...DAN Rida adalah Supervisor langsungnya
            if ($leave->user->direct_supervisor_id === $userId) {
                $canApprove = true;
            }
            // ...ATAU Karyawan ini TIDAK PUNYA Supervisor (Langsung HRD)
            elseif (empty($leave->user->direct_supervisor_id)) {
                $canApprove = true;
            }
        }

        return view('hr.leave_requests.show', [
            'item' => $leave,
            'canApprove' => $canApprove,
        ]);
    }

    public function approve(LeaveRequest $leave)
    {
        $this->authorizeAccess();

        // Pastikan status valid
        $allowedStatus = [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR];
        abort_unless(in_array($leave->status, $allowedStatus), 400, 'Status pengajuan tidak valid untuk disetujui.');

        // HRD pun tidak boleh approve diri sendiri (idealnya)
        if ($leave->user_id === auth()->id()) {
            // Kecuali mau di-bypass, tapi standardnya blocked
             return back()->with('error', 'Anda tidak dapat menyetujui pengajuan Anda sendiri.');
        }

        $leave->update([
            'status'      => LeaveRequest::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan disetujui.');
    }

    public function reject(LeaveRequest $leave)
    {
        $this->authorizeAccess();

        $allowedStatus = [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR];
        abort_unless(in_array($leave->status, $allowedStatus), 400, 'Status pengajuan tidak valid untuk ditolak.');

        if ($leave->user_id === auth()->id()) {
             return back()->with('error', 'Anda tidak dapat menolak pengajuan Anda sendiri.');
        }

        $leave->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }

    private function authorizeAccess()
    {
        $user = auth()->user();
        
        // Pastikan User punya Role HRD / HR STAFF / MANAGER HR
        // Sesuaikan method isHR() dengan logic Role di aplikasimu
        if (!$user || !method_exists($user, 'isHR') || !$user->isHR()) {
            // Fallback check manual role jika method isHR tidak ada
            if (!in_array($user->role, ['HRD', 'HR STAFF', 'MANAGER HR'])) {
                 abort(403, 'Akses khusus HRD');
            }
        }
    }
}