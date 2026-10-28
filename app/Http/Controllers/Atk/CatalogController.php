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
            return $this->respondAddToCart($request, 'Stok barang habis. Silakan ajukan penambahan stok.', null, $item->id, 0);
        }

        $cart = session('atk_cart', []);
        $qty = (int) $validated['qty'];
        $nextQty = ($cart[$item->id] ?? 0) + $qty;

        if ($nextQty > $item->stock_qty) {
            return $this->respondAddToCart($request, 'Jumlah melebihi stok tersedia.', null, $item->id, (int) ($cart[$item->id] ?? 0));
        }

        $cart[$item->id] = $nextQty;
        session(['atk_cart' => $cart]);

        return $this->respondAddToCart($request, null, 'Barang ditambahkan ke keranjang.', $item->id, $nextQty);
    }

    private function respondAddToCart(Request $request, ?string $warning, ?string $success, int $itemId, int $inCartQty)
    {
        $cartCount = array_sum(session('atk_cart', []));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success !== null,
                'message' => $warning ?? $success,
                'itemId' => $itemId,
                'inCartQty' => $inCartQty,
                'cartCount' => $cartCount,
            ]);
        }

        if ($warning !== null) {
            return back()->with('warning', $warning);
        }

        return back()->with('success', $success);
    }
}
