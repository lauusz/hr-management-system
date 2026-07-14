<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkNeedRequest;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\AtkStockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $trendStart = now()->subMonths(5)->startOfMonth();

        $approvedThisMonth = AtkRequest::whereIn('status', [AtkRequest::STATUS_APPROVED, AtkRequest::STATUS_PARTIAL])
            ->whereBetween('approved_at', [$monthStart, $monthEnd])
            ->count();

        $qtyOutThisMonth = DB::table('atk_request_items')
            ->join('atk_requests', 'atk_requests.id', '=', 'atk_request_items.atk_request_id')
            ->whereIn('atk_requests.status', [AtkRequest::STATUS_APPROVED, AtkRequest::STATUS_PARTIAL])
            ->where('atk_request_items.status', AtkRequestItem::STATUS_APPROVED)
            ->whereBetween('atk_requests.approved_at', [$monthStart, $monthEnd])
            ->sum('atk_request_items.qty');

        $topItemsThisMonth = DB::table('atk_request_items')
            ->join('atk_requests', 'atk_requests.id', '=', 'atk_request_items.atk_request_id')
            ->whereIn('atk_requests.status', [AtkRequest::STATUS_APPROVED, AtkRequest::STATUS_PARTIAL])
            ->where('atk_request_items.status', AtkRequestItem::STATUS_APPROVED)
            ->whereBetween('atk_requests.approved_at', [$monthStart, $monthEnd])
            ->select([
                'atk_request_items.item_name_snapshot',
                'atk_request_items.unit_name_snapshot',
                DB::raw('SUM(atk_request_items.qty) as total_qty'),
            ])
            ->groupBy('atk_request_items.item_name_snapshot', 'atk_request_items.unit_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $trendRows = collect(range(5, 0))->mapWithKeys(function ($monthsAgo) {
            $month = now()->subMonths($monthsAgo);

            return [$month->format('Y-m') => [
                'label' => $month->translatedFormat('M Y'),
                'total_qty' => 0,
            ]];
        })->all();

        DB::table('atk_request_items')
            ->join('atk_requests', 'atk_requests.id', '=', 'atk_request_items.atk_request_id')
            ->whereIn('atk_requests.status', [AtkRequest::STATUS_APPROVED, AtkRequest::STATUS_PARTIAL])
            ->where('atk_request_items.status', AtkRequestItem::STATUS_APPROVED)
            ->whereBetween('atk_requests.approved_at', [$trendStart, $monthEnd])
            ->select(['atk_requests.approved_at', 'atk_request_items.qty'])
            ->get()
            ->each(function ($row) use (&$trendRows) {
                $key = Carbon::parse($row->approved_at)->format('Y-m');
                if (isset($trendRows[$key])) {
                    $trendRows[$key]['total_qty'] += (int) $row->qty;
                }
            });

        $topPtsThisMonth = DB::table('atk_request_items')
            ->join('atk_requests', 'atk_requests.id', '=', 'atk_request_items.atk_request_id')
            ->whereIn('atk_requests.status', [AtkRequest::STATUS_APPROVED, AtkRequest::STATUS_PARTIAL])
            ->where('atk_request_items.status', AtkRequestItem::STATUS_APPROVED)
            ->whereBetween('atk_requests.approved_at', [$monthStart, $monthEnd])
            ->select([
                'atk_requests.pt_name_snapshot',
                DB::raw('COUNT(DISTINCT atk_requests.id) as request_count'),
                DB::raw('SUM(atk_request_items.qty) as total_qty'),
            ])
            ->groupBy('atk_requests.pt_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $masterWarnings = [
            ['label' => 'Tanpa kategori', 'count' => AtkItem::whereNull('atk_category_id')->count()],
            ['label' => 'Tanpa gambar', 'count' => AtkItem::whereNull('image_path')->orWhere('image_path', '')->count()],
            ['label' => 'Minimum stok belum diisi', 'count' => AtkItem::where('minimum_stock', '<=', 0)->count()],
        ];

        return view('atk.admin.dashboard', [
            'pendingRequests' => AtkRequest::where('status', AtkRequest::STATUS_PENDING)->count(),
            'lowStockItems' => AtkItem::whereColumn('stock_qty', '<=', 'minimum_stock')
                ->where('minimum_stock', '>', 0)
                ->count(),
            'outOfStockItems' => AtkItem::where('stock_qty', '<=', 0)->count(),
            'needRequests' => AtkNeedRequest::where('status', 'PENDING')->count(),
            'itemsCount' => AtkItem::count(),
            'approvedThisMonth' => $approvedThisMonth,
            'qtyOutThisMonth' => $qtyOutThisMonth,
            'topItemsThisMonth' => $topItemsThisMonth,
            'trendRows' => array_values($trendRows),
            'topPtsThisMonth' => $topPtsThisMonth,
            'recentStockMovements' => AtkStockMovement::with('item')->latest()->limit(5)->get(),
            'masterWarnings' => $masterWarnings,
            'lowStockPreview' => AtkItem::whereColumn('stock_qty', '<=', 'minimum_stock')
                ->where('minimum_stock', '>', 0)
                ->orderBy('stock_qty')
                ->limit(5)
                ->get(),
            'latestRequests' => AtkRequest::latest()->limit(5)->get(),
        ]);
    }
}
