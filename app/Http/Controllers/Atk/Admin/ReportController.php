<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Exports\AtkUsageReportExport;
use App\Http\Controllers\Controller;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\Pt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }

        $period = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $dateFrom = $period->copy()->startOfDay();
        $dateTo = $period->copy()->endOfMonth()->endOfDay();
        $periodLabel = $period->translatedFormat('F Y');

        $baseQuery = function () use ($request, $dateFrom, $dateTo) {
            return DB::table('atk_request_items')
                ->join('atk_requests', 'atk_requests.id', '=', 'atk_request_items.atk_request_id')
                ->where('atk_requests.module', AtkRequest::MODULE_ATK)
                ->whereIn('atk_requests.status', [AtkRequest::STATUS_APPROVED, AtkRequest::STATUS_PARTIAL])
                ->where('atk_request_items.status', AtkRequestItem::STATUS_APPROVED)
                ->whereBetween('atk_requests.approved_at', [$dateFrom, $dateTo])
                ->when($request->filled('pt_id'), fn ($query) => $query->where('atk_requests.pt_id', $request->integer('pt_id')));
        };

        $summaryRow = $baseQuery()
            ->select([
                DB::raw('COUNT(DISTINCT atk_requests.id) as request_count'),
                DB::raw('COUNT(DISTINCT atk_requests.user_id) as user_count'),
                DB::raw('COUNT(DISTINCT atk_requests.pt_id) as pt_count'),
                DB::raw('COUNT(DISTINCT atk_request_items.item_name_snapshot) as item_count'),
            ])
            ->first();

        $summary = [
            'request_count' => (int) ($summaryRow->request_count ?? 0),
            'user_count' => (int) ($summaryRow->user_count ?? 0),
            'pt_count' => (int) ($summaryRow->pt_count ?? 0),
            'item_count' => (int) ($summaryRow->item_count ?? 0),
        ];

        $ptRows = $baseQuery()
            ->select([
                'atk_requests.pt_name_snapshot',
                DB::raw('COUNT(DISTINCT atk_requests.id) as request_count'),
                DB::raw('COUNT(DISTINCT atk_requests.user_id) as user_count'),
                DB::raw('COUNT(DISTINCT atk_request_items.item_name_snapshot) as item_count'),
            ])
            ->groupBy('atk_requests.pt_name_snapshot')
            ->orderByDesc('request_count')
            ->orderBy('atk_requests.pt_name_snapshot')
            ->get();

        $ptColors = ['#7C4DDE', '#0F766E', '#2563EB', '#D97706', '#DB2777', '#4F46E5', '#16A34A', '#DC2626'];
        $ptRequestTotal = (int) $ptRows->sum('request_count');
        $ptChartPosition = 0.0;
        $ptChartSegments = [];

        foreach ($ptRows as $index => $row) {
            $percentage = $ptRequestTotal > 0 ? ((int) $row->request_count / $ptRequestTotal) * 100 : 0;
            $row->percentage = round($percentage, 1);
            $row->color = $ptColors[$index % count($ptColors)];
            $ptChartSegments[] = sprintf('%s %.2f%% %.2f%%', $row->color, $ptChartPosition, $ptChartPosition + $percentage);
            $ptChartPosition += $percentage;
        }

        $ptChartGradient = $ptChartSegments ? implode(', ', $ptChartSegments) : '#E5E7EB 0% 100%';

        $itemRows = $baseQuery()
            ->select([
                'atk_request_items.item_name_snapshot',
                'atk_request_items.unit_name_snapshot',
                DB::raw('SUM(atk_request_items.qty) as total_qty'),
                DB::raw('COUNT(DISTINCT atk_requests.id) as request_count'),
                DB::raw('COUNT(DISTINCT atk_requests.pt_id) as pt_count'),
            ])
            ->groupBy('atk_request_items.item_name_snapshot', 'atk_request_items.unit_name_snapshot')
            ->orderByDesc('total_qty')
            ->orderBy('atk_request_items.item_name_snapshot')
            ->get();

        $itemQtyTotal = (int) $itemRows->sum('total_qty');
        $itemChartRows = $itemRows->take(5)->values();
        $otherQty = (int) $itemRows->skip(5)->sum('total_qty');

        if ($otherQty > 0) {
            $itemChartRows->push((object) [
                'item_name_snapshot' => 'Lainnya',
                'total_qty' => $otherQty,
            ]);
        }

        $itemChartPosition = 0.0;
        $itemChartSegments = [];

        foreach ($itemChartRows as $index => $row) {
            $percentage = $itemQtyTotal > 0 ? ((int) $row->total_qty / $itemQtyTotal) * 100 : 0;
            $row->percentage = round($percentage, 1);
            $row->color = $ptColors[$index % count($ptColors)];
            $itemChartSegments[] = sprintf('%s %.2f%% %.2f%%', $row->color, $itemChartPosition, $itemChartPosition + $percentage);
            $itemChartPosition += $percentage;
        }

        $itemChartGradient = $itemChartSegments ? implode(', ', $itemChartSegments) : '#E5E7EB 0% 100%';

        $requesterRows = $baseQuery()
            ->select([
                'atk_requests.user_name_snapshot',
                DB::raw('COUNT(DISTINCT atk_requests.id) as request_count'),
            ])
            ->groupBy('atk_requests.user_name_snapshot')
            ->orderByDesc('request_count')
            ->orderBy('atk_requests.user_name_snapshot')
            ->limit(10)
            ->get();

        $detailRows = $baseQuery()
            ->select([
                'atk_requests.approved_at',
                'atk_requests.request_number',
                'atk_requests.user_name_snapshot',
                'atk_requests.pt_name_snapshot',
                'atk_request_items.item_name_snapshot',
                'atk_request_items.unit_name_snapshot',
                'atk_request_items.qty',
            ])
            ->orderByDesc('atk_requests.approved_at')
            ->orderByDesc('atk_requests.id')
            ->get();

        $pts = Pt::orderBy('name')->get();
        $selectedPtName = $request->filled('pt_id')
            ? ($pts->firstWhere('id', $request->integer('pt_id'))?->name ?? 'PT tidak ditemukan')
            : 'Semua PT';
        $generatedAt = now();

        return view('atk.admin.reports.index', compact(
            'pts',
            'month',
            'periodLabel',
            'selectedPtName',
            'generatedAt',
            'summary',
            'ptRows',
            'ptChartGradient',
            'ptRequestTotal',
            'itemChartRows',
            'itemChartGradient',
            'itemQtyTotal',
            'requesterRows',
            'detailRows'
        ));
    }

    public function export(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }

        $ptId = $request->filled('pt_id') ? $request->integer('pt_id') : null;
        $periodLabel = Carbon::createFromFormat('Y-m', $month)->translatedFormat('F_Y');

        $filename = 'rekap-atk-'.$periodLabel.($ptId ? '-pt'.$ptId : '').'.xlsx';

        return Excel::download(new AtkUsageReportExport($month, $ptId), $filename);
    }
}
