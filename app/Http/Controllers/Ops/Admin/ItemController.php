<?php

namespace App\Http\Controllers\Ops\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = AtkItem::query()
            ->forModule(AtkItem::MODULE_OPS)
            ->whereNull('deleted_at')
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('ops.admin.items.index', compact('items'));
    }

    public function create()
    {
        return view('ops.admin.items.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request, true);

        DB::transaction(function () use ($validated, $request): void {
            $item = AtkItem::create($validated + [
                'module' => AtkItem::MODULE_OPS,
                'atk_category_id' => null,
                'unit_size' => 1,
                'content_unit_name' => $validated['unit_name'],
                'minimum_stock' => 0,
                'min_request_qty' => 1,
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);

            if ($item->stock_qty > 0) {
                AtkStockMovement::create([
                    'atk_item_id' => $item->id,
                    'movement_type' => AtkStockMovement::TYPE_IN,
                    'qty' => $item->stock_qty,
                    'stock_before' => 0,
                    'stock_after' => $item->stock_qty,
                    'notes' => 'Stok awal',
                    'created_by' => $request->user()->id,
                ]);
            }
        });

        return redirect()->route('v2.ops.admin.items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(AtkItem $item)
    {
        $this->ensureOpsItem($item);

        return view('ops.admin.items.edit', compact('item'));
    }

    public function update(Request $request, AtkItem $item)
    {
        $this->ensureOpsItem($item);
        $validated = $this->validateItem($request, false);
        $validated['is_active'] = $request->boolean('is_active');
        $item->update($validated);

        return redirect()->route('v2.ops.admin.items.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Request $request, AtkItem $item)
    {
        $this->ensureOpsItem($item);
        $validated = $request->validate(['deletion_note' => ['required', 'string', 'max:1000']]);

        $item->update([
            'is_active' => false,
            'deleted_at' => now(),
            'deleted_by' => $request->user()->id,
            'deletion_note' => $validated['deletion_note'],
        ]);

        return redirect()->route('v2.ops.admin.items.index')->with('success', 'Barang berhasil dihapus.');
    }

    private function validateItem(Request $request, bool $withStock): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'unit_name' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'stock_qty' => [$withStock ? 'required' : 'nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function ensureOpsItem(AtkItem $item): void
    {
        abort_unless($item->module === AtkItem::MODULE_OPS && $item->deleted_at === null, 404);
    }
}
