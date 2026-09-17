<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorOvertimeController extends Controller
{
    /**
     * Menampilkan rekap data lembur bawahan.
     */
    public function master(Request $request)
    {
        /** @var \App\Models\User $me */
        $me = Auth::user();

        $query = OvertimeRequest::with(['user.profile.pt', 'user.division'])
            ->whereHas('user', function ($q) use ($me) {
                $q->where('direct_supervisor_id', $me->id);
            })
            ->orderByDesc('created_at');

        // Filter Tanggal
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('overtime_date', [$dates[0], $dates[1]]);
            } else {
                $query->whereDate('overtime_date', $dates[0]);
            }
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search Karyawan
        if ($request->filled('q')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%');
            });
        }

        $overtimes = $query->paginate(20);
        $statusOptions = [
            OvertimeRequest::STATUS_PENDING_SUPERVISOR,
            OvertimeRequest::STATUS_APPROVED_SUPERVISOR,
            OvertimeRequest::STATUS_REJECTED,
            OvertimeRequest::STATUS_CANCELLED,
        ];

        return view('supervisor.overtime_requests.master', compact('overtimes', 'statusOptions'));
    }

    /**
     * Menampilkan daftar pengajuan lembur bawahan (Satu Divisi & Satu PT)
     */
    public function index()
    {
        /** @var \App\Models\User $me */
        $me = Auth::user();
        
        $overtimes = OvertimeRequest::where('status', OvertimeRequest::STATUS_PENDING_SUPERVISOR)
            ->whereHas('user', function ($query) use ($me) {
                $query->where('direct_supervisor_id', $me->id);
            })
            ->with(['user.profile.pt', 'user.division'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $subordinates = \App\Models\User::where('direct_supervisor_id', $me->id)
            ->with(['division', 'position'])
            ->orderBy('name')
            ->get();

        return view('supervisor.overtime_requests.index', compact('overtimes', 'subordinates'));
    }

    /**
     * Menampilkan detail pengajuan lembur.
     */
    public function show(OvertimeRequest $overtimeRequest)
    {
        $redirect = $this->authorizeSupervisor($overtimeRequest);
        if ($redirect) {
            return $redirect;
        }
        return view('supervisor.overtime_requests.show', compact('overtimeRequest'));
    }

    /**
     * Setujui pengajuan lembur.
     */
    public function approve(OvertimeRequest $overtimeRequest)
    {
        $redirect = $this->authorizeSupervisor($overtimeRequest);
        if ($redirect) {
            return $redirect;
        }

        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
            return back()->with('error', 'Status tidak valid.');
        }

        $overtimeRequest->update([
            'status' => OvertimeRequest::STATUS_APPROVED_SUPERVISOR,
            'approved_by_supervisor_id' => Auth::id(),
            'approved_by_supervisor_at' => now(),
        ]);

        return redirect()->route('supervisor.overtime-requests.index')
            ->with('success', 'Pengajuan lembur berhasil disetujui.');
    }

    /**
     * Tolak pengajuan lembur.
     */
    public function reject(Request $request, OvertimeRequest $overtimeRequest)
    {
        $redirect = $this->authorizeSupervisor($overtimeRequest);
        if ($redirect) {
            return $redirect;
        }

        $request->validate([
            'rejection_note' => 'required|string|max:255',
        ]);

        if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
            return back()->with('error', 'Status tidak valid.');
        }

        $overtimeRequest->update([
            'status' => OvertimeRequest::STATUS_REJECTED,
            'rejected_by_id' => Auth::id(),
            'rejection_note' => $request->rejection_note,
        ]);

        return redirect()->route('supervisor.overtime-requests.index')
            ->with('success', 'Pengajuan lembur ditolak.');
    }

    private function authorizeSupervisor(OvertimeRequest $overtime)
    {
        $me = Auth::user();
        if ($overtime->user->direct_supervisor_id !== $me->id) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda bukan atasan langsung karyawan ini.');
        }
        return null;
    }
}
