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
        $movements = AtkStockMovement::with(['item', 'createdBy'])
            ->when($request->filled('item_id'), fn ($query) => $query->where('atk_item_id', $request->integer('item_id')))
            ->when($request->filled('movement_type'), fn ($query) => $query->where('movement_type', $request->string('movement_type')))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $requestSources = AtkRequest::whereIn(
            'id',
            $movements->getCollection()
                ->where('source_type', AtkStockMovement::SOURCE_REQUEST)
                ->pluck('source_id')
                ->filter()
                ->unique()
        )->get(['id', 'pt_name_snapshot', 'user_name_snapshot'])->keyBy('id');
        $items = AtkItem::orderBy('name')->get();

        return view('atk.admin.stock_movements.index', compact('items', 'movements', 'requestSources'));
    }
}
