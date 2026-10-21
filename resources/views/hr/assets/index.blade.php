<x-app title="Inventaris Karyawan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Inventaris Karyawan</h1>
                <p class="section-subtitle">Catat laptop, perangkat, dan fasilitas yang sedang dipegang karyawan</p>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="lm-alert lm-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="lm-alert lm-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="lm-cta-bar">
        <a href="{{ route('hr.assets.create') }}" class="lm-btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Asset
        </a>
    </div>

    {{-- Stats Summary --}}
    <div class="lm-stats" data-asset-stats>
        <div class="lm-stat">
            <div class="lm-stat-value">{{ $totalCount ?? $assets->total() }}</div>
            <div class="lm-stat-label">Total Asset</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--success">{{ $assignedCount ?? 0 }}</div>
            <div class="lm-stat-label">Sedang Dipakai</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--neutral">{{ $availableCount ?? 0 }}</div>
            <div class="lm-stat-label">Tersedia</div>
        </div>
    </div>

    @php
        $statusFilter = trim((string) request('status', ''));
        $searchQuery = trim((string) request('q', ''));
        $hasAdvancedFilter = $statusFilter !== '';
        $activeFilterCount = collect([$statusFilter !== '' ? $statusFilter : null])->filter()->count();
    @endphp

    {{-- Filter Card --}}
    <div class="lm-filter-card">
        <form method="GET" action="{{ route('hr.assets.index') }}" id="filterForm">
            <div class="lm-search-row">
                <div class="lm-search-input-wrap">
                    <svg class="lm-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $searchQuery }}"
                        placeholder="Cari kode, nama, serial, hostname, email laptop..."
                        autocomplete="off"
                        class="lm-search-input"
                        aria-label="Cari asset"
                        data-asset-live-search>
                </div>

                @if($searchQuery !== '' || $hasAdvancedFilter)
                    <a href="{{ route('hr.assets.index') }}" class="lm-btn-reset">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif

                <button type="button" class="lm-btn-toggle-filter {{ $hasAdvancedFilter ? 'active' : '' }}" onclick="toggleFilterPanel()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
                        <label class="lm-filter-label" for="asset_status">Status Asset</label>
                        <select name="status" id="asset_status" class="lm-filter-input" aria-label="Filter status">
                            <option value="">Semua status</option>
                            @foreach(['AVAILABLE' => 'Available', 'ASSIGNED' => 'Assigned', 'SERVICE' => 'Service', 'LOST' => 'Lost', 'DISPOSAL' => 'Disposal'] as $value => $label)
                                <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                            @endforeach
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
    <div class="lm-table-card" data-asset-results aria-live="polite">
        @if($assets->isEmpty())
            <div class="lm-empty">
                <div class="lm-empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                @if($searchQuery !== '' || $hasAdvancedFilter)
                    <h3 class="lm-empty-title">Asset Tidak Ditemukan</h3>
                    <p class="lm-empty-desc">Tidak ada asset yang sesuai dengan pencarian atau filter yang dipilih.</p>
                @else
                    <h3 class="lm-empty-title">Belum Ada Asset</h3>
                    <p class="lm-empty-desc">Belum ada data asset yang tercatat. Tambahkan asset pertama melalui tombol "Tambah Asset" di atas.</p>
                @endif
            </div>
        @else
            <div class="lm-table-wrap">
                <table class="lm-data-table">
                    <thead>
                        <tr>
                            <th style="width: 56px;">#</th>
                            <th>Kode</th>
                            <th>Asset</th>
                            <th>Kategori</th>
                            <th>Pemegang</th>
                            <th>Status</th>
                            <th class="lm-actions-cell" style="width: 190px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $i => $asset)
                            <tr>
                                <td class="lm-cell-center lm-cell-muted">
                                    {{ ($assets->firstItem() ?? 1) + $i }}
                                </td>

                                <td>
                                    <span class="lm-code-pill">{{ $asset->asset_code }}</span>
                                </td>

                                <td>
                                    <div class="lm-employee">
                                        @if($asset->photo_path)
                                            <button
                                                type="button"
                                                data-image-viewer-src="{{ asset('storage/'.$asset->photo_path) }}"
                                                data-image-viewer-alt="Foto asset {{ $asset->name }}"
                                                class="lm-thumb-btn"
                                                title="Lihat foto asset">
                                                <img class="lm-thumb" src="{{ asset('storage/'.$asset->photo_path) }}" alt="Foto {{ $asset->name }}">
                                            </button>
                                        @else
                                            <div class="lm-employee-avatar">{{ strtoupper(substr($asset->name, 0, 1)) }}</div>
                                        @endif
                                        <div class="lm-employee-info">
                                            <span class="lm-employee-name">{{ $asset->name }}</span>
                                            <div class="lm-employee-detail">
                                                {{ $asset->serial_number ?: '-' }} · {{ $asset->hostname ?: '-' }} · {{ $asset->email_laptop ?: '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($asset->category)
                                        <span class="lm-badge lm-badge--info">{{ $asset->category->name }}</span>
                                    @else
                                        <span class="lm-cell-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($asset->currentUser)
                                        <span class="lm-holder-name">{{ $asset->currentUser->name }}</span>
                                        @if($asset->currentPt)
                                            <div class="lm-employee-detail">{{ $asset->currentPt->name }}</div>
                                        @endif
                                    @else
                                        <span class="lm-cell-muted">Belum diserahkan</span>
                                    @endif
                                </td>

                                <td>
                                    @if($asset->asset_status === 'ASSIGNED')
                                        <span class="lm-badge lm-badge--success">{{ $asset->asset_status }}</span>
                                    @elseif($asset->asset_status === 'AVAILABLE')
                                        <span class="lm-badge lm-badge--neutral">{{ $asset->asset_status }}</span>
                                    @else
                                        <span class="lm-badge lm-badge--warning">{{ $asset->asset_status }}</span>
                                    @endif
                                </td>

                                <td class="lm-actions-cell">
                                    <div class="lm-action-buttons">
                                        <a href="{{ route('hr.assets.show', $asset) }}" class="lm-btn-action lm-btn-action--detail" title="Detail">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Detail
                                        </a>

                                        <a href="{{ route('hr.assets.edit', $asset) }}" class="lm-btn-action lm-btn-action--edit" title="Edit">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($assets->hasPages())
                <div class="lm-pagination-wrap">
                    <x-pagination :items="$assets" preserve-query />
                </div>
            @endif
        @endif
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

        (() => {
            const searchInput = document.querySelector('[data-asset-live-search]');
            const form = document.getElementById('filterForm');
            const resultsContainer = document.querySelector('[data-asset-results]');
            const statsContainer = document.querySelector('[data-asset-stats]');

            if (!searchInput || !form || !resultsContainer) {
                return;
            }

            let debounceTimer = null;
            let activeController = null;

            const buildUrl = () => {
                const url = new URL(form.action, window.location.origin);
                const formData = new FormData(form);

                for (const [key, value] of formData.entries()) {
                    const normalized = String(value).trim();
                    if (normalized !== '') {
                        url.searchParams.append(key, normalized);
                    }
                }

                return url;
            };

            const requestFilteredResults = async () => {
                const url = buildUrl();

                if (activeController) {
                    activeController.abort();
                }

                activeController = new AbortController();
                resultsContainer.classList.add('is-loading');

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                        signal: activeController.signal,
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const html = await response.text();
                    const parsed = new DOMParser().parseFromString(html, 'text/html');
                    const nextResults = parsed.querySelector('[data-asset-results]');
                    const nextStats = parsed.querySelector('[data-asset-stats]');

                    if (!nextResults) {
                        window.location.assign(url.toString());
                        return;
                    }

                    resultsContainer.innerHTML = nextResults.innerHTML;
                    if (statsContainer && nextStats) {
                        statsContainer.innerHTML = nextStats.innerHTML;
                    }
                    window.history.replaceState({}, '', url.toString());
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        window.location.assign(url.toString());
                    }
                } finally {
                    resultsContainer.classList.remove('is-loading');
                }
            };

            searchInput.addEventListener('input', () => {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(requestFilteredResults, 280);
            });
        })();
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
            color: var(--text-primary, #111827);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
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
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .lm-alert svg { flex-shrink: 0; }
        .lm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }

        /* ========================================== */
        /* CTA BAR (selaras dengan /hr/leave/master)  */
        /* ========================================== */
        .lm-cta-bar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 16px;
        }
        .lm-btn-primary {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 20px;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.25);
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .lm-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.35);
            transform: translateY(-1px);
        }
        .lm-btn-primary:active {
            transform: translateY(0);
        }

        /* ========================================== */
        /* STATS BAR                                  */
        /* ========================================== */
        .lm-stats {
            display: flex;
            align-items: center;
            background: var(--white, #FFFFFF);
            border-radius: 14px;
            padding: 16px 24px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #F3F4F6);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .lm-stat {
            flex: 1;
            text-align: center;
        }
        .lm-stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-dark, #0A3D62);
            line-height: 1.2;
        }
        .lm-stat-value--success { color: #16a34a; }
        .lm-stat-value--neutral { color: var(--text-muted, #6B7280); }
        .lm-stat-label {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            margin-top: 2px;
        }
        .lm-stat-divider {
            width: 1px;
            height: 36px;
            background: var(--border, #E5E7EB);
            margin: 0 16px;
        }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .lm-filter-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #F3F4F6);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .lm-search-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .lm-search-input-wrap {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
        }
        .lm-search-icon {
            position: absolute;
            left: 14px;
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }
        .lm-search-input {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 0.875rem;
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
            box-sizing: border-box;
        }
        .lm-search-input::placeholder { color: var(--text-light, #9CA3AF); }
        .lm-search-input:focus {
            border-color: var(--primary, #145DA0);
            background: var(--white, #FFFFFF);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }

        .lm-btn-toggle-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 11px 16px;
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #374151);
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
            font-family: inherit;
        }
        .lm-btn-toggle-filter:hover,
        .lm-btn-toggle-filter.active {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: rgba(20, 93, 160, 0.04);
        }
        .lm-filter-badge {
            width: 18px;
            height: 18px;
            background: var(--primary, #145DA0);
            color: #fff;
            border-radius: 50%;
            font-size: 0.6875rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .lm-btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 11px 14px;
            background: var(--white, #FFFFFF);
            color: var(--text-muted, #6B7280);
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .lm-btn-reset:hover {
            border-color: var(--error, #EF4444);
            color: var(--error, #EF4444);
        }

        .lm-filter-panel {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border-light, #F3F4F6);
        }
        .lm-filter-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
        }
        .lm-filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .lm-filter-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
        }
        .lm-filter-input {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 0.8125rem;
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
            outline: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .lm-filter-input:focus {
            border-color: var(--primary, #145DA0);
            background: var(--white, #FFFFFF);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .lm-filter-actions {
            display: flex;
            align-items: flex-end;
        }
        .lm-btn-apply {
            width: 100%;
            padding: 10px 18px;
            background: var(--primary-dark, #0A3D62);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            font-family: inherit;
        }
        .lm-btn-apply:hover {
            background: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* TABLE CARD                                 */
        /* ========================================== */
        .lm-table-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #F3F4F6);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transition: opacity 0.2s ease;
        }
        .lm-table-card.is-loading {
            opacity: 0.55;
            pointer-events: none;
        }
        .lm-table-wrap { overflow-x: auto; }
        .lm-data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 820px;
        }
        .lm-data-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--gray-50, #F5F7FA);
            border-bottom: 1px solid var(--border, #E5E7EB);
            white-space: nowrap;
        }
        .lm-data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light, #F3F4F6);
            vertical-align: middle;
            font-size: 0.8125rem;
        }
        .lm-data-table tbody tr {
            transition: background 0.15s ease;
        }
        .lm-data-table tbody tr:hover {
            background: rgba(20, 93, 160, 0.02);
        }
        .lm-data-table tbody tr:last-child td {
            border-bottom: none;
        }
        .lm-cell-center { text-align: center; }
        .lm-cell-muted { color: var(--text-muted, #6B7280); }

        /* ========================================== */
        /* ASSET ROW CELLS                            */
        /* ========================================== */
        .lm-code-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 8px;
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }
        .lm-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .lm-employee-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            font-size: 0.8125rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .lm-thumb-btn {
            border: 0;
            padding: 0;
            background: transparent;
            cursor: pointer;
            flex-shrink: 0;
        }
        .lm-thumb {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border, #E5E7EB);
            background: var(--gray-50, #F5F7FA);
            display: block;
        }
        .lm-employee-info {
            min-width: 0;
        }
        .lm-employee-name {
            font-weight: 600;
            color: var(--text-primary, #111827);
            font-size: 0.8125rem;
            display: block;
            line-height: 1.3;
        }
        .lm-employee-detail {
            font-size: 0.6875rem;
            color: var(--text-muted, #6B7280);
            line-height: 1.4;
            margin-top: 2px;
        }
        .lm-holder-name {
            font-weight: 600;
            color: var(--text-primary, #111827);
            font-size: 0.8125rem;
            display: block;
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
            font-size: 0.6875rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .lm-badge--info {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145DA0);
        }
        .lm-badge--success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }
        .lm-badge--warning {
            background: rgba(245, 158, 11, 0.13);
            color: #b45309;
        }
        .lm-badge--neutral {
            background: var(--gray-100, #F3F4F6);
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* ACTION BUTTONS                             */
        /* ========================================== */
        .lm-actions-cell {
            text-align: right;
            white-space: nowrap;
        }
        .lm-action-buttons {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
        }
        .lm-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid var(--border, #E5E7EB);
            background: var(--white, #FFFFFF);
            color: var(--text-secondary, #374151);
            font-family: inherit;
        }
        .lm-btn-action--detail:hover {
            background: rgba(10, 61, 98, 0.06);
            color: var(--primary-dark, #0A3D62);
            border-color: rgba(10, 61, 98, 0.25);
        }
        .lm-btn-action--edit:hover {
            background: rgba(20, 93, 160, 0.06);
            color: var(--primary, #145DA0);
            border-color: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* EMPTY STATE & PAGINATION                   */
        /* ========================================== */
        .lm-empty {
            text-align: center;
            padding: 48px 24px;
        }
        .lm-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50, #F5F7FA);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light, #9CA3AF);
        }
        .lm-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin: 0 0 6px;
        }
        .lm-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 360px;
            line-height: 1.5;
        }
        .lm-pagination-wrap {
            padding: 12px 16px;
            border-top: 1px solid var(--border-light, #F3F4F6);
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (max-width: 768px) {
            .lm-stats {
                padding: 12px 10px;
            }
            .lm-stat-value {
                font-size: 1.2rem;
            }
            .lm-stat-divider {
                margin: 0 8px;
                height: 28px;
            }
            .lm-filter-grid {
                grid-template-columns: 1fr;
            }
            .lm-search-row {
                flex-wrap: wrap;
            }
            .lm-search-input-wrap {
                flex-basis: 100%;
            }
            .lm-btn-toggle-filter,
            .lm-btn-reset {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</x-app>
