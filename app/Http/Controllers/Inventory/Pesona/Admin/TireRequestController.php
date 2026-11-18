<?php

namespace App\Http\Controllers\Inventory\Pesona\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaTire;
use App\Models\InvPesonaTireRequest;
use App\Models\InvPesonaVehicle;
use App\Services\Inventory\PesonaTireRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Approval ban oleh Admin Pesona. Transaksi dan riwayat ada di PesonaTireRequestService / PesonaTireService. */
class TireRequestController extends Controller
{
    public function __construct(private PesonaTireRequestService $requests) {}

    public function index(Request $request): View
    {
        $f = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::in(array_keys(InvPesonaTireRequest::STATUSES))]]);
        $q = trim($f['q'] ?? '');
        $status = $f['status'] ?? '';
        $items = InvPesonaTireRequest::query()
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.addcslashes($q, '%_\\').'%';
                $query->where(fn ($w) => $w->where('request_number', 'like', $like)->orWhere('nopol_snapshot', 'like', $like)->orWhere('requested_by_name_snapshot', 'like', $like));
            })
            ->orderByRaw("status = 'PENDING' desc")->orderByDesc('created_at')->orderByDesc('id')->paginate(20)->appends(['q' => $q, 'status' => $status]);

        return view('inventory.pesona.admin.tire_requests.index', compact('items', 'q', 'status'));
    }

    public function show(InvPesonaTireRequest $tireRequest): View
    {
        $vehicle = InvPesonaVehicle::query()->find($tireRequest->inv_pesona_vehicle_id);
        $current = InvPesonaTire::query()->where('inv_pesona_vehicle_id', $tireRequest->inv_pesona_vehicle_id)
            ->where('position_number', $tireRequest->position_number)->first(['id', 'serial_number', 'tire_type', 'tire_condition', 'is_usable']);
        $pending = $tireRequest->status === InvPesonaTireRequest::STATUS_PENDING;
        $ready = $pending && $vehicle?->is_active
            ? InvPesonaTire::query()->ready()->orderBy('serial_number')->limit(500)->get(['id', 'serial_number', 'tire_type', 'brand', 'size', 'tire_condition'])
            : collect();

        return view('inventory.pesona.admin.tire_requests.show', ['r' => $tireRequest, 'vehicle' => $vehicle, 'current' => $current, 'ready' => $ready]);
    }

    public function approve(Request $request, InvPesonaTireRequest $tireRequest): RedirectResponse
    {
        $replace = $tireRequest->request_type === InvPesonaTireRequest::TYPE_REPLACE;
        $data = $request->validate([
            'tire_id' => ['required', 'integer', 'min:1', 'exists:inv_pesona_tires,id'],
            'tire_condition' => [Rule::requiredIf($replace), 'nullable', Rule::in(array_keys(InvPesonaTire::CONDITIONS))],
            'is_usable' => [Rule::requiredIf($replace), 'nullable', Rule::in(['1', '0', 'unknown'])],
        ], ['tire_id.required' => 'Pilih ban siap pakai dari gudang.']);

        $oldState = $replace ? ['tire_condition' => $data['tire_condition'], 'is_usable' => ['1' => true, '0' => false][$data['is_usable']] ?? null] : null;
        $done = $this->requests->approve($tireRequest->id, (int) $data['tire_id'], $oldState, $request->user());

        return redirect()->route('inventory.pesona.admin.tire-requests.show', $done)
            ->with('success', "Pengajuan {$done->request_number} disetujui. Ban {$done->assigned_tire_snapshot['serial_number']} tercatat pada {$done->nopol_snapshot}, posisi {$done->position_number}.");
    }

    public function reject(Request $request, InvPesonaTireRequest $tireRequest): RedirectResponse
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']], ['rejection_reason.required' => 'Alasan penolakan wajib diisi.']);
        $done = $this->requests->reject($tireRequest->id, trim($data['rejection_reason']), $request->user());

        return redirect()->route('inventory.pesona.admin.tire-requests.show', $done)->with('success', "Pengajuan {$done->request_number} ditolak.");
    }
}
