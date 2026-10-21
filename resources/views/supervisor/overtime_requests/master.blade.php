<x-app title="Daftar Lembur Bawahan">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Daftar Lembur</h1>
                <p class="section-subtitle">Riwayat pengajuan lembur staf di bawah supervisi Anda</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="som-alert som-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="som-alert som-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- STATS ROW                                    --}}
    {{-- ============================================== --}}
    <div class="som-stats" data-supervisor-overtime-stats>
        <div class="som-stat">
            <div class="som-stat-value">{{ $overtimes->total() }}</div>
            <div class="som-stat-label">Total</div>
        </div>
        <div class="som-stat-divider"></div>
        <div class="som-stat">
            <div class="som-stat-value som-stat-value--warning">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)->count() }}
            </div>
            <div class="som-stat-label">Menunggu</div>
        </div>
        <div class="som-stat-divider"></div>
        <div class="som-stat">
            <div class="som-stat-value som-stat-value--success">
                {{ $overtimes->whereIn('status', [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])->count() }}
            </div>
            <div class="som-stat-label">Disetujui</div>
        </div>
        <div class="som-stat-divider"></div>
        <div class="som-stat">
            <div class="som-stat-value som-stat-value--error">
                {{ $overtimes->where('status', \App\Models\OvertimeRequest::STATUS_REJECTED)->count() }}
            </div>
            <div class="som-stat-label">Ditolak</div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- FILTER CARD                                  --}}
    {{-- ============================================== --}}
    @php
        $hasAdvancedFilter = request()->filled('date_range') || request()->filled('status');
        $activeFilterCount = collect([request('date_range'), request('status')])->filter()->count();
        $statusLabels = [
            \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR => 'Menunggu Approval',
            \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR => 'Disetujui',
            \App\Models\OvertimeRequest::STATUS_APPROVED_HRD => 'Disetujui',
            \App\Models\OvertimeRequest::STATUS_REJECTED => 'Ditolak',
            \App\Models\OvertimeRequest::STATUS_CANCELLED => 'Dibatalkan',
        ];
    @endphp
    <div class="lm-filter-card">
        <form method="GET" action="{{ route('supervisor.overtime-requests.master') }}" id="filterForm" data-has-filters="{{ $hasAdvancedFilter ? '1' : '0' }}">
            <div class="lm-search-row">
                <div class="lm-search-input-wrap">
                    <svg class="lm-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Cari nama karyawan..." autocomplete="off" class="lm-search-input" aria-label="Cari nama karyawan">
                </div>
                <a href="{{ route('supervisor.overtime-requests.master') }}" id="searchReset" class="lm-btn-reset" @if(!request('q') && !$hasAdvancedFilter) hidden @endif>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </a>
                <button type="button" id="filterToggle" class="lm-btn-toggle-filter {{ $hasAdvancedFilter ? 'active' : '' }}" aria-controls="filterPanel" aria-expanded="{{ $hasAdvancedFilter ? 'true' : 'false' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                    <span id="filterBadge" class="lm-filter-badge" @if(!$hasAdvancedFilter) hidden @endif>{{ $activeFilterCount }}</span>
                </button>
            </div>
            <div class="lm-filter-panel" id="filterPanel" @if(!$hasAdvancedFilter) hidden @endif>
                <div class="lm-filter-grid">
                    <div class="lm-filter-group">
                        <label class="lm-filter-label" for="date_range">Tanggal Lembur</label>
                        <input type="text" id="date_range" name="date_range" value="{{ request('date_range') }}" placeholder="Pilih rentang tanggal" class="lm-filter-input" autocomplete="off">
                    </div>
                    <div class="lm-filter-group">
                        <label class="lm-filter-label" for="status">Status</label>
                        <select name="status" id="status" class="lm-filter-input">
                            <option value="">Semua Status</option>
                            @foreach($statusOptions as $opt)
                                <option value="{{ $opt }}" @selected(request('status') === $opt)>{{ $statusLabels[$opt] ?? $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lm-filter-actions"><button type="submit" class="lm-btn-apply">Terapkan Filter</button></div>
                </div>
            </div>
        </form>
    </div>
    <p id="searchError" class="lm-search-error" role="alert" hidden>Pencarian gagal. Silakan coba lagi atau tekan Enter untuk memuat ulang.</p>

    {{-- ============================================== --}}
    {{-- TABLE CARD                                   --}}
    {{-- ============================================== --}}
    <div data-supervisor-overtime-results aria-live="polite">
    <div class="som-card som-card--table">
        <div class="som-table-wrapper">
            <table class="som-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Karyawan</th>
                        <th>Tgl Pengajuan</th>
                        <th>Tgl Lembur</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overtimes as $i => $overtime)
                        @php
                            $st = $overtime->status;
                            $badgeClass = 'som-badge--gray';
                            $statusLabel = $overtime->status_label;
                            $statusIcon = '';

                            if (in_array($st, [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])) {
                                $badgeClass = 'som-badge--success';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_REJECTED) {
                                $badgeClass = 'som-badge--error';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                                $badgeClass = 'som-badge--warning';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                            } elseif ($st === \App\Models\OvertimeRequest::STATUS_CANCELLED) {
                                $badgeClass = 'som-badge--gray';
                                $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                            }
                        @endphp
                        <tr class="som-data-row">
                            <td class="som-row-number som-text-muted som-text-center">
                                {{ $overtimes->firstItem() + $i }}
                            </td>
                            <td class="som-row-employee">
                                <div class="som-employee">
                                    <div class="som-employee-avatar">{{ substr($overtime->user->name, 0, 1) }}</div>
                                    <div class="som-employee-info">
                                        <span class="som-employee-name">{{ $overtime->user->name }}</span>
                                        <span class="som-employee-role">{{ $overtime->user->position->name ?? 'Staff' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="som-row-submitted som-text-muted">
                                {{ $overtime->created_at->translatedFormat('j F Y') }}
                                <div class="som-text-muted som-text-xs">{{ $overtime->created_at->format('H:i') }}</div>
                            </td>
                            <td class="som-row-overtime">
                                <span class="som-text-date">{{ $overtime->overtime_date->translatedFormat('j F Y') }}</span>
                                <div class="som-text-muted som-text-xs">{{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}</div>
                            </td>
                            <td class="som-row-duration">
                                <span class="som-duration">{{ $overtime->duration_human }}</span>
                            </td>
                            <td class="som-row-status">
                                <span class="som-badge {{ $badgeClass }}">
                                    {!! $statusIcon !!}
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="som-row-action som-text-right">
                                <a href="{{ route('supervisor.overtime-requests.show', $overtime->id) }}" class="som-action-link">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="som-empty-row">
                            <td colspan="7" class="som-empty-cell">
                                <div class="som-empty">
                                    <div class="som-empty-icon">
                                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="som-empty-title">Belum Ada Data</h3>
                                    <p class="som-empty-desc">Belum ada riwayat lembur dari bawahan Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($overtimes->hasPages())
        <div class="som-pagination">
            <x-pagination :items="$overtimes" :preserve-query="true" />
        </div>
    @endif
    </div>

    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
    <script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/3.7.1/jquery.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                allowInput: true,
                locale: { rangeSeparator: " to " }
            });
        });
    </script>
    <script data-supervisor-overtime-search>
        (function ($) {
            const form = $('#filterForm');
            const input = $('#q');
            const panel = $('#filterPanel');
            const toggle = $('#filterToggle');
            const badge = $('#filterBadge');
            const reset = $('#searchReset');
            const error = $('#searchError');
            let timer;
            let request;
            let version = 0;

            function cancelSearch() {
                version++;
                clearTimeout(timer);
                if (request) request.abort();
                request = null;
            }

            toggle.on('click', function () {
                const open = panel.prop('hidden');
                panel.prop('hidden', !open);
                toggle.attr('aria-expanded', String(open)).toggleClass('active', open);
            });

            form.on('change', function (event) {
                if (event.target !== input[0]) cancelSearch();
            });

            input.on('input', function () {
                cancelSearch();
                reset.prop('hidden', !input.val() && form[0].dataset.hasFilters !== '1');
                error.prop('hidden', true);
                const currentVersion = version;
                timer = setTimeout(function () {
                    const url = new URL(form[0].action, window.location.origin);
                    url.search = form.serialize();
                    url.searchParams.delete('page');
                    request = $.ajax({ url: url.toString(), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .done(function (html) {
                            if (currentVersion !== version) return;
                            const page = new DOMParser().parseFromString(html, 'text/html');
                            const selectors = ['[data-supervisor-overtime-stats]', '[data-supervisor-overtime-results]'];
                            const fragments = selectors.map(function (selector) {
                                return [document.querySelector(selector), page.querySelector(selector)];
                            });
                            if (fragments.some(function (fragment) { return !fragment[0] || !fragment[1]; })) {
                                error.prop('hidden', false);
                                return;
                            }
                            fragments.forEach(function (fragment) { fragment[0].innerHTML = fragment[1].innerHTML; });
                            const filterCount = ['date_range', 'status'].filter(function (name) {
                                return url.searchParams.get(name);
                            }).length;
                            form[0].dataset.hasFilters = filterCount ? '1' : '0';
                            badge.text(filterCount).prop('hidden', !filterCount);
                            reset.prop('hidden', !input.val() && !filterCount);
                            window.history.replaceState(null, '', url);
                        })
                        .fail(function (_xhr, status) {
                            if (status !== 'abort' && currentVersion === version) error.prop('hidden', false);
                        });
                }, 350);
            });
        })(jQuery);
    </script>

    <style>
        .lm-filter-card { margin-bottom: 16px; background: var(--white); border: 1px solid var(--border-light); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
        .lm-search-row { display: flex; flex-direction: column; gap: 10px; padding: 16px; }
        .lm-search-input-wrap { position: relative; flex: 1; }
        .lm-search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
        .lm-search-input { width: 100%; padding: 10px 14px 10px 42px; border: 1.5px solid var(--border); border-radius: 12px; font: inherit; font-size: 13px; color: var(--text-primary); outline: none; }
        .lm-search-input:focus, .lm-filter-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(20,93,160,.1); }
        .lm-search-input::placeholder { color: var(--text-light); }
        .lm-btn-reset, .lm-btn-toggle-filter { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 16px; background: var(--white); color: var(--text-muted); border: 1.5px solid var(--border); border-radius: 12px; font: inherit; font-size: 13px; cursor: pointer; text-decoration: none; white-space: nowrap; }
        .lm-btn-reset[hidden], .lm-filter-panel[hidden], .lm-filter-badge[hidden], .lm-search-error[hidden] { display: none; }
        .lm-btn-reset:hover { background: var(--danger-light); border-color: #fecaca; color: var(--danger); }
        .lm-btn-toggle-filter:hover { border-color: var(--primary); color: var(--primary); }
        .lm-btn-toggle-filter.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .lm-filter-badge { background: var(--error); color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; }
        .lm-filter-panel { background: var(--gray-50); border-top: 1px solid var(--border-light); padding: 14px 16px; }
        .lm-filter-grid { display: flex; flex-direction: column; gap: 14px; }
        .lm-filter-group { display: flex; flex-direction: column; gap: 6px; }
        .lm-filter-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; }
        .lm-filter-input { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px; font: inherit; font-size: 13px; color: var(--text-primary); background: var(--white); outline: none; }
        .lm-filter-actions { display: flex; justify-content: flex-end; }
        .lm-btn-apply { padding: 10px 20px; background: var(--primary); color: #fff; border: none; border-radius: 10px; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; }
        .lm-btn-apply:hover { background: var(--primary-dark); }
        .lm-search-error { color: #b91c1c; font-size: 13px; margin: 0 0 12px; }
        @media (min-width: 480px) {
            .lm-search-row { flex-direction: row; align-items: center; flex-wrap: wrap; padding: 16px 20px; } .lm-search-input-wrap { min-width: 260px; }
            .lm-filter-panel { padding: 16px 20px; } .lm-filter-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px 16px; align-items: end; } .lm-filter-actions { grid-column: 1 / -1; }
        }
        @media (min-width: 768px) { .lm-filter-grid { grid-template-columns: repeat(3, 1fr); } }
        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .som-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .som-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .som-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

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
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark, #0A3D62); }

        /* ========================================== */
        /* STATS ROW                                  */
        /* ========================================== */
        .som-stats {
            display: flex;
            align-items: center;
            gap: 1px;
            background: var(--border-light, #E5E7EB);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .som-stat {
            flex: 1;
            background: var(--white, #FFFFFF);
            padding: 10px 4px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            min-width: 0;
        }
        .som-stat-value {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1;
        }
        .som-stat-value--success { color: var(--success, #22C55E); }
        .som-stat-value--warning { color: var(--warning, #F59E0B); }
        .som-stat-value--error   { color: var(--error, #EF4444); }
        .som-stat-label {
            font-size: 0.5625rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .som-stat-divider {
            display: none;
        }

        /* ========================================== */
        /* CARDS                                      */
        /* ========================================== */
        .som-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
            margin-bottom: 16px;
        }
        .som-card--table {
            padding: 0;
        }

        /* ========================================== */
        /* TABLE                                      */
        /* ========================================== */
        .som-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        .som-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        .som-table thead th {
            background: var(--gray-50, #F5F7FA);
            padding: 14px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border, #E5E7EB);
            white-space: nowrap;
        }
        .som-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            font-size: 13.5px;
            color: var(--text-primary, #1f2937);
            vertical-align: middle;
        }
        .som-table tbody tr:last-child td { border-bottom: none; }
        .som-table tbody tr:hover td { background: var(--gray-50, #F5F7FA); }
        .som-table tbody tr { transition: background 0.15s ease; }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .som-badge {
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
        .som-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .som-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .som-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .som-badge--gray    { background: var(--gray-50, #F8FAFC); color: var(--text-secondary, #374151); }

        .som-duration {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #374151);
            border: 1px solid var(--border-light, #E5E7EB);
        }

        /* ========================================== */
        /* EMPLOYEE CELL                              */
        /* ========================================== */
        .som-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .som-employee-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145DA0);
            font-size: 0.875rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .som-employee-info {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }
        .som-employee-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .som-employee-role {
            font-size: 12px;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* ACTION LINK                                */
        /* ========================================== */
        .som-action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 14px;
            border: 1.5px solid var(--border, #E5E7EB);
            background: var(--white, #FFFFFF);
            color: var(--primary, #145DA0);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .som-action-link:hover {
            background: var(--gray-50, #F5F7FA);
            border-color: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .som-empty-cell {
            padding: 0 !important;
        }
        .som-empty {
            text-align: center;
            padding: 48px 24px;
        }
        .som-empty-icon {
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
        .som-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin: 0 0 6px;
        }
        .som-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .som-pagination {
            margin-top: 24px;
        }

        /* ========================================== */
        /* UTILITY                                    */
        /* ========================================== */
        .som-text-muted { color: var(--text-muted, #6B7280); font-size: 13px; }
        .som-text-center { text-align: center; }
        .som-text-right { text-align: right; }
        .som-text-date { font-weight: 600; color: var(--text-primary, #1f2937); font-size: 13.5px; }
        .som-text-xs { font-size: 11px; margin-top: 2px; }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .som-stats {
                display: flex;
                align-items: center;
                background: var(--white, #FFFFFF);
                padding: 14px 20px;
            }
            .som-stat {
                flex: 1;
                padding: 0;
                gap: 4px;
            }
            .som-stat-value {
                font-size: 1.375rem;
            }
            .som-stat-label {
                font-size: 0.6875rem;
                letter-spacing: 0.04em;
            }
            .som-stat-divider {
                display: block;
                width: 1px;
                height: 36px;
                background: var(--border, #D1D5DB);
                flex-shrink: 0;
            }
        }

        @media (min-width: 768px) {
            .som-table tbody td {
                padding: 16px;
            }
            .som-table thead th {
                padding: 16px;
            }
        }

        @media (max-width: 1024px) {
            .lm-search-input, .lm-filter-input { font-size: 16px; }
            .lm-btn-reset, .lm-btn-toggle-filter, .lm-btn-apply { min-height: 44px; }
            .som-card--table { background: transparent; border: 0; border-radius: 0; box-shadow: none; overflow: visible; }
            .som-table-wrapper { overflow: visible; }
            .som-table { display: block; min-width: 0; }
            .som-table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
            .som-table tbody { display: flex; flex-direction: column; gap: 10px; }
            .som-table tbody .som-data-row {
                display: grid;
                position: relative;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                grid-template-areas: "employee employee" "status status" "overtime submitted" "duration action";
                gap: 12px;
                padding: 16px;
                background: var(--white, #fff);
                border: 1px solid var(--border-light, #E5E7EB);
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(0,0,0,.04);
            }
            .som-table tbody td { display: block; min-width: 0; padding: 0; border: 0; overflow-wrap: anywhere; }
            .som-table tbody tr:hover td { background: transparent; }
            .som-row-number { position: absolute; top: 16px; right: 16px; }
            .som-row-employee { grid-area: employee; padding-right: 36px !important; }
            .som-row-status { grid-area: status; }
            .som-row-overtime { grid-area: overtime; }
            .som-row-submitted { grid-area: submitted; }
            .som-row-duration { grid-area: duration; }
            .som-row-action { grid-area: action; align-self: end; }
            .som-row-overtime::before, .som-row-submitted::before, .som-row-duration::before {
                display: block; margin-bottom: 4px; color: var(--text-muted, #6B7280);
                font-size: 10px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
            }
            .som-row-overtime::before { content: 'Tgl Lembur'; }
            .som-row-submitted::before { content: 'Tgl Pengajuan'; }
            .som-row-duration::before { content: 'Durasi'; }
            .som-employee-name { white-space: normal; overflow: visible; overflow-wrap: anywhere; }
            .som-employee-role { overflow-wrap: anywhere; }
            .som-badge { max-width: 100%; white-space: normal; overflow-wrap: anywhere; }
            .som-badge svg { flex-shrink: 0; }
            .som-action-link { min-height: 44px; }
            .som-empty-row { display: block; }
            .som-empty { background: var(--white, #fff); border: 1px solid var(--border-light, #E5E7EB); border-radius: 16px; }
        }

    </style>
</x-app>
