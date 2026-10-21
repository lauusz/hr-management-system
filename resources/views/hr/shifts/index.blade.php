<x-app title="Master Shift Kerja">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Master Shift Kerja</h1>
                <p class="section-subtitle">Atur pola jam kerja harian untuk karyawan</p>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="lm-alert lm-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="lm-alert lm-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="lm-cta-bar">
        <a href="{{ route('hr.shifts.create') }}" class="lm-btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Shift
        </a>
    </div>

    {{-- Stats Summary --}}
    <div class="lm-stats" data-shift-stats>
        <div class="lm-stat">
            <div class="lm-stat-value">{{ $totalCount ?? $items->total() }}</div>
            <div class="lm-stat-label">Total Shift</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--success">{{ $activeCount ?? $items->where('is_active', true)->count() }}</div>
            <div class="lm-stat-label">Shift Aktif</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--neutral">{{ $inactiveCount ?? $items->where('is_active', false)->count() }}</div>
            <div class="lm-stat-label">Nonaktif</div>
        </div>
    </div>

    @php
        $hasAdvancedFilter = !empty($status);
        $activeFilterCount = collect([$status ?? null])->filter()->count();
    @endphp

    {{-- Filter Card --}}
    <div class="lm-filter-card">
        <form method="GET" action="{{ route('hr.shifts.index') }}" id="filterForm">
            <div class="lm-search-row">
                <div class="lm-search-input-wrap">
                    <svg class="lm-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Cari nama atau deskripsi shift..."
                        autocomplete="off"
                        class="lm-search-input"
                        aria-label="Cari nama shift"
                        data-shift-live-search>
                </div>

                @if(($q ?? '') !== '' || $hasAdvancedFilter)
                    <a href="{{ route('hr.shifts.index') }}" class="lm-btn-reset">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif

                <button type="button" class="lm-btn-toggle-filter {{ $hasAdvancedFilter ? 'active' : '' }}" onclick="toggleFilterPanel()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    @if($hasAdvancedFilter)
                        <span class="lm-filter-badge">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </div>

            <div class="lm-filter-panel" id="filterPanel" style="{{ $hasAdvancedFilter ? '' : 'display: none;' }}">
                <div class="lm-filter-grid">
                    <div class="lm-filter-group">
                        <label class="lm-filter-label" for="shift_status">Status</label>
                        <select name="status" id="shift_status" class="lm-filter-input">
                            <option value="">Semua Status</option>
                            <option value="active" @selected(($status ?? '') === 'active')>Aktif</option>
                            <option value="inactive" @selected(($status ?? '') === 'inactive')>Nonaktif</option>
                        </select>
                    </div>

                    <div class="lm-filter-actions">
                        <button type="submit" class="lm-btn-apply">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="lm-table-card" data-shift-results aria-live="polite">
        @if($items->isEmpty())
            <div class="lm-empty">
                <div class="lm-empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="lm-empty-title">Belum Ada Data</h3>
                <p class="lm-empty-desc">Belum ada data shift kerja yang dibuat.</p>
            </div>
        @else
            <div class="lm-table-wrap">
                <table class="lm-data-table">
                    <thead>
                        <tr>
                            <th style="width: 56px;">#</th>
                            <th>Nama Shift</th>
                            <th>Status</th>
                            <th>Jumlah Hari Diatur</th>
                            <th class="lm-actions-cell" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $sh)
                            <tr>
                                <td class="lm-cell-center lm-cell-muted">
                                    {{ ($items->firstItem() ?? 1) + $i }}
                                </td>

                                <td>
                                    <div class="lm-employee">
                                        <div class="lm-employee-avatar">{{ strtoupper(substr($sh->name, 0, 1)) }}</div>
                                        <div class="lm-employee-info">
                                            <span class="lm-employee-name">{{ $sh->name }}</span>
                                            <div class="lm-employee-detail">{{ $sh->description ?: 'Pola jam kerja harian karyawan' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($sh->is_active)
                                        <span class="lm-badge lm-badge--success">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="lm-badge lm-badge--neutral">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="lm-type lm-type--izin">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $sh->days_count ?? $sh->days()->count() }} Hari
                                    </span>
                                </td>

                                <td class="lm-actions-cell">
                                    <div class="lm-action-buttons">
                                        <a href="{{ route('hr.shifts.edit', $sh->id) }}" class="lm-btn-action lm-btn-action--edit">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            data-modal-target="delete-shift-{{ $sh->id }}"
                                            class="lm-btn-action lm-btn-action--delete">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    <div data-shift-pagination>
        @if($items->hasPages())
            <div class="lm-pagination">
                <x-pagination :items="$items" />
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modals --}}
    <div data-shift-modals>
        @foreach($items as $sh)
            <x-modal
                id="delete-shift-{{ $sh->id }}"
                title="Hapus Shift?"
                type="confirm"
                variant="danger"
                confirmLabel="Hapus"
                cancelLabel="Batal"
                :confirmFormAction="route('hr.shifts.destroy', $sh->id)"
                confirmFormMethod="DELETE">
                <p style="margin:0 0 4px 0;">
                    Yakin ingin menghapus shift berikut?
                </p>
                <p style="margin:0; font-weight:700; color:var(--text-primary);">
                    {{ $sh->name }}
                </p>
                <p style="margin:8px 0 0 0; font-size:0.85rem; color:var(--text-muted);">
                    Perhatian: Jadwal karyawan yang menggunakan shift ini mungkin akan terpengaruh.
                </p>
            </x-modal>
        @endforeach
    </div>

    <script>
        (function () {
            const form = document.getElementById('filterForm');
            const input = form?.querySelector('[data-shift-live-search]');
            const results = document.querySelector('[data-shift-results]');
            let timer;
            let request;

            if (!form || !input || !results) return;

            async function loadShiftResults() {
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                params.delete('page');
                url.search = params.toString();

                request?.abort();
                const currentRequest = new AbortController();
                request = currentRequest;
                results.classList.add('is-loading');
                results.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: currentRequest.signal,
                    });
                    if (!response.ok) throw new Error('Pencarian shift gagal');

                    const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
                    const selectors = [
                        '[data-shift-stats]',
                        '[data-shift-results]',
                        '[data-shift-pagination]',
                        '[data-shift-modals]',
                    ];

                    selectors.forEach((selector) => {
                        const current = document.querySelector(selector);
                        const next = documentResult.querySelector(selector);
                        if (current && next) {
                            current.innerHTML = next.innerHTML;
                        }
                    });

                    window.history.replaceState(null, '', url);
                } catch (error) {
                    if (error.name !== 'AbortError') console.error(error);
                } finally {
                    if (request === currentRequest) {
                        results.classList.remove('is-loading');
                        results.setAttribute('aria-busy', 'false');
                    }
                }
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(loadShiftResults, 350);
            });
        })();

        // Delegated modal handlers so dynamically refreshed modals continue to work
        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-shift-results] [data-modal-target]');
            if (trigger) {
                const modal = document.getElementById(trigger.dataset.modalTarget);
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
                return;
            }

            const modalBackdrop = event.target.closest('[data-shift-modals] .modal-backdrop');
            if (modalBackdrop && (event.target === modalBackdrop || event.target.closest('[data-modal-close]'))) {
                modalBackdrop.style.display = 'none';
                document.body.style.overflow = '';
            }
        });

        function toggleFilterPanel() {
            const panel = document.getElementById('filterPanel');
            const btn = document.querySelector('.lm-btn-toggle-filter');

            if (panel.style.display === 'none') {
                panel.style.display = '';
                btn.classList.add('active');
            } else {
                panel.style.display = 'none';
                btn.classList.remove('active');
            }
        }
    </script>

    <style>
        /* ========================================== */
        /* SECTION HEADER (x-slot)                    */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

        /* ========================================== */
        /* BASE / CTA BAR                             */
        /* ========================================== */
        .lm-cta-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .lm-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            border: none;
            cursor: pointer;
            flex: 1;
        }
        .lm-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .lm-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .lm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* STATS ROW                                  */
        /* ========================================== */
        .lm-stats {
            display: flex;
            align-items: center;
            gap: 1px;
            background: var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
        }
        .lm-stat {
            flex: 1;
            background: var(--white);
            padding: 10px 4px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            min-width: 0;
        }
        .lm-stat-value {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
        }
        .lm-stat-value--success { color: var(--success); }
        .lm-stat-value--neutral { color: var(--text-muted); }
        .lm-stat-label {
            font-size: 0.5625rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .lm-stat-divider {
            display: none;
        }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .lm-filter-card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 16px;
            overflow: hidden;
        }
        .lm-search-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px 16px;
        }
        .lm-search-input-wrap {
            position: relative;
            flex: 1;
        }
        .lm-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }
        .lm-search-input {
            width: 100%;
            padding: 10px 14px 10px 42px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }
        .lm-search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .lm-search-input::placeholder { color: var(--text-light); }

        .lm-btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 14px;
            background: var(--white);
            color: var(--text-muted);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            width: 100%;
        }
        .lm-btn-reset:hover {
            background: var(--danger-light);
            border-color: #fecaca;
            color: var(--danger);
        }

        .lm-btn-toggle-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--white);
            color: var(--text-muted);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
            font-family: inherit;
            width: 100%;
        }
        .lm-btn-toggle-filter:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .lm-btn-toggle-filter.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .lm-btn-toggle-filter.active svg { stroke: #fff; }
        .lm-filter-badge {
            background: var(--error);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        .lm-btn-toggle-filter.active .lm-filter-badge {
            background: rgba(255,255,255,0.3);
        }

        /* ========================================== */
        /* FILTER PANEL                               */
        /* ========================================== */
        .lm-filter-panel {
            background: var(--gray-50);
            border-top: 1px solid var(--border-light);
            margin: 0;
            padding: 14px 16px;
            animation: lmSlideDown 0.2s ease;
        }
        @keyframes lmSlideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .lm-filter-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .lm-filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .lm-filter-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .lm-filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }
        .lm-filter-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .lm-filter-actions {
            display: flex;
            justify-content: flex-end;
        }
        .lm-btn-apply {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 20px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .lm-btn-apply:hover { background: var(--primary-dark); }

        /* ========================================== */
        /* TABLE CARD                                 */
        /* ========================================== */
        .lm-table-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: opacity 0.15s ease;
        }
        .lm-table-card.is-loading { opacity: 0.55; pointer-events: none; }
        .lm-table-wrap {
            overflow-x: auto;
        }
        .lm-data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }
        .lm-data-table th {
            text-align: left;
            padding: 14px 16px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--gray-50);
            border-bottom: 1px solid var(--border-light);
        }
        .lm-data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
            font-size: 0.8125rem;
        }
        .lm-data-table tr:last-child td {
            border-bottom: none;
        }
        .lm-data-table tbody tr:hover td {
            background: var(--gray-50, #F5F7FA);
        }

        /* ========================================== */
        /* SHIFT / ITEM CELL                          */
        /* ========================================== */
        .lm-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .lm-employee-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .lm-employee-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary);
            text-decoration: none;
        }
        .lm-employee-detail {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ========================================== */
        /* TYPE / COUNT BADGE                         */
        /* ========================================== */
        .lm-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .lm-type--izin {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
        }

        /* ========================================== */
        /* STATUS BADGE (PILL)                        */
        /* ========================================== */
        .lm-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }
        .lm-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .lm-badge--neutral { background: var(--gray-100); color: var(--text-muted); }

        /* ========================================== */
        /* ACTIONS                                    */
        /* ========================================== */
        .lm-actions-cell {
            text-align: right;
            white-space: nowrap;
        }
        .lm-action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .lm-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid transparent;
            text-decoration: none;
            transition: all 0.2s ease;
            line-height: 1.4;
            font-family: inherit;
        }
        .lm-btn-action--edit {
            background: var(--white);
            border-color: var(--border);
            color: var(--text-secondary);
        }
        .lm-btn-action--edit:hover {
            background: var(--gray-50);
            border-color: var(--primary);
            color: var(--primary);
        }
        .lm-btn-action--delete {
            background: var(--danger-light);
            border-color: #fecaca;
            color: var(--error);
        }
        .lm-btn-action--delete:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        /* ========================================== */
        /* CELL HELPERS                               */
        /* ========================================== */
        .lm-cell-center { text-align: center; }
        .lm-cell-muted { color: var(--text-muted); }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .lm-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white);
        }
        .lm-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
        }
        .lm-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin: 0 0 6px;
        }
        .lm-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .lm-pagination {
            margin-top: 16px;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .lm-cta-bar {
                justify-content: flex-start;
            }
            .lm-btn-primary {
                flex: none;
            }
            .lm-stats {
                display: flex;
                align-items: center;
                background: var(--white);
                padding: 14px 20px;
            }
            .lm-stat {
                flex: 1;
                padding: 0;
                gap: 4px;
            }
            .lm-stat-value {
                font-size: 1.375rem;
            }
            .lm-stat-label {
                font-size: 0.6875rem;
                letter-spacing: 0.04em;
            }
            .lm-stat-divider {
                display: block;
                width: 1px;
                height: 36px;
                background: var(--border);
                flex-shrink: 0;
            }
            .lm-search-row {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
                padding: 16px 20px;
            }
            .lm-search-input-wrap {
                min-width: 260px;
            }
            .lm-btn-reset,
            .lm-btn-toggle-filter {
                flex-shrink: 0;
                width: auto;
            }
            .lm-filter-panel {
                padding: 16px 20px;
            }
            .lm-filter-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 14px 16px;
                align-items: end;
            }
            .lm-filter-actions {
                justify-content: flex-end;
            }
        }

        @media (min-width: 1024px) {
            .lm-data-table th,
            .lm-data-table td {
                padding: 16px 20px;
            }
        }
    </style>

</x-app>