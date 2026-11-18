<?php

namespace App\Services\Inventory;

use App\Models\InvPesonaAccessUser;
use App\Models\InvPesonaTire;
use App\Models\InvPesonaTireHistory;
use App\Models\InvPesonaTireRequest;
use App\Models\InvPesonaVehicle;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pengajuan ban (satu posisi per pengajuan). Pemasangan/penggantian nyata hanya lewat PesonaTireService;
 * pengajuan tidak memasang, memindahkan, atau mereservasi ban dan tidak menyentuh stok barang umum.
 */
class PesonaTireRequestService
{
    public function __construct(private PesonaTireService $tires) {}

    /** Validasi ulang di server: pengguna, kendaraan, posisi, dan jenis kebutuhan terhadap keadaan saat ini. */
    public function create(User $user, int $vehicleId, int $position, string $type, string $needNotes): InvPesonaTireRequest
    {
        return DB::transaction(function () use ($user, $vehicleId, $position, $type, $needNotes) {
            $fresh = User::query()->find($user->id);
            if (! $fresh || ! $fresh->isActive() || ! InvPesonaAccessUser::query()->where('user_id', $user->id)->exists()) {
                $this->fail('request_type', 'Anda tidak memiliki akses untuk mengajukan ban.');
            }
            if (! PesonaTireLayout::has($position)) {
                $this->fail('position_number', "Posisi {$position} tidak aktif pada diagram ban (posisi 1–18).");
            }
            // Kunci kendaraan: pengajuan untuk armada yang sama diproses berurutan.
            $vehicle = InvPesonaVehicle::query()->lockForUpdate()->find($vehicleId);
            if (! $vehicle || ! $vehicle->is_active) {
                $this->fail('inv_pesona_vehicle_id', 'Armada tidak ditemukan atau sudah nonaktif. Pilih armada aktif.');
            }
            $label = PesonaTireLayout::label($position);
            $pending = InvPesonaTireRequest::query()->where('inv_pesona_vehicle_id', $vehicleId)
                ->where('position_number', $position)->where('status', InvPesonaTireRequest::STATUS_PENDING)->value('request_number');
            if ($pending) {
                $this->fail('position_number', "Sudah ada pengajuan yang menunggu untuk {$label} pada {$vehicle->nopol} (No. {$pending}).");
            }

            $current = InvPesonaTire::query()->where('inv_pesona_vehicle_id', $vehicleId)->where('position_number', $position)->first();
            if ($type === InvPesonaTireRequest::TYPE_INSTALL && $current) {
                $this->fail('request_type', "{$label} pada {$vehicle->nopol} sudah terisi ban. Ajukan Ganti Ban, atau muat ulang form.");
            }
            if ($type === InvPesonaTireRequest::TYPE_REPLACE && ! $current) {
                $this->fail('request_type', "{$label} pada {$vehicle->nopol} masih kosong. Ajukan Pasang Ban, atau muat ulang form.");
            }

            return InvPesonaTireRequest::query()->create([
                'request_number' => 'TB-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(8))),
                'requested_by' => $user->id,
                'requested_by_name_snapshot' => $user->name,
                'inv_pesona_vehicle_id' => $vehicle->id,
                'nopol_snapshot' => $vehicle->nopol,
                'position_number' => $position,
                'request_type' => $type,
                'need_notes' => $needNotes,
                'old_tire_id' => $current?->id,
                'old_tire_snapshot' => $current ? $this->tires->snapshot($current) : null,
                'status' => InvPesonaTireRequest::STATUS_PENDING,
            ]);
        });
    }

    /**
     * Setujui dan proses dalam satu transaksi (pengajuan dikunci lebih dulu, lalu kendaraan dan ban di PesonaTireService).
     * $oldState (tire_condition, is_usable) wajib untuk GANTI.
     */
    public function approve(int $requestId, int $tireId, ?array $oldState, User $admin): InvPesonaTireRequest
    {
        return DB::transaction(function () use ($requestId, $tireId, $oldState, $admin) {
            $request = InvPesonaTireRequest::query()->lockForUpdate()->findOrFail($requestId);
            if ($request->status !== InvPesonaTireRequest::STATUS_PENDING) {
                $this->fail('request', 'Pengajuan sudah diproses. Muat ulang halaman.');
            }
            // Pengajuan lama untuk posisi di luar skema aktif (mis. 19–22) tetap terbaca dan dapat ditolak, tetapi tidak dapat diproses.
            if (! PesonaTireLayout::has($request->position_number)) {
                $this->fail('request', "Posisi {$request->position_number} tidak aktif pada diagram ban (posisi 1–18), sehingga pengajuan ini tidak dapat disetujui. Tolak pengajuan bila tidak diperlukan.");
            }
            $note = "Pengajuan ban {$request->request_number}.";
            $at = now();

            if ($request->request_type === InvPesonaTireRequest::TYPE_INSTALL) {
                $assigned = $this->tires->install($tireId, $request->inv_pesona_vehicle_id, $request->position_number, $at, $note, $admin);
                $returnHistoryId = null;
            } else {
                if ($oldState === null) {
                    $this->fail('tire_condition', 'Kondisi dan kelayakan ban lama wajib dipilih.');
                }
                // Ban lama harus masih ban yang sama pada armada dan posisi yang diajukan; bila tidak, ditolak oleh service.
                [$old, $assigned] = $this->tires->replace($request->old_tire_id, $tireId, $request->inv_pesona_vehicle_id, $request->position_number, $oldState, $at, $note, $admin);
                $returnHistoryId = $this->lastHistoryId($old->id, 'KEMBALI');
            }

            $request->forceFill([
                'status' => InvPesonaTireRequest::STATUS_APPROVED,
                'assigned_tire_id' => $assigned->id,
                'assigned_tire_snapshot' => $this->tires->snapshot($assigned),
                'reviewed_by' => $admin->id,
                'reviewed_by_name_snapshot' => $admin->name,
                'reviewed_at' => $at,
                'install_history_id' => $this->lastHistoryId($assigned->id, 'PASANG'),
                'return_history_id' => $returnHistoryId,
            ])->save();

            return $request;
        });
    }

    public function reject(int $requestId, string $reason, User $admin): InvPesonaTireRequest
    {
        return DB::transaction(function () use ($requestId, $reason, $admin) {
            $request = InvPesonaTireRequest::query()->lockForUpdate()->findOrFail($requestId);
            if ($request->status !== InvPesonaTireRequest::STATUS_PENDING) {
                $this->fail('request', 'Pengajuan sudah diproses. Muat ulang halaman.');
            }
            $request->forceFill([
                'status' => InvPesonaTireRequest::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'reviewed_by' => $admin->id,
                'reviewed_by_name_snapshot' => $admin->name,
                'reviewed_at' => now(),
            ])->save();

            return $request;
        });
    }

    // Dibaca dalam transaksi yang sama, sehingga riwayat yang baru ditulis oleh PesonaTireService terlihat.
    private function lastHistoryId(int $tireId, string $event): int
    {
        return (int) InvPesonaTireHistory::query()->where('inv_pesona_tire_id', $tireId)->where('event_type', $event)->max('id');
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
