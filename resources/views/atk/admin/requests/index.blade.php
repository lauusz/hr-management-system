<x-atk-app title="Request Masuk ATK">
    @php
        $currentStatus = (string) request('status', '');
        $currentSearch = (string) request('q', '');
        $statusTabs = [
            '' => 'Semua',
            'PENDING' => 'Pending',
            'APPROVED' => 'Approved',
            'PARTIAL' => 'Partial',
            'REJECTED' => 'Rejected',
        ];
    @endphp

    <style>
        .atk-req-admin-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .atk-req-admin-title-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-req-admin-count-pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            background: #F1F5F9;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
        }
        .atk-req-admin-Controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .atk-req-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 10px 12px;
            margin-bottom: 12px;
            background: #FFFFFF;
            border: 1px solid var(--atk-line);
            border-radius: 10px;
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
        .atk-req-search-form {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1 1 260px;
            max-width: 340px;
        }
        .atk-req-search-wrap {
            position: relative;
            flex: 1;
        }
        .atk-req-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .atk-req-search-input {
            width: 100%;
            height: 32px;
            padding: 0 28px 0 30px;
            border: 1px solid var(--atk-line);
            border-radius: 7px;
            font-size: 12.5px;
            color: var(--atk-text);
            background: #F8FAFC;
            outline: none;
            transition: border-color .14s ease, background .14s ease;
        }
        .atk-req-search-input:focus {
            background: #FFFFFF;
            border-color: var(--atk-primary);
            box-shadow: 0 0 0 3px rgba(109, 76, 246, .1);
        }
        .atk-req-search-clear {
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
        .atk-req-search-clear:hover {
            color: var(--atk-text);
            background: #E2E8F0;
        }
        .atk-req-search-clear.is-visible {
            display: inline-flex;
        }
        .atk-req-results-wrap {
            transition: opacity .15s ease;
        }
        .atk-req-results-wrap.is-loading {
            opacity: .55;
            pointer-events: none;
        }

        /* Desktop Table */
        .atk-req-date {
            font-weight: 600;
            color: var(--atk-text);
            white-space: nowrap;
        }
        .atk-req-no {
            display: block;
            margin-top: 2px;
            font-size: 11px;
            color: #94A3B8;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .atk-req-user {
            font-weight: 600;
            color: var(--atk-text);
        }
        .atk-req-pt {
            color: var(--atk-muted);
            font-size: 12.5px;
        }
        .atk-req-items-cell {
            max-width: 280px;
        }
        .atk-req-items-count {
            font-size: 12px;
            font-weight: 700;
            color: var(--atk-text);
        }
        .atk-req-items-preview {
            margin-top: 2px;
            font-size: 11.5px;
            color: var(--atk-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Mobile List */
        .atk-admin-request-mobile-list {
            display: grid;
            gap: 10px;
        }
        .atk-admin-request-card {
            padding: 13px 14px;
            border: 1px solid var(--atk-line);
            border-radius: 10px;
            background: #FFFFFF;
        }
        .atk-admin-request-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #F1F5F9;
        }
        .atk-admin-request-top time {
            color: var(--atk-muted);
            font-size: 11.5px;
            font-weight: 600;
        }
        .atk-admin-request-person {
            margin-top: 10px;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
        }
        .atk-admin-request-person strong {
            font-size: 13.5px;
            color: var(--atk-text);
        }
        .atk-admin-request-person span {
            color: var(--atk-muted);
            font-size: 11.5px;
            font-weight: 600;
        }
        .atk-admin-request-count {
            margin: 8px 0 10px;
            padding: 6px 10px;
            border-radius: 6px;
            background: #F8FAFC;
            border: 1px solid #F1F5F9;
            color: var(--atk-text);
            font-size: 12px;
            font-weight: 600;
        }
        .atk-admin-request-action {
            width: 100%;
            height: 34px;
        }
        .atk-admin-request-desktop-table {
            display: none;
        }

        @media (min-width: 640px) {
            .atk-admin-request-mobile-list {
                display: none;
            }
            .atk-admin-request-desktop-table {
                display: block;
            }
        }
    </style>

    <div class="atk-req-admin-bar">
        <div class="atk-req-admin-title-group">
            <h1 class="atk-title">Request Masuk</h1>
            <span class="atk-req-admin-count-pill" id="adminRequestsCount">{{ $requests->total() }}</span>
        </div>
        <a class="atk-btn atk-btn-primary" href="{{ route('v2.atk.admin.requests.manual.create') }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Input Pengambilan Manual
        </a>
    </div>

    <div class="atk-req-filter-bar">
        <div class="atk-status-pills" id="adminRequestStatusPills" role="tablist" aria-label="Filter Status Request">
            @foreach($statusTabs as $statusValue => $statusLabel)
                <a
                    href="{{ route('v2.atk.admin.requests.index', array_filter(['status' => $statusValue, 'q' => $currentSearch])) }}"
                    data-status="{{ $statusValue }}"
                    class="atk-status-pill {{ $currentStatus === $statusValue ? 'is-active' : '' }}"
                >
                    {{ $statusLabel }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('v2.atk.admin.requests.index') }}" class="atk-req-search-form" id="adminRequestSearchForm">
            <input type="hidden" name="status" id="adminRequestStatusInput" value="{{ $currentStatus }}">
            <div class="atk-req-search-wrap">
                <svg class="atk-req-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input
                    class="atk-req-search-input"
                    id="adminRequestSearchInput"
                    name="q"
                    value="{{ $currentSearch }}"
                    placeholder="Ketik no request, nama user, atau PT..."
                    autocomplete="off"
                >
                <button
                    type="button"
                    class="atk-req-search-clear {{ $currentSearch !== '' ? 'is-visible' : '' }}"
                    id="adminRequestSearchClear"
                    aria-label="Hapus pencarian"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </form>
    </div>

    <div class="atk-req-results-wrap" id="adminRequestsResults">
        <div class="atk-admin-request-mobile-list">
            @forelse($requests as $request)
                @php
                    $badgeTone = $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'error' : 'warning');
                    $previewText = $request->items->take(2)->map(fn ($it) => $it->item_name_snapshot.' ('.$it->qty.')')->implode(', ');
                    if ($request->items->count() > 2) {
                        $previewText .= ', +' . ($request->items->count() - 2) . ' lainnya';
                    }
                @endphp
                <article class="atk-admin-request-card">
                    <div class="atk-admin-request-top">
                        <time datetime="{{ $request->created_at?->toIso8601String() }}">{{ $request->created_at?->format('d/m/Y H:i') }}</time>
                        <span class="atk-badge atk-badge-{{ $badgeTone }}">{{ $request->status }}</span>
                    </div>
                    <div class="atk-admin-request-person">
                        <strong>{{ $request->user_name_snapshot }}</strong>
                        <span>{{ $request->pt_name_snapshot ?? '-' }}</span>
                    </div>
                    <div class="atk-admin-request-count">
                        {{ $request->items->count() }} Item
                        @if($previewText)
                            <span style="font-weight:400;color:var(--atk-muted)">· {{ $previewText }}</span>
                        @endif
                    </div>
                    <a class="atk-btn {{ $request->status === 'PENDING' ? 'atk-btn-primary' : 'atk-btn-secondary' }} atk-admin-request-action" href="{{ route('v2.atk.admin.requests.show', $request) }}">
                        {{ $request->status === 'PENDING' ? 'Periksa Pengajuan' : 'Lihat Pemeriksaan' }}
                    </a>
                </article>
            @empty
                <div class="atk-card atk-empty">Belum ada request yang sesuai filter.</div>
            @endforelse
        </div>

        <div class="atk-table-wrap atk-admin-request-desktop-table">
            <table class="atk-table">
                <thead>
                    <tr>
                        <th>Tgl Pengajuan</th>
                        <th>User</th>
                        <th>PT</th>
                        <th>Item</th>
                        <th>Status</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        @php
                            $badgeTone = $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'error' : 'warning');
                            $previewText = $request->items->take(3)->map(fn ($it) => $it->item_name_snapshot.' ('.$it->qty.')')->implode(', ');
                            if ($request->items->count() > 3) {
                                $previewText .= ', +' . ($request->items->count() - 3) . ' lainnya';
                            }
                        @endphp
                        <tr>
                            <td>
                                <span class="atk-req-date">{{ $request->created_at?->format('d/m/Y H:i') }}</span>
                                <span class="atk-req-no">{{ $request->request_number }}</span>
                            </td>
                            <td>
                                <span class="atk-req-user">{{ $request->user_name_snapshot }}</span>
                            </td>
                            <td>
                                <span class="atk-req-pt">{{ $request->pt_name_snapshot ?? '-' }}</span>
                            </td>
                            <td class="atk-req-items-cell">
                                <div class="atk-req-items-count">{{ $request->items->count() }} Item</div>
                                @if($previewText)
                                    <div class="atk-req-items-preview" title="{{ $previewText }}">{{ $previewText }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="atk-badge atk-badge-{{ $badgeTone }}">{{ $request->status }}</span>
                            </td>
                            <td style="text-align:right;white-space:nowrap">
                                <a class="atk-btn {{ $request->status === 'PENDING' ? 'atk-btn-primary' : 'atk-btn-secondary' }} atk-btn-sm" href="{{ route('v2.atk.admin.requests.show', $request) }}">
                                    {{ $request->status === 'PENDING' ? 'Periksa Pengajuan' : 'Lihat Pemeriksaan' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="atk-empty">Belum ada request yang sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination :items="$requests" preserve-query />
    </div>

    <script>
        (function () {
            var form = document.getElementById('adminRequestSearchForm');
            var searchInput = document.getElementById('adminRequestSearchInput');
            var clearBtn = document.getElementById('adminRequestSearchClear');
            var statusInput = document.getElementById('adminRequestStatusInput');
            var pillsWrap = document.getElementById('adminRequestStatusPills');
            var resultsWrap = document.getElementById('adminRequestsResults');
            var countEl = document.getElementById('adminRequestsCount');

            if (!form || !searchInput || !resultsWrap) return;

            var debounceTimer = null;
            var activeController = null;
            var baseUrl = form.getAttribute('action');

            function buildUrl(q, status) {
                var params = new URLSearchParams();
                if (status) params.set('status', status);
                if (q) params.set('q', q);
                var qs = params.toString();
                return qs ? baseUrl + '?' + qs : baseUrl;
            }

            function syncPills(q, activeStatus) {
                if (!pillsWrap) return;
                pillsWrap.querySelectorAll('.atk-status-pill').forEach(function (pill) {
                    var st = pill.getAttribute('data-status') || '';
                    pill.classList.toggle('is-active', st === activeStatus);
                    pill.setAttribute('href', buildUrl(q, st));
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
                        var nextResults = doc.getElementById('adminRequestsResults');
                        var nextCount = doc.getElementById('adminRequestsCount');
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
                if (clearBtn) {
                    clearBtn.classList.toggle('is-visible', q.length > 0);
                }
                syncPills(q, st);
                var url = buildUrl(q, st);
                fetchResults(url, true);
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
        })();
    </script>
</x-atk-app>
