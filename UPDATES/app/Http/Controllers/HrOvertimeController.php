<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrOvertimeController extends Controller
{
    public function index()
    {
        return redirect()->route('hr.overtime-requests.master');
    }

    public function master(Request $request)
    {
        // Master Data: Tampilkan SEMUA request (History) dengan Filter
        $query = OvertimeRequest::with(['user.profile.pt', 'user.division', 'supervisorApprover', 'hrdApprover'])
            ->orderByDesc('created_at');

        // Filter: Search User Name
        if ($request->filled('q')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%');
            });
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Date Range (overtime_date)
        // Default: Current Month if not filtered
        if (! $request->filled('overtime_date_range')) {
            $startOfMonth = now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = now()->endOfMonth()->format('Y-m-d');
            $request->merge(['overtime_date_range' => "$startOfMonth sampai $endOfMonth"]);
        }

        if ($request->filled('overtime_date_range')) {
            $rangeVal = $request->overtime_date_range;
            $dates = [];

            if (str_contains($rangeVal, ' sampai ')) {
                $dates = explode(' sampai ', $rangeVal);
            } else {
                $dates = explode(' - ', $rangeVal);
            }

            if (count($dates) === 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);
                $query->whereDate('overtime_date', '>=', $startDate)
                    ->whereDate('overtime_date', '<=', $endDate);

                // Determine Title Context
                try {
                    $startObj = \Carbon\Carbon::parse($startDate);
                    $endObj = \Carbon\Carbon::parse($endDate);

                    if ($startObj->format('Y-m') === $endObj->format('Y-m')) {
                        $periodLabel = $startObj->translatedFormat('F Y');
                    } else {
                        $periodLabel = $startObj->translatedFormat('d M') . ' - ' . $endObj->translatedFormat('d M Y');
                    }
                } catch (\Exception $e) {
                    $periodLabel = 'Periode Tertentu';
                }
            } elseif (count($dates) === 1) {
                $query->whereDate('overtime_date', trim($dates[0]));
                $periodLabel = \Carbon\Carbon::parse($dates[0])->translatedFormat('d M Y');
            }
        }

        $overtimes = $query->paginate(20)->withQueryString();

        // Attendance matching untuk halaman yang ditampilkan
        $attendanceMap = $this->buildAttendanceMap($overtimes);
        $recapData = [];
        foreach ($overtimes as $overtime) {
            $key = $overtime->user_id . '|' . $overtime->overtime_date->format('Y-m-d');
            $attendance = $attendanceMap[$key] ?? null;
            $recapData[$overtime->id] = $this->computeRecapData($overtime, $attendance);
        }

        // Data untuk Filter UI
        $statusOptions = [
            OvertimeRequest::STATUS_PENDING_SUPERVISOR,
            OvertimeRequest::STATUS_APPROVED_SUPERVISOR,
            OvertimeRequest::STATUS_REJECTED,
            OvertimeRequest::STATUS_CANCELLED,
        ];

        // Keep input values
        $q = $request->q;
        $status = $request->status;
        $overtimeDateRange = $request->overtime_date_range;

        return view('hr.overtime_requests.master', compact(
            'overtimes',
            'statusOptions',
            'q',
            'status',
            'overtimeDateRange',
            'periodLabel',
            'recapData'
        ));
    }

    public function show(OvertimeRequest $overtimeRequest)
    {
        $attendance = Attendance::where('user_id', $overtimeRequest->user_id)
            ->whereDate('date', $overtimeRequest->overtime_date)
            ->first();

        $recap = $this->computeRecapData($overtimeRequest, $attendance);

        return view('hr.overtime_requests.show', compact('overtimeRequest', 'attendance', 'recap'));
    }

    public function approve(Request $request, OvertimeRequest $overtimeRequest)
    {
        abort(404);
    }

    public function reject(Request $request, OvertimeRequest $overtimeRequest)
    {
        abort(404);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Bangun mapping attendance berdasarkan user_id + date untuk page saat ini.
     * Hindari N+1 query.
     */
    private function buildAttendanceMap($overtimes): array
    {
        if ($overtimes->isEmpty()) {
            return [];
        }

        $userIds = $overtimes->pluck('user_id')->unique()->values()->all();
        $dates = $overtimes->pluck('overtime_date')->map(fn ($d) => $d->format('Y-m-d'))->unique()->values()->all();

        $attendances = Attendance::whereIn('user_id', $userIds)
            ->whereIn('date', $dates)
            ->get();

        $map = [];
        foreach ($attendances as $att) {
            $key = $att->user_id . '|' . $att->date->format('Y-m-d');
            $map[$key] = $att;
        }

        return $map;
    }

    /**
     * Hitung data recap attendance matching untuk satu overtime request.
     *
     * Status matching:
     * 1. Cocok                 -> clock_out_at >= requested_end_at
     * 2. Kurang dari pengajuan -> clock_out_at < requested_end_at
     * 3. Belum clock out       -> attendance ada tapi clock_out_at null
     * 4. Tidak ada absensi     -> attendance tidak ditemukan
     */
    private function computeRecapData(OvertimeRequest $overtime, ?Attendance $attendance): array
    {
        $requestedStart = Carbon::parse(
            $overtime->overtime_date->toDateString() . ' ' . $overtime->start_time->format('H:i:s')
        );
        $requestedEnd = Carbon::parse(
            $overtime->overtime_date->toDateString() . ' ' . $overtime->end_time->format('H:i:s')
        );

        if ($requestedEnd->lt($requestedStart)) {
            $requestedEnd->addDay();
        }

        if (! $attendance) {
            return [
                'requested_start' => $requestedStart,
                'requested_end' => $requestedEnd,
                'status' => 'Tidak ada absensi',
                'status_slug' => 'no_attendance',
                'status_color' => 'gray',
                'clock_in' => null,
                'clock_out' => null,
                'variance_minutes' => null,
            ];
        }

        if (! $attendance->clock_out_at) {
            return [
                'requested_start' => $requestedStart,
                'requested_end' => $requestedEnd,
                'status' => 'Belum clock out',
                'status_slug' => 'missing_clock_out',
                'status_color' => 'yellow',
                'clock_in' => $attendance->clock_in_at,
                'clock_out' => null,
                'variance_minutes' => null,
            ];
        }

        $diffSeconds = $attendance->clock_out_at->timestamp - $requestedEnd->timestamp;
        $varianceMinutes = (int) round($diffSeconds / 60);

        if ($attendance->clock_out_at >= $requestedEnd) {
            return [
                'requested_start' => $requestedStart,
                'requested_end' => $requestedEnd,
                'status' => 'Cocok',
                'status_slug' => 'match',
                'status_color' => 'green',
                'clock_in' => $attendance->clock_in_at,
                'clock_out' => $attendance->clock_out_at,
                'variance_minutes' => $varianceMinutes,
            ];
        }

        return [
            'requested_start' => $requestedStart,
            'requested_end' => $requestedEnd,
            'status' => 'Kurang dari pengajuan',
            'status_slug' => 'short',
            'status_color' => 'red',
            'clock_in' => $attendance->clock_in_at,
            'clock_out' => $attendance->clock_out_at,
            'variance_minutes' => $varianceMinutes,
        ];
    }
}
