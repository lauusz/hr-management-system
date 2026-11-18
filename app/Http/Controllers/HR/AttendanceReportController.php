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
    /** Bulan sah: 1–12 (boleh diawali 0) atau literal "all" (Semua Bulan). */
    private const MONTH_PATTERN = '/^(all|0?[1-9]|1[0-2])$/';

    private function normalizeMonth(?string $value, int $default): int|string
    {
        if ($value === 'all') {
            return 'all';
        }

        return $value === null || $value === '' ? $default : (int) $value;
    }

    /**
     * Rentang periode cut-off (inklusif); nama bulan mengikuti bulan AKHIR periode.
     * - Bulan M tahun Y : 26 (M-1) .. 25 M Y.
     * - "all" tahun Y   : 26 Desember Y-1 .. 25 Desember Y (12 periode, tidak dipotong hari ini).
     * Dipakai bersama oleh index dan detail agar batas tanggal selalu sama.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodRange(int|string $month, int $year): array
    {
        if ($month === 'all') {
            return [
                Carbon::create($year - 1, 12, 26, 0, 0, 0, 'Asia/Jakarta'),
                Carbon::create($year, 12, 25, 0, 0, 0, 'Asia/Jakarta'),
            ];
        }

        $end = Carbon::create($year, $month, 25, 0, 0, 0, 'Asia/Jakarta');

        return [$end->copy()->subMonthNoOverflow()->day(26), $end];
    }

    public function index(Request $request, MonthlyAttendanceReportService $report)
    {
        $filters = $request->validate([
            'month' => ['nullable', 'regex:'.self::MONTH_PATTERN],
            'year' => ['nullable', 'integer', 'between:2,9999'],
            'q' => ['nullable', 'string', 'max:255'],
            'pt_id' => ['nullable', 'integer', 'exists:pts,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
        ]);

        $now = Carbon::now('Asia/Jakarta');
        $month = $this->normalizeMonth($filters['month'] ?? null, $now->month);
        $year = (int) ($filters['year'] ?? $now->year);
        [$start, $end] = $this->periodRange($month, $year);
        $search = trim($filters['q'] ?? '');
        $ptId = $filters['pt_id'] ?? null;
        $positionId = $filters['position_id'] ?? null;

        $query = User::active()->with(['position', 'profile.pt']);
        if ($search !== '') {
            $query->whereNormalizedNameContains($search);
        }
        if ($ptId) {
            $query->whereHas('profile', fn ($query) => $query->where('pt_id', $ptId));
        }
        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        $items = $query->orderBy('name')->orderBy('id')->get();

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
            'month' => ['required', 'regex:'.self::MONTH_PATTERN],
            'year' => ['required', 'integer', 'between:2,9999'],
            'category' => ['required', Rule::in(array_keys(MonthlyAttendanceReportService::CATEGORIES))],
        ]);
        [$start, $end] = $this->periodRange($this->normalizeMonth($data['month'], 1), (int) $data['year']);
        $result = $report->calculate([$employee->id], $start, $end)[$employee->id] ?? [];
        $entries = $result[$data['category']] ?? [];
        ksort($entries);

        return response()->json([
            'employee' => $employee->name, 'category' => MonthlyAttendanceReportService::CATEGORIES[$data['category']],
            'start' => $start->toDateString(), 'end' => $end->toDateString(),
            'total' => count($entries), 'entries' => array_values($entries),
            'incomplete' => $data['category'] === 'TELAT' && ($result['incomplete'] ?? false),
            'leave_balance' => $data['category'] === 'CUTI' ? (float) ($employee->leave_balance ?? 0) : null,
        ]);
    }
}
