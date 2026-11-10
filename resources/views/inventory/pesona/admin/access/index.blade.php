<x-inv-pesona-app title="Akses Inventory Pesona">
    <style>
        .pesona-acc-shell {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Header */
        .pesona-acc-header {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .pesona-acc-title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        .pesona-acc-count-pill {
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

        /* Quick Filter Pills */
        .pesona-acc-pills-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pesona-acc-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 36px;
            padding: 6px 13px;
            border-radius: 999px;
            border: 1.5px solid #E2E8F0;
            background: #FFFFFF;
            color: #334155;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .pesona-acc-pill:hover {
            border-color: #CBD5E1;
            background: #F8FAFC;
        }
        .pesona-acc-pill.is-active {
            background: var(--pesona-primary);
            border-color: var(--pesona-primary);
            color: #FFFFFF;
        }
        .pesona-acc-pill-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: #F1F5F9;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
        }
        .pesona-acc-pill.is-active .pesona-acc-pill-num {
            background: rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
        }

        /* Filter Toolbar */
        .pesona-acc-toolbar {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid var(--pesona-border);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-acc-filter-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .pesona-acc-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }
        .pesona-acc-field-label {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
        }
        .pesona-acc-search-wrap {
            position: relative;
            width: 100%;
        }
        .pesona-acc-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .pesona-acc-input,
        .pesona-acc-select {
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
        .pesona-acc-input.has-icon {
            padding-left: 36px;
        }
        .pesona-acc-input:focus,
        .pesona-acc-select:focus {
            outline: none;
            background: #FFFFFF;
            border-color: var(--pesona-primary);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.12);
        }
        .pesona-acc-reset-btn {
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
        .pesona-acc-reset-btn:hover {
            background: #F8FAFC;
            color: #0F172A;
        }

        /* Results Container */
        .pesona-acc-results {
            transition: opacity 0.18s ease;
        }
        .pesona-acc-results.is-loading {
            opacity: 0.55;
            pointer-events: none;
        }

        /* User Identity Cell */
        .pesona-acc-user {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }
        .pesona-acc-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #F1F5F9;
            color: #334155;
            font-size: 13px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pesona-acc-avatar.is-admin {
            background: var(--pesona-primary);
            color: #FFFFFF;
        }
        .pesona-acc-avatar.is-user {
            background: #DCFCE7;
            color: #15803D;
        }
        .pesona-acc-user-main {
            min-width: 0;
        }
        .pesona-acc-user-name {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 13.5px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.3;
        }
        .pesona-acc-self-tag {
            display: inline-flex;
            align-items: center;
            padding: 1px 7px;
            border-radius: 999px;
            background: rgba(186, 57, 37, 0.12);
            color: var(--pesona-accent);
            font-size: 10.5px;
            font-weight: 800;
        }
        .pesona-acc-user-sub {
            margin-top: 2px;
            font-size: 12px;
            font-weight: 600;
            color: #64748B;
            line-height: 1.3;
        }

        /* PT & Account Status Chips */
        .pesona-acc-pt-chip {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 7px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            color: #334155;
            font-size: 11.5px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pesona-acc-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pesona-acc-status.is-active {
            color: #15803D;
        }
        .pesona-acc-status.is-inactive {
            color: #94A3B8;
        }
        .pesona-acc-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: currentColor;
        }

        /* Access Role Badges */
        .pesona-acc-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-height: 25px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            white-space: nowrap;
        }
        .pesona-acc-badge.is-admin {
            background: var(--pesona-primary);
            color: #FFFFFF;
        }
        .pesona-acc-badge.is-user {
            background: #DCFCE7;
            color: #14532D;
            border: 1px solid #86EFAC;
        }
        .pesona-acc-badge.is-none {
            background: #F1F5F9;
            color: #64748B;
            border: 1px solid #CBD5E1;
        }
        .pesona-acc-role-sub {
            margin-top: 3px;
            font-size: 11.5px;
            color: #64748B;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Action Buttons */
        .pesona-acc-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }
        .pesona-acc-actions form {
            margin: 0;
        }
        .pesona-acc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 32px;
            padding: 5px 11px;
            border-radius: 8px;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .pesona-acc-btn.is-grant {
            background: var(--pesona-primary);
            border: 1px solid var(--pesona-primary);
            color: #FFFFFF;
        }
        .pesona-acc-btn.is-grant:hover {
            background: #1E2946;
        }
        .pesona-acc-btn.is-promote {
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            color: #1E293B;
        }
        .pesona-acc-btn.is-promote:hover {
            border-color: var(--pesona-primary);
            color: var(--pesona-primary);
            background: #F8FAFC;
        }
        .pesona-acc-btn.is-demote {
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            color: #92400E;
        }
        .pesona-acc-btn.is-demote:hover {
            background: #FEF3C7;
        }
        .pesona-acc-btn.is-revoke {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #B91C1C;
        }
        .pesona-acc-btn.is-revoke:hover {
            background: #FEE2E2;
        }
        .pesona-acc-locked-note {
            font-size: 12px;
            color: #94A3B8;
            font-weight: 600;
            font-style: italic;
        }

        /* Mobile Cards (< 860px) */
        .pesona-acc-cards {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .pesona-acc-card {
            padding: 14px;
            border: 1px solid var(--pesona-border);
            border-radius: 14px;
            background: #FFFFFF;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-acc-card.has-access {
            border-left: 4px solid #16A34A;
        }
        .pesona-acc-card.has-admin {
            border-left: 4px solid var(--pesona-primary);
            background: #FAFCFF;
        }
        .pesona-acc-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .pesona-acc-card-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-top: 10px;
            border-top: 1px dashed #E2E8F0;
        }

        /* Desktop Table (>= 860px) */
        .pesona-acc-table-wrap {
            display: none;
            overflow-x: auto;
            border: 1px solid #CBD5E1;
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }
        .pesona-acc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .pesona-acc-table th {
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
        .pesona-acc-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: #0F172A;
        }
        .pesona-acc-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .pesona-acc-table tbody tr:hover td {
            background: #F8FAFC;
        }
        .pesona-acc-table tbody tr.is-admin-row td {
            background: #F8FAFF;
        }
        .pesona-acc-table tbody tr.is-admin-row td:first-child {
            box-shadow: inset 4px 0 0 var(--pesona-primary);
        }
        .pesona-acc-table tbody tr.is-user-row td {
            background: #F7FEFA;
        }
        .pesona-acc-table tbody tr.is-user-row td:first-child {
            box-shadow: inset 4px 0 0 #16A34A;
        }

        /* Empty State */
        .pesona-acc-empty {
            padding: 42px 20px;
            border: 1px dashed #CBD5E1;
            border-radius: 14px;
            background: #FFFFFF;
            text-align: center;
        }
        .pesona-acc-empty-title {
            margin: 0 0 4px;
            font-size: 15px;
            font-weight: 800;
            color: #0F172A;
        }
        .pesona-acc-empty-sub {
            margin: 0;
            font-size: 13px;
            color: #64748B;
        }

        @media (min-width: 640px) {
            .pesona-acc-filter-row {
                grid-template-columns: 1fr 220px auto;
                align-items: end;
            }
        }

        @media (min-width: 860px) {
            .pesona-acc-cards {
                display: none;
            }
            .pesona-acc-table-wrap {
                display: block;
            }
        }
    </style>

    @php
        $hasActiveFilter = $filters['q'] !== '' || $filters['access'] !== 'all';
        $counts = $counts ?? ['all' => $users->total(), 'admin' => 0, 'user' => 0, 'none' => 0];
    @endphp

    <div class="pesona-acc-shell">
        {{-- Header --}}
        <div class="pesona-acc-header">
            <div class="pesona-acc-title-row">
                <h1 class="pesona-title">Akses Inventory Pesona</h1>
                <span class="pesona-acc-count-pill" id="pesona-acc-total-pill">{{ $users->total() }} karyawan</span>
            </div>
            <p class="pesona-subtitle">
                Kelola pengguna dan admin Inventory Pesona. Karyawan yang memiliki akses sudah diurutkan otomatis di bagian teratas.
            </p>
        </div>

        {{-- Quick Filter Pills --}}
        <div class="pesona-acc-pills-bar" id="pesona-acc-pills">
            <button
                type="button"
                class="pesona-acc-pill {{ $filters['access'] === 'all' ? 'is-active' : '' }}"
                data-access="all"
            >
                Semua Karyawan
                <span class="pesona-acc-pill-num">{{ $counts['all'] }}</span>
            </button>
            <button
                type="button"
                class="pesona-acc-pill {{ $filters['access'] === 'admin' ? 'is-active' : '' }}"
                data-access="admin"
            >
                Admin
                <span class="pesona-acc-pill-num">{{ $counts['admin'] }}</span>
            </button>
            <button
                type="button"
                class="pesona-acc-pill {{ $filters['access'] === 'user' ? 'is-active' : '' }}"
                data-access="user"
            >
                Pengguna
                <span class="pesona-acc-pill-num">{{ $counts['user'] }}</span>
            </button>
            <button
                type="button"
                class="pesona-acc-pill {{ $filters['access'] === 'none' ? 'is-active' : '' }}"
                data-access="none"
            >
                Tanpa Akses
                <span class="pesona-acc-pill-num">{{ $counts['none'] }}</span>
            </button>
        </div>

        {{-- Filter & Live Search Toolbar --}}
        <form
            id="pesona-acc-filter-form"
            class="pesona-acc-toolbar"
            method="GET"
            action="{{ route('inventory.pesona.admin.access.index') }}"
        >
            <div class="pesona-acc-filter-row">
                <label class="pesona-acc-field">
                    <span class="pesona-acc-field-label">Cari Nama Karyawan, Username, atau PT</span>
                    <div class="pesona-acc-search-wrap">
                        <svg class="pesona-acc-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input
                            type="search"
                            id="pesona-acc-q"
                            name="q"
                            class="pesona-acc-input has-icon"
                            value="{{ $filters['q'] }}"
                            maxlength="200"
                            placeholder="Ketik nama karyawan, username, atau nama PT..."
                            autocomplete="off"
                        >
                    </div>
                </label>

                <label class="pesona-acc-field">
                    <span class="pesona-acc-field-label">Status Akses</span>
                    <select id="pesona-acc-select" name="access" class="pesona-acc-select">
                        <option value="all" @selected($filters['access'] === 'all')>Semua Status Akses</option>
                        <option value="admin" @selected($filters['access'] === 'admin')>Admin</option>
                        <option value="user" @selected($filters['access'] === 'user')>Pengguna</option>
                        <option value="none" @selected($filters['access'] === 'none')>Tanpa Akses</option>
                    </select>
                </label>

                <div style="display:flex; align-items:center; gap:8px;">
                    <a
                        id="pesona-acc-reset"
                        class="pesona-acc-reset-btn"
                        href="{{ route('inventory.pesona.admin.access.index') }}"
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
        <div id="pesona-access-results" class="pesona-acc-results" data-total="{{ $users->total() }}">
            @if($users->isEmpty())
                <div class="pesona-acc-empty">
                    <p class="pesona-acc-empty-title">Tidak Ada Karyawan yang Sesuai</p>
                    <p class="pesona-acc-empty-sub">
                        {{ $filters['q'] !== '' || $filters['access'] !== 'all' ? 'Tidak ada user yang sesuai dengan pencarian atau filter.' : 'Tidak ada user untuk ditampilkan pada halaman ini.' }}
                    </p>
                </div>
            @else
                {{-- Mobile Cards (< 860px) --}}
                <div class="pesona-acc-cards">
                    @foreach($users as $user)
                        @php
                            $self = (int) auth()->id() === (int) $user->id;
                            $hasAccess = $user->pesona_access_id !== null;
                            $admin = $hasAccess && (bool) $user->pesona_is_admin;
                            $params = array_merge(['user' => $user->id], $filters);
                            $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1)) ?: 'U';
                            $cardClass = $admin ? 'has-admin' : ($hasAccess ? 'has-access' : '');
                            $avatarClass = $admin ? 'is-admin' : ($hasAccess ? 'is-user' : '');
                            $badgeClass = $admin ? 'is-admin' : ($hasAccess ? 'is-user' : 'is-none');
                            $roleLabel = $admin ? 'Admin' : ($hasAccess ? 'Pengguna' : 'Tanpa Akses');
                        @endphp

                        <article class="pesona-acc-card {{ $cardClass }}">
                            <div class="pesona-acc-card-top">
                                <div class="pesona-acc-user">
                                    <span class="pesona-acc-avatar {{ $avatarClass }}">{{ $initial }}</span>
                                    <div class="pesona-acc-user-main">
                                        <div class="pesona-acc-user-name">
                                            <span>{{ $user->name }}</span>
                                            @if($self)<span class="pesona-acc-self-tag">Anda</span>@endif
                                        </div>
                                        <div class="pesona-acc-user-sub">
                                            {{ $user->username ? '@'.$user->username : 'Tanpa username' }}
                                            · {{ $user->pt?->name ?? 'Tanpa PT' }}
                                        </div>
                                    </div>
                                </div>
                                <span class="pesona-acc-badge {{ $badgeClass }}">{{ $roleLabel }}</span>
                            </div>

                            <div class="pesona-acc-card-meta">
                                <span class="pesona-acc-status {{ $user->isActive() ? 'is-active' : 'is-inactive' }}">
                                    <span class="pesona-acc-dot"></span>
                                    Akun {{ $user->isActive() ? 'Aktif' : 'Nonaktif' }}
                                </span>

                                <div class="pesona-acc-actions">
                                    @if($self)
                                        <span class="pesona-acc-locked-note">Akun Anda sendiri</span>
                                    @elseif(! $user->isActive() && ! $hasAccess)
                                        <span class="pesona-acc-locked-note">User nonaktif</span>
                                    @else
                                        @if($user->isActive() && ! $hasAccess)
                                            <form method="POST" action="{{ route('inventory.pesona.admin.access.grant', $params) }}">
                                                @csrf
                                                <button class="pesona-acc-btn is-grant" type="submit">Berikan Akses</button>
                                            </form>
                                        @endif
                                        @if($user->isActive() && ! $admin)
                                            <button class="pesona-acc-btn is-promote" type="button" data-access-modal="pesona_access_grant_admin_{{ $user->id }}">Jadikan Admin</button>
                                        @endif
                                        @if($admin && ! $self)
                                            <button class="pesona-acc-btn is-demote" type="button" data-access-modal="pesona_access_revoke_admin_{{ $user->id }}">Cabut Admin</button>
                                        @endif
                                        @if($hasAccess && ! $self)
                                            <button class="pesona-acc-btn is-revoke" type="button" data-access-modal="pesona_access_revoke_{{ $user->id }}">Cabut Akses</button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Desktop Table (>= 860px) --}}
                <div class="pesona-acc-table-wrap">
                    <table class="pesona-acc-table">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>PT</th>
                                <th>Status User</th>
                                <th>Hak Akses</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                @php
                                    $self = (int) auth()->id() === (int) $user->id;
                                    $hasAccess = $user->pesona_access_id !== null;
                                    $admin = $hasAccess && (bool) $user->pesona_is_admin;
                                    $params = array_merge(['user' => $user->id], $filters);
                                    $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1)) ?: 'U';
                                    $trClass = $admin ? 'is-admin-row' : ($hasAccess ? 'is-user-row' : '');
                                    $avatarClass = $admin ? 'is-admin' : ($hasAccess ? 'is-user' : '');
                                    $badgeClass = $admin ? 'is-admin' : ($hasAccess ? 'is-user' : 'is-none');
                                    $roleLabel = $admin ? 'Admin' : ($hasAccess ? 'Pengguna' : 'Tanpa Akses');
                                    $roleDesc = $admin ? 'Kelola barang & stok' : ($hasAccess ? 'Bisa ambil barang' : 'Belum bisa masuk');

                                    $actions = [];
                                    if ($user->isActive() && ! $admin) {
                                        $actions[] = ['key' => 'grant_admin', 'label' => 'Jadikan Admin', 'btnClass' => 'is-promote', 'route' => 'grant-admin', 'method' => 'POST', 'title' => 'Berikan Hak Admin?', 'message' => 'Berikan hak admin Pesona kepada'];
                                    }
                                    if ($admin && ! $self) {
                                        $actions[] = ['key' => 'revoke_admin', 'label' => 'Cabut Admin', 'btnClass' => 'is-demote', 'route' => 'revoke-admin', 'method' => 'DELETE', 'title' => 'Cabut Hak Admin?', 'message' => 'Cabut hak admin Pesona dari'];
                                    }
                                    if ($hasAccess && ! $self) {
                                        $actions[] = ['key' => 'revoke', 'label' => 'Cabut Akses', 'btnClass' => 'is-revoke', 'route' => 'revoke', 'method' => 'DELETE', 'title' => 'Cabut Akses Pesona?', 'message' => 'Cabut seluruh akses Pesona dari'];
                                    }
                                @endphp
                                <tr class="{{ $trClass }}">
                                    <td>
                                        <div class="pesona-acc-user">
                                            <span class="pesona-acc-avatar {{ $avatarClass }}">{{ $initial }}</span>
                                            <div class="pesona-acc-user-main">
                                                <div class="pesona-acc-user-name">
                                                    <span>{{ $user->name }}</span>
                                                    @if($self)<span class="pesona-acc-self-tag">Anda</span>@endif
                                                </div>
                                                <div class="pesona-acc-user-sub">
                                                    Username: {{ $user->username ?: '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->pt?->name)
                                            <span class="pesona-acc-pt-chip">{{ $user->pt->name }}</span>
                                        @else
                                            <span style="color:#94A3B8;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="pesona-acc-status {{ $user->isActive() ? 'is-active' : 'is-inactive' }}">
                                            <span class="pesona-acc-dot"></span>
                                            {{ $user->isActive() ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="pesona-acc-badge {{ $badgeClass }}">{{ $roleLabel }}</span>
                                        </div>
                                        <div class="pesona-acc-role-sub">{{ $roleDesc }}</div>
                                    </td>
                                    <td>
                                        <div class="pesona-acc-actions">
                                            @if($self)
                                                <span class="pesona-acc-locked-note">Akun Anda sendiri</span>
                                            @elseif(! $user->isActive() && empty($actions))
                                                <span class="pesona-acc-locked-note">User nonaktif</span>
                                            @else
                                                @if($user->isActive() && ! $hasAccess)
                                                    <form method="POST" action="{{ route('inventory.pesona.admin.access.grant', $params) }}">
                                                        @csrf
                                                        <button class="pesona-acc-btn is-grant" type="submit">Berikan Akses</button>
                                                    </form>
                                                @endif
                                                @foreach($actions as $action)
                                                    @php
                                                        $modalId = 'pesona_access_'.$action['key'].'_'.$user->id;
                                                    @endphp
                                                    <button
                                                        class="pesona-acc-btn {{ $action['btnClass'] }}"
                                                        type="button"
                                                        data-access-modal="{{ $modalId }}"
                                                        aria-haspopup="dialog"
                                                        aria-controls="{{ $modalId }}"
                                                    >
                                                        {{ $action['label'] }}
                                                    </button>
                                                @endforeach
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Shared Confirmation Modals (Accessible on Both Mobile & Desktop) --}}
                @foreach($users as $user)
                    @php
                        $self = (int) auth()->id() === (int) $user->id;
                        $hasAccess = $user->pesona_access_id !== null;
                        $admin = $hasAccess && (bool) $user->pesona_is_admin;
                        $params = array_merge(['user' => $user->id], $filters);
                        $actions = [];
                        if ($user->isActive() && ! $admin) {
                            $actions[] = ['key' => 'grant_admin', 'label' => 'Jadikan Admin', 'route' => 'grant-admin', 'method' => 'POST', 'title' => 'Berikan Hak Admin?', 'message' => 'Berikan hak admin Pesona kepada'];
                        }
                        if ($admin && ! $self) {
                            $actions[] = ['key' => 'revoke_admin', 'label' => 'Cabut Admin', 'route' => 'revoke-admin', 'method' => 'DELETE', 'title' => 'Cabut Hak Admin?', 'message' => 'Cabut hak admin Pesona dari'];
                        }
                        if ($hasAccess && ! $self) {
                            $actions[] = ['key' => 'revoke', 'label' => 'Cabut Akses', 'route' => 'revoke', 'method' => 'DELETE', 'title' => 'Cabut Akses Pesona?', 'message' => 'Cabut seluruh akses Pesona dari'];
                        }
                    @endphp
                    @foreach($actions as $action)
                        @php
                            $modalId = 'pesona_access_'.$action['key'].'_'.$user->id;
                        @endphp
                        <x-modal
                            :id="$modalId"
                            :title="$action['title']"
                            type="confirm"
                            variant="warning"
                            :confirmLabel="$action['label']"
                            cancelLabel="Batal"
                            :confirmFormAction="route('inventory.pesona.admin.access.'.$action['route'], $params)"
                            :confirmFormMethod="$action['method']"
                        >
                            <p style="margin:0;">{{ $action['message'] }} <strong>{{ $user->name }}</strong>?</p>
                        </x-modal>
                    @endforeach
                @endforeach
            @endif

            <div style="margin-top: 14px;">
                <x-pagination :items="$users" />
            </div>
        </div>
    </div>

    <script>
        (() => {
            /* Confirmation Modal Logic */
            let activeModal = null;
            let trigger = null;
            const selector = 'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

            function closeModal() {
                if (!activeModal) return;
                activeModal.style.display = 'none';
                activeModal = null;
                document.body.style.overflow = '';
                if (trigger) trigger.focus();
            }

            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-access-modal]');
                if (button) {
                    trigger = button;
                    activeModal = document.getElementById(button.dataset.accessModal);
                    if (activeModal) {
                        activeModal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                        activeModal.querySelector('[data-modal-close]')?.focus();
                    }
                } else if (activeModal && (event.target === activeModal || event.target.closest('[data-modal-close]')?.closest('.modal-backdrop') === activeModal)) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (!activeModal) return;
                if (event.key === 'Escape') closeModal();
                if (event.key !== 'Tab') return;
                const controls = Array.from(activeModal.querySelectorAll(selector)).filter((element) => element.getClientRects().length);
                const first = controls[0], last = controls[controls.length - 1];
                if (event.shiftKey && (document.activeElement === first || !activeModal.contains(document.activeElement))) {
                    event.preventDefault();
                    last?.focus();
                } else if (!event.shiftKey && (document.activeElement === last || !activeModal.contains(document.activeElement))) {
                    event.preventDefault();
                    first?.focus();
                }
            });

            /* AJAX Live Search & Filter Logic */
            const form = document.getElementById('pesona-acc-filter-form');
            const qInput = document.getElementById('pesona-acc-q');
            const accessSelect = document.getElementById('pesona-acc-select');
            const resetBtn = document.getElementById('pesona-acc-reset');
            const pillsWrap = document.getElementById('pesona-acc-pills');
            const totalPill = document.getElementById('pesona-acc-total-pill');
            let resultsBox = document.getElementById('pesona-access-results');

            if (!form || !resultsBox) return;

            let debounceTimer = null;
            let activeController = null;

            function syncUiState() {
                const curAccess = accessSelect ? accessSelect.value : 'all';
                if (pillsWrap) {
                    pillsWrap.querySelectorAll('[data-access]').forEach((btn) => {
                        btn.classList.toggle('is-active', btn.getAttribute('data-access') === curAccess);
                    });
                }
                if (resetBtn) {
                    const hasAny = (qInput && qInput.value.trim() !== '') || curAccess !== 'all';
                    resetBtn.style.display = hasAny ? '' : 'none';
                }
            }

            function buildUrl(customUrl) {
                if (customUrl) return customUrl;
                const params = new URLSearchParams();
                if (qInput && qInput.value.trim() !== '') params.set('q', qInput.value.trim());
                if (accessSelect && accessSelect.value !== 'all') params.set('access', accessSelect.value);
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
                        const nextResults = doc.getElementById('pesona-access-results');
                        if (nextResults) {
                            resultsBox.replaceWith(nextResults);
                            resultsBox = nextResults;
                            const nextTotal = nextResults.getAttribute('data-total');
                            if (totalPill && nextTotal !== null) {
                                totalPill.textContent = nextTotal + ' karyawan';
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
                    }, 260);
                });
            }

            if (accessSelect) {
                accessSelect.addEventListener('change', () => {
                    fetchResults();
                });
            }

            if (pillsWrap) {
                pillsWrap.addEventListener('click', (e) => {
                    const pill = e.target.closest('[data-access]');
                    if (!pill || !accessSelect) return;
                    accessSelect.value = pill.getAttribute('data-access') || 'all';
                    fetchResults();
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (qInput) qInput.value = '';
                    if (accessSelect) accessSelect.value = 'all';
                    fetchResults(form.action);
                });
            }

            document.addEventListener('click', (e) => {
                const link = e.target.closest('#pesona-access-results a[href]');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#')) return;
                e.preventDefault();
                fetchResults(href);
            });
        })();
    </script>
</x-inv-pesona-app>
