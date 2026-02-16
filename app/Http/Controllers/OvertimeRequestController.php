<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    /**
     * Menampilkan daftar pengajuan lembur saya.
     */
    public function index()
    {
        $userId = Auth::id();
        
        $overtimes = OvertimeRequest::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(100);

        return view('overtime_requests.index', compact('overtimes'));
    }

    /**
     * Form pengajuan lembur baru.
     */
    public function create()
    {
        return view('overtime_requests.create');
    }

    /**
     * Simpan pengajuan lembur.
     */
    public function store(Request $request)
    {
        $request->validate([
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'required|string|max:5000',
        ]);

        $userId = Auth::id();
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        
        // Hitung durasi dalam menit (pastikan absolute)
        $durationMinutes = abs($endTime->diffInMinutes($startTime));


        $user = Auth::user();
        $initialStatus = OvertimeRequest::STATUS_PENDING_SUPERVISOR; // Default

        // LOGIC PENENTUAN STATUS AWAL
        // 1. Employee -> Cek Supervisor
        if ($user->isEmployee()) {
            if (!$user->direct_supervisor_id) {
                // Tidak punya SPV -> Langsung ke HR
                $initialStatus = OvertimeRequest::STATUS_APPROVED_SUPERVISOR;
            }
        }
        // 2. Supervisor -> Cek Manager
        elseif ($user->isSupervisor()) {
            if ($user->manager_id) {
                // Punya Manager -> Approval Manager (dianggap flow SPV)
                $initialStatus = OvertimeRequest::STATUS_PENDING_SUPERVISOR;
                // Note: Manager akan dianggap sebagai 'Supervisor Approver' di sistem ini
            } else {
                // Tidak punya Manager -> Langsung ke HR
                $initialStatus = OvertimeRequest::STATUS_APPROVED_SUPERVISOR;
            }
        }
        // 3. Manager -> Langsung ke HR
        elseif ($user->isManager()) {
            $initialStatus = OvertimeRequest::STATUS_APPROVED_SUPERVISOR;
        }

        OvertimeRequest::create([
            'user_id' => $userId,
            'overtime_date' => $request->overtime_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $durationMinutes,
            'description' => $request->description,
            'status' => $initialStatus,
        ]);

        return redirect()->route('overtime-requests.index')
            ->with('success', 'Pengajuan lembur berhasil dibuat.');
    }
    public function update(Request $request, OvertimeRequest $overtimeRequest)
    {
        $user = Auth::user();
        $isOwner = $user->id === $overtimeRequest->user_id;

        // Cek apakah User adalah HRD / Manager
        // Note: Logic isHRD() di model User perlu dicek, atau manual check role
        $roleStr = strtoupper($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role);
        $isHRD = in_array($roleStr, ['HRD', 'HR STAFF', 'MANAGER']);

        if (!$isOwner && !$isHRD) abort(403, 'Anda tidak berhak mengubah data ini.');

        // Owner hanya bisa edit jika status masih pending
        if ($isOwner && !$isHRD) {
            if (!in_array($overtimeRequest->status, [
                OvertimeRequest::STATUS_PENDING_SUPERVISOR, 
                // Note: Kalau sudah approved SPV (alias Pending HR), user biasa gabisa edit
            ])) {
                return back()->withErrors('Pengajuan sudah diproses, tidak dapat diubah sendiri. Hubungi HRD.');
            }
        }

        $request->validate([
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'required|string|max:5000',
        ]);

        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        $durationMinutes = abs($endTime->diffInMinutes($startTime));

        $overtimeRequest->update([
            'overtime_date' => $request->overtime_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_minutes' => $durationMinutes,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Data lembur berhasil diperbarui.');
    }

    public function destroy(OvertimeRequest $overtimeRequest)
    {
        $user = Auth::user();
        $isOwner = $user->id === $overtimeRequest->user_id;
        $roleStr = strtoupper($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role);
        $isHRD = in_array($roleStr, ['HRD', 'HR STAFF', 'MANAGER']);

        if (!$isOwner && !$isHRD) abort(403, 'Anda tidak berhak menghapus data ini.');

        // Owner hanya bisa hapus jika status masih pending supervisor
        if ($isOwner && !$isHRD) {
             if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                return back()->with('error', 'Hanya pengajuan yang belum diproses atasan yang bisa dibatalkan.');
            }
        }
        
        // Update status to CANCELLED instead of deleting
        $overtimeRequest->update([
            'status' => OvertimeRequest::STATUS_CANCELLED
        ]);

        if ($isHRD && !$isOwner) return redirect()->route('hr.overtime-requests.master')->with('success', 'Pengajuan lembur berhasil dibatalkan oleh HRD.');
        return redirect()->route('overtime-requests.index')->with('success', 'Pengajuan lembur berhasil dibatalkan.');
    }
}
