<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class HRAttendanceController extends Controller
{
    public function filter(Request $request)
    {
        if ($request->input('action') === 'reset') {
            $request->session()->forget('hr_attendance_filters');

            return redirect()->route('hr.attendances.index');
        }

        $filters = $request->validate([
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d'],
            'shift_id' => [
                'nullable',
                'integer',
                Rule::exists('shifts', 'id')->where('is_active', true),
            ],
            'status' => ['nullable', Rule::in(['HADIR', 'TERLAMBAT', 'DINAS_LUAR'])],
            'completion_status' => [
                'nullable',
                Rule::in([
                    Attendance::COMPLETION_OPEN,
                    Attendance::COMPLETION_CLOSED,
                    Attendance::COMPLETION_MISSED_CLOCK_OUT,
                    Attendance::COMPLETION_LATE_CLOCK_OUT,
                ]),
            ],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $request->session()->put(
            'hr_attendance_filters',
            array_filter($filters, fn ($value) => $value !== null && $value !== '')
        );

        return redirect()->route('hr.attendances.index');
    }

    public function index(Request $request)
    {
        $filterKeys = ['date_start', 'date_end', 'status', 'shift_id', 'completion_status', 'q'];
        $queryFilters = $request->only($filterKeys);
        $hasQueryFilters = collect($queryFilters)
            ->contains(fn ($value) => $value !== null && $value !== '');
        $filters = $hasQueryFilters
            ? $queryFilters
            : $request->session()->get('hr_attendance_filters', []);

        $dateStart = $filters['date_start'] ?? null;
        $dateEnd   = $filters['date_end'] ?? null;
        $status    = $filters['status'] ?? null;
        $shiftId   = $filters['shift_id'] ?? null;
        $completionStatus = $filters['completion_status'] ?? null;
        $q         = $filters['q'] ?? null;

        $shifts = Shift::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        if (!$dateStart && !$dateEnd) {
            $today     = now()->toDateString();
            $dateStart = $today;
            $dateEnd   = $today;
        } elseif ($dateStart && !$dateEnd) {
            $dateEnd = $dateStart;
        } elseif (!$dateStart && $dateEnd) {
            $dateStart = $dateEnd;
        }

        $query = Attendance::with([
            'user',
            'shift',
            'employeeShift', // penting supaya HR bisa lihat shift pada hari itu
        ])->orderBy('clock_in_at');

        if ($dateStart && $dateEnd) {
            $from = Carbon::parse($dateStart)->toDateString();
            $to   = Carbon::parse($dateEnd)->toDateString();

            if ($from > $to) {
                $tmp  = $from;
                $from = $to;
                $to   = $tmp;
            }

            $query->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to);
            $dateStart = $from;
            $dateEnd   = $to;
        }

        if ($status === 'TERLAMBAT' || $status === 'HADIR') {
            $query->where('type', 'WFO')->where('status', $status);
        } elseif ($status === 'DINAS_LUAR') {
            $query->where('type', 'DINAS_LUAR');
        } else {
            $status = null;
        }

        $validShiftIds = $shifts->modelKeys();
        if ($shiftId !== null && in_array((int) $shiftId, $validShiftIds, true)) {
            $shiftId = (int) $shiftId;
            $query->where('shift_id', $shiftId);
        } else {
            $shiftId = null;
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

        if ($q) {
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%');
            });
        }

        $items = $query->paginate(20);

        if ($hasQueryFilters) {
            $items->appends($queryFilters);
        }

        return view('hr.attendances.index', [
            'items'             => $items,
            'date_start'        => $dateStart,
            'date_end'          => $dateEnd,
            'status'            => $status,
            'shift_id'          => $shiftId,
            'shifts'            => $shifts,
            'completion_status' => $completionStatus,
            'q'                 => $q,
        ]);
    }
}
