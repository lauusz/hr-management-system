<?php

namespace App\Services\Attendance;

use App\Models\AttendanceEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceHistoryService
{
    /**
     * @return Collection<int, object>
     */
    public function getHistory(int $userId, Carbon $startDate, Carbon $endDate): Collection
    {
        $events = AttendanceEvent::query()
            ->with(['attendanceShift', 'location'])
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [
                $startDate->copy()->subDay()->startOfDay(),
                $endDate->copy()->addDay()->endOfDay(),
            ])
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        return $this->buildSessions($events)
            ->filter(fn (object $session) => $session->work_date->betweenIncluded(
                $startDate->toDateString(),
                $endDate->toDateString()
            ))
            ->sortByDesc('latest_occurred_at')
            ->values();
    }

    /**
     * @param  Collection<int, AttendanceEvent>  $events
     * @return Collection<int, object>
     */
    private function buildSessions(Collection $events): Collection
    {
        $sessions = collect();
        $eventsByWorkDate = [];

        foreach ($events as $event) {
            $workDate = $this->resolveWorkDate($event);
            $key = $workDate->toDateString();
            $eventsByWorkDate[$key] ??= ['work_date' => $workDate, 'events' => collect()];
            $eventsByWorkDate[$key]['events']->push($event);
        }

        foreach ($eventsByWorkDate as $entry) {
            $sessions->push($this->formatSession($entry['events'], $entry['work_date']));
        }

        return $sessions;
    }

    /**
     * @param  Collection<int, AttendanceEvent>  $events
     */
    private function formatSession(Collection $events, Carbon $workDate): object
    {
        $clockIns = $events->where('event_type', AttendanceEvent::CLOCK_IN)->values();
        $clockOuts = $events->where('event_type', AttendanceEvent::CLOCK_OUT)->values();
        $firstClockIn = $clockIns->first();
        $lastClockOut = $clockOuts->last();
        $anchorEvent = $firstClockIn ?? $lastClockOut ?? $events->first();

        return (object) [
            'work_date' => $workDate,
            'events' => $events,
            'clock_in' => $firstClockIn,
            'clock_out' => $lastClockOut,
            'clock_ins' => $clockIns,
            'clock_outs' => $clockOuts,
            'latest_occurred_at' => $events->last()->occurred_at,
            'shift_name' => $anchorEvent?->shift_name_snapshot ?? ($anchorEvent?->schedule_is_holiday ? 'Libur' : ($anchorEvent?->attendanceShift?->name ?? 'Reguler')),
            'work_schedule' => $this->formatScheduleText($anchorEvent),
            'has_pending' => $events->contains(fn ($e) => $e->approval_status === 'PENDING'),
            'has_rejected' => $events->contains(fn ($e) => $e->approval_status === 'REJECTED'),
            'hrd_notes' => $events->pluck('hrd_note')->filter()->unique()->values(),
            'user_notes' => $events->pluck('notes')->filter()->unique()->values(),
        ];
    }

    private function formatScheduleText(?AttendanceEvent $event): string
    {
        if ($event?->schedule_is_holiday) {
            return 'Libur';
        }

        if (! $event || ! $event->shift_start_time || ! $event->shift_end_time) {
            return '-';
        }

        $start = substr((string) $event->shift_start_time, 0, 5);
        $end = substr((string) $event->shift_end_time, 0, 5);
        $suffix = $event->is_overnight ? ' (lintas hari)' : '';

        return "{$start} - {$end}{$suffix}";
    }

    private function resolveWorkDate(AttendanceEvent $event): Carbon
    {
        $isOvernight = (bool) ($event->is_overnight ?? $event->attendanceShift?->is_overnight);

        if (! $isOvernight) {
            return $event->local_occurred_at->copy()->startOfDay();
        }

        if ($event->work_date !== null) {
            return $event->work_date->copy()->startOfDay();
        }

        $date = $event->local_occurred_at->copy()->startOfDay();

        if ($event->shift_end_time && $event->local_occurred_at->format('H:i:s') <= $event->shift_end_time) {
            return $date->subDay();
        }

        return $date;
    }
}
