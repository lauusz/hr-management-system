<?php

namespace App\Http\Controllers\Atk;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkRequest;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show()
    {
        return view('atk.cart', [
            'cartRows' => $this->cartRows(),
        ]);
    }

    public function remove(AtkItem $item)
    {
        $cart = session('atk_cart', []);
        unset($cart[$item->id]);
        session(['atk_cart' => $cart]);

        return back()->with('success', 'Barang dihapus dari keranjang.');
    }

    public function update(Request $request, AtkItem $item)
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $cart = session('atk_cart', []);

        if (! isset($cart[$item->id])) {
            $warning = 'Barang tidak ada di keranjang.';

            return $this->respondCartUpdate($request, $warning, null, 0);
        }

        $qty = (int) $validated['qty'];

        // Cek stok sebelum update — sama seperti addToCart.
        if ($qty > $item->stock_qty) {
            $warning = 'Jumlah melebihi stok tersedia ('.$item->stock_qty.').';

            return $this->respondCartUpdate($request, $warning, null, 0);
        }

        $cart[$item->id] = $qty;
        session(['atk_cart' => $cart]);

        return $this->respondCartUpdate($request, null, 'Jumlah barang diperbarui.', $qty);
    }

    /**
     * Format respons untuk update qty cart.
     * - Request biasa: redirect back dengan flash message (kompatibilitas tanpa JS).
     * - Request AJAX (expectsJson): kembalikan JSON {success, message, qty, cartCount}
     *   agar frontend stepper bisa update UI tanpa reload.
     */
    private function respondCartUpdate(Request $request, ?string $warning, ?string $success, ?int $qty)
    {
        $cartCount = array_sum(session('atk_cart', []));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success !== null,
                'message' => $warning ?? $success,
                'qty' => $qty,
                'cartCount' => $cartCount,
            ]);
        }

        if ($warning !== null) {
            return back()->with('warning', $warning);
        }

        return back()->with('success', $success);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cartRows = $this->cartRows();

        if ($cartRows->isEmpty()) {
            return back()->with('warning', 'Keranjang masih kosong.');
        }

        foreach ($cartRows as $row) {
            if ($row['qty'] > $row['item']->stock_qty) {
                return back()->with('warning', 'Stok '.$row['item']->name.' tidak cukup.');
            }
        }

        $user = $request->user();
        $atkRequest = AtkRequest::createPending($user, $cartRows, $validated['notes'] ?? null);

        session()->forget('atk_cart');

        return redirect()
            ->route('v2.atk.requests.show', $atkRequest)
            ->with('success', 'Pengajuan ATK berhasil dibuat.');
    }

    private function cartRows()
    {
        $cart = collect(session('atk_cart', []))
            ->map(fn ($qty, $id) => ['id' => (int) $id, 'qty' => (int) $qty])
            ->filter(fn ($row) => $row['qty'] > 0);

        if ($cart->isEmpty()) {
            return collect();
        }

        // Hanya item aktif yang boleh di-submit — konsisten dengan CatalogController@addToCart
        // yang sudah filter `is_active=true`. Mencegah item yang baru dinonaktifkan admin
        // tetap lolos submit dari cart lama.
        $items = AtkItem::where('is_active', true)
            ->whereIn('id', $cart->pluck('id'))
            ->get()
            ->keyBy('id');

        return $cart->map(fn ($row) => [
            'item' => $items->get($row['id']),
            'qty' => $row['qty'],
        ])->filter(fn ($row) => $row['item']);
    }
}
