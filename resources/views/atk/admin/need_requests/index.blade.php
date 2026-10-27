<x-atk-app title="Pengajuan Barang ATK">
    @php
        $currentStatus = (string) request('status', '');
        $currentModule = (string) request('module', '');
        $currentSearch = (string) request('q', '');
        $statusTabs = [
            '' => 'Semua',
            'PENDING' => 'Pending',
            'DONE' => 'Done',
            'REJECTED' => 'Rejected',
        ];
    @endphp

    <style>
        .atk-need-admin-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .atk-need-admin-title-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-need-admin-count-pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            background: #F1F5F9;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
        }
        .atk-need-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 10px 12px;
            margin-bottom: 12px;
            background: #FFFFFF;
            border: 1px solid var(--atk-border);
            border-radius: 10px;
        }
        .atk-need-filter-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .atk-status-pills {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .atk-status-pill {
            display: inline-flex;
            align-items: center;
            height: 30px;
            padding: 0 11px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--atk-muted);
            text-decoration: none;
            border: 1px solid transparent;
            transition: all .14s ease;
        }
        .atk-status-pill:hover {
            color: var(--atk-text);
            background: #F8FAFC;
        }
        .atk-status-pill.is-active {
            background: #F1F5F9;
            color: var(--atk-text);
            border-color: #CBD5E1;
            font-weight: 700;
        }
        .atk-module-select {
            height: 30px;
            min-height: 30px;
            padding: 0 26px 0 10px;
            border-radius: 6px;
            border: 1px solid var(--atk-border);
            background-color: #F8FAFC;
            font-size: 12px;
            font-weight: 600;
            color: var(--atk-text-secondary);
            width: auto;
        }
        .atk-need-search-form {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1 1 250px;
            max-width: 340px;
        }
        .atk-need-search-wrap {
            position: relative;
            flex: 1;
        }
        .atk-need-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .atk-need-search-input {
            width: 100%;
            height: 32px;
            padding: 0 28px 0 30px;
            border: 1px solid var(--atk-border);
            border-radius: 7px;
            font-size: 12.5px;
            color: var(--atk-text);
            background: #F8FAFC;
            outline: none;
            transition: border-color .14s ease, background .14s ease;
        }
        .atk-need-search-input:focus {
            background: #FFFFFF;
            border-color: var(--atk-primary);
            box-shadow: 0 0 0 3px rgba(109, 76, 246, .1);
        }
        .atk-need-search-clear {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 0;
            border-radius: 4px;
            background: transparent;
            color: #94A3B8;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
        }
        .atk-need-search-clear:hover {
            color: var(--atk-text);
            background: #E2E8F0;
        }
        .atk-need-search-clear.is-visible {
            display: inline-flex;
        }
        .atk-need-results-wrap {
            transition: opacity .15s ease;
        }
        .atk-need-results-wrap.is-loading {
            opacity: .55;
            pointer-events: none;
        }

        /* Table Cells */
        .atk-admin-need-user-name {
            display: block;
            font-weight: 600;
            color: var(--atk-text);
            line-height: 1.35;
        }
        .atk-admin-need-date {
            display: block;
            margin-top: 2px;
            font-size: 11px;
            color: #94A3B8;
        }
        .atk-admin-need-pt {
            color: var(--atk-muted);
            font-size: 12.5px;
            font-weight: 500;
        }
        .atk-admin-need-item-head {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .atk-admin-need-item-head strong {
            font-size: 13px;
            font-weight: 600;
            color: var(--atk-text);
        }
        .atk-admin-need-reason {
            margin-top: 3px;
            font-size: 12px;
            color: var(--atk-muted);
            line-height: 1.4;
        }
        .atk-admin-need-qty {
            font-weight: 600;
            color: var(--atk-text);
            white-space: nowrap;
        }
        .atk-admin-need-note {
            font-size: 12px;
            color: var(--atk-muted);
        }

        /* Actions */
        .atk-admin-need-action-list {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .atk-admin-need-action-list .atk-btn {
            min-height: 30px;
            height: 30px;
            padding: 0 10px;
            font-size: 12px;
            border-radius: 7px;
            white-space: nowrap;
        }
        .atk-admin-need-reject {
            position: relative;
        }
        .atk-admin-need-reject > summary {
            cursor: pointer;
            list-style: none;
        }
        .atk-admin-need-reject > summary::-webkit-details-marker {
            display: none;
        }
        .atk-admin-need-reject-box {
            margin-top: 8px;
            padding: 10px;
            border: 1px solid var(--atk-border);
            border-radius: 8px;
            background: #F8FAFC;
            display: grid;
            gap: 8px;
            min-width: 230px;
        }
        .atk-admin-need-reject-box textarea {
            min-height: 54px;
            padding: 6px 9px;
            font-size: 12px;
            border-radius: 6px;
        }

        @media (max-width: 639px) {
            .atk-admin-need-mobile-table {
                overflow: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }
            .atk-admin-need-table {
                display: block;
                min-width: 0;
            }
            .atk-admin-need-table thead {
                display: none;
            }
            .atk-admin-need-table tbody {
                display: grid;
                gap: 10px;
            }
            .atk-admin-need-card {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "item status"
                    "user user"
                    "pt pt"
                    "qty qty"
                    "actions actions";
                gap: 0 10px;
                padding: 13px 14px;
                border: 1px solid var(--atk-border);
                border-radius: 10px;
                background: var(--atk-surface);
            }
            .atk-admin-need-card td {
                display: grid;
                grid-template-columns: minmax(82px, .65fr) minmax(0, 1fr);
                gap: 10px;
                padding: 6px 0;
                border: 0;
                font-size: 12px;
            }
            .atk-admin-need-card td::before {
                content: attr(data-label);
                color: var(--atk-muted);
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            .atk-admin-need-item {
                grid-area: item;
                display: block !important;
                padding-top: 0 !important;
            }
            .atk-admin-need-item::before {
                display: none;
            }
            .atk-admin-need-status {
                grid-area: status;
                display: block !important;
                padding-top: 0 !important;
            }
            .atk-admin-need-status::before {
                display: none;
            }
            .atk-admin-need-user { grid-area: user; }
            .atk-admin-need-pt { grid-area: pt; }
            .atk-admin-need-qty { grid-area: qty; }
            .atk-admin-need-actions {
                grid-area: actions;
                display: block !important;
                margin-top: 4px;
                padding-bottom: 0 !important;
                border-top: 1px solid var(--atk-border-soft) !important;
            }
            .atk-admin-need-actions::before {
                display: block;
                margin: 8px 0 6px;
            }
            .atk-admin-need-action-list {
                display: flex;
                width: 100%;
            }
            .atk-admin-need-action-list > form,
            .atk-admin-need-reject {
                flex: 1;
            }
            .atk-admin-need-action-list .atk-btn {
                width: 100%;
                height: 34px;
            }
            .atk-admin-need-reject-box {
                min-width: 0;
            }
            .atk-admin-need-empty {
                display: block;
                padding: 18px;
                border: 1px solid var(--atk-border);
                border-radius: 10px;
                background: var(--atk-surface);
                text-align: center;
            }
            .atk-admin-need-empty td {
                display: block;
                padding: 0;
                border: 0;
            }
        }
    </style>

    <div class="atk-need-admin-bar">
        <div class="atk-need-admin-title-group">
            <h1 class="atk-title">Pengajuan Barang</h1>
            <span class="atk-need-admin-count-pill" id="adminNeedRequestsCount">{{ $needRequests->total() }}</span>
        </div>
    </div>

    <div class="atk-need-filter-bar">
        <div class="atk-need-filter-left">
            <div class="atk-status-pills" id="adminNeedStatusPills" role="tablist" aria-label="Filter Status">
                @foreach($statusTabs as $statusValue => $statusLabel)
                    <a
                        href="{{ route('v2.atk.admin.need-requests.index', array_filter(['status' => $statusValue, 'module' => $currentModule, 'q' => $currentSearch])) }}"
                        data-status="{{ $statusValue }}"
                        class="atk-status-pill {{ $currentStatus === $statusValue ? 'is-active' : '' }}"
                    >
                        {{ $statusLabel }}
                    </a>
                @endforeach
            </div>

            <select class="atk-select atk-module-select" id="adminNeedModuleSelect" aria-label="Filter Modul">
                <option value="" @selected($currentModule === '')>Semua Modul</option>
                <option value="ATK" @selected($currentModule === 'ATK')>ATK</option>
                <option value="OPS" @selected($currentModule === 'OPS')>OPS</option>
                <option value="ATK_MKS" @selected($currentModule === 'ATK_MKS')>ATK MKS</option>
            </select>
        </div>

        <form method="GET" action="{{ route('v2.atk.admin.need-requests.index') }}" class="atk-need-search-form" id="adminNeedSearchForm">
            <input type="hidden" name="status" id="adminNeedStatusInput" value="{{ $currentStatus }}">
            <input type="hidden" name="module" id="adminNeedModuleInput" value="{{ $currentModule }}">
            <div class="atk-need-search-wrap">
                <svg class="atk-need-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input
                    class="atk-need-search-input"
                    id="adminNeedSearchInput"
                    name="q"
                    value="{{ $currentSearch }}"
                    placeholder="Ketik nama barang, user, atau PT..."
                    autocomplete="off"
                >
                <button
                    type="button"
                    class="atk-need-search-clear {{ $currentSearch !== '' ? 'is-visible' : '' }}"
                    id="adminNeedSearchClear"
                    aria-label="Hapus pencarian"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </form>
    </div>

    <div class="atk-need-results-wrap" id="adminNeedResults">
        <div class="atk-table-wrap atk-admin-need-mobile-table">
            <table class="atk-table atk-admin-need-table">
                <thead>
                    <tr>
                        <th>Pemohon</th>
                        <th>PT</th>
                        <th>Barang &amp; Alasan</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th style="text-align:right">Aksi / Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($needRequests as $needRequest)
                        <tr class="atk-admin-need-card">
                            <td class="atk-admin-need-user" data-label="Pemohon">
                                <span class="atk-admin-need-user-name">{{ $needRequest->user_name_snapshot }}</span>
                                @if($needRequest->created_at)
                                    <span class="atk-admin-need-date">{{ $needRequest->created_at->format('d/m/Y H:i') }}</span>
                                @endif
                            </td>
                            <td class="atk-admin-need-pt" data-label="PT">{{ $needRequest->pt_name_snapshot ?? '-' }}</td>
                            <td class="atk-admin-need-item" data-label="Barang">
                                <div class="atk-admin-need-item-head">
                                    <strong>{{ $needRequest->requested_item_name }}</strong>
                                    <span class="atk-badge atk-badge-{{ $needRequest->module === \App\Models\AtkNeedRequest::MODULE_OPS ? 'success' : 'warning' }}">{{ str_replace('_', ' ', $needRequest->module) }}</span>
                                </div>
                                <div class="atk-admin-need-reason">{{ $needRequest->reason }}</div>
                            </td>
                            <td class="atk-admin-need-qty" data-label="Jumlah">{{ $needRequest->qty }} {{ $needRequest->unit_name }}</td>
                            <td class="atk-admin-need-status" data-label="Status">
                                <span class="atk-badge atk-badge-{{ $needRequest->status === 'DONE' ? 'success' : ($needRequest->status === 'REJECTED' ? 'error' : 'warning') }}">
                                    {{ $needRequest->status }}
                                </span>
                            </td>
                            <td class="atk-admin-need-actions" data-label="{{ $needRequest->status === 'PENDING' ? 'Aksi' : 'Catatan Admin' }}" style="text-align:right">
                                @if($needRequest->status === 'PENDING')
                                    <div class="atk-admin-need-action-list">
                                        <form method="POST" action="{{ route('v2.atk.admin.need-requests.process', $needRequest) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="DONE">
                                            <button class="atk-btn atk-btn-primary" type="submit">Tandai Selesai</button>
                                        </form>

                                        <details class="atk-admin-need-reject">
                                            <summary class="atk-btn atk-btn-muted">Tolak</summary>
                                            <form class="atk-admin-need-reject-box" method="POST" action="{{ route('v2.atk.admin.need-requests.process', $needRequest) }}" style="text-align:left">
                                                @csrf
                                                <input type="hidden" name="status" value="REJECTED">
                                                <label class="atk-label" for="reject-note-{{ $needRequest->id }}" style="margin-bottom:2px">Catatan penolakan</label>
                                                <textarea class="atk-input" id="reject-note-{{ $needRequest->id }}" name="admin_note" rows="2" placeholder="Alasan ditolak..."></textarea>
                                                <button class="atk-btn atk-btn-danger" type="submit" style="width:100%">Konfirmasi Tolak</button>
                                            </form>
                                        </details>
                                    </div>
                                @else
                                    <span class="atk-admin-need-note">{{ $needRequest->admin_note ?? '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="atk-admin-need-empty"><td colspan="6" class="atk-empty">Belum ada pengajuan barang yang sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination :items="$needRequests" preserve-query />
    </div>

    <script>
        (function () {
            var form = document.getElementById('adminNeedSearchForm');
            var searchInput = document.getElementById('adminNeedSearchInput');
            var clearBtn = document.getElementById('adminNeedSearchClear');
            var statusInput = document.getElementById('adminNeedStatusInput');
            var moduleInput = document.getElementById('adminNeedModuleInput');
            var moduleSelect = document.getElementById('adminNeedModuleSelect');
            var pillsWrap = document.getElementById('adminNeedStatusPills');
            var resultsWrap = document.getElementById('adminNeedResults');
            var countEl = document.getElementById('adminNeedRequestsCount');

            if (!form || !searchInput || !resultsWrap) return;

            var debounceTimer = null;
            var activeController = null;
            var baseUrl = form.getAttribute('action');

            function buildUrl(q, status, mod) {
                var params = new URLSearchParams();
                if (status) params.set('status', status);
                if (mod) params.set('module', mod);
                if (q) params.set('q', q);
                var qs = params.toString();
                return qs ? baseUrl + '?' + qs : baseUrl;
            }

            function syncPills(q, activeStatus, mod) {
                if (!pillsWrap) return;
                pillsWrap.querySelectorAll('.atk-status-pill').forEach(function (pill) {
                    var st = pill.getAttribute('data-status') || '';
                    pill.classList.toggle('is-active', st === activeStatus);
                    pill.setAttribute('href', buildUrl(q, st, mod));
                });
            }

            function fetchResults(url, pushHistory) {
                if (activeController) {
                    activeController.abort();
                }
                activeController = new AbortController();
                resultsWrap.classList.add('is-loading');

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeController.signal
                })
                    .then(function (res) { return res.text(); })
                    .then(function (html) {
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        var nextResults = doc.getElementById('adminNeedResults');
                        var nextCount = doc.getElementById('adminNeedRequestsCount');
                        if (nextResults) {
                            resultsWrap.innerHTML = nextResults.innerHTML;
                        }
                        if (nextCount && countEl) {
                            countEl.textContent = nextCount.textContent;
                        }
                        if (pushHistory && window.history && window.history.replaceState) {
                            window.history.replaceState({}, '', url);
                        }
                    })
                    .catch(function (err) {
                        if (err && err.name === 'AbortError') return;
                    })
                    .finally(function () {
                        resultsWrap.classList.remove('is-loading');
                    });
            }

            function triggerSearch() {
                var q = (searchInput.value || '').trim();
                var st = statusInput ? (statusInput.value || '') : '';
                var mod = moduleInput ? (moduleInput.value || '') : '';
                if (clearBtn) {
                    clearBtn.classList.toggle('is-visible', q.length > 0);
                }
                syncPills(q, st, mod);
                fetchResults(buildUrl(q, st, mod), true);
            }

            searchInput.addEventListener('input', function () {
                var q = (searchInput.value || '').trim();
                if (clearBtn) {
                    clearBtn.classList.toggle('is-visible', q.length > 0);
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(triggerSearch, 240);
            });

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearTimeout(debounceTimer);
                triggerSearch();
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    searchInput.focus();
                    clearTimeout(debounceTimer);
                    triggerSearch();
                });
            }

            if (pillsWrap) {
                pillsWrap.addEventListener('click', function (e) {
                    var pill = e.target.closest('.atk-status-pill');
                    if (!pill) return;
                    e.preventDefault();
                    var st = pill.getAttribute('data-status') || '';
                    if (statusInput) statusInput.value = st;
                    clearTimeout(debounceTimer);
                    triggerSearch();
                });
            }

            if (moduleSelect) {
                moduleSelect.addEventListener('change', function () {
                    if (moduleInput) moduleInput.value = moduleSelect.value || '';
                    clearTimeout(debounceTimer);
                    triggerSearch();
                });
            }
        })();
    </script>
</x-atk-app>
