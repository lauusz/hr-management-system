<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ApprovalAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureApprovalSchema();
        $filters = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'event_type' => ['nullable', 'in:CLOCK_IN,CLOCK_OUT'],
            'tab' => ['nullable', 'in:flexible,remote'],
        ]);
        $name = trim($filters['name'] ?? '');
        $tab = $filters['tab'] ?? 'flexible';

        $pendingAttendances = AttendanceEvent::query()
            ->with(['user', 'location'])
            ->pendingApproval()
            ->where('type', $tab === 'remote' ? 'DINAS_LUAR' : 'WFO')
            ->when($name !== '', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->whereNormalizedNameContains($name)))
            ->when($filters['event_type'] ?? null, fn ($query, $eventType) => $query->where('event_type', $eventType))
            ->latest('occurred_at')
            ->get();

        return view('hr.approval_attendance.index', compact('pendingAttendances', 'filters', 'tab'));
    }

    public function approve(Request $request, AttendanceEvent $event)
    {
        $user = Auth::user();
        if (! ($user && $user->isHR())) {
            return back()->with('error', 'Anda tidak berhak menyetujui absensi ini.');
        }

        $validated = $request->validate([
            'hrd_note' => ['nullable', 'string', 'max:255'],
        ]);

        return $this->updatePending($event, [
            'approval_status' => 'APPROVED',
            'approved_by' => $user->id,
            'hrd_note' => $validated['hrd_note'] ?? null,
        ], 'Absensi berhasil disetujui.');
    }

    public function bulkApprove(Request $request)
    {
        $user = Auth::user();
        if (! ($user && $user->isHR())) {
            return back()->with('error', 'Anda tidak berhak menyetujui absensi ini.');
        }

        $this->ensureApprovalSchema();

        $validated = $request->validate([
            'attendance_ids' => ['required', 'array', 'min:1'],
            'attendance_ids.*' => ['integer', 'distinct', 'exists:attendance_events,id'],
            'hrd_notes' => ['nullable', 'array'],
            'hrd_notes.*' => ['nullable', 'string', 'max:255'],
        ]);

        $updated = DB::transaction(function () use ($validated, $user) {
            return collect($validated['attendance_ids'])->sum(function ($attendanceId) use ($validated, $user) {
                return AttendanceEvent::query()
                    ->whereKey($attendanceId)
                    ->pendingApproval()
                    ->update([
                        'approval_status' => 'APPROVED',
                        'approved_by' => $user->id,
                        'hrd_note' => $validated['hrd_notes'][$attendanceId] ?? null,
                    ]);
            });
        });

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
        $this->ensureApprovalSchema();
        $updated = AttendanceEvent::query()
            ->whereKey($event)
            ->pendingApproval()
            ->update($attributes);

        return $updated === 1
            ? back()->with('success', $success)
            : back()->with('error', 'Absensi ini tidak tersedia untuk persetujuan.');
    }

    private function ensureApprovalSchema(): void
    {
        abort_unless(
            Schema::hasColumn('attendance_events', 'is_flexible')
                && Schema::hasColumn('attendance_events', 'is_outside_reference_location')
                && Schema::hasColumn('attendance_events', 'hrd_note'),
            503,
            'Pembaruan absensi belum tersedia. Hubungi HR atau IT.'
        );
    }
}
