<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $items = AtkItem::query()
            ->forModule(AtkItem::MODULE_OPS)
            ->available()
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderByRaw('CASE WHEN stock_qty > 0 THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('ops.catalog', compact('items'));
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'atk_item_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $item = AtkItem::query()
            ->forModule(AtkItem::MODULE_OPS)
            ->available()
            ->findOrFail($validated['atk_item_id']);

        $cart = session('ops_cart', []);
        $nextQty = ($cart[$item->id] ?? 0) + (int) $validated['qty'];

        if ($item->stock_qty <= 0 || $nextQty > $item->stock_qty) {
            return back()->with('warning', 'Jumlah melebihi stok tersedia.');
        }

        $cart[$item->id] = $nextQty;
        session(['ops_cart' => $cart]);

        return back()->with('success', 'Barang ditambahkan.');
    }
}
