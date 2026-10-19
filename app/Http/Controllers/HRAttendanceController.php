<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HRAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $rangeStart = Carbon::parse($request->query('date_start') ?: $request->query('date_end') ?: now())->startOfDay();
        $rangeEnd = Carbon::parse($request->query('date_end') ?: $request->query('date_start') ?: now())->endOfDay();

        if ($rangeStart->gt($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
        }

        $q = trim((string) $request->query('q'));
        $items = AttendanceEvent::query()
            ->with(['user.employeeShift.shift.days', 'location'])
            ->whereBetween('occurred_at', [$rangeStart, $rangeEnd])
            ->whereHas('user', function ($userQuery) use ($q) {
                $userQuery->active();

                if ($q !== '') {
                    $userQuery->whereNormalizedNameContains($q);
                }
            })
            ->latest('created_at')
            ->latest('id')
            ->get();

        $items->each(function (AttendanceEvent $event): void {
            $shiftDay = $event->user?->employeeShift?->shift?->days
                ->first(fn ($day) => (int) $day->day_of_week === $event->occurred_at->dayOfWeekIso && ! $day->is_holiday);

            $event->setAttribute('work_schedule', $shiftDay?->start_time && $shiftDay?->end_time
                ? substr($shiftDay->start_time, 0, 5).'–'.substr($shiftDay->end_time, 0, 5)
                : '-');

            $lateMinutes = 0;

            if ($event->event_type === AttendanceEvent::CLOCK_IN && $shiftDay?->start_time) {
                $shiftStart = Carbon::parse($event->occurred_at->toDateString().' '.$shiftDay->start_time);
                $lateMinutes = max(0, (int) floor($shiftStart->diffInSeconds($event->occurred_at, false) / 60));
            }

            $event->setAttribute('late_label', match (true) {
                $lateMinutes === 0 => '-',
                $lateMinutes < 60 => $lateMinutes.' min',
                default => intdiv($lateMinutes, 60).'j'.($lateMinutes % 60 ? ' '.($lateMinutes % 60).'m' : ''),
            });
        });

        return view('hr.attendances.index', [
            'items' => $items,
            'date_start' => $rangeStart->toDateString(),
            'date_end' => $rangeEnd->toDateString(),
            'q' => $q,
        ]);
    }

    public function moveEventType(AttendanceEvent $event)
    {
        $eventType = $event->event_type === AttendanceEvent::CLOCK_IN
            ? AttendanceEvent::CLOCK_OUT
            : AttendanceEvent::CLOCK_IN;
        $updated = AttendanceEvent::query()
            ->whereKey($event)
            ->where('approval_status', 'PENDING')
            ->update([
                'event_type' => $eventType,
            ]);

        return $updated === 1
            ? back()->with('success', 'Tipe event absensi berhasil diperbarui.')
            : back()->withErrors(['attendance' => 'Event absensi yang sudah diputus tidak dapat dikoreksi.']);
    }
}
