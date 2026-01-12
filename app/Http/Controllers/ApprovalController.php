<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ApprovalController extends Controller
{
    /**
     * Inbox Approval.
     * Logic: Menampilkan pengajuan dari bawahan langsung (berdasarkan supervisor_id).
     */
    public function index(Request $request)
    {
        $me = auth()->user();
        
        // Pastikan hanya SUPERVISOR dan MANAGER yang bisa akses
        // (Bisa juga ditangani middleware, tapi double check disini aman)
        if (!in_array($me->role, [UserRole::SUPERVISOR, UserRole::MANAGER])) {
            abort(403, 'Anda tidak memiliki akses approval.');
        }

        $leaves = LeaveRequest::with(['user.profile.pt', 'user.division', 'user.position'])
            // 1. Filter Status: Hanya ambil yang statusnya MENUNGGU ATASAN
            // Pastikan constant PENDING_SUPERVISOR di model LeaveRequest nilainya sesuai DB 
            // (misal: 'MENUNGGU ATASAN' atau 'pending_supervisor')
            ->where('status', LeaveRequest::PENDING_SUPERVISOR)
            
            // 2. Filter KUNCI: Ambil user yang supervisor-nya adalah SAYA
            ->whereHas('user', function (Builder $q) use ($me) {
                $q->where('direct_supervisor_id', $me->id);
            })
            
            ->orderByDesc('created_at')
            ->paginate(20);

        // Arahkan ke view yang sesuai
        return view('supervisor.leave_requests.index', compact('leaves'));
    }

    /**
     * Tampilkan Detail
     */
    public function show(LeaveRequest $leave)
    {
        $this->authorizeApproval($leave);

        return view('supervisor.leave_requests.show', [
            'item' => $leave->load(['user.profile.pt', 'user.division', 'approver']),
        ]);
    }

    /**
     * Setujui (Approve)
     * Mengubah status menjadi PENDING_HR (Lanjut ke HRD)
     */
    public function approve(LeaveRequest $leave)
    {
        $this->authorizeApproval($leave);

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return back()->with('error', 'Status pengajuan sudah berubah, tidak bisa diproses.');
        }

        $leave->update([
            // Setelah disetujui atasan, status lanjut ke HR
            'status'             => LeaveRequest::PENDING_HR,
            'supervisor_ack_at'  => now(),
            // Mencatat siapa yang melakukan approval (Gharin)
            'approver_id'        => auth()->id(), 
        ]);

        return back()->with('success', 'Pengajuan disetujui dan diteruskan ke HRD.');
    }

    /**
     * Tolak (Reject)
     */
    public function reject(LeaveRequest $leave)
    {
        $this->authorizeApproval($leave);

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
             return back()->with('error', 'Status pengajuan sudah berubah.');
        }

        $leave->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approver_id' => auth()->id(), // Mencatat siapa yang menolak
            'rejected_at' => now(), // Opsional: jika ada kolom ini
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }

    /**
     * VALIDASI KEAMANAN: Memastikan User benar-benar bawahan saya
     */
    private function authorizeApproval(LeaveRequest $leave)
    {
        $me = auth()->user();

        // Cek apakah user pembuat cuti ini benar-benar bawahan saya?
        // Logic: ID Supervisor si User harus sama dengan ID saya.
        if ($leave->user->direct_supervisor_id !== $me->id) {
            abort(403, 'Akses Ditolak: Karyawan ini bukan bawahan langsung Anda.');
        }
    }
}