<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
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
            'event_type' => ['nullable', 'in:CLOCK_IN,CLOCK_OUT'],
        ]);
        $name = trim($filters['name'] ?? '');
        $submittedRange = trim($filters['submitted_range'] ?? '');

        $pendingAttendances = AttendanceEvent::query()
            ->with(['user', 'location'])
            ->where('approval_status', 'PENDING')
            ->when($name !== '', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->whereNormalizedNameContains($name)))
            ->when($filters['event_type'] ?? null, fn ($query, $eventType) => $query->where('event_type', $eventType))
            ->when($submittedRange !== '', function ($query) use ($submittedRange) {
                try {
                    $dates = preg_split('/\s+(to|sampai)\s+/i', $submittedRange);
                    $query->whereBetween('occurred_at', [
                        Carbon::parse(trim($dates[0]))->startOfDay(),
                        Carbon::parse(trim($dates[1] ?? $dates[0]))->endOfDay(),
                    ]);
                } catch (\Throwable) {
                    // Input manual yang tidak valid diabaikan seperti filter master cuti.
                }
            })
            ->latest('occurred_at')
            ->get();

        return view('hr.approval_attendance.index', compact('pendingAttendances', 'filters'));
    }

    public function approve(AttendanceEvent $event)
    {
        $user = Auth::user();
        if (! ($user && $user->isHR())) {
            return back()->with('error', 'Anda tidak berhak menyetujui absensi ini.');
        }

        return $this->updatePending($event, ['approval_status' => 'APPROVED', 'approved_by' => $user->id], 'Absensi berhasil disetujui.');
    }

    public function bulkApprove(Request $request)
    {
        $user = Auth::user();
        if (! ($user && $user->isHR())) {
            return back()->with('error', 'Anda tidak berhak menyetujui absensi ini.');
        }

        $validated = $request->validate([
            'attendance_ids' => ['required', 'array', 'min:1'],
            'attendance_ids.*' => ['integer', 'distinct', 'exists:attendance_events,id'],
        ]);

        $updated = AttendanceEvent::query()
            ->whereIn('id', $validated['attendance_ids'])
            ->where('approval_status', 'PENDING')
            ->update(['approval_status' => 'APPROVED', 'approved_by' => $user->id]);

        return $updated > 0
            ? back()->with('success', $updated.' absensi berhasil disetujui.')
            : back()->with('error', 'Absensi yang dipilih tidak lagi dalam status menunggu.');
    }

    public function moveClockSide(AttendanceEvent $event)
    {
        $user = Auth::user();
        if (! ($user && $user->isHR())) {
            return back()->with('error', 'Anda tidak berhak mengoreksi absensi ini.');
        }

        $eventType = $event->event_type === AttendanceEvent::CLOCK_IN
            ? AttendanceEvent::CLOCK_OUT
            : AttendanceEvent::CLOCK_IN;

        return $this->updatePending($event, ['event_type' => $eventType], 'Tipe event absensi berhasil diperbarui.');
    }

    public function reject(Request $request, AttendanceEvent $event)
    {
        $user = Auth::user();
        if (! ($user && $user->isHR())) {
            return back()->with('error', 'Anda tidak berhak menolak absensi ini.');
        }

        $validated = $request->validate(['rejection_note' => ['required', 'string', 'max:255']]);

        return $this->updatePending($event, [
            'approval_status' => 'REJECTED',
            'rejection_note' => $validated['rejection_note'],
            'approved_by' => $user->id,
        ], 'Absensi ditolak.');
    }

    private function updatePending(AttendanceEvent $event, array $attributes, string $success)
    {
        $updated = AttendanceEvent::query()
            ->whereKey($event)
            ->where('approval_status', 'PENDING')
            ->update($attributes);

        return $updated === 1
            ? back()->with('success', $success)
            : back()->with('error', 'Absensi ini tidak dalam status menunggu.');
    }
}
