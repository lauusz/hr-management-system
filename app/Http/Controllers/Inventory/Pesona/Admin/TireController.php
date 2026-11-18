<?php

namespace App\Http\Controllers\Inventory\Pesona\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaTire;
use App\Models\InvPesonaTireHistory;
use App\Models\InvPesonaVehicle;
use App\Services\Inventory\PesonaTireLayout;
use App\Services\Inventory\PesonaTireService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TireController extends Controller
{
    private const TOKEN_KEY = 'pesona_tire_create_tokens';

    public function __construct(private PesonaTireService $tires) {}

    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'all'])],
            'location' => ['nullable', Rule::in(['all', 'GUDANG', 'ARMADA'])],
            'vehicle_id' => ['nullable', 'integer', 'min:1'],
            'usable' => ['nullable', Rule::in(['all', '1', '0', 'unknown'])],
        ]);
        $filters = [
            'q' => trim($f['q'] ?? ''),
            'status' => $f['status'] ?? 'active',
            'location' => $f['location'] ?? 'all',
            'vehicle_id' => $f['vehicle_id'] ?? '',
            'usable' => $f['usable'] ?? 'all',
        ];

        $tires = InvPesonaTire::query()->with('vehicle:id,nopol')
            ->when($filters['q'] !== '', fn ($q) => $q->where('serial_number', 'like', '%'.addcslashes($filters['q'], '%_\\').'%'))
            ->when($filters['status'] !== 'all', fn ($q) => $q->where('is_active', $filters['status'] === 'active'))
            ->when($filters['location'] !== 'all', fn ($q) => $q->where('location_status', $filters['location']))
            ->when($filters['vehicle_id'] !== '', fn ($q) => $q->where('inv_pesona_vehicle_id', $filters['vehicle_id']))
            ->when($filters['usable'] === 'unknown', fn ($q) => $q->whereNull('is_usable'))
            ->when(in_array($filters['usable'], ['1', '0'], true), fn ($q) => $q->where('is_usable', $filters['usable'] === '1'))
            ->orderByDesc('id')->paginate(20)->appends($filters);

        return view('inventory.pesona.admin.tires.index', [
            'tires' => $tires,
            'filters' => $filters,
            'vehicles' => InvPesonaVehicle::query()->orderBy('nopol')->get(['id', 'nopol', 'is_active']),
            'readyCount' => InvPesonaTire::query()->ready()->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $tokens = array_slice((array) $request->session()->get(self::TOKEN_KEY, []), -9);
        $tokens[] = $token = (string) Str::uuid();
        $request->session()->put(self::TOKEN_KEY, $tokens);

        return view('inventory.pesona.admin.tires.form', [
            'tire' => new InvPesonaTire(['tire_condition' => 'BARU']),
            'vehicles' => $this->activeVehicles(),
            'token' => $token,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tokens = (array) $request->session()->get(self::TOKEN_KEY, []);
        $submitted = $request->input('form_token');
        if (! is_string($submitted) || ! in_array($submitted, $tokens, true)) {
            throw ValidationException::withMessages(['form_token' => 'Form sudah dikirim atau kedaluwarsa. Periksa daftar ban, lalu buka form tambah kembali jika perlu.']);
        }

        $onVehicle = $request->input('mode') === 'AWAL' && $request->input('location_status') === 'ARMADA';
        $rules = $this->metaRules() + [
            'mode' => ['required', Rule::in(['AWAL', 'MASUK'])],
            'location_status' => ['required_if:mode,AWAL', 'nullable', Rule::in(['GUDANG', 'ARMADA'])],
            'inv_pesona_vehicle_id' => [Rule::requiredIf($onVehicle), 'nullable', 'integer', 'min:1'],
            'position_number' => [Rule::requiredIf($onVehicle), 'nullable', 'integer', Rule::in(PesonaTireLayout::numbers())],
        ] + $this->eventRules();
        // Penerimaan baru wajib bernomor seri; kosong hanya untuk pencatatan awal.
        if ($request->input('mode') === 'MASUK') {
            $rules['serial_number'][0] = 'required';
        }
        $data = $request->validate($rules);
        $data = $this->normalize($data);

        $event = $data['mode'];
        if ($event === 'MASUK') {
            $data['location_status'] = InvPesonaTire::LOCATION_WAREHOUSE;
        }
        $tire = $this->tires->create($data, $event, $this->occurredAt($data), $data['event_notes'] ?? null, $request->user());

        $request->session()->put(self::TOKEN_KEY, array_values(array_diff($tokens, [$submitted])));

        return redirect()->route('inventory.pesona.admin.tires.show', $tire)->with('success', $event === 'MASUK' ? 'Penerimaan ban berhasil dicatat.' : 'Pencatatan awal ban berhasil disimpan.');
    }

    public function show(InvPesonaTire $tire): View
    {
        $tire->load('vehicle:id,nopol,is_active');

        return view('inventory.pesona.admin.tires.show', [
            'tire' => $tire,
            'histories' => $tire->histories()->orderByDesc('occurred_at')->orderByDesc('id')->paginate(20, ['*'], 'riwayat'),
            'vehicles' => $this->activeVehicles(),
        ]);
    }

    public function edit(InvPesonaTire $tire): View
    {
        return view('inventory.pesona.admin.tires.form', ['tire' => $tire, 'vehicles' => collect(), 'token' => null]);
    }

    public function update(Request $request, InvPesonaTire $tire): RedirectResponse
    {
        $data = $this->normalize($request->validate($this->metaRules($tire) + $this->eventRules()));
        $changed = $this->tires->updateMetadata($tire->id, $data, $this->occurredAt($data), $data['event_notes'] ?? null, $request->user());

        return redirect()->route('inventory.pesona.admin.tires.show', $tire)
            ->with($changed ? 'success' : 'warning', $changed ? 'Data ban berhasil diperbarui.' : 'Tidak ada perubahan data.');
    }

    public function install(Request $request, InvPesonaTire $tire): RedirectResponse
    {
        $data = $request->validate([
            'inv_pesona_vehicle_id' => ['required', 'integer', 'min:1'],
            'position_number' => ['required', 'integer', Rule::in(PesonaTireLayout::numbers())],
        ] + $this->eventRules());
        $data = $this->normalize($data);
        $this->tires->install($tire->id, (int) $data['inv_pesona_vehicle_id'], (int) $data['position_number'], $this->occurredAt($data), $data['event_notes'] ?? null, $request->user());

        return redirect()->route('inventory.pesona.admin.tires.show', $tire)->with('success', 'Ban berhasil dipasang pada armada.');
    }

    public function returnToWarehouse(Request $request, InvPesonaTire $tire): RedirectResponse
    {
        $data = $request->validate([
            'tire_condition' => ['required', Rule::in(array_keys(InvPesonaTire::CONDITIONS))],
            'is_usable' => ['required', Rule::in(['1', '0', 'unknown'])],
            // Hanya dikirim dari diagram: asal diperiksa ulang di service; redirect kembali ke diagram.
            // Posisi tidak dibatasi mapping aktif: ban legacy (mis. 19–22) tetap dapat dicopot; service mencocokkan kendaraan dan posisi aktual.
            'expected_vehicle_id' => ['nullable', 'integer', 'min:1'],
            'expected_position' => ['nullable', 'integer', 'min:1'],
        ] + $this->eventRules());
        $expectedVehicle = isset($data['expected_vehicle_id']) ? (int) $data['expected_vehicle_id'] : null;
        $expectedPosition = isset($data['expected_position']) ? (int) $data['expected_position'] : null;
        unset($data['expected_vehicle_id'], $data['expected_position']);
        $usable = ['1' => true, '0' => false][$data['is_usable']] ?? null;
        unset($data['is_usable']);
        $data = $this->normalize($data);
        $this->tires->returnToWarehouse($tire->id, ['tire_condition' => $data['tire_condition'], 'is_usable' => $usable], $this->occurredAt($data), $data['event_notes'] ?? null, $request->user(), $expectedVehicle, $expectedPosition);

        if ($expectedVehicle !== null) {
            return redirect()->route('inventory.pesona.admin.tires.diagram', ['vehicle_id' => $expectedVehicle, 'pos' => $expectedPosition])->with('success', 'Ban berhasil dicopot ke gudang.');
        }

        return redirect()->route('inventory.pesona.admin.tires.show', $tire)->with('success', 'Ban berhasil dikembalikan ke gudang.');
    }

    /** Diagram 18 posisi untuk satu armada, plus daftar ban terpasang. Hanya membaca; tidak mengubah data. */
    public function diagram(Request $request): View|Response
    {
        $input = $request->validate(['vehicle_id' => ['nullable', 'integer', 'min:1'], 'pos' => ['nullable', 'integer']]);
        $vehicles = InvPesonaVehicle::query()->orderBy('nopol')->get(['id', 'nopol', 'is_active']);
        $vehicle = isset($input['vehicle_id']) ? $vehicles->firstWhere('id', (int) $input['vehicle_id']) : null;

        $bySlot = collect();
        $installed = collect();
        $ready = collect();
        $selected = null;
        if ($vehicle) {
            // Satu query untuk semua posisi armada ini; ban terpasang selalu aktif (aturan SQL).
            $installed = InvPesonaTire::query()->where('inv_pesona_vehicle_id', $vehicle->id)->where('location_status', InvPesonaTire::LOCATION_VEHICLE)
                ->get(['id', 'serial_number', 'tire_type', 'brand', 'size', 'tire_condition', 'is_usable', 'notes', 'position_number'])
                ->sortBy('position_number')->values();
            $installDates = $this->installDates($installed, $vehicle->id);
            $installed->each(function ($t) use ($installDates) {
                $t->legacy = ! PesonaTireLayout::has($t->position_number); // posisi di luar skema aktif (mis. 19–22)
                $t->install_event = $installDates[$t->id] ?? null;
            });
            // Posisi lama tidak digambar sebagai posisi aktif; tetap muncul pada daftar ban terpasang.
            $bySlot = $installed->reject->legacy->keyBy('position_number');
            $pos = isset($input['pos']) ? (int) $input['pos'] : null;
            $selected = $pos !== null && PesonaTireLayout::has($pos) ? $pos : null;
            // Hanya penanda ada/tidaknya ban siap pakai; daftar pilihan dicari lewat readySearch (combobox).
            if ($selected !== null && $vehicle->is_active) {
                $ready = InvPesonaTire::query()->ready()->limit(1)->get(['id']);
            }
        }

        // Respons parsial untuk AJAX (data dan aturan sama dengan halaman penuh; middleware admin tetap berlaku):
        // 'area' = seluruh bagian yang bergantung pada armada; 'panel' = panel posisi saja.
        $partial = $request->query('partial');
        if ($partial === 'area') {
            return response()->view('inventory.pesona.admin.tires._diagram_area', compact('vehicle', 'bySlot', 'installed', 'ready', 'selected'))->header('X-Fleet-Partial', 'area');
        }
        if ($partial === 'panel') {
            abort_unless($vehicle, 404);

            return response()->view('inventory.pesona.admin.tires._diagram_panel', ['vehicle' => $vehicle, 'selected' => $selected, 'selectedTire' => $selected !== null ? $bySlot->get($selected) : null, 'ready' => $ready])->header('X-Fleet-Partial', 'panel');
        }

        return view('inventory.pesona.admin.tires.diagram', compact('vehicles', 'vehicle', 'bySlot', 'installed', 'ready', 'selected'));
    }

    /**
     * Kejadian pemasangan yang mewakili siklus aktif tiap ban (satu query batch). Kejadian terakhir yang diterapkan
     * (id riwayat terbesar, bukan occurred_at terbesar) di antara PASANG/KEMBALI/AWAL harus berupa PASANG atau AWAL
     * ke armada dan posisi ban saat ini. PASANG → tanggal occurred_at; AWAL → ['initial' => true] (tanpa bukti tanggal pasang).
     *
     * @return array<int, array{date: ?\Carbon\CarbonInterface, initial: bool}>
     */
    private function installDates($tires, int $vehicleId): array
    {
        if ($tires->isEmpty()) {
            return [];
        }
        $latest = InvPesonaTireHistory::query()->whereIn('inv_pesona_tire_id', $tires->pluck('id'))
            ->whereIn('event_type', ['PASANG', 'KEMBALI', 'AWAL'])->selectRaw('MAX(id)')->groupBy('inv_pesona_tire_id');
        $events = InvPesonaTireHistory::query()->whereIn('id', $latest)
            ->get(['id', 'inv_pesona_tire_id', 'event_type', 'occurred_at', 'to_vehicle_id', 'to_position_number'])->keyBy('inv_pesona_tire_id');
        $result = [];
        foreach ($tires as $t) {
            $e = $events->get($t->id);
            if (! $e || (int) $e->to_vehicle_id !== $vehicleId || (int) $e->to_position_number !== (int) $t->position_number) {
                continue;
            }
            $result[$t->id] = $e->event_type === 'PASANG' ? ['date' => $e->occurred_at, 'initial' => false] : ['date' => null, 'initial' => $e->event_type === 'AWAL'];
        }

        return $result;
    }

    /** Pencarian ban siap pakai (combobox modal). Hanya baca; ready() selalu berlaku, kata kunci dikelompokkan. */
    public function readySearch(Request $request): JsonResponse
    {
        $f = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1', 'max:1000']]);
        $query = InvPesonaTire::query()->ready();
        $labels = InvPesonaTire::TYPES + InvPesonaTire::CONDITIONS;
        foreach (preg_split('/\s+/u', trim($f['q'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $term) {
            if ($term === '—' || $term === '-') {
                continue; // pemisah label, bukan kata kunci
            }
            $like = '%'.addcslashes($term, '%_\\').'%';
            $codes = array_keys(array_filter($labels, fn ($label) => mb_stripos($label, $term) !== false));
            $query->where(fn ($w) => $w->where('serial_number', 'like', $like)->orWhere('brand', 'like', $like)->orWhere('size', 'like', $like)
                ->orWhereIn('tire_type', $codes)->orWhereIn('tire_condition', $codes));
        }
        $page = $query->orderBy('serial_number')->orderBy('id')
            ->simplePaginate(20, ['id', 'serial_number', 'tire_type', 'brand', 'size', 'tire_condition'], 'page', (int) ($f['page'] ?? 1));

        return response()->json([
            'items' => $page->getCollection()->map(fn ($t) => ['id' => $t->id, 'label' => $t->pickerLabel()])->values(),
            'has_more' => $page->hasMorePages(),
        ]);
    }

    /** Pemasangan dari slot diagram; transaksi dan riwayat tetap lewat PesonaTireService::install. */
    public function diagramInstall(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tire_id' => ['required', 'integer', 'min:1', 'exists:inv_pesona_tires,id'],
            'inv_pesona_vehicle_id' => ['required', 'integer', 'min:1'],
            'position_number' => ['required', 'integer', Rule::in(PesonaTireLayout::numbers())],
        ] + $this->eventRules());
        $data = $this->normalize($data);
        $this->tires->install((int) $data['tire_id'], (int) $data['inv_pesona_vehicle_id'], (int) $data['position_number'], $this->occurredAt($data), $data['event_notes'] ?? null, $request->user());

        return redirect()->route('inventory.pesona.admin.tires.diagram', ['vehicle_id' => $data['inv_pesona_vehicle_id'], 'pos' => $data['position_number']])->with('success', 'Ban berhasil dipasang pada '.PesonaTireLayout::label((int) $data['position_number']).'.');
    }

    /** Ganti ban dari slot terisi; satu transaksi di PesonaTireService::replace. */
    public function diagramReplace(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'old_tire_id' => ['required', 'integer', 'min:1', 'exists:inv_pesona_tires,id'],
            'new_tire_id' => ['required', 'integer', 'min:1', 'different:old_tire_id', 'exists:inv_pesona_tires,id'],
            'inv_pesona_vehicle_id' => ['required', 'integer', 'min:1'],
            'position_number' => ['required', 'integer', Rule::in(PesonaTireLayout::numbers())],
            'tire_condition' => ['required', Rule::in(array_keys(InvPesonaTire::CONDITIONS))],
            'is_usable' => ['required', Rule::in(['1', '0', 'unknown'])],
        ] + $this->eventRules(), ['new_tire_id.different' => 'Ban pengganti harus berbeda dari ban yang dicopot.']);
        $usable = ['1' => true, '0' => false][$data['is_usable']] ?? null;
        $data = $this->normalize($data);
        [$old, $new] = $this->tires->replace((int) $data['old_tire_id'], (int) $data['new_tire_id'], (int) $data['inv_pesona_vehicle_id'], (int) $data['position_number'],
            ['tire_condition' => $data['tire_condition'], 'is_usable' => $usable], $this->occurredAt($data), $data['event_notes'] ?? null, $request->user());
        $nopol = InvPesonaVehicle::query()->whereKey($data['inv_pesona_vehicle_id'])->value('nopol');

        return redirect()->route('inventory.pesona.admin.tires.diagram', ['vehicle_id' => $data['inv_pesona_vehicle_id'], 'pos' => $data['position_number']])
            ->with('success', "Ban {$old->serial_number} dikembalikan ke gudang. Ban {$new->serial_number} dipasang pada {$nopol}, posisi {$data['position_number']}.");
    }

    public function deactivate(Request $request, InvPesonaTire $tire): RedirectResponse
    {
        return $this->toggle($request, $tire, false);
    }

    public function activate(Request $request, InvPesonaTire $tire): RedirectResponse
    {
        return $this->toggle($request, $tire, true);
    }

    private function toggle(Request $request, InvPesonaTire $tire, bool $active): RedirectResponse
    {
        $data = $this->normalize($request->validate($this->eventRules()));
        $this->tires->setActive($tire->id, $active, $this->occurredAt($data), $data['event_notes'] ?? null, $request->user());

        return redirect()->route('inventory.pesona.admin.tires.show', $tire)
            ->with('success', $active ? 'Ban diaktifkan kembali.' : 'Ban dinonaktifkan. Data dan riwayat tetap tersimpan.');
    }

    private function metaRules(?InvPesonaTire $tire = null): array
    {
        return [
            // Teks: nol awal dan tanda hubung dipertahankan; unik termasuk ban nonaktif.
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('inv_pesona_tires', 'serial_number')->ignore($tire?->id)],
            'tire_type' => ['required', Rule::in(array_keys(InvPesonaTire::TYPES))],
            'brand' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:30'],
            'tire_condition' => ['required', Rule::in(array_keys(InvPesonaTire::CONDITIONS))],
            'is_usable' => ['nullable', Rule::in(['0', '1'])],
            'received_on' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'supplier_name' => ['nullable', 'string', 'max:150'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function eventRules(): array
    {
        return [
            'occurred_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'event_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** Trim teks; kosong menjadi null; is_usable '0'/'1' menjadi bool (kecuali pada form kembali). */
    private function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                $data[$key] = $value === '' ? null : $value;
            }
        }
        if (array_key_exists('is_usable', $data) && in_array($data['is_usable'], ['0', '1'], true)) {
            $data['is_usable'] = $data['is_usable'] === '1';
        }

        return $data;
    }

    private function occurredAt(array $data): Carbon
    {
        $now = now();

        return Carbon::createFromFormat('Y-m-d', $data['occurred_on'])->setTimeFrom($now);
    }

    private function activeVehicles()
    {
        return InvPesonaVehicle::query()->where('is_active', true)->orderBy('nopol')->get(['id', 'nopol']);
    }
}
