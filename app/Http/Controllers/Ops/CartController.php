<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkRequest;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show()
    {
        return view('ops.cart', ['cartRows' => $this->cartRows()]);
    }

    public function update(Request $request, AtkItem $item)
    {
        $this->ensureOpsItem($item);
        $validated = $request->validate(['qty' => ['required', 'integer', 'min:1']]);
        $cart = session('ops_cart', []);

        abort_unless(isset($cart[$item->id]), 404);

        if ((int) $validated['qty'] > $item->stock_qty) {
            return back()->with('warning', 'Jumlah melebihi stok tersedia.');
        }

        $cart[$item->id] = (int) $validated['qty'];
        session(['ops_cart' => $cart]);

        return back()->with('success', 'Jumlah diperbarui.');
    }

    public function remove(AtkItem $item)
    {
        $this->ensureOpsItem($item);
        $cart = session('ops_cart', []);
        unset($cart[$item->id]);
        session(['ops_cart' => $cart]);

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

        $opsRequest = AtkRequest::createPending(
            $request->user(),
            $rows,
            $validated['notes'] ?? null,
            AtkRequest::MODULE_OPS,
        );

        session()->forget('ops_cart');

        return redirect()->route('v2.ops.requests.show', $opsRequest)
            ->with('success', 'Pengajuan berhasil dibuat.');
    }

    private function cartRows()
    {
        $cart = collect(session('ops_cart', []))
            ->map(fn ($qty, $id) => ['id' => (int) $id, 'qty' => (int) $qty])
            ->filter(fn ($row) => $row['qty'] > 0);

        $items = AtkItem::query()
            ->forModule(AtkItem::MODULE_OPS)
            ->available()
            ->whereIn('id', $cart->pluck('id'))
            ->get()
            ->keyBy('id');

        return $cart->map(fn ($row) => [
            'item' => $items->get($row['id']),
            'qty' => $row['qty'],
        ])->filter(fn ($row) => $row['item']);
    }

    private function ensureOpsItem(AtkItem $item): void
    {
        abort_unless($item->module === AtkItem::MODULE_OPS && $item->deleted_at === null, 404);
    }
}
