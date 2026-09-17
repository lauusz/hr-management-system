<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkRequest;
use App\Models\AtkStockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $modules = [AtkItem::MODULE_ATK, AtkItem::MODULE_OPS, AtkItem::MODULE_ATK_MKS];
        $selectedModule = in_array($request->string('module')->toString(), $modules, true)
            ? $request->string('module')->toString()
            : null;

        $movements = AtkStockMovement::with(['item', 'createdBy'])
            ->whereHas('item', fn ($query) => $query->whereIn('module', $modules))
            ->when($selectedModule, fn ($query) => $query->whereHas('item', fn ($itemQuery) => $itemQuery->where('module', $selectedModule)))
            ->when($request->filled('item_id'), fn ($query) => $query->where('atk_item_id', $request->integer('item_id')))
            ->when($request->filled('movement_type'), fn ($query) => $query->where('movement_type', $request->string('movement_type')))
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
        $requestSources = AtkRequest::whereIn('module', $modules)->whereIn(
            'id',
            $movements->getCollection()
                ->where('source_type', AtkStockMovement::SOURCE_REQUEST)
                ->pluck('source_id')
                ->filter()
                ->unique()
        )->get(['id', 'pt_name_snapshot', 'user_name_snapshot'])->keyBy('id');
        $items = AtkItem::whereIn('module', $selectedModule ? [$selectedModule] : $modules)->orderBy('name')->get();

        return view('atk.admin.stock_movements.index', compact('items', 'movements', 'requestSources'));
    }
}
