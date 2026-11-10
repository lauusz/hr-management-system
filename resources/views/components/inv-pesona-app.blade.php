@props(['title' => 'Inventory Pesona'])
@php
    $isPesonaAdmin = \App\Models\InvPesonaAccessUser::query()
        ->where('user_id', auth()->id())
        ->where('is_admin', true)
        ->exists();
    $pesonaPendingRequestCount = 0;
    if ($isPesonaAdmin) {
        try {
            $pesonaPendingRequestCount = \App\Models\InvPesonaRequest::query()
                ->where('status', \App\Models\InvPesonaRequest::STATUS_PENDING)
                ->count();
        } catch (\Throwable) {
            $pesonaPendingRequestCount = 0;
        }
    }
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        svg:not([width]) { width: 1em; }
        svg:not([height]) { height: 1em; }
        html, body { margin: 0; overflow-x: clip; }
        .pesona-shell {
            --pesona-primary: #28355A;
            --pesona-primary-dark: #1E2846;
            --pesona-primary-soft: #EEF1F7;
            --pesona-primary-softer: #F6F8FC;
            --pesona-accent: #BA3925;
            --pesona-accent-dark: #992B1B;
            --pesona-accent-soft: #FCEEEB;
            --pesona-bg: #F5F7FA;
            --pesona-surface: #FFFFFF;
            --pesona-text: #111827;
            --pesona-text-secondary: #374151;
            --pesona-muted: #6B7280;
            --pesona-border: #E5E7EB;
            --pesona-border-soft: #F3F4F6;
            --error: #EF4444;
            min-height: 100dvh;
            display: block;
            background: var(--pesona-bg);
            color: var(--pesona-text);
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .pesona-shell * { box-sizing: border-box; }

        /* ========== SIDEBAR (Mobile-First) ========== */
        .pesona-sidebar {
            width: min(82vw, 256px);
            height: 100dvh;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--pesona-surface);
            border-right: 1px solid var(--pesona-border);
            transform: translateX(-100%);
            visibility: hidden;
            transition: transform .2s ease;
        }
        .pesona-sidebar.open {
            transform: translateX(0);
            visibility: visible;
            box-shadow: 0 12px 32px rgba(17, 24, 39, 0.14);
        }
        .pesona-backdrop {
            position: fixed;
            inset: 0;
            z-index: 999;
            display: none;
            background: rgba(17, 24, 39, .35);
            backdrop-filter: blur(2px);
        }
        .pesona-backdrop.show { display: block; }

        .pesona-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--pesona-border-soft);
        }
        .pesona-brand-logo {
            height: 34px;
            width: auto;
            max-width: 84px;
            object-fit: contain;
            display: block;
            flex-shrink: 0;
        }
        .pesona-brand-copy {
            min-width: 0;
            flex: 1;
        }
        .pesona-brand-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--pesona-primary);
            line-height: 1.25;
        }
        .pesona-brand-subtitle {
            margin: 1px 0 0;
            font-size: 11px;
            color: var(--pesona-accent);
            font-weight: 600;
        }

        .pesona-menu {
            flex: 1;
            padding: 8px 10px;
            overflow-y: auto;
            display: block;
        }
        .pesona-menu-title {
            margin: 12px 8px 4px;
            font-size: 10px;
            color: #9CA3AF;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .pesona-menu-title:first-child { margin-top: 4px; }

        .pesona-menu-item {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 36px;
            padding: 6px 10px;
            border-radius: 8px;
            margin-bottom: 2px;
            color: var(--pesona-text-secondary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: background-color .12s ease, color .12s ease;
        }
        .pesona-icon-sprite {
            position: absolute;
            width: 0;
            height: 0;
            overflow: hidden;
        }
        .pesona-menu-icon {
            width: 17px;
            height: 17px;
            flex: 0 0 17px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            opacity: .78;
        }
        .pesona-menu-item:hover {
            color: var(--pesona-primary);
            background: var(--pesona-primary-softer);
        }
        .pesona-menu-item.active {
            color: var(--pesona-primary);
            background: var(--pesona-primary-soft);
            font-weight: 600;
            box-shadow: inset 3px 0 0 var(--pesona-accent);
        }
        .pesona-menu-item.active .pesona-menu-icon {
            color: var(--pesona-accent);
            opacity: 1;
        }

        .pesona-menu-badge {
            margin-left: auto;
            background: var(--pesona-accent);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 999px;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
        }

        .pesona-sidebar-footer {
            padding: 10px 12px;
            border-top: 1px solid var(--pesona-border-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .pesona-user-card {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex: 1;
        }
        .pesona-user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: var(--pesona-primary);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pesona-user-info { min-width: 0; }
        .pesona-user-name {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--pesona-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pesona-user-role {
            font-size: 11px;
            color: var(--pesona-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pesona-logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 32px;
            padding: 0 10px;
            border: 1px solid var(--pesona-border);
            border-radius: 8px;
            background: var(--pesona-surface);
            color: var(--pesona-text-secondary);
            font: inherit;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            flex-shrink: 0;
            transition: all .15s ease;
        }
        .pesona-logout-btn:hover {
            background: #FEF2F2;
            border-color: #FECACA;
            color: #B91C1C;
        }

        .pesona-button { display: inline-flex; align-items: center; justify-content: center; min-height: 36px; padding: 0 10px; border: 1px solid #E5E7EB; border-radius: 9px; background: #fff; color: #374151; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: background-color .15s ease, color .15s ease, border-color .15s ease; }
        .pesona-button:hover { color: var(--pesona-primary); background: var(--pesona-primary-soft); border-color: #CBD5E1; }
        .pesona-shell :focus-visible, #confirm-logout :focus-visible { outline: 3px solid rgba(40, 53, 90, 0.35); outline-offset: 2px; }
        .pesona-shell .hrd-page-btn--active { background: var(--pesona-primary); border-color: var(--pesona-primary); color: #fff; }
        .pesona-main { min-width: 0; min-height: 100dvh; }
        .pesona-topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            height: 54px;
            padding: 0 14px;
            background: rgba(255, 255, 255, .94);
            border-bottom: 1px solid var(--pesona-border);
            backdrop-filter: blur(8px);
        }
        .pesona-burger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border: 1px solid var(--pesona-border);
            border-radius: 9px;
            background: var(--pesona-surface);
            color: var(--pesona-primary);
            cursor: pointer;
            flex-shrink: 0;
        }
        .pesona-burger svg { width: 18px; height: 18px; }
        .pesona-topbar-title { min-width: 0; flex: 1; }
        .pesona-topbar-title strong {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: var(--pesona-primary);
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pesona-topbar-shortcut {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: var(--pesona-primary-soft);
            color: var(--pesona-primary);
            text-decoration: none;
            flex-shrink: 0;
            transition: background-color .15s ease;
        }
        .pesona-topbar-shortcut:hover { background: #DFE5F2; }
        .pesona-content { width: 100%; max-width: 1400px; margin: 0 auto; padding: 14px 14px 72px; }
        .pesona-title { margin: 0; font-size: 18px; font-weight: 700; letter-spacing: -.01em; overflow-wrap: anywhere; }
        .pesona-subtitle { margin: 2px 0 0; color: #6B7280; font-size: 12.5px; }
        .pesona-alert { padding: 10px 14px; border-radius: 10px; margin-bottom: 12px; font-size: 13px; font-weight: 600; overflow-wrap: anywhere; }
        .pesona-alert-success { color: #15803D; background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.25); }
        .pesona-alert-warning { color: #B45309; background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.25); }
        .pesona-alert-error { color: #B91C1C; background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.25); }
        @media (min-width: 1024px) {
            .pesona-shell { display: flex; }
            .pesona-sidebar {
                width: 244px;
                height: 100dvh;
                position: sticky;
                top: 0;
                inset: auto;
                transform: none;
                visibility: visible;
                flex-shrink: 0;
            }
            .pesona-backdrop,
            .pesona-topbar {
                display: none !important;
            }
            .pesona-menu {
                display: block;
                overflow-x: visible;
                overflow-y: auto;
            }
            .pesona-main { flex: 1; height: 100dvh; overflow-y: auto; }
            .pesona-content { padding: 22px 26px 72px; }
        }
        @media (prefers-reduced-motion: reduce) { .pesona-sidebar { transition: none; } }
    </style>
</head>
<body>
    <div class="pesona-shell">
        <div class="pesona-backdrop" id="pesonaBackdrop" aria-hidden="true"></div>
        <aside class="pesona-sidebar" id="pesonaSidebar" aria-label="Navigasi Inventory Pesona">
            <svg class="pesona-icon-sprite" aria-hidden="true">
                <symbol id="pesona-icon-catalog" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></symbol>
                <symbol id="pesona-icon-cart" viewBox="0 0 24 24"><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/><path d="m16 19 2 2 4-4"/></symbol>
                <symbol id="pesona-icon-request" viewBox="0 0 24 24"><path d="M9 5h6"/><path d="M9 3h6a2 2 0 0 1 2 2v1h2v15H5V6h2V5a2 2 0 0 1 2-2Z"/><path d="M9 11h6M9 15h6"/></symbol>
                <symbol id="pesona-icon-inbox" viewBox="0 0 24 24"><path d="M4 4h16l2 10v6H2v-6L4 4Z"/><path d="M2 14h6l2 3h4l2-3h6"/></symbol>
                <symbol id="pesona-icon-items" viewBox="0 0 24 24"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></symbol>
                <symbol id="pesona-icon-stock" viewBox="0 0 24 24"><path d="M7 3v15M3 7l4-4 4 4M17 21V6M13 17l4 4 4-4"/></symbol>
                <symbol id="pesona-icon-vehicle" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1.5"/><path d="M16 8h4l3 3v5h-7V8Z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></symbol>
                <symbol id="pesona-icon-access" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M16 11h6M19 8v6"/></symbol>
                <symbol id="pesona-icon-portal" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
            </svg>
            <div class="pesona-brand">
                <img class="pesona-brand-logo" src="{{ asset('images/pesona-logo.png') }}" alt="Logo PT Pesona Lintas Samudra" width="84" height="34">
                <div class="pesona-brand-copy">
                    <p class="pesona-brand-title">Inventory Pesona</p>
                    <p class="pesona-brand-subtitle">Persediaan Garasi</p>
                </div>
            </div>
            <nav class="pesona-menu" aria-label="Menu Inventory Pesona">
                <div class="pesona-menu-title">Menu</div>
                <a class="pesona-menu-item {{ request()->routeIs('inventory.pesona.catalog') ? 'active' : '' }}" href="{{ route('inventory.pesona.catalog') }}" @if(request()->routeIs('inventory.pesona.catalog')) aria-current="page" @endif>
                    <svg class="pesona-menu-icon" width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><use href="#pesona-icon-catalog"/></svg>
                    <span>Daftar Barang</span>
                </a>
                <a class="pesona-menu-item {{ request()->routeIs('inventory.pesona.cart.*') ? 'active' : '' }}" href="{{ route('inventory.pesona.cart.show') }}" @if(request()->routeIs('inventory.pesona.cart.*')) aria-current="page" @endif>
                    <svg class="pesona-menu-icon" width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><use href="#pesona-icon-cart"/></svg>
                    <span>Daftar Ambil</span>
                </a>
                <a class="pesona-menu-item {{ request()->routeIs('inventory.pesona.requests.*') ? 'active' : '' }}" href="{{ route('inventory.pesona.requests.index') }}" @if(request()->routeIs('inventory.pesona.requests.*')) aria-current="page" @endif>
                    <svg class="pesona-menu-icon" width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><use href="#pesona-icon-request"/></svg>
                    <span>Riwayat Pengambilan</span>
                </a>

                @if($isPesonaAdmin)
                    <div class="pesona-menu-title">Admin Pesona</div>
                    <a class="pesona-menu-item {{ request()->routeIs('inventory.pesona.admin.items.*', 'inventory.pesona.admin.stock-in.*', 'inventory.pesona.admin.stock-adjustment.*') ? 'active' : '' }}" href="{{ route('inventory.pesona.admin.items.index') }}" @if(request()->routeIs('inventory.pesona.admin.items.*', 'inventory.pesona.admin.stock-in.*', 'inventory.pesona.admin.stock-adjustment.*')) aria-current="page" @endif>
                        <svg class="pesona-menu-icon" width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><use href="#pesona-icon-items"/></svg>
                        <span>Master Barang</span>
                    </a>
                    <a class="pesona-menu-item {{ request()->routeIs('inventory.pesona.admin.requests.*') ? 'active' : '' }}" href="{{ route('inventory.pesona.admin.requests.index') }}" @if(request()->routeIs('inventory.pesona.admin.requests.*')) aria-current="page" @endif>
                        <svg class="pesona-menu-icon" width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><use href="#pesona-icon-inbox"/></svg>
                        <span>Approval Pengambilan</span>
                        @if($pesonaPendingRequestCount > 0)
                            <span class="pesona-menu-badge">{{ $pesonaPendingRequestCount > 99 ? '99+' : $pesonaPendingRequestCount }}</span>
                        @endif
                    </a>
                    <a class="pesona-menu-item {{ request()->routeIs('inventory.pesona.admin.stock-movements.*') ? 'active' : '' }}" href="{{ route('inventory.pesona.admin.stock-movements.index') }}" @if(request()->routeIs('inventory.pesona.admin.stock-movements.*')) aria-current="page" @endif>
                        <svg class="pesona-menu-icon" width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><use href="#pesona-icon-stock"/></svg>
                        <span>Riwayat Stok</span>
                    </a>
                    <a class="pesona-menu-item {{ request()->routeIs('inventory.pesona.admin.vehicles.*') ? 'active' : '' }}" href="{{ route('inventory.pesona.admin.vehicles.index') }}" @if(request()->routeIs('inventory.pesona.admin.vehicles.*')) aria-current="page" @endif>
                        <svg class="pesona-menu-icon" width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><use href="#pesona-icon-vehicle"/></svg>
                        <span>Daftar Nopol</span>
                    </a>
                    <a class="pesona-menu-item {{ request()->routeIs('inventory.pesona.admin.access.*') ? 'active' : '' }}" href="{{ route('inventory.pesona.admin.access.index') }}" @if(request()->routeIs('inventory.pesona.admin.access.*')) aria-current="page" @endif>
                        <svg class="pesona-menu-icon" width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><use href="#pesona-icon-access"/></svg>
                        <span>Akses</span>
                    </a>
                @endif

                <div class="pesona-menu-title">Pindah Layanan</div>
                <a class="pesona-menu-item" href="{{ route('v2.access') }}">
                    <svg class="pesona-menu-icon" width="17" height="17" viewBox="0 0 24 24" aria-hidden="true"><use href="#pesona-icon-portal"/></svg>
                    <span>Pilih Layanan</span>
                </a>
            </nav>
            <div class="pesona-sidebar-footer">
                <div class="pesona-user-card">
                    <div class="pesona-user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="pesona-user-info">
                        <div class="pesona-user-name">{{ auth()->user()->name }}</div>
                        <div class="pesona-user-role">{{ auth()->user()->role->label() }}</div>
                    </div>
                </div>
                <button class="pesona-logout-btn" id="pesonaLogout" type="button" data-modal-target="confirm-logout" aria-haspopup="dialog" aria-controls="confirm-logout">Keluar</button>
            </div>
        </aside>
        <main class="pesona-main">
            <header class="pesona-topbar">
                <button class="pesona-burger pesona-open" id="pesonaOpen" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="pesonaSidebar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="pesona-topbar-title">
                    <strong>{{ $title }}</strong>
                </div>
                <a class="pesona-topbar-shortcut" href="{{ route('inventory.pesona.cart.show') }}" aria-label="Buka Daftar Ambil" title="Daftar Ambil">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <use href="#pesona-icon-cart"/>
                    </svg>
                </a>
            </header>
            <div class="pesona-content">
                @if(session('success'))
                    <div class="pesona-alert pesona-alert-success" role="status">{{ session('success') }}</div>
                @endif
                @if(session('warning') && session('warning') !== session('success'))
                    <div class="pesona-alert pesona-alert-warning" role="status">{{ session('warning') }}</div>
                @endif
                @if(session('error') && session('error') !== session('success') && session('error') !== session('warning'))
                    <div class="pesona-alert pesona-alert-error" role="alert">{{ session('error') }}</div>
                @endif
                @php
                    $firstErrorKey = array_key_first($errors->getMessages());
                    $manualInlineError = request()->routeIs('inventory.pesona.admin.requests.manual.create')
                        && $firstErrorKey !== null && (in_array($firstErrorKey, ['user_id', 'form_token', 'items', 'notes'], true) || str_starts_with($firstErrorKey, 'items.'));
                    $reviewInlineError = request()->routeIs('inventory.pesona.admin.requests.show')
                        && in_array($firstErrorKey, ['status', 'admin_note'], true) && old('review_form_id') !== null;
                @endphp
                @if(! request()->routeIs('inventory.pesona.admin.items.create', 'inventory.pesona.admin.items.edit', 'inventory.pesona.admin.stock-in.create', 'inventory.pesona.admin.stock-adjustment.create', 'inventory.pesona.admin.stock-movements.index') && ! $manualInlineError && ! $reviewInlineError && $errors->any() && ! (request()->routeIs('inventory.pesona.cart.show') && $errors->first() === $errors->first('notes')) && $errors->first() !== session('error') && $errors->first() !== session('warning') && $errors->first() !== session('success'))
                    <div class="pesona-alert pesona-alert-error" role="alert">{{ $errors->first() }}</div>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>
    <x-modal id="confirm-logout" title="Keluar dari Sistem?" type="confirm" variant="danger"
        confirmLabel="Ya, Keluar" cancelLabel="Batal" :confirmFormAction="route('logout')" confirmFormMethod="POST">
        <p style="margin:0;">Apakah Anda yakin ingin mengakhiri sesi ini?</p>
    </x-modal>
    <script>
        (() => {
            const sidebar = document.getElementById('pesonaSidebar');
            const opener = document.getElementById('pesonaOpen');
            const backdrop = document.getElementById('pesonaBackdrop');
            const logout = document.getElementById('pesonaLogout');
            const modal = document.getElementById('confirm-logout');
            const desktop = window.matchMedia('(min-width: 1024px)');
            const focusable = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
            let drawerOpen = false;
            let modalOpen = false;

            function setDrawer(open, restoreFocus = true) {
                drawerOpen = open && !desktop.matches;
                sidebar.classList.toggle('open', drawerOpen);
                backdrop.classList.toggle('show', drawerOpen);
                opener.setAttribute('aria-expanded', String(drawerOpen));
                document.body.style.overflow = drawerOpen || modalOpen ? 'hidden' : '';
                if (drawerOpen) (sidebar.querySelector('a.active') || sidebar.querySelector('a'))?.focus();
                else if (restoreFocus && !desktop.matches) opener.focus();
            }

            function setModal(open) {
                modalOpen = open;
                modal.style.display = open ? 'flex' : 'none';
                document.body.style.overflow = drawerOpen || modalOpen ? 'hidden' : '';
                if (open) modal.querySelector('[data-modal-close]').focus();
                else if (desktop.matches) logout.focus();
                else opener.focus();
            }

            function trapFocus(event, container) {
                const controls = Array.from(container.querySelectorAll(focusable)).filter((element) => element.getClientRects().length);
                if (!controls.length) return;
                const first = controls[0], last = controls[controls.length - 1];
                if (event.shiftKey && (document.activeElement === first || !container.contains(document.activeElement))) {
                    event.preventDefault(); last.focus();
                } else if (!event.shiftKey && (document.activeElement === last || !container.contains(document.activeElement))) {
                    event.preventDefault(); first.focus();
                }
            }

            opener.addEventListener('click', () => setDrawer(!drawerOpen));
            backdrop.addEventListener('click', () => setDrawer(false));
            sidebar.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => setDrawer(false, false));
            });
            logout.addEventListener('click', () => { setDrawer(false, false); setModal(true); });
            modal.addEventListener('click', (event) => {
                if (event.target === modal || event.target.closest('[data-modal-close]')) setModal(false);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    if (modalOpen) setModal(false);
                    else if (drawerOpen) setDrawer(false);
                } else if (event.key === 'Tab') {
                    if (modalOpen) trapFocus(event, modal);
                    else if (drawerOpen) trapFocus(event, sidebar);
                }
            });
            desktop.addEventListener('change', () => {
                if (drawerOpen) {
                    const focusWasInDrawer = sidebar.contains(document.activeElement);
                    setDrawer(false, false);
                    if (desktop.matches) sidebar.querySelector('a')?.focus();
                    else if (focusWasInDrawer) opener.focus();
                } else if (!desktop.matches && !modalOpen && sidebar.contains(document.activeElement)) {
                    opener.focus();
                }
            });
        })();
    </script>
</body>
</html>
