<?php

namespace App\Http\Controllers\Inventory\Pesona\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaItem;
use App\Models\InvPesonaRequest;
use App\Models\InvPesonaStockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->query('ajax_items') === '1') {
            $itemQuery = trim((string) $request->query('item_q', ''));
            $matchingItems = InvPesonaItem::query()
                ->when($itemQuery !== '', function ($q) use ($itemQuery): void {
                    $kw = '%'.$itemQuery.'%';
                    $q->where(function ($sub) use ($kw): void {
                        $sub->where('name', 'like', $kw)
                            ->orWhere('unit_name', 'like', $kw);
                    });
                })
                ->orderBy('name')
                ->orderBy('id')
                ->limit(30)
                ->get(['id', 'name', 'unit_name', 'stock_qty', 'is_active', 'deleted_at'])
                ->map(fn (InvPesonaItem $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit_name' => $item->unit_name,
                    'stock' => (int) $item->stock_qty,
                    'is_active' => (bool) $item->is_active && $item->deleted_at === null,
                ])
                ->values();

            return response()->json(['items' => $matchingItems]);
        }

        $type = $request->query('movement_type');
        $type = in_array($type, ['IN', 'OUT', 'ADJUSTMENT'], true) ? $type : '';
        $input = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'item_id' => ['nullable', 'integer', 'min:1', 'exists:inv_pesona_items,id'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $from = $input['date_from'] ?? null;
        $to = $input['date_to'] ?? null;
        if ($from !== null && $to !== null && $from > $to) {
            throw ValidationException::withMessages(['date_to' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.']);
        }
        $filters = [
            'q' => trim($input['q'] ?? ''),
            'item_id' => $input['item_id'] ?? '',
            'movement_type' => $type,
            'date_from' => $from ?? '',
            'date_to' => $to ?? '',
        ];
        $timezone = config('app.timezone');
        $movements = InvPesonaStockMovement::query()->with(['item:id,name,unit_name', 'createdBy:id,name'])
            ->when($filters['item_id'] !== '', fn ($query) => $query->where('inv_pesona_item_id', $filters['item_id']))
            ->when($filters['movement_type'] !== '', fn ($query) => $query->where('movement_type', $filters['movement_type']))
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $keyword = '%'.$filters['q'].'%';
                $matchingRequestIds = InvPesonaRequest::query()
                    ->where('request_number', 'like', $keyword)
                    ->orWhere('user_name_snapshot', 'like', $keyword)
                    ->pluck('id');
                $query->where(function ($query) use ($keyword, $matchingRequestIds): void {
                    $query->whereHas('item', fn ($item) => $item->where('name', 'like', $keyword))
                        ->orWhereHas('createdBy', fn ($user) => $user->where('name', 'like', $keyword))
                        ->orWhere('notes', 'like', $keyword);
                    if ($matchingRequestIds->isNotEmpty()) {
                        $query->orWhere(function ($sub) use ($matchingRequestIds): void {
                            $sub->where('source_type', InvPesonaRequest::class)
                                ->whereIn('source_id', $matchingRequestIds);
                        });
                    }
                });
            })
            ->when($from !== null, fn ($query) => $query->where('created_at', '>=', Carbon::createFromFormat('!Y-m-d', $from, $timezone)->toDateTimeString()))
            ->when($to !== null, fn ($query) => $query->where('created_at', '<', Carbon::createFromFormat('!Y-m-d', $to, $timezone)->addDay()->toDateTimeString()))
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(20)->appends($filters);

        $sourceIds = $movements->getCollection()->where('source_type', InvPesonaRequest::class)->pluck('source_id')->filter()->unique();
        $requestSources = $sourceIds->isEmpty()
            ? collect()
            : InvPesonaRequest::query()->whereIn('id', $sourceIds)->get(['id', 'request_number', 'user_name_snapshot', 'nopol_snapshot'])->keyBy('id');
        $selectedItem = $filters['item_id'] === '' ? null : InvPesonaItem::query()->find($filters['item_id'], ['id', 'name', 'unit_name']);
        $items = InvPesonaItem::query()->orderBy('name')->orderBy('id')->get(['id', 'name', 'unit_name', 'stock_qty', 'is_active', 'deleted_at']);

        return view('inventory.pesona.admin.stock_movements.index', compact('movements', 'requestSources', 'selectedItem', 'items', 'filters'));
    }
}
