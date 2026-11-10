<?php

namespace App\Http\Controllers\Inventory\Pesona\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaItem;
use App\Services\Image\ImageCompressor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ItemController extends Controller
{
    public function __construct(protected ImageCompressor $imageCompressor) {}

    public function index(Request $request): View
    {
        $q = $request->query('q');
        $status = $request->query('status');
        $stock = $request->query('stock');
        $filters = [
            'q' => is_string($q) ? trim(mb_substr($q, 0, 150)) : '',
            'status' => in_array($status, ['all', 'active', 'inactive'], true) ? $status : 'all',
            'stock' => in_array($stock, ['all', 'out', 'low', 'available'], true) ? $stock : 'all',
        ];

        $items = InvPesonaItem::query()->whereNull('deleted_at')
            ->when($filters['q'] !== '', fn ($query) => $query->where('name', 'like', '%'.$filters['q'].'%'))
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($filters['stock'] === 'out', fn ($query) => $query->where('stock_qty', 0))
            ->when($filters['stock'] === 'low', fn ($query) => $query->where('stock_qty', '>', 0)->where('minimum_stock', '>', 0)->whereColumn('stock_qty', '<=', 'minimum_stock'))
            ->when($filters['stock'] === 'available', fn ($query) => $query->where('stock_qty', '>', 0)->where(fn ($query) => $query->where('minimum_stock', 0)->orWhereColumn('stock_qty', '>', 'minimum_stock')))
            ->orderBy('name')->orderBy('id')->paginate(20)->appends($filters);

        return view('inventory.pesona.admin.items.index', compact('items', 'filters'));
    }

    public function create(): View
    {
        return view('inventory.pesona.admin.items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        foreach (['name', 'unit_name', 'content_unit_name'] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => trim($request->input($field))]);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'unit_name' => ['required', 'string', 'max:30'],
            'unit_size' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'content_unit_name' => ['required', 'string', 'max:30'],
            'minimum_stock' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'min_request_qty' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'extensions:jpg,jpeg,png,webp,heic,heif', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            try {
                $imagePath = $this->imageCompressor->compressAndStore($request->file('image'), 'photo', 'inventory/pesona/items', 'pesona_', 'public');
            } catch (ValidationException $exception) {
                throw ValidationException::withMessages(['image' => $exception->errors()['photo'][0] ?? 'Foto gagal diproses. Gunakan gambar yang valid.']);
            }
        }

        try {
            InvPesonaItem::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'unit_name' => $validated['unit_name'],
                'unit_size' => $validated['unit_size'],
                'content_unit_name' => $validated['content_unit_name'],
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
                'min_request_qty' => $validated['min_request_qty'] ?? 1,
                'stock_qty' => 0,
                'is_active' => true,
                'created_by' => $request->user()->id,
                'image_path' => $imagePath,
            ]);
        } catch (\Throwable $exception) {
            if ($imagePath !== null && ! Storage::disk('public')->delete($imagePath)) {
                Log::warning('Gagal membersihkan foto barang Pesona setelah penyimpanan gagal.', ['path' => $imagePath]);
            }
            report($exception);

            return back()->withInput($request->only('name', 'description', 'unit_name', 'unit_size', 'content_unit_name', 'minimum_stock', 'min_request_qty'))
                ->with('error', 'Barang gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('inventory.pesona.admin.items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(InvPesonaItem $item): View
    {
        abort_if($item->deleted_at !== null, 404);

        return view('inventory.pesona.admin.items.edit', [
            'item' => $item,
            'unitsLocked' => $this->unitsLocked($item),
        ]);
    }

    public function update(Request $request, InvPesonaItem $item): RedirectResponse
    {
        abort_if($item->deleted_at !== null, 404);

        foreach (['name', 'unit_name', 'content_unit_name'] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => trim($request->input($field))]);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'minimum_stock' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'min_request_qty' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'extensions:jpg,jpeg,png,webp,heic,heif', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            try {
                $imagePath = $this->imageCompressor->compressAndStore($request->file('image'), 'photo', 'inventory/pesona/items', 'pesona_', 'public');
            } catch (ValidationException $exception) {
                throw ValidationException::withMessages(['image' => $exception->errors()['photo'][0] ?? 'Foto gagal diproses. Gunakan gambar yang valid.']);
            }
        }

        try {
            DB::transaction(function () use ($request, $item, $validated, $imagePath): void {
                $current = InvPesonaItem::query()->lockForUpdate()->findOrFail($item->id);
                abort_if($current->deleted_at !== null, 404);
                $unitsLocked = $this->unitsLocked($current);
                $presence = $unitsLocked ? 'sometimes' : 'required';
                $units = Validator::make($request->only('unit_name', 'unit_size', 'content_unit_name'), [
                    'unit_name' => [$presence, 'required', 'string', 'max:30'],
                    'unit_size' => [$presence, 'required', 'integer', 'min:1', 'max:4294967295'],
                    'content_unit_name' => [$presence, 'required', 'string', 'max:30'],
                ])->validate();

                if ($unitsLocked) {
                    foreach ($units as $field => $value) {
                        $same = $field === 'unit_size' ? (int) $value === $current->unit_size : $value === $current->$field;
                        if (! $same) {
                            throw ValidationException::withMessages([$field => 'Satuan tidak dapat diubah karena barang sudah memiliki stok atau riwayat.']);
                        }
                    }
                }

                $changes = [
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'minimum_stock' => $validated['minimum_stock'] ?? 0,
                    'min_request_qty' => $validated['min_request_qty'] ?? 1,
                    'is_active' => $validated['is_active'],
                ];
                if (! $unitsLocked) {
                    $changes += $units;
                }
                if ($imagePath !== null) {
                    $changes['image_path'] = $imagePath;
                }
                $current->update($changes);
            });
        } catch (\Throwable $exception) {
            if ($imagePath !== null && ! Storage::disk('public')->delete($imagePath)) {
                Log::warning('Gagal membersihkan foto baru barang Pesona setelah perubahan gagal.', ['path' => $imagePath]);
            }
            if ($exception instanceof ValidationException || $exception instanceof HttpExceptionInterface || $exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            report($exception);

            return back()->withInput($request->only('name', 'description', 'unit_name', 'unit_size', 'content_unit_name', 'minimum_stock', 'min_request_qty', 'is_active'))
                ->with('error', 'Perubahan barang gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('inventory.pesona.admin.items.index')->with('success', 'Barang berhasil diperbarui.');
    }

    private function unitsLocked(InvPesonaItem $item): bool
    {
        return $item->stock_qty !== 0 || $item->stockMovements()->exists() || $item->requestItems()->exists();
    }
}
