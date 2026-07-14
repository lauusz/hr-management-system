<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkStockMovement;
use App\Services\Image\ImageCompressor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    public function __construct(protected ImageCompressor $imageCompressor) {}

    public function index()
    {
        $items = AtkItem::with('category')
            ->when(request()->filled('q'), function ($query): void {
                $keyword = '%'.request()->string('q')->toString().'%';
                $query->where('name', 'like', $keyword);
            })
            ->when(request()->filled('category_id'), fn ($query) => $query->where('atk_category_id', request()->integer('category_id')))
            ->when(request('stock') === 'out', fn ($query) => $query->where('stock_qty', 0))
            ->when(request('stock') === 'low', fn ($query) => $query->whereColumn('stock_qty', '<=', 'minimum_stock')->where('minimum_stock', '>', 0))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        $categories = AtkCategory::where('is_active', true)->orderBy('name')->get();

        return view('atk.admin.items.index', compact('categories', 'items'));
    }

    public function create()
    {
        return view('atk.admin.items.create', [
            'categories' => AtkCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'atk_category_id' => ['nullable', 'exists:atk_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,gif,bmp,tif,tiff,avif', 'max:2048'],
            'unit_name' => ['required', 'string', 'max:30'],
            'unit_size' => ['required', 'integer', 'min:1'],
            'content_unit_name' => ['required', 'string', 'max:30'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'min_request_qty' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->storeItemImage($request);
        }

        unset($validated['image']);
        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
        $validated['min_request_qty'] = $validated['min_request_qty'] ?? 1;

        DB::transaction(function () use ($validated, $request): void {
            $item = AtkItem::create($validated + [
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);

            // Catat saldo pembuka sebagai movement IN agar audit trail stok lengkap.
            if ((int) $validated['stock_qty'] > 0) {
                AtkStockMovement::create([
                    'atk_item_id' => $item->id,
                    'movement_type' => AtkStockMovement::TYPE_IN,
                    'qty' => (int) $validated['stock_qty'],
                    'stock_before' => 0,
                    'stock_after' => (int) $validated['stock_qty'],
                    // INITIAL: tidak ada source model, source_type dibiarkan NULL.
                    'source_type' => null,
                    'notes' => 'Saldo awal saat tambah barang',
                    'created_by' => $request->user()->id,
                ]);
            }
        });

        return redirect()->route('v2.atk.admin.items.index')->with('success', 'Barang ATK berhasil ditambahkan.');
    }

    public function edit(AtkItem $item)
    {
        return view('atk.admin.items.edit', [
            'item' => $item,
            'categories' => AtkCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, AtkItem $item)
    {
        $validated = $request->validate([
            'atk_category_id' => ['nullable', 'exists:atk_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,gif,bmp,tif,tiff,avif', 'max:2048'],
            'unit_name' => ['required', 'string', 'max:30'],
            'unit_size' => ['required', 'integer', 'min:1'],
            'content_unit_name' => ['required', 'string', 'max:30'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'min_request_qty' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->storeItemImage($request);
        }

        unset($validated['image']);
        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
        $validated['min_request_qty'] = $validated['min_request_qty'] ?? 1;
        $validated['is_active'] = $request->boolean('is_active');

        $item->update($validated);

        return redirect()->route('v2.atk.admin.items.index')->with('success', 'Barang ATK berhasil diperbarui.');
    }

    private function storeItemImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');

        try {
            return $this->imageCompressor->compressAndStore($file, 'photo', 'atk-items', 'atk_');
        } catch (ValidationException $exception) {
            if (! in_array(strtolower($file->getClientOriginalExtension()), ['heic', 'heif'], true)) {
                throw $exception;
            }

            return $file->store('atk-items', 'public');
        }
    }
}
