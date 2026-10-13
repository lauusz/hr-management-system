<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'submitted_range' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:100'],
            'clock_in' => ['nullable', 'in:ada,tidak_ada'],
            'clock_out' => ['nullable', 'in:ada,tidak_ada'],
        ]);
        $name = trim($filters['name'] ?? '');
        $submittedRange = trim($filters['submitted_range'] ?? '');

        $pendingAttendancesQuery = Attendance::with(['user', 'shift', 'location'])
            ->where('approval_status', 'PENDING')
            ->when($name !== '', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$name.'%')))
            ->when($filters['clock_in'] ?? null, fn ($query, $clockIn) => $clockIn === 'ada' ? $query->whereNotNull('clock_in_at') : $query->whereNull('clock_in_at'))
            ->when($filters['clock_out'] ?? null, fn ($query, $clockOut) => $clockOut === 'ada' ? $query->whereNotNull('clock_out_at') : $query->whereNull('clock_out_at'));

        if ($submittedRange !== '') {
            try {
                $dates = preg_split('/\s+(to|sampai)\s+/i', $submittedRange);
                $from = Carbon::parse(trim($dates[0]))->startOfDay();
                $to = Carbon::parse(trim($dates[1] ?? $dates[0]))->endOfDay();
                $pendingAttendancesQuery->whereBetween('created_at', [$from, $to]);
            } catch (\Throwable) {
                // Input manual yang tidak valid diabaikan seperti filter master cuti.
            }
        }

        $pendingAttendances = $pendingAttendancesQuery
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hr.approval_attendance.index', compact('pendingAttendances', 'filters'));
    }

    public function approve(Request $request, Attendance $attendance)
    {
        $user = Auth::user();
        if (! ($user && $user->isHR())) {
            return redirect()->back()->with('error', 'Anda tidak berhak menyetujui absensi ini.');
        }

        if ($attendance->approval_status !== 'PENDING') {
            return back()->with('error', 'Absensi ini tidak dalam status menunggu.');
        }

        $updated = Attendance::query()
            ->whereKey($attendance)
            ->where('approval_status', 'PENDING')
            ->update([
                'approval_status' => 'APPROVED',
                'approved_by' => $user->id,
            ]);

        if ($updated === 0) {
            return back()->with('error', 'Absensi ini tidak dalam status menunggu.');
        }

        return back()->with('success', 'Absensi berhasil disetujui.');
    }

    public function bulkApprove(Request $request)
    {
        $user = Auth::user();
        if (! ($user && $user->isHR())) {
            return redirect()->back()->with('error', 'Anda tidak berhak menyetujui absensi ini.');
        }

        $validated = $request->validate([
            'attendance_ids' => ['required', 'array', 'min:1'],
            'attendance_ids.*' => ['integer', 'distinct', 'exists:attendances,id'],
        ]);

        $updated = Attendance::query()
            ->whereIn('id', $validated['attendance_ids'])
            ->where('approval_status', 'PENDING')
            ->update([
                'approval_status' => 'APPROVED',
                'approved_by' => $user->id,
            ]);

        if ($updated === 0) {
            return back()->with('error', 'Absensi yang dipilih tidak lagi dalam status menunggu.');
        }

        return back()->with('success', $updated.' absensi berhasil disetujui.');
    }

    public function reject(Request $request, Attendance $attendance)
    {
        $user = Auth::user();
        if (! ($user && $user->isHR())) {
            return redirect()->back()->with('error', 'Anda tidak berhak menolak absensi ini.');
        }

        if ($attendance->approval_status !== 'PENDING') {
            return back()->with('error', 'Absensi ini tidak dalam status menunggu.');
        }

        $request->validate([
            'rejection_note' => 'required|string|max:255',
        ]);

        $updated = Attendance::query()
            ->whereKey($attendance)
            ->where('approval_status', 'PENDING')
            ->update([
                'approval_status' => 'REJECTED',
                'rejection_note' => $request->rejection_note,
                'approved_by' => $user->id,
                'status' => 'REJECTED',
            ]);

        if ($updated === 0) {
            return back()->with('error', 'Absensi ini tidak dalam status menunggu.');
        }

        return back()->with('success', 'Absensi ditolak.');
    }
}
