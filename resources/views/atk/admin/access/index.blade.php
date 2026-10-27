<x-atk-app title="Akses Admin ATK">
    @php
        $currentRole = (string) request('role', '');
        $currentSearch = (string) request('q', '');
        $roleTabs = [
            '' => 'Semua User',
            'admin' => 'Admin ATK',
            'user' => 'User Biasa',
        ];
    @endphp

    <style>
        .atk-access-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .atk-access-title-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-access-count-pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            background: #F1F5F9;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
        }

        /* Single-Row Filter & Search Bar */
        .atk-access-filter-bar {
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
        .atk-role-pills {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .atk-role-pill {
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
        .atk-role-pill:hover {
            color: var(--atk-text);
            background: #F8FAFC;
        }
        .atk-role-pill.is-active {
            background: #F1F5F9;
            color: var(--atk-text);
            border-color: #CBD5E1;
            font-weight: 700;
        }

        .atk-access-search-wrap {
            position: relative;
            flex: 1 1 260px;
            max-width: 340px;
        }
        .atk-access-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .atk-access-search-input {
            width: 100%;
            height: 32px;
            min-height: 32px;
            padding: 0 28px 0 30px;
            border: 1px solid var(--atk-border);
            border-radius: 7px;
            font-size: 12.5px;
            color: var(--atk-text);
            background: #F8FAFC;
            outline: none;
            transition: border-color .14s ease, background .14s ease;
        }
        .atk-access-search-input:focus {
            background: #FFFFFF;
            border-color: var(--atk-primary);
            box-shadow: 0 0 0 3px rgba(109, 40, 217, .12);
        }
        .atk-access-search-clear {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border-radius: 4px;
            color: #94A3B8;
            display: none;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .atk-access-search-clear:hover {
            color: var(--atk-text);
            background: #E2E8F0;
        }
        .atk-access-search-clear.is-visible {
            display: inline-flex;
        }

        /* Results & Table */
        #atkAccessResults {
            transition: opacity .15s ease;
        }
        #atkAccessResults.is-loading {
            opacity: .55;
            pointer-events: none;
        }
        .atk-access-table td {
            vertical-align: middle;
            padding: 10px 14px;
        }
        .atk-access-row-admin {
            background: #FAF8FF;
        }
        .atk-access-name {
            font-weight: 600;
            color: var(--atk-text);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .atk-access-self-tag {
            font-size: 10.5px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 4px;
            background: #F1F5F9;
            color: #64748B;
        }
        .atk-access-pt {
            color: var(--atk-muted);
            font-size: 12.5px;
        }
        .atk-access-email {
            color: var(--atk-muted);
            font-size: 12.5px;
        }
        .atk-access-btn {
            min-height: 30px;
            height: 30px;
            padding: 0 11px;
            font-size: 12px;
            border-radius: 7px;
            white-space: nowrap;
        }
        .atk-access-btn-revoke {
            background: #FFFFFF;
            border: 1px solid #FECACA;
            color: #DC2626;
        }
        .atk-access-btn-revoke:hover:not(:disabled) {
            background: #FEF2F2;
            border-color: #FCA5A5;
        }

        @media (max-width: 639px) {
            .atk-access-search-wrap {
                max-width: 100%;
                width: 100%;
            }
            .atk-access-table-wrap {
                overflow: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }
            .atk-access-table {
                display: block;
                min-width: 0;
            }
            .atk-access-table thead {
                display: none;
            }
            .atk-access-table tbody {
                display: grid;
                gap: 10px;
            }
            .atk-access-table tr {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 4px 10px;
                padding: 12px 14px;
                border: 1px solid var(--atk-border);
                border-radius: 10px;
                background: #FFFFFF;
            }
            .atk-access-table td {
                padding: 2px 0;
                border: 0;
                font-size: 12px;
            }
            .atk-access-cell-name {
                grid-column: 1 / 2;
            }
            .atk-access-cell-role {
                grid-column: 2 / 3;
                text-align: right;
            }
            .atk-access-cell-pt,
            .atk-access-cell-email {
                grid-column: 1 / -1;
            }
            .atk-access-cell-action {
                grid-column: 1 / -1;
                margin-top: 6px;
                padding-top: 8px !important;
                border-top: 1px solid var(--atk-border-soft) !important;
            }
            .atk-access-cell-action form,
            .atk-access-cell-action .atk-btn {
                width: 100%;
                height: 34px;
            }
        }
    </style>

    <div class="atk-access-bar">
        <div class="atk-access-title-group">
            <h1 class="atk-title">Akses Admin ATK</h1>
            <span class="atk-access-count-pill" id="atkAccessCount">{{ $users->total() }}</span>
        </div>
    </div>

    <form method="GET" id="atkAccessSearchForm" action="{{ route('v2.atk.admin.access.index') }}" class="atk-access-filter-bar">
        <input type="hidden" name="role" id="atkAccessRoleInput" value="{{ $currentRole }}">

        <div class="atk-role-pills" id="atkAccessRolePills" role="tablist" aria-label="Filter Akses">
            @foreach($roleTabs as $roleValue => $roleLabel)
                <a
                    href="{{ route('v2.atk.admin.access.index', array_filter(['role' => $roleValue, 'q' => $currentSearch])) }}"
                    data-role="{{ $roleValue }}"
                    class="atk-role-pill {{ $currentRole === $roleValue ? 'is-active' : '' }}"
                >
                    {{ $roleLabel }}
                </a>
            @endforeach
        </div>

        <div class="atk-access-search-wrap">
            <svg class="atk-access-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input
                class="atk-input atk-access-search-input"
                name="q"
                value="{{ $currentSearch }}"
                placeholder="Cari nama, email, username, atau PT..."
                autocomplete="off"
                data-async-search
            >
            <a
                class="atk-access-search-clear {{ $currentSearch !== '' ? 'is-visible' : '' }}"
                href="{{ route('v2.atk.admin.access.index') }}"
                data-async-reset
                aria-label="Reset pencarian"
            >
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </a>
        </div>
    </form>

    <div id="atkAccessResults">
        <div class="atk-table-wrap atk-access-table-wrap">
            <table class="atk-table atk-access-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>PT</th>
                        <th>Email</th>
                        <th>Akses</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php($hasAtkAccess = $user->canManageAtk())
                        <tr class="{{ $hasAtkAccess ? 'atk-access-row-admin' : '' }}">
                            <td class="atk-access-cell-name">
                                <span class="atk-access-name">
                                    {{ $user->name }}
                                    @if(auth()->id() === $user->id)
                                        <span class="atk-access-self-tag">Anda</span>
                                    @endif
                                </span>
                            </td>
                            <td class="atk-access-cell-pt"><span class="atk-access-pt">{{ $user->pt?->name ?? '-' }}</span></td>
                            <td class="atk-access-cell-email"><span class="atk-access-email">{{ $user->email ?? '-' }}</span></td>
                            <td class="atk-access-cell-role">
                                @if($hasAtkAccess)
                                    <span class="atk-badge atk-badge-success">Admin ATK</span>
                                @else
                                    <span class="atk-badge atk-badge-neutral">User</span>
                                @endif
                            </td>
                            <td class="atk-access-cell-action" style="text-align:right">
                                @if($hasAtkAccess)
                                    <form method="POST" action="{{ route('v2.atk.admin.access.revoke', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="atk-btn atk-access-btn atk-access-btn-revoke" type="submit" @disabled(auth()->id() === $user->id)>Cabut</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('v2.atk.admin.access.grant', $user) }}">
                                        @csrf
                                        <button class="atk-btn atk-btn-secondary atk-access-btn" type="submit">Jadikan Admin</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="atk-empty">User tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination :items="$users" preserve-query />
    </div>

    <script>
        (function () {
            const form = document.getElementById('atkAccessSearchForm');
            const input = form ? form.querySelector('[data-async-search]') : null;
            const reset = form ? form.querySelector('[data-async-reset]') : null;
            const roleInput = document.getElementById('atkAccessRoleInput');
            const rolePills = document.getElementById('atkAccessRolePills');
            const results = document.getElementById('atkAccessResults');
            const countEl = document.getElementById('atkAccessCount');
            if (!form || !input || !results) return;

            let timer;
            function load(url) {
                results.classList.add('is-loading');
                fetch(url.toString())
                    .then(function (response) { return response.text(); })
                    .then(function (html) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const next = doc.getElementById('atkAccessResults');
                        const nextCount = doc.getElementById('atkAccessCount');
                        if (!next) {
                            window.location.href = url.toString();
                            return;
                        }
                        results.innerHTML = next.innerHTML;
                        if (nextCount && countEl) {
                            countEl.textContent = nextCount.textContent;
                        }
                        window.history.replaceState(null, '', url.toString());
                    })
                    .catch(function () {
                        window.location.href = url.toString();
                    })
                    .finally(function () {
                        results.classList.remove('is-loading');
                    });
            }

            function syncUI() {
                const q = (input.value || '').trim();
                const role = roleInput ? (roleInput.value || '') : '';
                if (reset) {
                    reset.classList.toggle('is-visible', q.length > 0);
                }
                if (rolePills) {
                    rolePills.querySelectorAll('.atk-role-pill').forEach(function (pill) {
                        const r = pill.getAttribute('data-role') || '';
                        pill.classList.toggle('is-active', r === role);
                    });
                }
            }

            function search() {
                syncUI();
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                if (!params.get('q')) params.delete('q');
                if (!params.get('role')) params.delete('role');
                url.search = params.toString();
                load(url);
            }

            input.addEventListener('input', function () {
                syncUI();
                clearTimeout(timer);
                timer = setTimeout(search, 250);
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                clearTimeout(timer);
                search();
            });

            if (reset) {
                reset.addEventListener('click', function (event) {
                    event.preventDefault();
                    input.value = '';
                    input.focus();
                    clearTimeout(timer);
                    search();
                });
            }

            if (rolePills) {
                rolePills.addEventListener('click', function (event) {
                    const pill = event.target.closest('.atk-role-pill');
                    if (!pill) return;
                    event.preventDefault();
                    if (roleInput) roleInput.value = pill.getAttribute('data-role') || '';
                    clearTimeout(timer);
                    search();
                });
            }

            results.addEventListener('click', function (event) {
                const link = event.target.closest('a');
                if (!link || !link.closest('.hrd-pagination')) return;

                event.preventDefault();
                load(new URL(link.href));
            });
        })();
    </script>
</x-atk-app>
