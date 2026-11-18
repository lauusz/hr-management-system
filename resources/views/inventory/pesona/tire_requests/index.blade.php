<x-inv-pesona-app title="Pengajuan Ban — Inventory Pesona">
    @include('inventory.pesona.admin.tires._style')
    @include('inventory.pesona._fleet_ajax')
    <div class="tire-wrap">
        <div class="tire-head">
            <div>
                <h1 class="pesona-title">Pengajuan Ban</h1>
                <p class="pesona-subtitle">Pengajuan pasang atau ganti ban milik Anda. Nomor seri ditentukan admin dari ban yang tersedia.</p>
            </div>
            <a class="tire-btn tire-btn-primary" href="{{ route('inventory.pesona.tire-requests.create') }}">+ Ajukan Ban</a>
        </div>

        <form method="GET" id="req-filter" class="tire-card" action="{{ route('inventory.pesona.tire-requests.index') }}" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap;">
            <label class="tire-field"><span class="tire-label">Status</span>
                <select class="tire-input" name="status" id="req-status">
                    <option value="">Semua</option>
                    @foreach(\App\Models\InvPesonaTireRequest::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select></label>
            <noscript><button class="tire-btn tire-btn-primary" type="submit">Terapkan</button></noscript>
            <span class="fleet-status" id="fleet-status" role="status" aria-live="polite"></span>
        </form>

        <div class="tire-card" id="req-list" data-url="{{ route('inventory.pesona.tire-requests.index') }}">
            @include('inventory.pesona.tire_requests._list', ['items' => $items, 'status' => $status])
        </div>
    </div>
    <script>
        // Filter status dan pagination memperbarui daftar lewat AJAX; halaman pagination kembali ke awal saat status berubah.
        (() => {
            const list = document.getElementById('req-list');
            const select = document.getElementById('req-status');
            const form = document.getElementById('req-filter');
            const status = document.getElementById('fleet-status');
            const F = window.PesonaFleet;
            if (!list || !select || !form || !F || !window.fetch) return;
            let seq = 0;
            let controller = null;
            let retry = null;

            // target: path+query halaman daftar (tanpa parameter partial)
            async function load(target, push) {
                seq += 1; const my = seq;
                if (controller) controller.abort();
                controller = new AbortController();
                retry = () => load(target, push);
                F.busy(list, true); status.textContent = 'Memuat daftar…';
                try {
                    const html = await F.get(target, 'list', controller.signal);
                    if (my !== seq) return;
                    list.innerHTML = html;
                    if (push) history.pushState(null, '', target);
                } catch (error) {
                    if (error.name === 'AbortError' || my !== seq) return;
                    list.replaceChildren(F.errorBox(error.kind));
                } finally {
                    if (my === seq) { F.busy(list, false); status.textContent = ''; }
                }
            }
            const filterTarget = () => {
                const u = new URL(list.dataset.url, location.origin);
                if (select.value) u.searchParams.set('status', select.value);
                return u.pathname + u.search; // tanpa page: kembali ke halaman pertama
            };

            select.addEventListener('change', () => load(filterTarget(), true));
            form.addEventListener('submit', (event) => { event.preventDefault(); load(filterTarget(), true); });
            list.addEventListener('click', (event) => {
                if (event.target.closest('[data-retry]')) { retry?.(); return; }
                const a = event.target.closest('a[href*="page="]'); // hanya tautan pagination
                if (!a || event.metaKey || event.ctrlKey || event.shiftKey || event.button) return;
                event.preventDefault();
                const u = new URL(a.href);
                load(u.pathname + u.search, true);
            });
            history.replaceState(null, '');
            window.addEventListener('popstate', () => {
                select.value = new URLSearchParams(location.search).get('status') || '';
                load(location.pathname + location.search, false);
            });
        })();
    </script>
</x-inv-pesona-app>
