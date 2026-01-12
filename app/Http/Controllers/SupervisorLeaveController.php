<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class SupervisorLeaveController extends Controller
{
    /**
     * Menampilkan daftar pengajuan bawahan (Satu Divisi & Satu PT)
     */
    public function index(Request $request)
    {
        $me = auth()->user();
        
        // Load profile supervisor untuk tahu dia PT mana
        // Kita gunakan eager loading 'profile' agar hemat query
        $me->load('profile'); 
        $myPtId = $me->profile?->pt_id;

        $leaves = LeaveRequest::with(['user.profile.pt', 'user.division']) // Load info PT & Divisi user
            ->pendingSupervisor()
            // 1. Filter: Hanya tampilkan karyawan satu Divisi
            ->whereHas('user', function (Builder $q) use ($me) {
                $q->where('division_id', $me->division_id);
            })
            // 2. Filter (Security): Hanya tampilkan karyawan satu PT
            // Mencegah Supervisor Divisi IT (PT A) melihat pengajuan Divisi IT (PT B)
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
     * Menampilkan Detail Pengajuan
     */
    public function show(LeaveRequest $leave)
    {
        // Cek validasi akses (PT & Divisi)
        $this->authorizeSupervisor($leave);

        return view('supervisor.leave_requests.show', [
            'item' => $leave->load(['user.profile.pt', 'user.division', 'approver']),
        ]);
    }

    /**
     * Acknowledge (Mengetahui & Meneruskan ke HR)
     */
    public function ack(LeaveRequest $leave)
    {
        $this->authorizeSupervisor($leave);

        // Pastikan status masih Pending Supervisor
        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
            return back()->with('error', 'Status pengajuan tidak valid atau sudah berubah.');
        }

        $leave->update([
            'status'             => LeaveRequest::PENDING_HR,
            'supervisor_ack_at'  => now(),
        ]);

        return back()->with('success', 'Pengajuan telah diketahui dan diteruskan ke HR.');
    }

    /**
     * Menolak Pengajuan (Reject)
     */
    public function reject(LeaveRequest $leave)
    {
        $this->authorizeSupervisor($leave);

        if ($leave->status !== LeaveRequest::PENDING_SUPERVISOR) {
             return back()->with('error', 'Status pengajuan tidak valid atau sudah berubah.');
        }

        $leave->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            // Opsional: Catat siapa yang menolak jika ada kolom approved_by
            // 'approved_by' => auth()->id(), 
            // 'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan ditolak oleh Supervisor.');
    }

    /**
     * FUNGSI HELPER PRIVATE
     * Memastikan Supervisor hanya mengakses data karyawan yang valid (Satu PT & Satu Divisi)
     */
    private function authorizeSupervisor(LeaveRequest $leave)
    {
        $me = auth()->user();

        // 1. Cek Kesamaan Divisi
        $isSameDivision = $me->division_id === optional($leave->user)->division_id;
        
        // 2. Cek Kesamaan PT (Melalui Profile)
        $myPtId   = optional($me->profile)->pt_id;
        $userPtId = optional($leave->user->profile)->pt_id;

        // Logika: Jika Supervisor punya data PT, maka User yg dilihat WAJIB PT-nya sama.
        // Jika data PT kosong (null), kita anggap false agar aman.
        $isSamePt = ($myPtId && $userPtId) ? ($myPtId === $userPtId) : false;

        // Jika salah satu syarat tidak terpenuhi, blokir akses (403 Forbidden)
        abort_unless($isSameDivision && $isSamePt, 403, 'Akses Ditolak: Karyawan berbeda Divisi atau PT.');
    }
}