<?php

namespace App\Services\Inventory;

use App\Models\InvPesonaAccessUser;
use App\Models\InvPesonaItem;
use App\Models\InvPesonaRequest;
use App\Models\InvPesonaRequestItem;
use App\Models\InvPesonaVehicle;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesonaRequestCreator
{
    public function create(User $user, array $quantities, ?string $notes, int $vehicleId, bool $checkAccess = false): array
    {
        return DB::transaction(function () use ($user, $quantities, $notes, $vehicleId, $checkAccess): array {
            if ($checkAccess) {
                $user = User::query()->whereKey($user->id)->lockForUpdate()->first();
                if (! $user || ! $user->isActive() || ! InvPesonaAccessUser::query()->where('user_id', $user->id)->lockForUpdate()->first()) {
                    throw ValidationException::withMessages(['user_id' => 'Pemohon harus aktif dan memiliki akses Inventory Pesona.']);
                }
            }
            $vehicle = InvPesonaVehicle::query()->whereKey($vehicleId)->lockForUpdate()->first();
            if (! $vehicle || ! $vehicle->is_active) {
                throw ValidationException::withMessages(['inv_pesona_vehicle_id' => 'Nopol tidak tersedia atau sudah nonaktif. Pilih nopol aktif.']);
            }
            $ids = array_keys($quantities);
            sort($ids, SORT_NUMERIC);
            $items = InvPesonaItem::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
            if ($items->count() !== count($ids)) {
                throw ValidationException::withMessages(['items' => 'Ada barang yang tidak tersedia. Periksa pilihan barang.']);
            }
            foreach ($items as $item) {
                $qty = (int) $quantities[$item->id];
                if (! $item->is_active || $item->deleted_at !== null || $item->stock_qty <= 0 || $qty < $item->min_request_qty || $qty > $item->stock_qty) {
                    throw ValidationException::withMessages(['items' => 'Barang '.$item->name.' tidak tersedia atau jumlah tidak sesuai minimum dan stok terbaru.']);
                }
            }
            $pt = $user->pt;
            $number = 'IP-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(8)));
            $submission = InvPesonaRequest::create([
                'request_number' => $number, 'user_id' => $user->id, 'user_name_snapshot' => $user->name,
                'pt_id' => $pt?->id, 'pt_name_snapshot' => $pt?->name,
                'inv_pesona_vehicle_id' => $vehicle->id, 'nopol_snapshot' => $vehicle->nopol,
                'status' => InvPesonaRequest::STATUS_PENDING, 'notes' => $notes,
            ]);
            foreach ($items as $item) {
                $submission->items()->create([
                    'inv_pesona_item_id' => $item->id, 'qty' => (int) $quantities[$item->id],
                    'item_name_snapshot' => $item->name, 'unit_name_snapshot' => $item->unit_name,
                    'unit_size_snapshot' => $item->unit_size, 'content_unit_name_snapshot' => $item->content_unit_name,
                    'status' => InvPesonaRequestItem::STATUS_PENDING,
                ]);
            }

            return ['id' => $submission->id, 'number' => $number];
        });
    }
}
