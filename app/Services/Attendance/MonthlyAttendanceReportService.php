<?php

namespace App\Services\Attendance;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyAttendanceReportService
{
    public const CATEGORIES = [
        'TELAT' => 'Telat', 'IZIN_TELAT' => 'Izin Telat', 'CUTI' => 'Cuti',
        'SAKIT' => 'Sakit', 'IZIN' => 'Izin', 'CUTI_KHUSUS' => 'Cuti Khusus', 'OFF_SPV' => 'Libur SPV',
    ];

    public function calculate(array $userIds, Carbon $start, Carbon $end): array
    {
        if (! $userIds) {
            return [];
        }

        $from = $start->toDateString();
        $to = $end->toDateString();
        $result = [];
        $leaveTypes = array_diff(array_keys(self::CATEGORIES), ['TELAT']);
        $days = DB::table('leave_request_days as d')
            ->join('leave_requests as r', 'r.id', '=', 'd.leave_request_id')
            ->whereIn('r.user_id', $userIds)->where('r.status', 'APPROVED')
            ->whereIn('r.type', $leaveTypes)->whereBetween('d.leave_date', [$from, $to])
            ->select('r.user_id', 'r.type', 'r.id', 'r.status', 'd.leave_date')->get();
        foreach ($days as $day) {
            $date = $day->leave_date;
            $result[$day->user_id][$day->type][$date]['date'] = $date;
            $result[$day->user_id][$day->type][$date]['references'][] = ['id' => $day->id, 'type' => $day->type, 'status' => $day->status];
        }

        $events = DB::table('attendance_events')->whereIn('user_id', $userIds)
            ->where('event_type', 'CLOCK_IN')
            ->where(function ($query) use ($from, $to, $start, $end) {
                $query->whereBetween('work_date', [$from, $to])->orWhere(function ($query) use ($start, $end) {
                    $query->whereNull('work_date')->whereBetween('occurred_at', [$start->copy()->subDays(2)->startOfDay()->toDateTimeString(), $end->copy()->addDays(2)->endOfDay()->toDateTimeString()]);
                });
            })->orderBy('occurred_at')->orderBy('id')->get();
        $linkedIds = $events->pluck('legacy_attendance_id')->filter()->all();
        $candidates = [];
        foreach ($events as $event) {
            if ($event->approval_status === 'REJECTED') {
                continue;
            }
            if (($event->schedule_is_holiday ?? null) == 1) {
                continue;
            }
            $needsApproval = $event->type === 'DINAS_LUAR' || ($event->type === 'WFO' && $event->is_flexible && $event->is_outside_reference_location !== 0 && $event->is_outside_reference_location !== '0');
            if ($needsApproval && $event->approval_status !== 'APPROVED') {
                continue;
            }
            $wita = $event->is_wita;
            if ($wita === null) {
                $result[$event->user_id]['incomplete'] = true;
                if ($event->work_date && $event->work_date >= $from && $event->work_date <= $to) {
                    $candidates[$event->user_id][$event->work_date][] = ['instant' => $event->occurred_at, 'clock_in' => null, 'start' => null];
                }
                continue;
            }
            $local = Carbon::parse($event->occurred_at, config('app.timezone'))->timezone($wita ? 'Asia/Makassar' : 'Asia/Jakarta');
            if (! $event->work_date && $event->is_overnight !== 0 && $event->is_overnight !== '0') {
                $result[$event->user_id]['incomplete'] = true;
                continue;
            }
            $date = $event->work_date ?? $local->toDateString();
            if ($date < $from || $date > $to) {
                continue;
            }
            $candidates[$event->user_id][$date][] = ['instant' => $event->occurred_at, 'clock_in' => $local->toDateTimeString(), 'start' => $event->shift_start_time];
        }

        $legacy = DB::table('attendances')->whereIn('user_id', $userIds)->whereBetween('date', [$from, $to])
            ->whereNotNull('clock_in_at')->get();
        foreach ($legacy as $row) {
            if (in_array($row->id, $linkedIds) || $row->approval_status === 'REJECTED' || ($row->type === 'DINAS_LUAR' && $row->approval_status !== 'APPROVED')) {
                continue;
            }
            $result[$row->user_id]['incomplete'] = true;
            $candidates[$row->user_id][$row->date][] = ['instant' => $row->clock_in_at, 'clock_in' => null, 'start' => null];
        }
        foreach ($candidates as $userId => $dates) {
            foreach ($dates as $date => $entries) {
                if (isset($result[$userId]['IZIN_TELAT'][$date])) {
                    continue;
                }
                usort($entries, fn ($a, $b) => strcmp($a['instant'], $b['instant']));
                $first = $entries[0];
                if (! $first['start'] || ! $first['clock_in']) {
                    $result[$userId]['incomplete'] = true;
                } elseif ($first['clock_in'] > $date.' '.$first['start']) {
                    $result[$userId]['TELAT'][$date] = ['date' => $date, 'start' => $first['start'], 'clock_in' => $first['clock_in']];
                }
            }
        }
        return $result;
    }
}
