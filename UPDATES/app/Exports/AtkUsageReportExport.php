<?php

namespace App\Exports;

use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AtkUsageReportExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected string $month,
        protected ?int $ptId = null,
    ) {}

    public function view(): View
    {
        $period = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $dateFrom = $period->copy()->startOfDay();
        $dateTo = $period->copy()->endOfMonth()->endOfDay();

        $baseQuery = function () use ($dateFrom, $dateTo) {
            return DB::table('atk_request_items')
                ->join('atk_requests', 'atk_requests.id', '=', 'atk_request_items.atk_request_id')
                ->whereIn('atk_requests.status', [AtkRequest::STATUS_APPROVED, AtkRequest::STATUS_PARTIAL])
                ->where('atk_request_items.status', AtkRequestItem::STATUS_APPROVED)
                ->whereBetween('atk_requests.approved_at', [$dateFrom, $dateTo])
                ->when($this->ptId, fn ($query) => $query->where('atk_requests.pt_id', $this->ptId));
        };

        $summaryRow = $baseQuery()
            ->select([
                DB::raw('COUNT(DISTINCT atk_requests.id) as request_count'),
                DB::raw('COALESCE(SUM(atk_request_items.qty), 0) as total_qty'),
                DB::raw('COUNT(DISTINCT atk_requests.pt_id) as pt_count'),
            ])
            ->first();

        $ptRows = $baseQuery()
            ->select([
                'atk_requests.pt_name_snapshot',
                DB::raw('COUNT(DISTINCT atk_requests.id) as request_count'),
                DB::raw('SUM(atk_request_items.qty) as total_qty'),
            ])
            ->groupBy('atk_requests.pt_name_snapshot')
            ->orderByDesc('total_qty')
            ->orderBy('atk_requests.pt_name_snapshot')
            ->get();

        $itemRows = $baseQuery()
            ->select([
                'atk_request_items.item_name_snapshot',
                'atk_request_items.unit_name_snapshot',
                DB::raw('SUM(atk_request_items.qty) as total_qty'),
            ])
            ->groupBy('atk_request_items.item_name_snapshot', 'atk_request_items.unit_name_snapshot')
            ->orderByDesc('total_qty')
            ->orderBy('atk_request_items.item_name_snapshot')
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

        return view('atk.admin.reports.export', [
            'periodLabel' => $period->translatedFormat('F Y'),
            'month' => $this->month,
            'summary' => [
                'request_count' => (int) ($summaryRow->request_count ?? 0),
                'total_qty' => (int) ($summaryRow->total_qty ?? 0),
                'pt_count' => (int) ($summaryRow->pt_count ?? 0),
            ],
            'ptRows' => $ptRows,
            'itemRows' => $itemRows,
            'detailRows' => $detailRows,
        ]);
    }
}
