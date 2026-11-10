<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Pt;
use App\Models\User;
use App\Services\Attendance\MonthlyAttendanceReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceReportController extends Controller
{
    public function index(Request $request, MonthlyAttendanceReportService $report)
    {
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2,9999'],
            'q' => ['nullable', 'string', 'max:255'],
            'pt_id' => ['nullable', 'integer', 'exists:pts,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
        ]);

        $now = Carbon::now('Asia/Jakarta');
        $month = (int) ($filters['month'] ?? $now->month);
        $year = (int) ($filters['year'] ?? $now->year);
        $end = Carbon::create($year, $month, 25, 0, 0, 0, 'Asia/Jakarta');
        $start = $end->copy()->subMonthNoOverflow()->day(26);
        $search = trim($filters['q'] ?? '');
        $ptId = $filters['pt_id'] ?? null;
        $positionId = $filters['position_id'] ?? null;

        $query = User::with(['position', 'profile.pt']);
        if ($search !== '') {
            $query->whereNormalizedNameContains($search);
        }
        if ($ptId) {
            $query->whereHas('profile', fn ($query) => $query->where('pt_id', $ptId));
        }
        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        $items = $query->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [User::STATUS_ACTIVE])
            ->orderBy('name')->orderBy('id')->get();

        return view('hr.reports.attendance', [
            'items' => $items, 'month' => $month, 'year' => $year,
            'start' => $start, 'end' => $end, 'search' => $search,
            'ptId' => $ptId, 'positionId' => $positionId, 'now' => $now,
            'pts' => Pt::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
            'report' => $report->calculate($items->modelKeys(), $start, $end),
        ]);
    }

    public function detail(Request $request, User $employee, MonthlyAttendanceReportService $report)
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2,9999'],
            'category' => ['required', Rule::in(array_keys(MonthlyAttendanceReportService::CATEGORIES))],
        ]);
        $end = Carbon::create((int) $data['year'], (int) $data['month'], 25, 0, 0, 0, 'Asia/Jakarta');
        $start = $end->copy()->subMonthNoOverflow()->day(26);
        $result = $report->calculate([$employee->id], $start, $end)[$employee->id] ?? [];
        $entries = $result[$data['category']] ?? [];
        ksort($entries);

        return response()->json([
            'employee' => $employee->name, 'category' => MonthlyAttendanceReportService::CATEGORIES[$data['category']],
            'start' => $start->toDateString(), 'end' => $end->toDateString(),
            'total' => count($entries), 'entries' => array_values($entries),
            'incomplete' => $data['category'] === 'TELAT' && ($result['incomplete'] ?? false),
        ]);
    }
}
