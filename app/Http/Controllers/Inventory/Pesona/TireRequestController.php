<?php

namespace App\Http\Controllers\Inventory\Pesona;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaTire;
use App\Models\InvPesonaTireRequest;
use App\Models\InvPesonaVehicle;
use App\Services\Inventory\PesonaTireLayout;
use App\Services\Inventory\PesonaTireRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Pengajuan ban milik pengguna sendiri. Tidak memasang/memindahkan ban dan tidak menyentuh stok barang umum. */
class TireRequestController extends Controller
{
    public function __construct(private PesonaTireRequestService $requests) {}

    public function index(Request $request): View|Response
    {
        $f = $request->validate(['status' => ['nullable', Rule::in(array_keys(InvPesonaTireRequest::STATUSES))]]);
        $status = $f['status'] ?? '';
        $items = InvPesonaTireRequest::query()->where('requested_by', $request->user()->id)
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(15)->appends(['status' => $status]);

        // Daftar saja untuk AJAX (filter status/pagination); tetap dibatasi pengajuan milik pengguna.
        if ($request->query('partial') === 'list') {
            return response()->view('inventory.pesona.tire_requests._list', compact('items', 'status'))->header('X-Fleet-Partial', 'list');
        }

        return view('inventory.pesona.tire_requests.index', compact('items', 'status'));
    }

    public function create(Request $request): View|Response
    {
        $f = $request->validate(['vehicle_id' => ['nullable', 'integer', 'min:1'], 'pos' => ['nullable', 'integer']]);
        $vehicles = InvPesonaVehicle::query()->where('is_active', true)->orderBy('nopol')->get(['id', 'nopol']);
        $vehicle = isset($f['vehicle_id']) ? $vehicles->firstWhere('id', (int) $f['vehicle_id']) : null;

        // Kondisi posisi dari server (hanya nomor seri; untuk tampilan, divalidasi ulang saat submit).
        $bySlot = collect();
        $selected = null;
        if ($vehicle) {
            $bySlot = InvPesonaTire::query()->where('inv_pesona_vehicle_id', $vehicle->id)->get(['serial_number', 'position_number', 'tire_type', 'brand', 'size', 'tire_condition', 'is_usable', 'notes'])
                ->filter(fn ($t) => PesonaTireLayout::has($t->position_number))->keyBy('position_number');
            // Setelah validasi gagal, posisi lama (untuk armada yang sama) dipulihkan; selain itu pakai ?pos=.
            $old = (int) $request->old('inv_pesona_vehicle_id') === $vehicle->id ? $request->old('position_number') : null;
            $pos = $old ?? ($f['pos'] ?? null);
            $selected = $pos !== null && PesonaTireLayout::has((int) $pos) ? (int) $pos : null;
        }

        // Respons parsial (AJAX), hanya data pengajuan tanpa panel/aksi admin: 'area' = bagian yang bergantung pada armada;
        // 'position' = isi modal satu posisi (informasi ban terpasang dan form pengajuan).
        $partial = $request->query('partial');
        if ($partial === 'area') {
            return response()->view('inventory.pesona.tire_requests._create_area', compact('vehicle', 'bySlot', 'selected'))->header('X-Fleet-Partial', 'area');
        }
        if ($partial === 'position') {
            abort_unless($vehicle && $selected !== null, 404);

            return response()->view('inventory.pesona.tire_requests._create_modal', ['vehicle' => $vehicle, 'selected' => $selected, 'tire' => $bySlot->get($selected)])->header('X-Fleet-Partial', 'position');
        }

        return view('inventory.pesona.tire_requests.create', compact('vehicles', 'vehicle', 'bySlot', 'selected'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'inv_pesona_vehicle_id' => ['required', 'integer', 'min:1'],
            'position_number' => ['required', 'integer', Rule::in(PesonaTireLayout::numbers())],
            'request_type' => ['required', Rule::in(array_keys(InvPesonaTireRequest::TYPES))],
            'need_notes' => ['required', 'string', 'max:1000'],
        ], ['need_notes.required' => 'Keterangan kebutuhan wajib diisi.']);

        $tireRequest = $this->requests->create($request->user(), (int) $data['inv_pesona_vehicle_id'], (int) $data['position_number'], $data['request_type'], trim($data['need_notes']));

        return redirect()->route('inventory.pesona.tire-requests.show', $tireRequest)->with('success', 'Pengajuan ban '.$tireRequest->request_number.' berhasil dikirim.');
    }

    public function show(Request $request, InvPesonaTireRequest $tireRequest): View
    {
        abort_unless((int) $tireRequest->requested_by === (int) $request->user()->id, 404);

        return view('inventory.pesona.tire_requests.show', ['r' => $tireRequest]);
    }
}
