<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
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
            ->paginate(20);

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
        $user = Auth::user();

        if (! $user->direct_supervisor_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pengajuan lembur tidak dapat dibuat karena supervisor langsung belum diatur. Hubungi HRD.');
        }

        $validated = $request->validate([
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|different:start_time',
            'description' => 'required|string|max:5000',
        ], [
            'end_time.different' => 'Jam selesai lembur tidak boleh sama dengan jam mulai.',
        ]);

        $durationMinutes = $this->calculateDurationMinutes($validated['start_time'], $validated['end_time']);

        OvertimeRequest::create([
            'user_id' => $user->id,
            'overtime_date' => $validated['overtime_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $durationMinutes,
            'description' => $validated['description'],
            'status' => OvertimeRequest::STATUS_PENDING_SUPERVISOR,
        ]);

        return redirect()->route('overtime-requests.index')
            ->with('success', 'Pengajuan lembur berhasil dibuat.');
    }
    public function update(Request $request, OvertimeRequest $overtimeRequest)
    {
        $user = Auth::user();
        $isOwner = $user->id === $overtimeRequest->user_id;

        $roleStr = strtoupper($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role);
        $isHRD = in_array($roleStr, ['HRD', 'HR STAFF', 'MANAGER']);

        if (!$isOwner && !$isHRD) abort(403, 'Anda tidak berhak mengubah data ini.');

        if ($isOwner && !$isHRD) {
            if ($overtimeRequest->status !== OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                return redirect()->back()->with('error', 'Pengajuan sudah diproses, tidak dapat diubah sendiri. Hubungi supervisor atau HRD untuk koreksi data.');
            }
        }

        $validated = $request->validate([
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|different:start_time',
            'description' => 'required|string|max:5000',
        ], [
            'end_time.different' => 'Jam selesai lembur tidak boleh sama dengan jam mulai.',
        ]);

        $durationMinutes = $this->calculateDurationMinutes($validated['start_time'], $validated['end_time']);

        $overtimeRequest->update([
            'overtime_date' => $validated['overtime_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $durationMinutes,
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Data lembur berhasil diperbarui.');
    }

    private function calculateDurationMinutes(string $startTime, string $endTime): int
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', '2000-01-01 '.$startTime);
        $end = Carbon::createFromFormat('Y-m-d H:i', '2000-01-01 '.$endTime);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end);
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
