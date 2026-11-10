@php
    $statusLabels = [
        'PENDING' => 'Menunggu Pemeriksaan',
        'APPROVED' => 'Disetujui',
        'PARTIAL' => 'Disetujui Sebagian',
        'REJECTED' => 'Ditolak',
    ];
    $statusShortLabels = [
        '' => 'Semua',
        'PENDING' => 'Pending',
        'APPROVED' => 'Disetujui',
        'PARTIAL' => 'Sebagian',
        'REJECTED' => 'Ditolak',
    ];
    $badgeType = static fn (string $st): string => match ($st) {
        'APPROVED' => 'success',
        'REJECTED' => 'error',
        default => 'warning',
    };
@endphp
<x-inv-pesona-app title="Riwayat Pengambilan — Inventory Pesona">
    <div class="pesona-req-toolbar">
        <div class="pesona-req-heading">
            <div>
                <div class="pesona-req-title-row">
                    <h1 class="pesona-title">Riwayat Pengambilan</h1>
                    <span class="pesona-req-count" id="pesonaReqCount">{{ $requests->total() }} pengambilan</span>
                </div>
                <p class="pesona-subtitle">Daftar permintaan pengambilan barang persediaan garasi yang telah Anda ajukan.</p>
            </div>
        </div>

        <form method="GET" id="pesonaReqFilterForm" action="{{ route('inventory.pesona.requests.index') }}" class="pesona-req-filter-form">
            <div class="pesona-req-search-box">
                <svg class="pesona-req-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input
                    class="pesona-req-search-input"
                    type="search"
                    name="q"
                    maxlength="100"
                    value="{{ $search }}"
                    placeholder="Cari nomor pengambilan..."
                    aria-label="Cari nomor pengambilan"
                    autocomplete="off"
                    data-async-search
                >
            </div>

            <div class="pesona-status-pills" role="group" aria-label="Filter status">
                @foreach($statusShortLabels as $value => $label)
                    <button
                        type="button"
                        class="pesona-status-pill {{ $status === $value ? 'active' : '' }}"
                        data-status-pill="{{ $value }}"
                    >{{ $label }}</button>
                @endforeach
            </div>

            <div class="pesona-req-select-wrap">
                <select class="pesona-req-select" name="status" aria-label="Filter status pengambilan" data-async-filter>
                    <option value="" @selected($status === '')>Semua Status</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div id="pesonaReqResults">
        @if($requests->isEmpty())
            <div class="pesona-card pesona-req-empty">
                <div class="pesona-req-empty-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#pesona-icon-request"/>
                    </svg>
                </div>
                @if(! $hasRequests)
                    <p class="pesona-req-empty-title">Belum ada riwayat pengambilan</p>
                    <p class="pesona-req-empty-sub">Permintaan pengambilan yang Anda kirim akan muncul di halaman ini.</p>
                    <a class="pesona-btn pesona-btn-primary" href="{{ route('inventory.pesona.catalog') }}">Lihat Daftar Barang</a>
                @elseif($requests->total() === 0)
                    <p class="pesona-req-empty-title">Tidak ada pengambilan yang sesuai</p>
                    <p class="pesona-req-empty-sub">Coba kata kunci nomor lain atau ubah filter status.</p>
                    <a class="pesona-btn pesona-btn-secondary" href="{{ route('inventory.pesona.requests.index') }}" data-async-reset>Reset Filter</a>
                @else
                    <p class="pesona-req-empty-title">Tidak ada pengambilan pada halaman ini</p>
                @endif
            </div>
        @else
            {{-- Mobile Card List (< 640px) --}}
            <div class="pesona-request-mobile-list">
                @foreach($requests as $entry)
                    @php
                        $itemCount = (int) $entry->items_count;
                        $loadedItems = $entry->relationLoaded('items') ? $entry->items : collect();
                        if ($loadedItems->isNotEmpty()) {
                            $itemPreview = $loadedItems->take(2)->map(fn ($i) => $i->item_name_snapshot . ' (' . $i->qty . ' ' . $i->unit_name_snapshot . ')')->implode(', ');
                            if ($itemCount > 2) {
                                $itemPreview .= ' +' . ($itemCount - 2) . ' lainnya';
                            }
                        } else {
                            $itemPreview = $entry->items_min_item_name_snapshot ?? '';
                            if ($itemPreview !== '' && $itemCount > 1) {
                                $itemPreview .= ' +' . ($itemCount - 1) . ' lainnya';
                            }
                        }
                    @endphp
                    <article class="pesona-request-card">
                        <div class="pesona-request-card-top">
                            <div>
                                <a href="{{ route('inventory.pesona.requests.show', $entry) }}" class="pesona-request-number">{{ $entry->request_number }}</a>
                                <div class="pesona-request-sub">
                                    {{ $entry->pt_name_snapshot ?? '—' }} · Nopol: {{ $entry->nopol_snapshot ?? 'Belum dicatat' }} · {{ $entry->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}
                                </div>
                            </div>
                            <span class="pesona-badge pesona-badge-{{ $badgeType($entry->status) }}">
                                {{ $statusLabels[$entry->status] ?? $entry->status }}
                            </span>
                        </div>

                        @if($itemCount > 0)
                            <div class="pesona-request-items-preview">
                                <span class="pesona-request-items-count">{{ $itemCount }} barang:</span>
                                {{ $itemPreview !== '' ? $itemPreview : $itemCount . ' baris barang' }}
                            </div>
                        @endif

                        <div class="pesona-request-card-foot">
                            <a class="pesona-btn pesona-btn-secondary pesona-request-detail" href="{{ route('inventory.pesona.requests.show', $entry) }}">Lihat Detail</a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Desktop Table (>= 640px) --}}
            <div class="pesona-table-wrap pesona-request-desktop-table">
                <table class="pesona-table pesona-req-table">
                    <thead>
                        <tr>
                            <th>No. Pengambilan</th>
                            <th>Barang</th>
                            <th>PT</th>
                            <th>Waktu Pengajuan</th>
                            <th>Status</th>
                            <th style="width:1%;white-space:nowrap;text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $entry)
                            @php
                                $itemCount = (int) $entry->items_count;
                                $loadedItems = $entry->relationLoaded('items') ? $entry->items : collect();
                                if ($loadedItems->isNotEmpty()) {
                                    $itemPreview = $loadedItems->take(2)->map(fn ($i) => $i->item_name_snapshot . ' (' . $i->qty . ' ' . $i->unit_name_snapshot . ')')->implode(', ');
                                    if ($itemCount > 2) {
                                        $itemPreview .= ' +' . ($itemCount - 2) . ' lainnya';
                                    }
                                } else {
                                    $itemPreview = $entry->items_min_item_name_snapshot ?? '';
                                    if ($itemPreview !== '' && $itemCount > 1) {
                                        $itemPreview .= ' +' . ($itemCount - 1) . ' lainnya';
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="pesona-req-num-cell">
                                    <a href="{{ route('inventory.pesona.requests.show', $entry) }}" class="pesona-req-link">{{ $entry->request_number }}</a><div>Nopol: {{ $entry->nopol_snapshot ?? 'Belum dicatat' }}</div>
                                </td>
                                <td class="pesona-req-items-cell">
                                    @if($itemCount > 0)
                                        <span class="pesona-req-item-summary">{{ $itemPreview !== '' ? $itemPreview : $itemCount . ' baris barang' }}</span>
                                        <span class="pesona-req-item-count">{{ $itemCount }} jenis barang</span>
                                    @else
                                        <span class="pesona-req-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $entry->pt_name_snapshot ?? '—' }}</td>
                                <td class="pesona-req-date-cell">{{ $entry->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    <span class="pesona-badge pesona-badge-{{ $badgeType($entry->status) }}">
                                        {{ $statusLabels[$entry->status] ?? $entry->status }}
                                    </span>
                                </td>
                                <td style="text-align:right">
                                    <a class="pesona-btn pesona-btn-secondary pesona-btn-sm" href="{{ route('inventory.pesona.requests.show', $entry) }}">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <x-pagination :items="$requests" />
    </div>

    <style>
        /* ========== TOOLBAR & FILTERS (Mobile-First) ========== */
        .pesona-req-toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 14px;
        }
        .pesona-req-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .pesona-req-title-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pesona-req-count {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--pesona-primary);
            background: var(--pesona-primary-soft);
            border: 1px solid rgba(40, 53, 90, 0.12);
            padding: 2px 9px;
            border-radius: 999px;
        }
        .pesona-req-filter-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin: 0;
        }
        .pesona-req-search-box {
            position: relative;
            flex: 1;
            min-width: 0;
        }
        .pesona-req-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            pointer-events: none;
        }
        .pesona-req-search-input,
        .pesona-req-select {
            width: 100%;
            height: 38px;
            min-height: 38px;
            border: 1.5px solid var(--pesona-border);
            border-radius: 10px;
            background: #FFFFFF;
            color: var(--pesona-text);
            font: inherit;
            font-size: 13px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .pesona-req-search-input {
            padding: 0 12px 0 36px;
        }
        .pesona-req-select {
            padding: 0 28px 0 12px;
            font-weight: 600;
            color: var(--pesona-text-secondary);
            cursor: pointer;
        }
        .pesona-req-search-input:focus,
        .pesona-req-select:focus {
            outline: none;
            border-color: var(--pesona-primary);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.12);
        }

        .pesona-status-pills {
            display: none;
            align-items: center;
            gap: 4px;
            padding: 3px;
            background: var(--pesona-surface);
            border: 1px solid var(--pesona-border);
            border-radius: 10px;
            flex-shrink: 0;
        }
        .pesona-status-pill {
            padding: 5px 11px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            font: inherit;
            font-size: 12px;
            font-weight: 600;
            color: var(--pesona-muted);
            cursor: pointer;
            transition: background-color .12s ease, color .12s ease;
        }
        .pesona-status-pill:hover {
            color: var(--pesona-text);
            background: var(--pesona-bg);
        }
        .pesona-status-pill.active {
            background: var(--pesona-primary);
            color: #FFFFFF;
        }

        /* ========== SHARED BUTTONS & BADGES ========== */
        .pesona-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 38px;
            padding: 0 14px;
            border: 0;
            border-radius: 10px;
            text-decoration: none;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease;
        }
        .pesona-btn-primary {
            color: #FFFFFF;
            background: var(--pesona-primary);
        }
        .pesona-btn-primary:hover {
            background: var(--pesona-primary-dark);
        }
        .pesona-btn-secondary {
            color: var(--pesona-primary);
            background: var(--pesona-primary-soft);
            border: 1px solid rgba(40, 53, 90, 0.12);
        }
        .pesona-btn-secondary:hover {
            background: #E2E7F3;
        }
        .pesona-btn-sm {
            height: 30px;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 12px;
        }

        .pesona-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pesona-badge-success { color: #15803D; background: rgba(34, 197, 94, .12); }
        .pesona-badge-warning { color: #B45309; background: rgba(245, 158, 11, .14); }
        .pesona-badge-error { color: #B91C1C; background: rgba(239, 68, 68, .12); }

        #pesonaReqResults {
            transition: opacity .15s ease;
        }
        #pesonaReqResults.is-loading {
            opacity: .6;
        }

        .pesona-card {
            background: var(--pesona-surface);
            border: 1px solid var(--pesona-border);
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 1px 3px rgba(17, 24, 39, 0.05);
        }
        .pesona-req-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 8px;
            padding: 40px 20px;
        }
        .pesona-req-empty-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--pesona-primary-soft);
            color: var(--pesona-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2px;
        }
        .pesona-req-empty-title {
            margin: 0;
            font-size: 14.5px;
            font-weight: 700;
            color: var(--pesona-text);
        }
        .pesona-req-empty-sub {
            margin: 0 0 6px;
            font-size: 12.5px;
            color: var(--pesona-muted);
        }

        /* ========== MOBILE CARDS (< 640px) ========== */
        .pesona-request-mobile-list {
            display: grid;
            gap: 10px;
        }
        .pesona-request-card {
            padding: 12px 14px;
            border: 1px solid var(--pesona-border);
            border-radius: 12px;
            background: var(--pesona-surface);
            box-shadow: 0 1px 3px rgba(17, 24, 39, 0.04);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .pesona-request-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .pesona-request-number {
            display: inline-block;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--pesona-primary);
            text-decoration: none;
            line-height: 1.3;
        }
        .pesona-request-number:hover {
            text-decoration: underline;
        }
        .pesona-request-sub {
            margin-top: 2px;
            font-size: 11.5px;
            color: var(--pesona-muted);
        }
        .pesona-request-items-preview {
            font-size: 12px;
            color: var(--pesona-text-secondary);
            background: var(--pesona-bg);
            padding: 7px 10px;
            border-radius: 8px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }
        .pesona-request-items-count {
            font-weight: 700;
            color: var(--pesona-text);
        }
        .pesona-request-card-foot {
            display: flex;
            justify-content: flex-end;
        }
        .pesona-request-detail {
            width: 100%;
            height: 34px;
            min-height: 34px;
            font-size: 12px;
            border-radius: 8px;
        }

        /* ========== DESKTOP TABLE (>= 640px) ========== */
        .pesona-request-desktop-table {
            display: none;
            overflow-x: auto;
            background: var(--pesona-surface);
            border: 1px solid var(--pesona-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(17, 24, 39, 0.05);
        }
        .pesona-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 680px;
        }
        .pesona-table th,
        .pesona-table td {
            padding: 11px 16px;
            border-bottom: 1px solid var(--pesona-border-soft);
            text-align: left;
            font-size: 13px;
            vertical-align: middle;
        }
        .pesona-table th {
            padding: 10px 16px;
            color: var(--pesona-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            background: var(--pesona-primary-softer);
            border-bottom: 1px solid var(--pesona-border);
        }
        .pesona-table tr:last-child td {
            border-bottom: 0;
        }
        .pesona-req-table tbody tr:hover {
            background: var(--pesona-primary-softer);
        }
        .pesona-req-link {
            font-weight: 700;
            color: var(--pesona-primary);
            text-decoration: none;
        }
        .pesona-req-link:hover {
            text-decoration: underline;
        }
        .pesona-req-items-cell {
            max-width: 340px;
        }
        .pesona-req-item-summary {
            display: block;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--pesona-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pesona-req-item-count {
            display: block;
            font-size: 11px;
            color: var(--pesona-muted);
        }
        .pesona-req-date-cell {
            white-space: nowrap;
            color: var(--pesona-muted);
            font-size: 12.5px;
        }
        .pesona-req-muted {
            color: var(--pesona-muted);
        }

        @media (min-width: 640px) {
            .pesona-req-filter-form {
                flex-direction: row;
                align-items: center;
            }
            .pesona-request-mobile-list {
                display: none;
            }
            .pesona-request-desktop-table {
                display: block;
            }
        }

        @media (min-width: 900px) {
            .pesona-req-toolbar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            }
            .pesona-req-search-box {
                width: 240px;
                flex: 0 1 240px;
            }
            .pesona-status-pills {
                display: inline-flex;
            }
            .pesona-req-select-wrap {
                display: none;
            }
        }
    </style>

    <script>
        (function () {
            const form = document.getElementById('pesonaReqFilterForm');
            const input = form ? form.querySelector('[data-async-search]') : null;
            const select = form ? form.querySelector('[data-async-filter]') : null;
            const pills = form ? Array.from(form.querySelectorAll('[data-status-pill]')) : [];
            const results = document.getElementById('pesonaReqResults');
            const countBadge = document.getElementById('pesonaReqCount');
            if (!form || !input || !results) return;

            let timer;
            let activeController = null;

            function syncStatusControls(statusValue) {
                if (select && select.value !== statusValue) {
                    select.value = statusValue;
                }
                pills.forEach(function (pill) {
                    pill.classList.toggle('active', pill.getAttribute('data-status-pill') === statusValue);
                });
            }

            function load(url) {
                if (activeController) {
                    activeController.abort();
                }
                activeController = new AbortController();
                results.classList.add('is-loading');

                fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeController.signal
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Network error');
                        return response.text();
                    })
                    .then(function (html) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const nextResults = doc.getElementById('pesonaReqResults');
                        const nextCount = doc.getElementById('pesonaReqCount');
                        if (!nextResults) {
                            window.location.href = url.toString();
                            return;
                        }
                        results.innerHTML = nextResults.innerHTML;
                        if (countBadge && nextCount) {
                            countBadge.textContent = nextCount.textContent;
                        }
                        window.history.replaceState(null, '', url.toString());
                    })
                    .catch(function (err) {
                        if (err && err.name === 'AbortError') return;
                        window.location.href = url.toString();
                    })
                    .finally(function () {
                        results.classList.remove('is-loading');
                    });
            }

            function search() {
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams();
                const q = (input.value || '').trim();
                const statusVal = select ? select.value : '';
                if (q) params.set('q', q);
                if (statusVal) params.set('status', statusVal);
                url.search = params.toString();
                load(url);
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                clearTimeout(timer);
                search();
            });

            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(search, 300);
            });

            if (select) {
                select.addEventListener('change', function () {
                    clearTimeout(timer);
                    syncStatusControls(select.value);
                    search();
                });
            }

            pills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    clearTimeout(timer);
                    const val = pill.getAttribute('data-status-pill') || '';
                    syncStatusControls(val);
                    search();
                });
            });

            results.addEventListener('click', function (event) {
                const resetLink = event.target.closest('[data-async-reset]');
                if (resetLink && results.contains(resetLink)) {
                    event.preventDefault();
                    input.value = '';
                    syncStatusControls('');
                    load(new URL(resetLink.href, window.location.origin));
                    return;
                }

                const pageLink = event.target.closest('a');
                if (!pageLink || !pageLink.closest('.hrd-pagination')) return;

                event.preventDefault();
                load(new URL(pageLink.href, window.location.origin));
            });
        })();
    </script>
</x-inv-pesona-app>
