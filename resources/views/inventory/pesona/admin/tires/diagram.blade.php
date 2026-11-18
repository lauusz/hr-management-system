@php
    use App\Services\Inventory\PesonaTireLayout as Layout;
    $modalOpen = $vehicle && $selected !== null;
    $selectedTire = $modalOpen ? $bySlot->get($selected) : null;
    $modalTitle = $modalOpen ? $vehicle->nopol.' — '.Layout::part($selected).', Posisi '.$selected : '';
@endphp
<x-inv-pesona-app title="Ban Armada — Inventory Pesona">
    @include('inventory.pesona.admin.tires._style')
    @include('inventory.pesona._fleet_ajax')
    <style>
        .diag-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 18px; padding: 10px 14px; }
        .diag-bar .tire-input { min-height: 36px; width: auto; min-width: 190px; }
        .diag-note { margin: 0; font-size: 11.5px; color: var(--pesona-muted); }
        #fleet-area { display: flex; flex-direction: column; gap: 14px; }
        /* Combobox ban siap pakai: daftar mengalir di bawah input (bukan melayang) agar tidak terpotong dialog/mobile. */
        .rc-box { position: relative; }
        .rc-box .tire-input { width: 100%; padding-right: 36px; }
        .rc-clear { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); width: 32px; height: 32px; border: 0; background: transparent; font-size: 20px; line-height: 1; cursor: pointer; color: var(--pesona-muted); }
        .rc-box .tire-input:placeholder-shown ~ .rc-clear { display: none; }
        .rc-list { list-style: none; margin: 4px 0 0; padding: 4px; max-height: min(240px, 38dvh); overflow-y: auto; overscroll-behavior: contain; border: 1px solid var(--pesona-border, #E5E7EB); border-radius: 10px; background: #fff; }
        .rc-list li { padding: 9px 10px; border-radius: 8px; font-size: 13px; cursor: pointer; overflow-wrap: anywhere; }
        .rc-list li[role=option]:hover, .rc-list li.rc-active { background: var(--pesona-primary-softer, #EEF2FF); }
        .rc-list li[role=option][aria-selected=true] { font-weight: 800; }
        .rc-list li.rc-note { cursor: default; color: var(--pesona-muted); display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .rc-status { min-height: 16px; margin-top: 2px; }
    </style>
    <div class="tire-wrap">
        <div class="tire-head">
            <h1 class="pesona-title">Ban Armada</h1>
            <a class="tire-btn" href="{{ route('inventory.pesona.admin.tires.index') }}">Master Ban</a>
        </div>

        <div class="tire-card diag-bar">
            <form method="GET" id="fleet-form" action="{{ route('inventory.pesona.admin.tires.diagram') }}" style="display:flex;gap:8px;align-items:center;">
                <label class="tire-label" for="diag-vehicle">Armada</label>
                <select class="tire-input" id="diag-vehicle" name="vehicle_id">
                    <option value="">— pilih armada —</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected($vehicle && $vehicle->id === $v->id)>{{ $v->nopol }}{{ $v->is_active ? '' : ' (nonaktif)' }}</option>
                    @endforeach
                </select>
                <noscript><button class="tire-btn tire-btn-primary" type="submit">Tampilkan</button></noscript>
            </form>
            <span class="fleet-status" id="fleet-status" role="status" aria-live="polite"></span>
        </div>

        {{-- Area yang diganti lewat AJAX saat armada berubah; wadah ini stabil sehingga listener dipasang sekali. --}}
        <div id="fleet-area" data-url="{{ route('inventory.pesona.admin.tires.diagram') }}" data-vehicle="{{ $vehicle?->id }}">
            @include('inventory.pesona.admin.tires._diagram_area', ['vehicle' => $vehicle, 'bySlot' => $bySlot, 'installed' => $installed, 'selected' => $selected])
        </div>
    </div>

    {{-- Satu modal untuk seluruh posisi; isinya partial _diagram_panel (informasi + form Pasang/Copot/Ganti existing). --}}
    @include('inventory.pesona._fleet_modal', [
        'open' => $modalOpen, 'pos' => $selected, 'title' => $modalTitle,
        'bodyView' => 'inventory.pesona.admin.tires._diagram_panel',
        'bodyData' => ['vehicle' => $vehicle, 'selected' => $selected, 'selectedTire' => $selectedTire, 'ready' => $ready],
    ])

    <script>
        // Satu inisialisasi; listener didelegasikan ke wadah stabil (#fleet-area dan dialog) sehingga tetap bekerja setelah konten diganti.
        (() => {
            const area = document.getElementById('fleet-area');
            const select = document.getElementById('diag-vehicle');
            const form = document.getElementById('fleet-form');
            const status = document.getElementById('fleet-status');
            const dialog = document.getElementById('fleet-modal');
            const F = window.PesonaFleet;
            if (!area || !select || !form || !dialog || !F || !window.fetch) return;
            let seq = 0;          // hanya respons dari permintaan terbaru yang dipakai
            let controller = null;
            let retry = null;

            const urlFor = (vehicleId, pos) => {
                const u = new URL(area.dataset.url, location.origin);
                if (vehicleId) u.searchParams.set('vehicle_id', vehicleId);
                if (vehicleId && pos) u.searchParams.set('pos', pos);
                return u.pathname + u.search;
            };
            const mark = (pos) => area.querySelectorAll('a.slot').forEach((a) => {
                const on = a.dataset.slot === String(pos ?? '');
                a.classList.toggle('selected', on);
                if (on) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
            });
            const clearFlash = () => document.querySelectorAll('.pesona-content > .pesona-alert').forEach((el) => el.remove());
            const begin = () => { combo?.stop(); seq += 1; if (controller) controller.abort(); controller = new AbortController(); return [seq, controller.signal]; };
            const invalidate = () => { combo?.stop(); seq += 1; if (controller) controller.abort(); };

            // Modal ditutup (tombol, Escape, atau latar): batalkan pemuatan yang tertunda, hapus penanda, rapikan URL/riwayat.
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

            // Armada berubah: tutup modal dan ganti seluruh area (info armada, gambar, daftar di luar mapping).
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
                    clearFlash();
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

            // Posisi dipilih: buka modal (loading, tanpa info lama), lalu isi dengan partial panel dari server.
            async function openPosition(pos, push) {
                const vehicleId = area.dataset.vehicle;
                if (!vehicleId) return;
                const [my, signal] = begin();
                retry = () => openPosition(pos, push);
                modal.begin(vehicleId + '|' + pos);
                mark(pos);
                try {
                    const html = await F.get(urlFor(vehicleId, pos), 'panel', signal);
                    if (my !== seq) return;
                    modal.show(html);
                    clearFlash();
                    if (push) history.pushState({ fleetPos: pos }, '', urlFor(vehicleId, pos));
                } catch (error) {
                    if (error.name === 'AbortError' || my !== seq) return;
                    modal.error(error.kind);
                }
            }

            select.addEventListener('change', () => loadArea(select.value, null, true));
            form.addEventListener('submit', (event) => { event.preventDefault(); loadArea(select.value, null, true); });
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
            dialog.addEventListener('click', (event) => { if (event.target.closest('[data-retry]')) retry?.(); });
            // Ringkasan Ganti Ban mengikuti ban pengganti yang dipilih (nomor seri = bagian pertama label).
            dialog.addEventListener('change', (event) => {
                const picker = event.target.closest('[data-replace-select]');
                const summary = dialog.querySelector('[data-replace-summary]');
                if (!picker || !summary) return;
                const label = picker.closest('[data-ready-combo]').querySelector('[data-rc-input]').value;
                const serial = picker.value ? label.split(' — ')[0] : '[pilih ban pengganti]';
                summary.textContent = 'Ban ' + summary.dataset.old + ' dikembalikan ke gudang. Ban ' + serial + ' dipasang pada ' + summary.dataset.place + '.';
            });
            // Combobox ban siap pakai: pencarian ke server (debounce 250 ms, 20 hasil per halaman + Muat Lagi), delegasi pada dialog.
            // Satu combobox aktif pada satu waktu; respons lama diabaikan lewat seq dan dibatalkan saat konteks modal berganti.
            const combo = (() => {
                let seq = 0, timer = null, ctl = null, items = [], active = -1, escAt = 0;
                const q = (box, sel) => box.querySelector(sel);
                const stop = () => { clearTimeout(timer); seq += 1; if (ctl) ctl.abort(); ctl = null; };
                const isOpen = (box) => !q(box, '[data-rc-list]').hidden;
                function close(box) {
                    q(box, '[data-rc-list]').hidden = true;
                    q(box, '[data-rc-input]').setAttribute('aria-expanded', 'false');
                    q(box, '[data-rc-input]').removeAttribute('aria-activedescendant');
                    active = -1;
                }
                function note(text, retry) {
                    const li = document.createElement('li');
                    li.className = 'rc-note'; li.setAttribute('role', 'presentation');
                    li.append(text);
                    if (retry) {
                        const b = document.createElement('button');
                        b.type = 'button'; b.className = 'tire-btn'; b.setAttribute('data-rc-retry', ''); b.textContent = 'Coba Lagi';
                        li.append(b);
                    }
                    return li;
                }
                function paint(box, more, failure) {
                    const list = q(box, '[data-rc-list]');
                    const input = q(box, '[data-rc-input]');
                    const chosen = q(box, '[data-rc-id]').value;
                    list.replaceChildren();
                    items.forEach((it, i) => {
                        const li = document.createElement('li');
                        li.id = input.id.replace('-input', '-opt-') + i;
                        li.setAttribute('role', 'option'); li.dataset.i = i;
                        li.setAttribute('aria-selected', String(String(it.id) === chosen));
                        li.textContent = it.label;
                        if (i === active) { li.classList.add('rc-active'); input.setAttribute('aria-activedescendant', li.id); }
                        list.appendChild(li);
                    });
                    if (failure) list.appendChild(note(failure, true));
                    else if (!items.length) list.appendChild(note('Ban siap pakai tidak ditemukan', false));
                    if (more) {
                        const li = document.createElement('li');
                        li.className = 'rc-note'; li.setAttribute('role', 'presentation');
                        const b = document.createElement('button');
                        b.type = 'button'; b.className = 'tire-btn'; b.setAttribute('data-rc-more', ''); b.textContent = 'Muat Lagi';
                        li.appendChild(b); list.appendChild(li);
                    }
                    list.hidden = false;
                    input.setAttribute('aria-expanded', 'true');
                    list.querySelector('.rc-active')?.scrollIntoView({ block: 'nearest' });
                }
                // Muat satu halaman hasil; page > 1 menambah ke daftar. Kata kunci kosong = daftar awal.
                async function load(box, term, page) {
                    stop();
                    const my = seq;
                    ctl = new AbortController();
                    const status = q(box, '[data-rc-status]');
                    if (page === 1) { items = []; active = -1; }
                    status.textContent = 'Memuat…';
                    box.dataset.term = term; box.dataset.page = page;
                    q(box, '[data-rc-list]').hidden = false;
                    try {
                        const u = new URL(box.dataset.url, location.origin);
                        u.searchParams.set('q', term); u.searchParams.set('page', page);
                        const res = await fetch(u, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin', signal: ctl.signal });
                        if (res.redirected || res.status === 401 || res.status === 419) throw new Error('Sesi berakhir. Muat ulang halaman atau login kembali.');
                        if (!res.ok) throw new Error('Gagal memuat ban. Coba lagi.');
                        const data = await res.json();
                        if (my !== seq || !box.isConnected) return;
                        items = items.concat(data.items || []);
                        status.textContent = items.length ? items.length + ' ban ditampilkan' : '';
                        paint(box, !!data.has_more, null);
                    } catch (error) {
                        if (error.name === 'AbortError' || my !== seq || !box.isConnected) return;
                        status.textContent = '';
                        paint(box, false, error instanceof Error && error.name === 'Error' ? error.message : 'Gagal memuat ban. Periksa koneksi, lalu coba lagi.');
                    }
                }
                function choose(box, i) {
                    const it = items[i];
                    if (!it) return;
                    stop();
                    const input = q(box, '[data-rc-input]');
                    const id = q(box, '[data-rc-id]');
                    input.value = it.label;
                    input.setCustomValidity('');
                    id.value = it.id;
                    q(box, '[data-rc-status]').textContent = 'Dipilih: ' + it.label;
                    close(box);
                    id.dispatchEvent(new Event('change', { bubbles: true }));
                }
                function reset(box) { // teks diubah/dihapus: ID lama tidak boleh ikut terkirim
                    const id = q(box, '[data-rc-id]');
                    if (!id.value) return;
                    id.value = '';
                    id.dispatchEvent(new Event('change', { bubbles: true }));
                }
                const openBox = (box) => load(box, q(box, '[data-rc-id]').value ? '' : q(box, '[data-rc-input]').value.trim(), 1);
                const move = (box, step) => {
                    if (!isOpen(box)) { openBox(box); return; }
                    if (!items.length) return;
                    active = (active + step + items.length) % items.length;
                    paint(box, !!q(box, '[data-rc-more]'), null);
                };

                dialog.addEventListener('focusin', (e) => {
                    const box = e.target.closest?.('[data-rc-input]')?.closest('[data-ready-combo]');
                    if (box && !isOpen(box)) openBox(box);
                });
                dialog.addEventListener('click', (e) => {
                    const box = e.target.closest?.('[data-ready-combo]');
                    if (!box) return;
                    if (e.target.closest('[data-rc-input]')) { if (!isOpen(box)) openBox(box); return; }
                    if (e.target.closest('[data-rc-clear]')) {
                        const input = q(box, '[data-rc-input]');
                        stop(); input.value = ''; input.setCustomValidity(''); reset(box); input.focus(); openBox(box); return;
                    }
                    if (e.target.closest('[data-rc-more]')) { load(box, box.dataset.term || '', Number(box.dataset.page || 1) + 1); return; }
                    if (e.target.closest('[data-rc-retry]')) { load(box, box.dataset.term || '', Number(box.dataset.page || 1)); return; }
                    const opt = e.target.closest('[role=option]');
                    if (opt) choose(box, Number(opt.dataset.i));
                });
                dialog.addEventListener('mousedown', (e) => { if (e.target.closest?.('[data-rc-list], [data-rc-clear]')) e.preventDefault(); }); // fokus tetap di input
                dialog.addEventListener('input', (e) => {
                    const input = e.target.closest?.('[data-rc-input]');
                    if (!input) return;
                    const box = input.closest('[data-ready-combo]');
                    input.setCustomValidity('');
                    reset(box);
                    clearTimeout(timer);
                    timer = setTimeout(() => load(box, input.value.trim(), 1), 250);
                });
                dialog.addEventListener('keydown', (e) => {
                    const input = e.target.closest?.('[data-rc-input]');
                    if (!input) return;
                    const box = input.closest('[data-ready-combo]');
                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); move(box, e.key === 'ArrowDown' ? 1 : -1); }
                    else if (e.key === 'Enter') { e.preventDefault(); if (isOpen(box) && active >= 0) choose(box, active); }
                    else if (e.key === 'Escape' && isOpen(box)) { e.preventDefault(); escAt = Date.now(); close(box); }
                });
                dialog.addEventListener('cancel', (e) => { if (Date.now() - escAt < 150) e.preventDefault(); }); // Escape pertama hanya menutup daftar
                dialog.addEventListener('focusout', (e) => {
                    const box = e.target.closest?.('[data-ready-combo]');
                    if (box && !box.contains(e.relatedTarget)) { stop(); close(box); }
                });
                // Kirim hanya bila ID dipilih dari daftar: teks bebas tidak pernah menjadi nilai transaksi.
                dialog.addEventListener('submit', (e) => {
                    const box = e.target.querySelector?.('[data-ready-combo]');
                    if (box && !q(box, '[data-rc-id]').value) {
                        e.preventDefault();
                        const input = q(box, '[data-rc-input]');
                        input.setCustomValidity('Pilih ban dari daftar hasil pencarian.');
                        input.reportValidity();
                    }
                });
                dialog.addEventListener('close', stop);
                return { stop };
            })();

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
