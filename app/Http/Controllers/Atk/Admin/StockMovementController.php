<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkStockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $movements = AtkStockMovement::with('item')
            ->when($request->filled('item_id'), fn ($query) => $query->where('atk_item_id', $request->integer('item_id')))
            ->when($request->filled('movement_type'), fn ($query) => $query->where('movement_type', $request->string('movement_type')))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $items = AtkItem::orderBy('name')->get();

        return view('atk.admin.stock_movements.index', compact('items', 'movements'));
    }
}
