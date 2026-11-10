<?php

namespace App\Http\Controllers\Inventory\Pesona;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        if (is_string($request->input('q'))) {
            $request->merge(['q' => trim($request->input('q'))]);
        }
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:150']]);
        $search = $validated['q'] ?? '';
        $stock = $request->query('stock');
        $stock = in_array($stock, ['all', 'available', 'out'], true) ? $stock : 'all';

        $items = InvPesonaItem::query()->available()
            ->select('id', 'name', 'description', 'image_path', 'unit_name', 'unit_size', 'content_unit_name', 'stock_qty', 'minimum_stock', 'min_request_qty')
            ->when($search !== '', function ($query) use ($search): void {
                $keyword = '%'.$search.'%';
                $query->where(function ($query) use ($keyword): void {
                    $query->where('name', 'like', $keyword)->orWhere('description', 'like', $keyword);
                });
            })
            ->when($stock === 'available', fn ($query) => $query->where('stock_qty', '>', 0))
            ->when($stock === 'out', fn ($query) => $query->where('stock_qty', 0))
            ->orderByRaw('CASE WHEN stock_qty > 0 THEN 0 ELSE 1 END')
            ->orderBy('name')->orderBy('id')->paginate(20)
            ->appends(['q' => $search, 'stock' => $stock]);

        $hasActiveItems = $items->total() > 0
            || (($search !== '' || $stock !== 'all') && InvPesonaItem::query()->available()->exists());

        return view('inventory.pesona.catalog', compact('items', 'search', 'stock', 'hasActiveItems'));
    }
}
