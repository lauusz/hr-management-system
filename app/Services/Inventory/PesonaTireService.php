<?php

namespace App\Services\Inventory;

use App\Models\InvPesonaTire;
use App\Models\InvPesonaTireHistory;
use App\Models\InvPesonaVehicle;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Semua perubahan ban dan riwayatnya: satu transaksi, baris dikunci, status divalidasi ulang.
 * Tidak menyentuh inv_pesona_items maupun stock_qty.
 */
class PesonaTireService
{
    private const META = ['serial_number', 'tire_type', 'brand', 'size', 'tire_condition', 'is_usable', 'received_on', 'supplier_name', 'document_number', 'notes'];

    /** $event: AWAL atau MASUK. Lokasi ARMADA hanya untuk AWAL dan memerlukan vehicle_id + position_number. */
    public function create(array $data, string $event, CarbonInterface $at, ?string $historyNotes, User $user): InvPesonaTire
    {
        return $this->run(function () use ($data, $event, $at, $historyNotes, $user) {
            $onVehicle = $event === 'AWAL' && ($data['location_status'] ?? null) === InvPesonaTire::LOCATION_VEHICLE;
            $attributes = array_intersect_key($data, array_flip(self::META)) + [
                'location_status' => InvPesonaTire::LOCATION_WAREHOUSE,
                'is_active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
            if ($onVehicle) {
                $this->assertSeat($data['inv_pesona_vehicle_id'], $data['position_number']);
                if (($attributes['serial_number'] ?? null) === null) {
                    $this->fail('serial_number', 'Ban yang sudah terpasang pada armada wajib memiliki nomor seri.');
                }
                $attributes['location_status'] = InvPesonaTire::LOCATION_VEHICLE;
                $attributes['inv_pesona_vehicle_id'] = $data['inv_pesona_vehicle_id'];
                $attributes['position_number'] = $data['position_number'];
            }

            $tire = new InvPesonaTire($attributes);
            $tire->save();
            $this->log($tire, null, $event, $at, $historyNotes, $user);

            return $tire;
        });
    }

    /** Edit metadata saja. Mengembalikan false bila tidak ada perubahan (tanpa riwayat). */
    public function updateMetadata(int $id, array $data, CarbonInterface $at, ?string $historyNotes, User $user): bool
    {
        return $this->run(function () use ($id, $data, $at, $historyNotes, $user) {
            $tire = InvPesonaTire::query()->lockForUpdate()->findOrFail($id);
            $before = $this->snapshot($tire);
            $tire->fill(array_intersect_key($data, array_flip(self::META)));
            if (! $tire->isDirty()) {
                return false;
            }
            if ($tire->location_status === InvPesonaTire::LOCATION_VEHICLE && $tire->serial_number === null) {
                $this->fail('serial_number', 'Ban yang terpasang pada armada tidak boleh dikosongkan nomor serinya.');
            }
            $tire->updated_by = $user->id;
            $tire->save();
            $this->log($tire, $before, 'UBAH', $at, $historyNotes, $user);

            return true;
        });
    }

    public function install(int $id, int $vehicleId, int $position, CarbonInterface $at, ?string $historyNotes, User $user): InvPesonaTire
    {
        return $this->run(function () use ($id, $vehicleId, $position, $at, $historyNotes, $user) {
            // Kunci kendaraan lebih dulu agar pemasangan pada armada yang sama berurutan.
            $this->assertSeat($vehicleId, $position);
            $tire = InvPesonaTire::query()->lockForUpdate()->findOrFail($id);
            if (! $tire->is_active) {
                $this->fail('tire', 'Ban nonaktif tidak dapat dipasang. Aktifkan kembali terlebih dahulu.');
            }
            if ($tire->location_status !== InvPesonaTire::LOCATION_WAREHOUSE) {
                $this->fail('tire', 'Ban sedang terpasang pada armada. Kembalikan ke gudang terlebih dahulu.');
            }
            if ($tire->serial_number === null) {
                $this->fail('tire', 'Ban tanpa nomor seri tidak dapat dipasang. Lengkapi nomor seri terlebih dahulu.');
            }
            if ($tire->is_usable !== true) {
                $this->fail('tire', 'Hanya ban berstatus layak yang dapat dipasang.');
            }

            $before = $this->snapshot($tire);
            $tire->forceFill([
                'location_status' => InvPesonaTire::LOCATION_VEHICLE,
                'inv_pesona_vehicle_id' => $vehicleId,
                'position_number' => $position,
                'updated_by' => $user->id,
            ])->save();
            $this->log($tire, $before, 'PASANG', $at, $historyNotes, $user);

            return $tire;
        });
    }

    /** $data: tire_condition (BARU/BEKAS) dan is_usable (bool|null) hasil pemeriksaan petugas. */
    /** Bila $expectedVehicleId/$expectedPosition diberikan (dari diagram), asal diperiksa ulang di bawah lock. */
    public function returnToWarehouse(int $id, array $data, CarbonInterface $at, ?string $historyNotes, User $user, ?int $expectedVehicleId = null, ?int $expectedPosition = null): InvPesonaTire
    {
        return $this->run(function () use ($id, $data, $at, $historyNotes, $user, $expectedVehicleId, $expectedPosition) {
            $tire = InvPesonaTire::query()->lockForUpdate()->findOrFail($id);
            if ($tire->location_status !== InvPesonaTire::LOCATION_VEHICLE) {
                $this->fail('tire', 'Ban tidak sedang terpasang pada armada. Perbarui tampilan diagram.');
            }
            if (($expectedVehicleId !== null && (int) $tire->inv_pesona_vehicle_id !== $expectedVehicleId)
                || ($expectedPosition !== null && $tire->position_number !== $expectedPosition)) {
                $this->fail('tire', 'Ban sudah berpindah posisi sejak halaman dibuka. Perbarui tampilan diagram; tidak ada perubahan yang disimpan.');
            }

            $before = $this->snapshot($tire);
            $tire->forceFill([
                'location_status' => InvPesonaTire::LOCATION_WAREHOUSE,
                'inv_pesona_vehicle_id' => null,
                'position_number' => null,
                'tire_condition' => $data['tire_condition'],
                'is_usable' => $data['is_usable'],
                'updated_by' => $user->id,
            ])->save();
            $this->log($tire, $before, 'KEMBALI', $at, $historyNotes, $user);

            return $tire;
        });
    }

    /**
     * Ganti ban: ban lama dicopot ke gudang (KEMBALI) dan ban pengganti dari gudang dipasang pada posisi yang sama
     * (PASANG), dalam satu transaksi. Kendaraan dikunci lebih dulu, lalu kedua ban berurutan menurut ID.
     * $oldState: tire_condition dan is_usable ban lama hasil pemeriksaan petugas. Mengembalikan [lama, pengganti].
     */
    public function replace(int $oldId, int $newId, int $vehicleId, int $position, array $oldState, CarbonInterface $at, ?string $historyNotes, User $user): array
    {
        return $this->run(function () use ($oldId, $newId, $vehicleId, $position, $oldState, $at, $historyNotes, $user) {
            if ($oldId === $newId) {
                $this->fail('new_tire_id', 'Ban pengganti harus berbeda dari ban yang dicopot.');
            }
            $vehicle = $this->lockActiveVehicle($vehicleId, $position);
            $locked = InvPesonaTire::query()->whereIn('id', [$oldId, $newId])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $old = $locked->get($oldId);
            $new = $locked->get($newId);
            if (! $old || ! $new) {
                $this->fail('tire', 'Ban tidak ditemukan.');
            }
            // Keadaan aktual dari database, bukan dari halaman yang dibuka sebelumnya.
            if ($old->location_status !== InvPesonaTire::LOCATION_VEHICLE || (int) $old->inv_pesona_vehicle_id !== $vehicleId || $old->position_number !== $position) {
                $this->fail('tire', 'Ban lama sudah berpindah atau berubah sejak halaman dibuka. Perbarui tampilan; tidak ada perubahan yang disimpan.');
            }
            if (! $new->is_active || $new->location_status !== InvPesonaTire::LOCATION_WAREHOUSE || $new->serial_number === null || $new->is_usable !== true) {
                $this->fail('new_tire_id', 'Ban pengganti harus aktif, berada di gudang, bernomor seri, dan layak. Perbarui tampilan dan pilih ban lain.');
            }

            $place = "{$vehicle->nopol}, posisi {$position}";
            $extra = $historyNotes ? ' Catatan: '.$historyNotes : '';
            $oldBefore = $this->snapshot($old);
            $old->forceFill([
                'location_status' => InvPesonaTire::LOCATION_WAREHOUSE,
                'inv_pesona_vehicle_id' => null,
                'position_number' => null,
                'tire_condition' => $oldState['tire_condition'],
                'is_usable' => $oldState['is_usable'],
                'updated_by' => $user->id,
            ])->save();
            $this->log($old, $oldBefore, 'KEMBALI', $at, "Penggantian ban: digantikan ban {$new->serial_number} pada {$place}.".$extra, $user);

            $newBefore = $this->snapshot($new);
            $new->forceFill([
                'location_status' => InvPesonaTire::LOCATION_VEHICLE,
                'inv_pesona_vehicle_id' => $vehicleId,
                'position_number' => $position,
                'updated_by' => $user->id,
            ])->save();
            $this->log($new, $newBefore, 'PASANG', $at, "Penggantian ban: menggantikan ban {$old->serial_number} pada {$place}.".$extra, $user);

            return [$old, $new];
        });
    }

    public function setActive(int $id, bool $active, CarbonInterface $at, ?string $historyNotes, User $user): InvPesonaTire
    {
        return $this->run(function () use ($id, $active, $at, $historyNotes, $user) {
            $tire = InvPesonaTire::query()->lockForUpdate()->findOrFail($id);
            if ($tire->is_active === $active) {
                $this->fail('tire', $active ? 'Ban sudah aktif.' : 'Ban sudah nonaktif.');
            }
            if (! $active && $tire->location_status !== InvPesonaTire::LOCATION_WAREHOUSE) {
                $this->fail('tire', 'Ban yang masih terpasang pada armada harus dikembalikan ke gudang terlebih dahulu.');
            }

            $before = $this->snapshot($tire);
            $tire->forceFill(['is_active' => $active, 'updated_by' => $user->id])->save();
            $this->log($tire, $before, $active ? 'AKTIF' : 'NONAKTIF', $at, $historyNotes, $user);

            return $tire;
        });
    }

    /** Snapshot lengkap, termasuk nopol saat kejadian. */
    public function snapshot(InvPesonaTire $tire): array
    {
        return [
            'id' => $tire->id,
            'serial_number' => $tire->serial_number,
            'tire_type' => $tire->tire_type,
            'brand' => $tire->brand,
            'size' => $tire->size,
            'tire_condition' => $tire->tire_condition,
            'is_usable' => $tire->is_usable,
            'received_on' => $tire->received_on?->format('Y-m-d'),
            'supplier_name' => $tire->supplier_name,
            'document_number' => $tire->document_number,
            'notes' => $tire->notes,
            'location_status' => $tire->location_status,
            'inv_pesona_vehicle_id' => $tire->inv_pesona_vehicle_id,
            'nopol' => $tire->inv_pesona_vehicle_id
                ? InvPesonaVehicle::query()->whereKey($tire->inv_pesona_vehicle_id)->value('nopol')
                : null,
            'position_number' => $tire->position_number,
            'is_active' => $tire->is_active,
        ];
    }

    private function log(InvPesonaTire $tire, ?array $before, string $event, CarbonInterface $at, ?string $notes, User $user): void
    {
        $after = $this->snapshot($tire);
        InvPesonaTireHistory::query()->create([
            'inv_pesona_tire_id' => $tire->id,
            'event_type' => $event,
            'occurred_at' => $at,
            'from_vehicle_id' => $before['inv_pesona_vehicle_id'] ?? null,
            'from_position_number' => $before['position_number'] ?? null,
            'to_vehicle_id' => $after['inv_pesona_vehicle_id'],
            'to_position_number' => $after['position_number'],
            'before_snapshot' => $before,
            'after_snapshot' => $after,
            'notes' => $notes,
            'created_by' => $user->id,
            'created_by_name_snapshot' => $user->name,
            'created_at' => now(),
        ]);
    }

    /** Kunci kendaraan aktif lalu pastikan posisi belum ditempati ban lain. */
    private function assertSeat(int $vehicleId, int $position): void
    {
        $vehicle = $this->lockActiveVehicle($vehicleId, $position);
        $occupied = InvPesonaTire::query()->where('inv_pesona_vehicle_id', $vehicleId)
            ->where('position_number', $position)->exists();
        if ($occupied) {
            $this->fail('position_number', "Posisi {$position} pada {$vehicle->nopol} sudah ditempati ban lain. Perbarui tampilan, atau kembalikan ban tersebut ke gudang terlebih dahulu.");
        }
    }

    /** Posisi harus ada pada mapping; kendaraan dikunci dan harus aktif. */
    private function lockActiveVehicle(int $vehicleId, int $position): InvPesonaVehicle
    {
        if (! PesonaTireLayout::has($position)) {
            $this->fail('position_number', "Posisi {$position} tidak aktif pada diagram ban (posisi 1–18); penempatan baru hanya pada posisi 1–18.");
        }
        $vehicle = InvPesonaVehicle::query()->lockForUpdate()->find($vehicleId);
        if (! $vehicle || ! $vehicle->is_active) {
            $this->fail('inv_pesona_vehicle_id', 'Kendaraan tidak ditemukan atau sudah nonaktif.');
        }

        return $vehicle;
    }

    private function run(callable $work): mixed
    {
        try {
            return DB::transaction($work);
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                if (str_contains($e->getMessage(), 'inv_pesona_tires_serial_unique')) {
                    $this->fail('serial_number', 'Nomor seri sudah terdaftar (termasuk ban nonaktif).');
                }
                if (str_contains($e->getMessage(), 'inv_pesona_tires_vehicle_position_unique')) {
                    $this->fail('position_number', 'Posisi tersebut sudah ditempati ban lain pada armada ini.');
                }
            }
            throw $e;
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
