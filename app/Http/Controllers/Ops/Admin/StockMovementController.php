<?php

namespace App\Http\Controllers\Ops\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkStockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $movements = AtkStockMovement::with(['item', 'createdBy'])
            ->whereHas('item', fn ($query) => $query->forModule(AtkItem::MODULE_OPS))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('q')->toString().'%';
                $query->whereHas('item', fn ($query) => $query->where('name', 'like', $keyword));
            })
            ->when($request->filled('month'), function ($query) use ($request): void {
                [$year, $month] = array_pad(explode('-', $request->string('month')->toString(), 2), 2, null);
                if (ctype_digit((string) $year) && ctype_digit((string) $month) && (int) $month >= 1 && (int) $month <= 12) {
                    $query->whereYear('created_at', (int) $year)->whereMonth('created_at', (int) $month);
                }
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('ops.admin.stock_movements.index', compact('movements'));
    }
}
