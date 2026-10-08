<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HRAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $dateStart = $request->query('date_start');
        $dateEnd = $request->query('date_end');
        $status = $request->query('status');
        $completionStatus = $request->query('completion_status');
        $shiftType = $request->query('shift_type');
        $q = $request->query('q');

        if (! $dateStart && ! $dateEnd) {
            $today = now()->toDateString();
            $dateStart = $today;
            $dateEnd = $today;
        } elseif ($dateStart && ! $dateEnd) {
            $dateEnd = $dateStart;
        } elseif (! $dateStart && $dateEnd) {
            $dateStart = $dateEnd;
        }

        $query = Attendance::with([
            'user',
            'shift',
            'employeeShift', // penting supaya HR bisa lihat shift pada hari itu
        ])->orderBy('clock_in_at');

        if ($dateStart && $dateEnd) {
            $from = Carbon::parse($dateStart)->toDateString();
            $to = Carbon::parse($dateEnd)->toDateString();

            if ($from > $to) {
                $tmp = $from;
                $from = $to;
                $to = $tmp;
            }

            $query->whereBetween('date', [$from, $to]);
            $dateStart = $from;
            $dateEnd = $to;
        }

        if ($status === 'TERLAMBAT' || $status === 'HADIR') {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        $validCompletionStatuses = [
            Attendance::COMPLETION_OPEN,
            Attendance::COMPLETION_CLOSED,
            Attendance::COMPLETION_MISSED_CLOCK_OUT,
            Attendance::COMPLETION_LATE_CLOCK_OUT,
        ];
        if (in_array($completionStatus, $validCompletionStatuses, true)) {
            $query->where('completion_status', $completionStatus);
        } else {
            $completionStatus = null;
        }

        if ($shiftType === 'overnight') {
            $query->whereColumn('normal_end_time', '<=', 'normal_start_time');
        } elseif ($shiftType === 'regular') {
            $query->whereColumn('normal_end_time', '>', 'normal_start_time');
        } else {
            $shiftType = null;
        }

        if ($q) {
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->whereNormalizedNameContains((string) $q);
            });
        }

        $items = $query->paginate(20)->appends([
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'status' => $status,
            'completion_status' => $completionStatus,
            'shift_type' => $shiftType,
            'q' => $q,
        ]);

        return view('hr.attendances.index', [
            'items' => $items,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'status' => $status,
            'completion_status' => $completionStatus,
            'shiftType' => $shiftType,
            'q' => $q,
        ]);
    }
}
