<x-inv-pesona-app title="Daftar Nopol — Inventory Pesona">
    <style>
        .pesona-veh-shell {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Header */
        .pesona-veh-header {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .pesona-veh-title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        .pesona-veh-count-pill {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            padding: 2px 10px;
            border-radius: 999px;
            background: rgba(40, 53, 90, 0.08);
            color: var(--pesona-primary);
            font-size: 12px;
            font-weight: 800;
        }
        .pesona-veh-add-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 40px;
            padding: 8px 16px;
            border-radius: 10px;
            background: var(--pesona-primary);
            color: #FFFFFF;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color 0.15s ease;
        }
        .pesona-veh-add-btn:hover {
            background: #1E2846;
        }

        /* Filter Toolbar */
        .pesona-veh-toolbar {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid var(--pesona-border);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-veh-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pesona-veh-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 34px;
            padding: 5px 13px;
            border-radius: 999px;
            border: 1.5px solid #E2E8F0;
            background: #F8FAFC;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .pesona-veh-pill:hover {
            border-color: #CBD5E1;
            background: #F1F5F9;
        }
        .pesona-veh-pill.is-active {
            background: var(--pesona-primary);
            border-color: var(--pesona-primary);
            color: #FFFFFF;
        }

        .pesona-veh-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .pesona-veh-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }
        .pesona-veh-field-label {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
        }
        .pesona-veh-search-wrap {
            position: relative;
            width: 100%;
        }
        .pesona-veh-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .pesona-veh-input,
        .pesona-veh-select {
            width: 100%;
            min-height: 40px;
            padding: 8px 12px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            background: #F8FAFC;
            color: #0F172A;
            font: inherit;
            font-size: 13px;
            box-sizing: border-box;
            transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
        }
        .pesona-veh-input.has-icon {
            padding-left: 36px;
        }
        .pesona-veh-input:focus,
        .pesona-veh-select:focus {
            outline: none;
            background: #FFFFFF;
            border-color: var(--pesona-primary);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.12);
        }
        .pesona-veh-reset-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 40px;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid #CBD5E1;
            background: #FFFFFF;
            color: #475569;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            box-sizing: border-box;
        }
        .pesona-veh-reset-btn:hover {
            background: #F8FAFC;
            color: #0F172A;
        }

        /* Results Container */
        .pesona-veh-results {
            transition: opacity 0.18s ease;
        }
        .pesona-veh-results.is-loading {
            opacity: 0.55;
            pointer-events: none;
        }

        /* Plate Badge & Status Badges */
        .pesona-veh-plate {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 12px;
            border-radius: 8px;
            background: #0F172A;
            color: #FFFFFF;
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 0.04em;
            border: 1.5px solid #334155;
        }
        .pesona-veh-plate.is-inactive {
            background: #F1F5F9;
            color: #475569;
            border-color: #CBD5E1;
        }

        .pesona-veh-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 25px;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            white-space: nowrap;
        }
        .pesona-veh-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: currentColor;
        }
        .pesona-veh-badge.is-active {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #86EFAC;
        }
        .pesona-veh-badge.is-inactive {
            background: #F1F5F9;
            color: #64748B;
            border: 1px solid #CBD5E1;
        }

        .pesona-veh-usage {
            font-size: 12.5px;
            color: #475569;
            font-weight: 600;
        }
        .pesona-veh-usage.is-used {
            color: #0F172A;
        }

        /* Action Buttons */
        .pesona-veh-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .pesona-veh-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 32px;
            padding: 5px 12px;
            border-radius: 8px;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .pesona-veh-btn-edit {
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            color: #1E293B;
        }
        .pesona-veh-btn-edit:hover {
            background: #F8FAFC;
            border-color: var(--pesona-primary);
            color: var(--pesona-primary);
        }
        .pesona-veh-btn-delete {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #B91C1C;
        }
        .pesona-veh-btn-delete:hover {
            background: #FEE2E2;
        }
        .pesona-veh-locked-hint {
            font-size: 11.5px;
            color: #94A3B8;
            font-weight: 600;
        }

        /* Mobile Cards (< 640px) */
        .pesona-veh-cards {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .pesona-veh-card {
            padding: 14px;
            border: 1px solid var(--pesona-border);
            border-radius: 14px;
            background: #FFFFFF;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-veh-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .pesona-veh-card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 10px;
            border-top: 1px dashed #E2E8F0;
        }

        /* Desktop Table (>= 640px) */
        .pesona-veh-table-wrap {
            display: none;
            overflow-x: auto;
            border: 1px solid #CBD5E1;
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }
        .pesona-veh-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .pesona-veh-table th {
            padding: 11px 16px;
            background: #F8FAFC;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            border-bottom: 1px solid #CBD5E1;
            white-space: nowrap;
        }
        .pesona-veh-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: #0F172A;
        }
        .pesona-veh-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .pesona-veh-table tbody tr:hover td {
            background: #F8FAFC;
        }

        /* Empty State */
        .pesona-veh-empty {
            padding: 44px 20px;
            border: 1px dashed #CBD5E1;
            border-radius: 14px;
            background: #FFFFFF;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .pesona-veh-empty-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(40, 53, 90, 0.08);
            color: var(--pesona-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2px;
        }
        .pesona-veh-empty-title {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #0F172A;
        }
        .pesona-veh-empty-sub {
            margin: 0 0 6px;
            font-size: 13px;
            color: #64748B;
        }

        @media (min-width: 640px) {
            .pesona-veh-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
            }
            .pesona-veh-filter-grid {
                grid-template-columns: 1fr 200px auto;
                align-items: end;
            }
            .pesona-veh-cards {
                display: none;
            }
            .pesona-veh-table-wrap {
                display: block;
            }
        }
    </style>

    @php
        $hasActiveFilter = $filters['q'] !== '' || $filters['status'] !== 'all';
    @endphp

    <div class="pesona-veh-shell">
        {{-- Header --}}
        <div class="pesona-veh-header">
            <div>
                <div class="pesona-veh-title-row">
                    <h1 class="pesona-title">Daftar Nopol</h1>
                    <span class="pesona-veh-count-pill" id="pesona-veh-total-pill">{{ $vehicles->total() }} nopol</span>
                </div>
                <p class="pesona-subtitle">
                    Kelola nomor polisi (nopol) armada untuk pengajuan pengambilan barang garasi PT Pesona.
                </p>
            </div>
            <div>
                <a class="pesona-veh-add-btn" href="{{ route('inventory.pesona.admin.vehicles.create') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    <span>Tambah Nopol</span>
                </a>
            </div>
        </div>

        {{-- Filter & Live Search Toolbar --}}
        <form
            id="pesona-veh-filter-form"
            class="pesona-veh-toolbar"
            method="GET"
            action="{{ route('inventory.pesona.admin.vehicles.index') }}"
        >
            <div class="pesona-veh-pills" id="pesona-veh-pills">
                <button
                    type="button"
                    class="pesona-veh-pill {{ $filters['status'] === 'all' ? 'is-active' : '' }}"
                    data-status="all"
                >
                    Semua Status
                </button>
                <button
                    type="button"
                    class="pesona-veh-pill {{ $filters['status'] === 'active' ? 'is-active' : '' }}"
                    data-status="active"
                >
                    Aktif
                </button>
                <button
                    type="button"
                    class="pesona-veh-pill {{ $filters['status'] === 'inactive' ? 'is-active' : '' }}"
                    data-status="inactive"
                >
                    Nonaktif
                </button>
            </div>

            <div class="pesona-veh-filter-grid">
                <label class="pesona-veh-field" for="vehicle-q">
                    <span class="pesona-veh-field-label">Cari Nomor Polisi (Nopol)</span>
                    <div class="pesona-veh-search-wrap">
                        <svg class="pesona-veh-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input
                            type="search"
                            id="vehicle-q"
                            name="q"
                            class="pesona-veh-input has-icon"
                            maxlength="30"
                            value="{{ $filters['q'] }}"
                            placeholder="Ketik nopol kendaraan (contoh: B 1234 XYZ)..."
                            autocomplete="off"
                        >
                    </div>
                </label>

                <label class="pesona-veh-field" for="vehicle-status">
                    <span class="pesona-veh-field-label">Status Kendaraan</span>
                    <select id="vehicle-status" name="status" class="pesona-veh-select">
                        <option value="all" @selected($filters['status'] === 'all')>Semua</option>
                        <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                    </select>
                </label>

                <div style="display:flex; align-items:center; gap:8px;">
                    <a
                        id="pesona-veh-reset"
                        class="pesona-veh-reset-btn"
                        href="{{ route('inventory.pesona.admin.vehicles.index') }}"
                        style="{{ $hasActiveFilter ? '' : 'display:none;' }}"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                        Reset
                    </a>
                    <noscript>
                        <button class="pesona-button" type="submit">Cari</button>
                    </noscript>
                </div>
            </div>
        </form>

        {{-- Results Area (Updated via AJAX) --}}
        <div id="pesona-vehicles-results" class="pesona-veh-results" data-total="{{ $vehicles->total() }}">
            @if($vehicles->isEmpty())
                <div class="pesona-veh-empty">
                    <div class="pesona-veh-empty-icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <p class="pesona-veh-empty-title">
                        {{ $hasVehicles ? 'Tidak Ada Nopol yang Sesuai' : 'Belum Ada Nopol Terdaftar' }}
                    </p>
                    <p class="pesona-veh-empty-sub">
                        {{ $hasVehicles ? 'Tidak ada nopol yang sesuai pencarian atau filter.' : 'Belum ada nopol terdaftar. Tambahkan nopol armada agar karyawan dapat mengajukan pengambilan barang.' }}
                    </p>
                    @if(! $hasVehicles)
                        <a class="pesona-veh-add-btn" href="{{ route('inventory.pesona.admin.vehicles.create') }}">
                            + Tambah Nopol Pertama
                        </a>
                    @endif
                </div>
            @else
                {{-- Mobile Cards (< 640px) --}}
                <div class="pesona-veh-cards">
                    @foreach($vehicles as $vehicle)
                        <article class="pesona-veh-card">
                            <div class="pesona-veh-card-top">
                                <span class="pesona-veh-plate {{ $vehicle->is_active ? '' : 'is-inactive' }}">
                                    {{ $vehicle->nopol }}
                                </span>
                                <span class="pesona-veh-badge {{ $vehicle->is_active ? 'is-active' : 'is-inactive' }}">
                                    <span class="pesona-veh-dot"></span>
                                    {{ $vehicle->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <div class="pesona-veh-card-bottom">
                                <span class="pesona-veh-usage {{ $vehicle->requests_exists ? 'is-used' : '' }}">
                                    {{ $vehicle->requests_exists ? 'Sudah dipakai di pengajuan' : 'Belum pernah dipakai' }}
                                </span>
                                <div class="pesona-veh-actions">
                                    <a class="pesona-veh-btn pesona-veh-btn-edit" href="{{ route('inventory.pesona.admin.vehicles.edit', $vehicle) }}">
                                        Edit
                                    </a>
                                    @if(! $vehicle->requests_exists)
                                        <button
                                            class="pesona-veh-btn pesona-veh-btn-delete"
                                            type="button"
                                            data-vehicle-modal="vehicle_delete_{{ $vehicle->id }}"
                                            aria-haspopup="dialog"
                                            aria-controls="vehicle_delete_{{ $vehicle->id }}"
                                        >
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Desktop Table (>= 640px) --}}
                <div class="pesona-veh-table-wrap">
                    <table class="pesona-veh-table">
                        <thead>
                            <tr>
                                <th>Nomor Polisi (Nopol)</th>
                                <th>Status</th>
                                <th>Riwayat Pemakaian</th>
                                <th style="width:1%;white-space:nowrap;text-align:right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $vehicle)
                                <tr>
                                    <td>
                                        <span class="pesona-veh-plate {{ $vehicle->is_active ? '' : 'is-inactive' }}">
                                            {{ $vehicle->nopol }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="pesona-veh-badge {{ $vehicle->is_active ? 'is-active' : 'is-inactive' }}">
                                            <span class="pesona-veh-dot"></span>
                                            {{ $vehicle->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="pesona-veh-usage {{ $vehicle->requests_exists ? 'is-used' : '' }}">
                                            {{ $vehicle->requests_exists ? 'Sudah digunakan pada pengajuan' : 'Belum pernah digunakan' }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;white-space:nowrap;">
                                        <div class="pesona-veh-actions" style="justify-content:flex-end;">
                                            <a class="pesona-veh-btn pesona-veh-btn-edit" href="{{ route('inventory.pesona.admin.vehicles.edit', $vehicle) }}">
                                                Edit
                                            </a>
                                            @if(! $vehicle->requests_exists)
                                                <button
                                                    class="pesona-veh-btn pesona-veh-btn-delete"
                                                    type="button"
                                                    data-vehicle-modal="vehicle_delete_{{ $vehicle->id }}"
                                                    aria-haspopup="dialog"
                                                    aria-controls="vehicle_delete_{{ $vehicle->id }}"
                                                >
                                                    Hapus
                                                </button>
                                            @else
                                                <span class="pesona-veh-locked-hint" title="Nopol yang sudah pernah dipakai tidak dapat dihapus">Terkunci</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Shared Delete Confirmation Modals --}}
                @foreach($vehicles as $vehicle)
                    @if(! $vehicle->requests_exists)
                        <x-modal
                            :id="'vehicle_delete_'.$vehicle->id"
                            title="Hapus Nopol?"
                            type="confirm"
                            variant="danger"
                            confirmLabel="Hapus"
                            cancelLabel="Batal"
                            :confirmFormAction="route('inventory.pesona.admin.vehicles.destroy', $vehicle)"
                            confirmFormMethod="DELETE"
                        >
                            <p style="margin:0;">Hapus nopol <strong>{{ $vehicle->nopol }}</strong>?</p>
                        </x-modal>
                    @endif
                @endforeach
            @endif

            <div style="margin-top: 14px;">
                <x-pagination :items="$vehicles" />
            </div>
        </div>
    </div>

    <script>
        (() => {
            /* Delete Confirmation Modal Logic */
            let modal = null, trigger = null;
            const close = () => {
                if (!modal) return;
                modal.style.display = 'none';
                modal = null;
                document.body.style.overflow = '';
                trigger?.focus();
            };

            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-vehicle-modal]');
                if (button) {
                    trigger = button;
                    modal = document.getElementById(button.dataset.vehicleModal);
                    if (modal) {
                        modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                        modal.querySelector('[data-modal-close]')?.focus();
                    }
                } else if (modal && (event.target === modal || event.target.closest('[data-modal-close]')?.closest('.modal-backdrop') === modal)) {
                    close();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (!modal) return;
                if (event.key === 'Escape') {
                    close();
                    return;
                }
                if (event.key !== 'Tab') return;
                const controls = Array.from(modal.querySelectorAll('button:not([disabled]), a[href], input:not([disabled])')).filter((el) => el.getClientRects().length);
                const first = controls[0], last = controls[controls.length - 1];
                if (event.shiftKey && (document.activeElement === first || !modal.contains(document.activeElement))) {
                    event.preventDefault();
                    last?.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first?.focus();
                }
            });

            /* AJAX Live Search & Filter Logic */
            const form = document.getElementById('pesona-veh-filter-form');
            const qInput = document.getElementById('vehicle-q');
            const statusSelect = document.getElementById('vehicle-status');
            const resetBtn = document.getElementById('pesona-veh-reset');
            const pillsWrap = document.getElementById('pesona-veh-pills');
            const totalPill = document.getElementById('pesona-veh-total-pill');
            let resultsBox = document.getElementById('pesona-vehicles-results');

            if (!form || !resultsBox) return;

            let debounceTimer = null;
            let activeController = null;

            function syncUiState() {
                const curStatus = statusSelect ? statusSelect.value : 'all';
                if (pillsWrap) {
                    pillsWrap.querySelectorAll('[data-status]').forEach((btn) => {
                        btn.classList.toggle('is-active', btn.getAttribute('data-status') === curStatus);
                    });
                }
                if (resetBtn) {
                    const hasAny = (qInput && qInput.value.trim() !== '') || curStatus !== 'all';
                    resetBtn.style.display = hasAny ? '' : 'none';
                }
            }

            function buildUrl(customUrl) {
                if (customUrl) return customUrl;
                const params = new URLSearchParams();
                if (qInput && qInput.value.trim() !== '') params.set('q', qInput.value.trim());
                if (statusSelect && statusSelect.value !== 'all') params.set('status', statusSelect.value);
                const qs = params.toString();
                return form.action + (qs ? '?' + qs : '');
            }

            function fetchResults(targetUrl, pushHistory) {
                const url = buildUrl(targetUrl);
                syncUiState();

                if (activeController) {
                    activeController.abort();
                }
                activeController = new AbortController();
                resultsBox.classList.add('is-loading');

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeController.signal
                })
                    .then((res) => {
                        if (!res.ok) throw new Error('Network error');
                        return res.text();
                    })
                    .then((html) => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const nextResults = doc.getElementById('pesona-vehicles-results');
                        if (nextResults) {
                            resultsBox.replaceWith(nextResults);
                            resultsBox = nextResults;
                            const nextTotal = nextResults.getAttribute('data-total');
                            if (totalPill && nextTotal !== null) {
                                totalPill.textContent = nextTotal + ' nopol';
                            }
                        }
                        if (pushHistory !== false && window.history && window.history.replaceState) {
                            window.history.replaceState({}, '', url);
                        }
                    })
                    .catch((err) => {
                        if (err.name !== 'AbortError') {
                            resultsBox.classList.remove('is-loading');
                        }
                    });
            }

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                fetchResults();
            });

            if (qInput) {
                qInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        fetchResults();
                    }, 250);
                });
            }

            if (statusSelect) {
                statusSelect.addEventListener('change', () => {
                    fetchResults();
                });
            }

            if (pillsWrap) {
                pillsWrap.addEventListener('click', (e) => {
                    const pill = e.target.closest('[data-status]');
                    if (!pill || !statusSelect) return;
                    statusSelect.value = pill.getAttribute('data-status') || 'all';
                    fetchResults();
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (qInput) qInput.value = '';
                    if (statusSelect) statusSelect.value = 'all';
                    fetchResults(form.action);
                });
            }

            document.addEventListener('click', (e) => {
                const link = e.target.closest('#pesona-vehicles-results a[href]');
                if (!link) return;
                if (link.classList.contains('pesona-veh-btn-edit') || link.classList.contains('pesona-veh-add-btn')) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#')) return;
                e.preventDefault();
                fetchResults(href);
            });
        })();
    </script>
</x-inv-pesona-app>
