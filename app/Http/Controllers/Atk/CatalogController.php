<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $items = AtkItem::query()
            ->forModule(AtkItem::MODULE_ATK)
            ->available()
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('q')->toString().'%');
            })
            ->orderByRaw('CASE WHEN stock_qty > 0 THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('atk.catalog', [
            'items' => $items,
            'cartCount' => array_sum(session('atk_cart', [])),
        ]);
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'atk_item_id' => ['required', 'exists:atk_items,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $item = AtkItem::forModule(AtkItem::MODULE_ATK)->available()->findOrFail($validated['atk_item_id']);

        if ($item->stock_qty <= 0) {
            return back()->with('warning', 'Stok barang habis. Silakan ajukan penambahan stok.');
        }

        $cart = session('atk_cart', []);
        $qty = (int) $validated['qty'];
        $nextQty = ($cart[$item->id] ?? 0) + $qty;

        if ($nextQty > $item->stock_qty) {
            return back()->with('warning', 'Jumlah melebihi stok tersedia.');
        }

        $cart[$item->id] = $nextQty;
        session(['atk_cart' => $cart]);

        return back()->with('success', 'Barang ditambahkan ke keranjang.');
    }
}
