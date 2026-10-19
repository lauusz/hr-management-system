<x-app title="Rekap Izin Bawahan">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Rekap Izin Bawahan</h1>
                <p class="section-subtitle">Riwayat pengajuan izin dan cuti yang dapat Anda lihat</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="lm-alert">{{ session('success') }}</div>
    @endif

    @php
        $hasAdvancedFilter = (bool) (($typeFilter ?? null) || ($status ?? null) || ($submittedRange ?? null));
        $activeFilterCount = collect([$typeFilter, $status, $submittedRange])->filter()->count();
        $statusLabels = [
            \App\Models\LeaveRequest::PENDING_SUPERVISOR => 'Perlu Diketahui',
            \App\Models\LeaveRequest::PENDING_HR => 'Atasan Mengetahui',
            \App\Models\LeaveRequest::STATUS_APPROVED => 'Disetujui',
            \App\Models\LeaveRequest::STATUS_REJECTED => 'Ditolak',
            \App\Models\LeaveRequest::STATUS_CANCELLED => 'Dibatalkan',
            'CANCEL_REQ' => 'Pengajuan Batal',
        ];
    @endphp

    <div class="lm-stats" data-supervisor-leave-stats>
        <div class="lm-stat"><div class="lm-stat-value">{{ $items->total() }}</div><div class="lm-stat-label">Total Data</div></div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat"><div class="lm-stat-value lm-stat-value--success">{{ $statusCounts->get(\App\Models\LeaveRequest::STATUS_APPROVED, 0) }}</div><div class="lm-stat-label">Disetujui</div></div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat"><div class="lm-stat-value lm-stat-value--warning">{{ $statusCounts->get(\App\Models\LeaveRequest::PENDING_SUPERVISOR, 0) + $statusCounts->get(\App\Models\LeaveRequest::PENDING_HR, 0) }}</div><div class="lm-stat-label">Menunggu</div></div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat"><div class="lm-stat-value lm-stat-value--error">{{ $statusCounts->get(\App\Models\LeaveRequest::STATUS_REJECTED, 0) }}</div><div class="lm-stat-label">Ditolak</div></div>
    </div>

    <div class="lm-filter-card">
        <form method="GET" action="{{ route('supervisor.leave.master') }}" id="filterForm" data-has-filters="{{ $hasAdvancedFilter ? '1' : '0' }}">
            <div class="lm-search-row">
                <div class="lm-search-input-wrap">
                    <svg class="lm-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" id="q" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama karyawan..." autocomplete="off" class="lm-search-input" aria-label="Cari nama karyawan">
                </div>
                <a href="{{ route('supervisor.leave.master') }}" id="searchReset" class="lm-btn-reset" @if(!($q ?? null) && !$hasAdvancedFilter) hidden @endif>
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
                        <label class="lm-filter-label" for="submitted_range">Tanggal Pengajuan</label>
                        <input type="text" id="submitted_range" name="submitted_range" value="{{ $submittedRange ?? '' }}" placeholder="Pilih rentang tanggal" class="lm-filter-input" autocomplete="off">
                    </div>
                    <div class="lm-filter-group">
                        <label class="lm-filter-label" for="type">Jenis</label>
                        <select name="type" id="type" class="lm-filter-input">
                            <option value="">Semua Jenis</option>
                            @foreach($typeOptions as $case)
                                <option value="{{ $case->value }}" @selected(($typeFilter ?? '') === $case->value)>{{ $case->value === \App\Enums\LeaveType::CUTI_KHUSUS->value ? 'Cuti Khusus' : $case->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lm-filter-group">
                        <label class="lm-filter-label" for="status">Status</label>
                        <select name="status" id="status" class="lm-filter-input">
                            <option value="">Semua Status</option>
                            @foreach($statusOptions as $opt)
                                <option value="{{ $opt }}" @selected(($status ?? '') === $opt)>{{ $statusLabels[$opt] ?? $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lm-filter-actions"><button type="submit" class="lm-btn-apply">Terapkan Filter</button></div>
                </div>
            </div>
        </form>
    </div>

    <p id="searchError" class="lm-search-error" role="alert" hidden>Pencarian gagal. Silakan coba lagi atau tekan Enter untuk memuat ulang.</p>

    <div data-supervisor-leave-results aria-live="polite">
        <div class="lm-table-card">
            @if($items->isEmpty())
                <div class="lm-empty">
                    <div class="lm-empty-icon"><svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
                    <h3 class="lm-empty-title">Belum Ada Data</h3>
                    <p class="lm-empty-desc">Belum ada data pengajuan izin/cuti yang sesuai dengan filter yang dipilih.</p>
                </div>
            @else
                <div class="lm-table-wrap">
                    <table class="lm-data-table">
                        <thead><tr><th>#</th><th>Karyawan</th><th>Tgl Pengajuan</th><th>Periode Izin</th><th>Jenis</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($items as $i => $row)
                                @php
                                    $st = $row->status;
                                    $badgeClass = match ($st) {
                                        \App\Models\LeaveRequest::STATUS_APPROVED => 'lm-badge--success',
                                        \App\Models\LeaveRequest::STATUS_REJECTED => 'lm-badge--error',
                                        \App\Models\LeaveRequest::PENDING_SUPERVISOR => 'lm-badge--warning',
                                        \App\Models\LeaveRequest::PENDING_HR => 'lm-badge--teal',
                                        default => 'lm-badge--neutral',
                                    };
                                    $typeValue = $row->type instanceof \App\Enums\LeaveType ? $row->type->value : (string) $row->type;
                                    $typeClass = match (true) {
                                        in_array($typeValue, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value]) => 'lm-type--cuti',
                                        $typeValue === \App\Enums\LeaveType::SAKIT->value => 'lm-type--sakit',
                                        in_array($typeValue, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value]) => 'lm-type--izin',
                                        $typeValue === \App\Enums\LeaveType::DINAS_LUAR->value => 'lm-type--dinas',
                                        default => 'lm-type--default',
                                    };
                                    $typeLabel = \Illuminate\Support\Str::contains($row->type_label ?? $typeValue, 'Cuti Khusus') ? 'Cuti Khusus' : ($row->type_label ?? $typeValue);
                                @endphp
                                <tr class="lm-clickable-row" onclick="window.location.href='{{ route('approval.show', $row) }}'">
                                    <td class="lm-cell-muted lm-row-number">{{ $items->firstItem() + $i }}</td>
                                    <td class="lm-row-employee">
                                        <div class="lm-employee">
                                            <div class="lm-employee-avatar">{{ substr($row->user->name, 0, 1) }}</div>
                                            <div>
                                                <a class="lm-employee-name" href="{{ route('approval.show', $row) }}">{{ $row->user->name }}</a>
                                                <div class="lm-employee-detail">{{ $row->user->position->name ?? '-' }} - {{ $row->user->division->name ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="lm-cell-muted lm-row-submitted">{{ $row->created_at->translatedFormat('D, d/m/Y') }}<br><span class="lm-cell-xs">{{ $row->created_at->format('H:i') }}</span></td>
                                    <td class="lm-row-period"><span class="lm-date-main">{{ $row->start_date->translatedFormat('D, d/m/Y') }}</span>@if($row->end_date && $row->end_date->ne($row->start_date))<span class="lm-date-range">s/d {{ $row->end_date->translatedFormat('D, d/m/Y') }}</span>@endif</td>
                                    <td class="lm-row-type">
                                        <span class="lm-type {{ $typeClass }}">
                                            @if($typeValue === \App\Enums\LeaveType::CUTI->value)
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            @elseif($typeValue === \App\Enums\LeaveType::CUTI_KHUSUS->value)
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                            @elseif($typeValue === \App\Enums\LeaveType::SAKIT->value)
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            @else
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                            {{ $typeLabel }}
                                        </span>
                                    </td>
                                    <td class="lm-row-status">
                                        <span class="lm-badge {{ $badgeClass }}">
                                            @if($st === \App\Models\LeaveRequest::STATUS_APPROVED)
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            @elseif($st === \App\Models\LeaveRequest::STATUS_REJECTED)
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            @elseif(in_array($st, [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR]))
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                            {{ $statusLabels[$st] ?? ($row->status_label ?? $st) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($items->hasPages())
            <div class="lm-pagination"><x-pagination :items="$items" :preserve-query="true" /></div>
        @endif
    </div>

    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
    <script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/3.7.1/jquery.min.js') }}"></script>
    <script>
        flatpickr("#submitted_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            allowInput: true,
            locale: { rangeSeparator: " sampai " }
        });
    </script>
    <script data-supervisor-leave-search>
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
                            const selectors = ['[data-supervisor-leave-stats]', '[data-supervisor-leave-results]'];
                            const fragments = selectors.map(function (selector) {
                                return [document.querySelector(selector), page.querySelector(selector)];
                            });
                            if (fragments.some(function (fragment) { return !fragment[0] || !fragment[1]; })) {
                                error.prop('hidden', false);
                                return;
                            }
                            fragments.forEach(function (fragment) { fragment[0].innerHTML = fragment[1].innerHTML; });
                            const filterCount = ['submitted_range', 'type', 'status'].filter(function (name) {
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
        .section-header-inline { display: flex; align-items: center; gap: 10px; }
        .section-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .section-icon svg { width: 16px; height: 16px; }
        .section-title { margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.01em; line-height: 1.25; }
        .section-subtitle { margin: 0; font-size: 0.8125rem; color: var(--text-muted); font-weight: 500; line-height: 1.35; }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }
        .lm-alert { padding: 12px 16px; margin-bottom: 16px; border-radius: 12px; color: #15803d; background: #f0fdf4; }
        .lm-stats { display: grid; grid-template-columns: repeat(4, 1fr); margin-bottom: 16px; padding: 14px 8px; background: var(--white); border-radius: 16px; border: 1px solid var(--border-light); }
        .lm-stat { text-align: center; }
        .lm-stat-value { color: var(--text-primary); font-size: 1.125rem; font-weight: 700; }
        .lm-stat-value--success { color: var(--success); } .lm-stat-value--warning { color: var(--warning); } .lm-stat-value--error { color: var(--error); }
        .lm-stat-label { font-size: .625rem; color: var(--text-muted); text-transform: uppercase; }
        .lm-stat-divider { display: none; }
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
        .lm-table-card { background: var(--white); border: 1px solid var(--border-light); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
        .lm-table-wrap { overflow-x: auto; }
        .lm-data-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .lm-data-table th { text-align: left; padding: 14px 16px; font-size: .6875rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; background: var(--gray-50); border-bottom: 1px solid var(--border-light); }
        .lm-data-table td { padding: 14px 16px; border-bottom: 1px solid var(--border-light); vertical-align: top; font-size: .8125rem; }
        .lm-data-table tr:last-child td { border-bottom: none; } .lm-data-table tbody tr:hover td { background: var(--gray-50); } .lm-clickable-row { cursor: pointer; }
        .lm-employee { display: flex; align-items: center; gap: 10px; }
        .lm-employee-avatar { width: 36px; height: 36px; border-radius: 10px; background: rgba(20,93,160,.08); color: var(--primary); font-size: .8rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .lm-employee-name { font-size: .8125rem; font-weight: 600; color: var(--text-primary); text-decoration: none; } .lm-employee-name:hover { color: var(--primary); }
        .lm-employee-detail, .lm-cell-xs, .lm-date-range { font-size: .75rem; color: var(--text-muted); }
        .lm-cell-muted { color: var(--text-muted); } .lm-date-main { font-weight: 500; } .lm-date-range { display: block; margin-top: 2px; }
        .lm-type { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 8px; font-size: .75rem; font-weight: 600; }
        .lm-type--default { background: var(--gray-100); color: var(--text-secondary); } .lm-type--cuti { background: rgba(59,130,246,.1); color: var(--info); }
        .lm-type--sakit { background: rgba(245,158,11,.1); color: #b45309; } .lm-type--izin { background: rgba(20,93,160,.08); color: var(--primary); } .lm-type--dinas { background: rgba(147,51,234,.1); color: #7c3aed; }
        .lm-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 9999px; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap; }
        .lm-badge--success { background: rgba(34,197,94,.1); color: #15803d; } .lm-badge--error { background: rgba(239,68,68,.1); color: #b91c1c; }
        .lm-badge--warning { background: rgba(245,158,11,.1); color: #a16207; } .lm-badge--teal { background: rgba(20,184,166,.1); color: #0f766e; border: 1px solid rgba(20,184,166,.2); } .lm-badge--neutral { background: var(--gray-100); color: var(--text-muted); }
        .lm-empty { text-align: center; padding: 48px 24px; } .lm-empty-icon { width: 72px; height: 72px; margin: 0 auto 16px; background: var(--gray-50); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-light); }
        .lm-empty-title { font-size: .9375rem; font-weight: 600; color: var(--text-secondary); margin: 0 0 6px; } .lm-empty-desc { font-size: .8125rem; color: var(--text-muted); margin: 0 auto; max-width: 280px; line-height: 1.5; }
        .lm-pagination { margin-top: 16px; }
        @media (min-width: 480px) {
            .lm-stats { display: flex; align-items: center; padding: 14px 20px; } .lm-stat { flex: 1; } .lm-stat-value { font-size: 1.375rem; } .lm-stat-label { font-size: .6875rem; letter-spacing: .04em; }
            .lm-stat-divider { display: block; width: 1px; height: 36px; background: var(--border); flex-shrink: 0; }
            .lm-search-row { flex-direction: row; align-items: center; flex-wrap: wrap; padding: 16px 20px; } .lm-search-input-wrap { min-width: 260px; }
            .lm-filter-panel { padding: 16px 20px; } .lm-filter-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px 16px; align-items: end; } .lm-filter-actions { grid-column: 1 / -1; }
        }
        @media (min-width: 768px) { .lm-filter-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 1024px) { .lm-data-table th, .lm-data-table td { padding: 16px 20px; } }
        @media (max-width: 1024px) {
            .lm-search-input, .lm-filter-input { font-size: 16px; }
            .lm-btn-reset, .lm-btn-toggle-filter, .lm-btn-apply { min-height: 44px; }
            .lm-table-card { background: transparent; border: 0; border-radius: 0; box-shadow: none; overflow: visible; }
            .lm-empty { background: var(--white); border: 1px solid var(--border-light); border-radius: 16px; }
            .lm-table-wrap { overflow: visible; }
            .lm-data-table { display: block; min-width: 0; }
            .lm-data-table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
            .lm-data-table tbody { display: flex; flex-direction: column; gap: 10px; }
            .lm-data-table tbody tr {
                display: grid;
                position: relative;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                grid-template-areas: "employee employee" "type status" "period submitted";
                gap: 12px;
                padding: 16px;
                background: var(--white);
                border: 1px solid var(--border-light);
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(0,0,0,.04);
            }
            .lm-data-table tbody td { display: block; min-width: 0; padding: 0; border: 0; }
            .lm-data-table tbody tr:hover td { background: transparent; }
            .lm-row-number { position: absolute; top: 16px; right: 16px; font-size: .75rem; }
            .lm-row-number::before { content: '#'; }
            .lm-row-employee { grid-area: employee; }
            .lm-data-table tbody td.lm-row-employee { padding-right: 36px; }
            .lm-row-type { grid-area: type; }
            .lm-row-status { grid-area: status; text-align: right; }
            .lm-row-period { grid-area: period; }
            .lm-row-submitted { grid-area: submitted; }
            .lm-row-period::before, .lm-row-submitted::before { display: block; margin-bottom: 4px; color: var(--text-muted); font-size: .625rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
            .lm-row-period::before { content: 'Periode Izin'; }
            .lm-row-submitted::before { content: 'Tgl Pengajuan'; }
            .lm-employee > div:last-child { min-width: 0; overflow-wrap: anywhere; }
            .lm-type, .lm-badge { max-width: 100%; white-space: normal; overflow-wrap: anywhere; }
        }
    </style>
</x-app>
