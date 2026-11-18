@php
    use App\Services\Inventory\PesonaTireLayout as Layout;
    $modalOpen = $vehicle && $selected !== null;
    $modalTitle = $modalOpen ? $vehicle->nopol.' — '.Layout::part($selected).', Posisi '.$selected : '';
@endphp
<x-inv-pesona-app title="Ajukan Ban — Inventory Pesona">
    @include('inventory.pesona.admin.tires._style')
    @include('inventory.pesona._fleet_ajax')
    <style>
        #req-area { display: flex; flex-direction: column; gap: 14px; }
    </style>
    <div class="tire-wrap">
        <div>
            <a class="tire-btn" href="{{ route('inventory.pesona.tire-requests.index') }}">← Pengajuan Ban</a>
            <h1 class="pesona-title" style="margin-top:10px;">Ajukan Ban</h1>
            <p class="pesona-subtitle">Satu pengajuan untuk satu posisi ban pada satu armada. Anda tidak memilih nomor seri; admin menentukan ban yang tersedia.</p>
        </div>

        <form method="GET" id="req-vehicle-form" class="tire-card" action="{{ route('inventory.pesona.tire-requests.create') }}">
            <label class="tire-field" style="max-width:360px;"><span class="tire-label">Pilih armada (nopol aktif)</span>
                <select class="tire-input" id="req-vehicle" name="vehicle_id">
                    <option value="">— pilih armada —</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected($vehicle && $vehicle->id === $v->id)>{{ $v->nopol }}</option>
                    @endforeach
                </select></label>
            <noscript><button class="tire-btn tire-btn-primary" type="submit" style="margin-top:8px;">Tampilkan armada</button></noscript>
            <div class="fleet-status" id="fleet-status" role="status" aria-live="polite" style="margin-top:6px;"></div>
        </form>

        {{-- Area yang diganti lewat AJAX saat armada berubah; wadah ini stabil sehingga listener dipasang sekali. --}}
        <div id="req-area" data-url="{{ route('inventory.pesona.tire-requests.create') }}" data-vehicle="{{ $vehicle?->id }}">
            @include('inventory.pesona.tire_requests._create_area', ['vehicle' => $vehicle, 'bySlot' => $bySlot, 'selected' => $selected])
        </div>
    </div>

    {{-- Satu modal untuk seluruh posisi; isinya partial _create_modal (informasi posisi + form pengajuan). --}}
    @include('inventory.pesona._fleet_modal', [
        'open' => $modalOpen, 'pos' => $selected, 'title' => $modalTitle,
        'bodyView' => 'inventory.pesona.tire_requests._create_modal',
        'bodyData' => ['vehicle' => $vehicle, 'selected' => $selected, 'tire' => $modalOpen ? $bySlot->get($selected) : null],
    ])

    <script>
        // Satu inisialisasi. Armada berubah: modal ditutup dan area diganti lewat AJAX. Posisi dipilih (gambar atau daftar):
        // modal dibuka dan isinya diambil dari server. Listener didelegasikan ke wadah stabil (#req-area dan dialog).
        (() => {
            const area = document.getElementById('req-area');
            const select = document.getElementById('req-vehicle');
            const vehicleForm = document.getElementById('req-vehicle-form');
            const status = document.getElementById('fleet-status');
            const dialog = document.getElementById('fleet-modal');
            const F = window.PesonaFleet;
            if (!area || !select || !vehicleForm || !dialog || !F || !window.fetch) return;
            let seq = 0;
            let controller = null;
            let retry = null;

            const urlFor = (vehicleId, pos) => {
                const u = new URL(area.dataset.url, location.origin);
                if (vehicleId) u.searchParams.set('vehicle_id', vehicleId);
                if (vehicleId && pos) u.searchParams.set('pos', pos);
                return u.pathname + u.search;
            };
            // Penanda gambar dan daftar posisi selalu mengikuti satu nilai posisi (dicari ulang tiap panggilan).
            const mark = (pos) => {
                area.querySelectorAll('a.slot').forEach((a) => {
                    const on = a.dataset.slot === String(pos ?? '');
                    a.classList.toggle('selected', on);
                    if (on) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
                });
                const list = area.querySelector('#req-position');
                if (list) list.value = pos ? String(pos) : '';
            };
            const begin = () => { seq += 1; if (controller) controller.abort(); controller = new AbortController(); return [seq, controller.signal]; };
            const invalidate = () => { seq += 1; if (controller) controller.abort(); };

            const modal = F.createModal(dialog, {
                onClose() {
                    invalidate();
                    mark(null);
                    status.textContent = '';
                    if (history.state && history.state.fleetPos) { history.back(); return; }
                    const u = new URL(location.href);
                    u.searchParams.delete('pos');
                    history.replaceState(null, '', u);
                },
            });

            async function loadArea(vehicleId, pos, push) {
                const [my, signal] = begin();
                retry = () => loadArea(vehicleId, pos, push);
                modal.close(true);
                select.value = vehicleId || '';
                F.busy(area, true); status.textContent = 'Memuat armada…';
                try {
                    const html = await F.get(urlFor(vehicleId, null), 'area', signal);
                    if (my !== seq) return;
                    area.innerHTML = html;
                    area.dataset.vehicle = vehicleId || '';
                    document.querySelectorAll('.pesona-content > .pesona-alert').forEach((el) => el.remove());
                    if (push) history.pushState(null, '', urlFor(vehicleId, null));
                    if (vehicleId && pos) { F.busy(area, false); status.textContent = ''; openPosition(pos, false); }
                } catch (error) {
                    if (error.name === 'AbortError' || my !== seq) return;
                    area.dataset.vehicle = ''; // jangan menyisakan konteks armada lama
                    area.replaceChildren(F.errorBox(error.kind));
                } finally {
                    if (my === seq) { F.busy(area, false); status.textContent = ''; }
                }
            }

            async function openPosition(pos, push) {
                const vehicleId = area.dataset.vehicle;
                if (!vehicleId || !pos) return;
                const [my, signal] = begin();
                retry = () => openPosition(pos, push);
                modal.begin(vehicleId + '|' + pos);
                mark(pos);
                try {
                    const html = await F.get(urlFor(vehicleId, pos), 'position', signal);
                    if (my !== seq) return;
                    modal.show(html);
                    document.querySelectorAll('.pesona-content > .pesona-alert').forEach((el) => el.remove());
                    if (push) history.pushState({ fleetPos: pos }, '', urlFor(vehicleId, pos));
                } catch (error) {
                    if (error.name === 'AbortError' || my !== seq) return;
                    modal.error(error.kind);
                }
            }

            select.addEventListener('change', () => loadArea(select.value, null, true));
            vehicleForm.addEventListener('submit', (event) => { event.preventDefault(); loadArea(select.value, null, true); });
            area.addEventListener('click', (event) => {
                if (event.target.closest('[data-retry]')) { retry?.(); return; }
                const a = event.target.closest('a.slot');
                if (!a || event.metaKey || event.ctrlKey || event.shiftKey || event.button) return;
                event.preventDefault();
                openPosition(a.dataset.slot, true);
            });
            area.addEventListener('keydown', (event) => {
                const a = event.target.closest?.('a.slot');
                if (a && event.key === ' ') { event.preventDefault(); openPosition(a.dataset.slot, true); }
            });
            area.addEventListener('change', (event) => {
                if (event.target.closest('#req-position') && event.target.value) openPosition(event.target.value, true);
            });
            area.addEventListener('submit', (event) => {
                if (event.target.closest('#req-position-form')) { event.preventDefault(); const v = area.querySelector('#req-position').value; if (v) openPosition(v, true); }
            });
            dialog.addEventListener('click', (event) => { if (event.target.closest('[data-retry]')) retry?.(); });
            window.addEventListener('popstate', () => {
                const q = new URLSearchParams(location.search);
                const vehicleId = q.get('vehicle_id') || '';
                const pos = q.get('pos');
                if (vehicleId !== (area.dataset.vehicle || '')) { loadArea(vehicleId, pos, false); return; }
                if (vehicleId && pos) { openPosition(pos, false); return; }
                invalidate(); modal.close(true); mark(null);
            });
            history.replaceState(null, '');
            if (dialog.dataset.pos) modal.adopt(area.dataset.vehicle + '|' + dialog.dataset.pos); // tautan langsung / validasi gagal
        })();
    </script>
</x-inv-pesona-app>
