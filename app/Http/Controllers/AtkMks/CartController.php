<?php

namespace App\Http\Controllers\AtkMks;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkRequest;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show()
    {
        return view('atk-mks.cart', ['cartRows' => $this->cartRows()]);
    }

    public function update(Request $request, AtkItem $item)
    {
        $this->ensureAtkMksItem($item);
        $validated = $request->validate(['qty' => ['required', 'integer', 'min:1']]);
        $cart = session('atk_mks_cart', []);

        if (! isset($cart[$item->id])) {
            return $this->respondCartUpdate($request, 'Barang tidak ada di keranjang.');
        }

        if ((int) $validated['qty'] > $item->stock_qty) {
            return $this->respondCartUpdate($request, 'Jumlah melebihi stok tersedia ('.$item->stock_qty.').');
        }

        $cart[$item->id] = (int) $validated['qty'];
        session(['atk_mks_cart' => $cart]);

        return $this->respondCartUpdate($request, null, (int) $validated['qty']);
    }

    private function respondCartUpdate(Request $request, ?string $warning = null, ?int $qty = null)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $warning === null,
                'message' => $warning ?? 'Jumlah barang diperbarui.',
                'qty' => $qty,
                'cartCount' => array_sum(session('atk_mks_cart', [])),
            ]);
        }

        return $warning === null
            ? back()->with('success', 'Jumlah barang diperbarui.')
            : back()->with('warning', $warning);
    }

    public function remove(AtkItem $item)
    {
        $this->ensureAtkMksItem($item);
        $cart = session('atk_mks_cart', []);
        unset($cart[$item->id]);
        session(['atk_mks_cart' => $cart]);

        return back()->with('success', 'Barang dihapus dari keranjang.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);
        $rows = $this->cartRows();

        if ($rows->isEmpty()) {
            return back()->with('warning', 'Keranjang masih kosong.');
        }

        foreach ($rows as $row) {
            if ($row['qty'] > $row['item']->stock_qty) {
                return back()->with('warning', 'Stok '.$row['item']->name.' tidak cukup.');
            }
        }

        $atkMksRequest = AtkRequest::createPending(
            $request->user(),
            $rows,
            $validated['notes'] ?? null,
            AtkRequest::MODULE_ATK_MKS,
        );

        session()->forget('atk_mks_cart');

        return redirect()->route('v2.atk-mks.requests.show', $atkMksRequest)
            ->with('success', 'Pengajuan berhasil dibuat.');
    }

    private function cartRows()
    {
        $cart = collect(session('atk_mks_cart', []))
            ->map(fn ($qty, $id) => ['id' => (int) $id, 'qty' => (int) $qty])
            ->filter(fn ($row) => $row['qty'] > 0);

        $items = AtkItem::query()
            ->forModule(AtkItem::MODULE_ATK_MKS)
            ->available()
            ->whereIn('id', $cart->pluck('id'))
            ->get()
            ->keyBy('id');

        return $cart->map(fn ($row) => [
            'item' => $items->get($row['id']),
            'qty' => $row['qty'],
        ])->filter(fn ($row) => $row['item']);
    }

    private function ensureAtkMksItem(AtkItem $item): void
    {
        abort_unless($item->module === AtkItem::MODULE_ATK_MKS && $item->deleted_at === null, 404);
    }
}

