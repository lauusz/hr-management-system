<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalAttendanceController extends Controller
{
    public function index()
    {
        // Hanya ambil yang PENDING
        $pendingAttendances = Attendance::with(['user', 'shift', 'location'])
            ->where('approval_status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hr.approval_attendance.index', compact('pendingAttendances'));
    }

    public function approve(Request $request, Attendance $attendance)
    {
        $user = Auth::user();
        abort_unless($user && $user->isHR(), 403, 'Anda tidak berhak menyetujui absensi ini.');

        if ($attendance->approval_status !== 'PENDING') {
            return back()->with('error', 'Absensi ini tidak dalam status pending.');
        }

        $attendance->update([
            'approval_status' => 'APPROVED',
            'approved_by'     => $user->id,
            'status'          => 'HADIR',
        ]);

        return back()->with('success', 'Absensi berhasil disetujui.');
    }

    public function reject(Request $request, Attendance $attendance)
    {
        $user = Auth::user();
        abort_unless($user && $user->isHR(), 403, 'Anda tidak berhak menolak absensi ini.');

        if ($attendance->approval_status !== 'PENDING') {
            return back()->with('error', 'Absensi ini tidak dalam status pending.');
        }

        $request->validate([
            'rejection_note' => 'required|string|max:255',
        ]);

        $attendance->update([
            'approval_status' => 'REJECTED',
            'rejection_note'  => $request->rejection_note,
            'approved_by'     => $user->id,
            'status'          => 'REJECTED',
        ]);

        return back()->with('success', 'Absensi ditolak.');
    }
}