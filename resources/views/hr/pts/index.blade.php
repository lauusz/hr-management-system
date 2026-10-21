<x-app title="Master PT">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4a2 2 0 012-2h2a2 2 0 012 2v4"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Master PT</h1>
                <p class="section-subtitle">Master data perusahaan (PT) untuk karyawan</p>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="lm-alert lm-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="lm-alert lm-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="lm-cta-bar">
        <a href="{{ route('hr.pts.create') }}" class="lm-btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah PT
        </a>
    </div>

    {{-- Stats Summary --}}
    <div class="lm-stats" data-pt-stats>
        <div class="lm-stat">
            <div class="lm-stat-value">{{ $totalCount ?? $items->total() }}</div>
            <div class="lm-stat-label">Total PT</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--success">{{ $activeCount ?? 0 }}</div>
            <div class="lm-stat-label">Memiliki Karyawan</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--neutral">{{ $emptyCount ?? 0 }}</div>
            <div class="lm-stat-label">Belum Ada Karyawan</div>
        </div>
    </div>

    @php
        $hasAdvancedFilter = !empty($status);
        $activeFilterCount = collect([$status ?? null])->filter()->count();
    @endphp

    {{-- Filter Card --}}
    <div class="lm-filter-card">
        <form method="GET" action="{{ route('hr.pts.index') }}" id="filterForm">
            <div class="lm-search-row">
                <div class="lm-search-input-wrap">
                    <svg class="lm-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Cari nama perusahaan (PT)..."
                        autocomplete="off"
                        class="lm-search-input"
                        aria-label="Cari PT"
                        data-pt-live-search
                        data-ptm-search>
                </div>

                @if(($q ?? '') !== '' || $hasAdvancedFilter)
                    <a href="{{ route('hr.pts.index') }}" class="lm-btn-reset">
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
                        <label class="lm-filter-label" for="pt_status">Status Karyawan</label>
                        <select name="status" id="pt_status" class="lm-filter-input">
                            <option value="">Semua PT</option>
                            <option value="with_employees" @selected(($status ?? '') === 'with_employees')>Memiliki Karyawan</option>
                            <option value="without_employees" @selected(($status ?? '') === 'without_employees')>Belum Ada Karyawan</option>
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
    <div class="lm-table-card" data-pt-results aria-live="polite">
        @if($items->isEmpty())
            <div class="lm-empty">
                <div class="lm-empty-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4a2 2 0 012-2h2a2 2 0 012 2v4"/>
                    </svg>
                </div>
                @if(($q ?? '') !== '' || $hasAdvancedFilter)
                    <h3 class="lm-empty-title">PT Tidak Ditemukan</h3>
                    <p class="lm-empty-desc">Tidak ada data PT yang cocok dengan pencarian atau filter Anda.</p>
                @else
                    <h3 class="lm-empty-title">Belum Ada PT</h3>
                    <p class="lm-empty-desc">Belum ada data PT yang terdaftar. Tambahkan PT pertama melalui tombol "Tambah PT" di atas.</p>
                @endif
            </div>
        @else
            <div class="lm-table-wrap">
                <table class="lm-data-table">
                    <thead>
                        <tr>
                            <th style="width: 56px;">#</th>
                            <th>Nama Perusahaan (PT)</th>
                            <th>Jumlah Karyawan</th>
                            <th class="lm-actions-cell" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $pt)
                            <tr>
                                <td class="lm-cell-center lm-cell-muted">
                                    {{ ($items->firstItem() ?? 1) + $i }}
                                </td>

                                <td>
                                    <div class="lm-employee">
                                        <div class="lm-employee-avatar">{{ strtoupper(substr($pt->name, 0, 1)) }}</div>
                                        <div class="lm-employee-info">
                                            <span class="lm-employee-name">{{ $pt->name }}</span>
                                            <div class="lm-employee-detail">Entitas Perusahaan</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if(($pt->profiles_count ?? 0) > 0)
                                        <span class="lm-badge lm-badge--info">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ $pt->profiles_count }} Karyawan
                                        </span>
                                    @else
                                        <span class="lm-badge lm-badge--neutral">Belum Ada Karyawan</span>
                                    @endif
                                </td>

                                <td class="lm-actions-cell">
                                    <div class="lm-action-buttons">
                                        <a href="{{ route('hr.pts.edit', $pt->id) }}" class="lm-btn-action lm-btn-action--edit" title="Edit">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            class="lm-btn-action lm-btn-action--delete"
                                            title="Hapus"
                                            data-modal-target="delete-pt-{{ $pt->id }}"
                                            data-modal-open="delete-pt-{{ $pt->id }}">
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

            @if($items->hasPages())
                <div class="lm-pagination-wrap">
                    <x-pagination :items="$items" />
                </div>
            @endif
        @endif
    </div>

    {{-- Delete Modals --}}
    <div data-pt-modals>
        @foreach($items as $pt)
            <x-modal
                :id="'delete-pt-' . $pt->id"
                title="Hapus PT?"
                variant="danger"
                type="confirm"
                confirmLabel="Hapus"
                cancelLabel="Batal"
                :confirmFormAction="route('hr.pts.destroy', $pt->id)"
                confirmFormMethod="DELETE"
            >
                <p style="margin:0 0 4px 0;">Apakah Anda yakin ingin menghapus PT berikut?</p>
                <p style="margin:0; font-weight:700; color:var(--text-primary);">{{ $pt->name }}</p>
                <p style="color:var(--text-muted); font-size:0.85rem; margin:8px 0 0 0;">Pastikan PT ini tidak sedang digunakan oleh karyawan aktif.</p>
            </x-modal>
        @endforeach
    </div>

    <script>
        function toggleFilterPanel() {
            const panel = document.getElementById('filterPanel');
            const btn = document.querySelector('.lm-btn-toggle-filter');
            if (!panel) return;
            if (panel.style.display === 'none') {
                panel.style.display = '';
                btn?.classList.add('active');
            } else {
                panel.style.display = 'none';
                btn?.classList.remove('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            const searchInput = form ? form.querySelector('[data-pt-live-search]') : null;
            let debounceTimer = null;
            let activeController = null;

            function submitFilterAjax() {
                if (!form) return;
                const url = new URL(form.action, window.location.origin);
                const formData = new FormData(form);
                formData.forEach((value, key) => {
                    if (String(value).trim() !== '') {
                        url.searchParams.set(key, String(value).trim());
                    }
                });

                if (activeController) {
                    activeController.abort();
                }
                activeController = new AbortController();

                fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeController.signal
                })
                .then(res => res.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newResults = doc.querySelector('[data-pt-results]');
                    const currentResults = document.querySelector('[data-pt-results]');
                    if (newResults && currentResults) {
                        currentResults.innerHTML = newResults.innerHTML;
                    }

                    const newStats = doc.querySelector('[data-pt-stats]');
                    const currentStats = document.querySelector('[data-pt-stats]');
                    if (newStats && currentStats) {
                        currentStats.innerHTML = newStats.innerHTML;
                    }

                    const newModals = doc.querySelector('[data-pt-modals]');
                    const currentModals = document.querySelector('[data-pt-modals]');
                    if (newModals && currentModals) {
                        currentModals.innerHTML = newModals.innerHTML;
                    }

                    window.history.replaceState({}, '', url.toString());
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        console.error(err);
                    }
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(submitFilterAjax, 300);
                });
            }

            // Delegated modal handlers (supports data-modal-target and data-modal-open)
            document.addEventListener('click', function(e) {
                const openTrigger = e.target.closest('[data-modal-target], [data-modal-open]');
                if (openTrigger) {
                    const id = openTrigger.getAttribute('data-modal-target') || openTrigger.getAttribute('data-modal-open');
                    const modal = id ? document.getElementById(id) : null;
                    if (modal) modal.style.display = 'flex';
                    return;
                }

                const closeTrigger = e.target.closest('[data-modal-close="true"]');
                if (closeTrigger) {
                    const modal = closeTrigger.closest('.modal-backdrop');
                    if (modal) modal.style.display = 'none';
                    return;
                }

                if (e.target.classList && e.target.classList.contains('modal-backdrop')) {
                    e.target.style.display = 'none';
                }
            });
        });
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
        /* FLASH MESSAGES (selaras dengan /hr/leave/master) */
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
        .lm-alert svg { flex-shrink: 0; }
        .lm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #15803d;
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #b91c1c;
        }

        /* ========================================== */
        /* CTA BAR                                    */
        /* ========================================== */
        .lm-cta-bar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .lm-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.25);
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }
        .lm-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.35);
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* STATS SUMMARY                              */
        /* ========================================== */
        .lm-stats {
            display: flex;
            align-items: center;
            background: var(--white);
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .lm-stat {
            flex: 1;
            text-align: center;
        }
        .lm-stat-value {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary-dark);
            line-height: 1.2;
        }
        .lm-stat-value--success { color: #16a34a; }
        .lm-stat-value--neutral { color: var(--text-muted); }
        .lm-stat-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }
        .lm-stat-divider {
            width: 1px;
            height: 32px;
            background: var(--border-light);
            flex-shrink: 0;
        }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .lm-filter-card {
            background: var(--white);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .lm-search-row {
            display: flex;
            gap: 10px;
            align-items: center;
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
            padding: 11px 14px 11px 42px;
            border: 1.5px solid var(--border-light);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--gray-50);
            transition: all 0.2s ease;
            font-family: inherit;
            outline: none;
        }
        .lm-search-input::placeholder { color: var(--text-light); }
        .lm-search-input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }

        .lm-btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 11px 14px;
            border-radius: 12px;
            border: 1.5px solid rgba(239, 68, 68, 0.25);
            background: rgba(239, 68, 68, 0.06);
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .lm-btn-reset:hover {
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.4);
        }

        .lm-btn-toggle-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 11px 16px;
            border-radius: 12px;
            border: 1.5px solid var(--border-light);
            background: var(--gray-50);
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            font-family: inherit;
            position: relative;
        }
        .lm-btn-toggle-filter:hover,
        .lm-btn-toggle-filter.active {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(20, 93, 160, 0.06);
        }
        .lm-filter-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 99px;
            background: var(--primary);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
        }

        .lm-filter-panel {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border-light);
        }
        .lm-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .lm-filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
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
            padding: 9px 12px;
            border: 1.5px solid var(--border-light);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-primary);
            background: var(--gray-50);
            transition: all 0.2s ease;
            font-family: inherit;
            outline: none;
        }
        .lm-filter-input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .lm-filter-actions {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .lm-btn-apply {
            flex: 1;
            padding: 9px 16px;
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
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .lm-table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .lm-data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        .lm-data-table thead th {
            background: var(--gray-50);
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
            white-space: nowrap;
        }
        .lm-data-table tbody tr {
            border-bottom: 1px solid var(--border-light);
            transition: background 0.15s ease;
        }
        .lm-data-table tbody tr:last-child { border-bottom: none; }
        .lm-data-table tbody tr:hover { background: rgba(20, 93, 160, 0.02); }
        .lm-data-table td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 13px;
            color: var(--text-primary);
        }
        .lm-cell-center { text-align: center; }
        .lm-cell-muted { color: var(--text-muted); font-size: 12px; }

        /* ========================================== */
        /* ENTITY CELL (selaras dengan .lm-employee)  */
        /* ========================================== */
        .lm-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .lm-employee-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        .lm-employee-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .lm-employee-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 13px;
            line-height: 1.3;
        }
        .lm-employee-detail {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.3;
        }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .lm-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .lm-badge--info {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            border: 1px solid rgba(20, 93, 160, 0.16);
        }
        .lm-badge--neutral {
            background: var(--gray-100);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        /* ========================================== */
        /* ACTION BUTTONS                             */
        /* ========================================== */
        .lm-actions-cell {
            text-align: right;
            white-space: nowrap;
        }
        thead .lm-actions-cell {
            text-align: right;
        }
        .lm-action-buttons {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            justify-content: flex-end;
        }
        .lm-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            cursor: pointer;
            font-family: inherit;
        }
        .lm-btn-action--edit {
            background: rgba(20, 93, 160, 0.06);
            color: var(--primary);
            border: 1px solid rgba(20, 93, 160, 0.15);
        }
        .lm-btn-action--edit:hover {
            background: rgba(20, 93, 160, 0.12);
            border-color: var(--primary);
        }
        .lm-btn-action--delete {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .lm-btn-action--delete:hover {
            background: rgba(239, 68, 68, 0.14);
            border-color: rgba(239, 68, 68, 0.35);
        }

        /* ========================================== */
        /* EMPTY STATE & PAGINATION                   */
        /* ========================================== */
        .lm-empty {
            text-align: center;
            padding: 48px 20px;
        }
        .lm-empty-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(20, 93, 160, 0.06);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }
        .lm-empty-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 4px;
        }
        .lm-empty-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
        }
        .lm-pagination-wrap {
            padding: 14px 16px;
            border-top: 1px solid var(--border-light);
            display: flex;
            justify-content: center;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 640px) {
            .lm-filter-grid {
                grid-template-columns: 1fr auto;
            }
        }

        @media (max-width: 639px) {
            .lm-cta-bar {
                flex-direction: column;
            }
            .lm-btn-primary {
                width: 100%;
                justify-content: center;
            }
            .lm-stats {
                padding: 12px;
            }
            .lm-stat-value {
                font-size: 18px;
            }
            .lm-search-row {
                flex-wrap: wrap;
            }
            .lm-search-input-wrap {
                flex-basis: 100%;
            }
            .lm-btn-reset,
            .lm-btn-toggle-filter {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</x-app>
