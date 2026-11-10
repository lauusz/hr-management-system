<?php

namespace App\Http\Controllers\Inventory\Pesona;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaItem;
use App\Models\InvPesonaVehicle;
use App\Services\Inventory\PesonaRequestCreator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    private function cart(Request $request): array
    {
        $cart = $request->attributes->get('pesona_state')['cart'];
        if (! is_array($cart)) {
            throw ValidationException::withMessages(['cart' => 'Data keranjang tidak valid. Silakan hubungi administrator.']);
        }
        foreach ($cart as $id => $qty) {
            if ((! is_int($qty) && ! is_string($qty))
                || filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false
                || filter_var($qty, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => min(PHP_INT_MAX, 4294967295)]]) === false) {
                throw ValidationException::withMessages(['cart' => 'Data keranjang tidak valid. Silakan hubungi administrator.']);
            }
        }

        return $cart;
    }

    private function validateItem(InvPesonaItem $item, int $qty): void
    {
        if (! $item->is_active || $item->deleted_at !== null || $item->stock_qty <= 0
            || $qty < $item->min_request_qty || $qty > $item->stock_qty) {
            throw ValidationException::withMessages(['qty' => 'Barang tidak tersedia atau jumlah tidak sesuai minimum dan stok terbaru.']);
        }
    }

    public function add(Request $request, InvPesonaItem $item): RedirectResponse
    {
        $data = $request->validate(['qty' => ['required', 'integer', 'min:1', 'max:'.min(PHP_INT_MAX, 4294967295)]]);
        $cart = $this->cart($request);
        $existing = (int) ($cart[$item->id] ?? 0);
        $qty = (int) $data['qty'];
        if ($qty > min(PHP_INT_MAX, 4294967295) - $existing) {
            throw ValidationException::withMessages(['qty' => 'Jumlah keranjang melebihi batas yang diperbolehkan.']);
        }
        $this->validateItem($item, $qty);
        $this->validateItem($item, $existing + $qty);
        $cart[$item->id] = $existing + $qty;
        $state = $request->attributes->get('pesona_state');
        $state['cart'] = $cart;

        return redirect()->route('inventory.pesona.cart.show')->with('success', 'Barang ditambahkan ke keranjang.');
    }

    public function show(Request $request): View
    {
        $cartInvalid = false;
        try {
            $cart = $this->cart($request);
        } catch (ValidationException $exception) {
            $cartInvalid = true;
            $raw = $request->attributes->get('pesona_state')['cart'];
            $cart = is_array($raw) ? $raw : [];
        }
        $ids = array_filter(array_keys($cart), fn ($id) => filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false);
        $items = InvPesonaItem::query()->whereIn('id', $ids)
            ->get(['id', 'name', 'description', 'image_path', 'unit_name', 'unit_size', 'content_unit_name', 'stock_qty', 'min_request_qty', 'is_active', 'deleted_at'])->keyBy('id');

        $state = $request->attributes->get('pesona_state');
        $legacy = $request->session()->get('inv_pesona_cart.'.$request->user()->id);
        $legacyNotice = ! $state['legacy_notified'] && is_array($legacy) && $legacy !== [];
        if ($legacyNotice) {
            $state['legacy_notified'] = true;
        }

        $vehicles = InvPesonaVehicle::query()->where('is_active', true)->orderBy('nopol')->orderBy('id')->get(['id', 'nopol']);
        return view('inventory.pesona.cart', compact('cart', 'items', 'cartInvalid', 'legacyNotice', 'vehicles'));
    }

    public function update(Request $request, InvPesonaItem $item): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate(['qty' => ['required', 'integer', 'min:1', 'max:'.min(PHP_INT_MAX, 4294967295)]]);
        $cart = $this->cart($request);
        if (! array_key_exists($item->id, $cart)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang tidak ada di daftar ambil.',
                    'qty' => null,
                    'cartCount' => array_sum($cart),
                ]);
            }

            return redirect()->route('inventory.pesona.cart.show')->with('warning', 'Barang tidak ada di keranjang.');
        }
        $this->validateItem($item, (int) $data['qty']);
        $cart[$item->id] = (int) $data['qty'];
        $state = $request->attributes->get('pesona_state');
        $state['cart'] = $cart;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jumlah barang diperbarui.',
                'qty' => (int) $data['qty'],
                'cartCount' => array_sum($cart),
            ]);
        }

        return redirect()->route('inventory.pesona.cart.show')->with('success', 'Jumlah barang diperbarui.');
    }

    public function remove(Request $request, string $item): RedirectResponse
    {
        $cart = $request->attributes->get('pesona_state')['cart'];
        if (is_array($cart) && array_key_exists($item, $cart)) {
            unset($cart[$item]);
            $state = $request->attributes->get('pesona_state');
            $state['cart'] = $cart;
        }

        return redirect()->route('inventory.pesona.cart.show')->with('success', 'Barang dihapus dari keranjang.');
    }

    public function submit(Request $request, PesonaRequestCreator $creator): RedirectResponse
    {
        $data = $request->validate(
            ['notes' => ['required', 'string', 'max:1000'], 'inv_pesona_vehicle_id' => ['required', 'integer', 'min:1']],
            [
                'notes.required' => 'Tujuan pengambilan wajib diisi.',
                'notes.max' => 'Tujuan pengambilan maksimal 1000 karakter.',
            ]
        );
        $cart = $this->cart($request);
        if ($cart === []) {
            return redirect()->route('inventory.pesona.cart.show')->withInput($request->only('notes', 'inv_pesona_vehicle_id'))->with('warning', 'Keranjang masih kosong.');
        }

        try {
            $result = $creator->create($request->user(), $cart, trim($data['notes']), (int) $data['inv_pesona_vehicle_id']);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('inventory.pesona.cart.show')->withInput($request->only('notes', 'inv_pesona_vehicle_id'))
                ->with('error', 'Pengajuan gagal disimpan. Silakan coba lagi.');
        }

        $state = $request->attributes->get('pesona_state');
        $state['cart'] = [];

        return redirect()->route('inventory.pesona.requests.show', $result['id'])->with('success', 'Pengajuan '.$result['number'].' berhasil dikirim.');
    }
}
