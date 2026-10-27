@php
    $atkPendingRequestCount = 0;
    $atkPendingNeedRequestCount = 0;

    if (auth()->check() && auth()->user()->canManageAtk()) {
        $atkPendingRequestCount = \App\Models\AtkRequest::query()
            ->forModule(\App\Models\AtkRequest::MODULE_ATK)
            ->where('status', \App\Models\AtkRequest::STATUS_PENDING)
            ->count();

        $atkPendingNeedRequestCount = \App\Models\AtkNeedRequest::query()
            ->where('status', \App\Models\AtkNeedRequest::STATUS_PENDING)
            ->count();
    }
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kebutuhan Kantor' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        svg:not([width]) { width: 1em; }
        svg:not([height]) { height: 1em; }
        html, body { overflow-x: clip; }
        body { margin: 0; }

        .atk-shell {
            --atk-primary: #6D28D9;
            --atk-primary-dark: #5B21B6;
            --atk-primary-soft: #F3EEFF;
            --atk-primary-softer: #FAF8FF;
            --atk-bg: #F6F7F9;
            --atk-surface: #FFFFFF;
            --atk-text: #111827;
            --atk-text-secondary: #374151;
            --atk-muted: #6B7280;
            --atk-border: #E5E7EB;
            --atk-line: #E5E7EB;
            --atk-border-soft: #F3F4F6;
            --atk-shadow: 0 1px 3px rgba(17, 24, 39, 0.05);
            --success: #22C55E;
            --warning: #F59E0B;
            --error: #EF4444;
            min-height: 100dvh;
            display: block;
            background: var(--atk-bg);
            color: var(--atk-text);
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .atk-shell * { box-sizing: border-box; }

        /* ========== SIDEBAR ========== */
        .atk-sidebar {
            width: min(82vw, 256px);
            height: 100dvh;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform .2s ease;
            border-right: 1px solid var(--atk-border);
            background: var(--atk-surface);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .atk-sidebar.open {
            transform: translateX(0);
            box-shadow: 0 12px 32px rgba(17, 24, 39, 0.14);
        }
        .atk-backdrop {
            position: fixed;
            inset: 0;
            z-index: 999;
            display: none;
            background: rgba(17, 24, 39, .35);
            backdrop-filter: blur(2px);
        }
        .atk-backdrop.show { display: block; }

        .atk-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--atk-border-soft);
        }
        .atk-brand-mark {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--atk-primary-soft);
            color: var(--atk-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .atk-brand-mark svg { width: 17px; height: 17px; }
        .atk-brand-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--atk-text);
            line-height: 1.25;
        }
        .atk-brand-subtitle {
            margin: 1px 0 0;
            font-size: 11px;
            color: var(--atk-muted);
            font-weight: 500;
        }

        .atk-menu {
            flex: 1;
            padding: 8px 10px;
            overflow-y: auto;
            display: block;
        }
        .atk-menu-title {
            margin: 12px 8px 4px;
            font-size: 10px;
            color: #9CA3AF;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .atk-menu-title:first-child { margin-top: 4px; }

        .atk-menu-item {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 36px;
            padding: 6px 10px;
            border-radius: 8px;
            margin-bottom: 2px;
            color: var(--atk-text-secondary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: background-color .12s ease, color .12s ease;
        }
        .atk-icon-sprite {
            position: absolute;
            width: 0;
            height: 0;
            overflow: hidden;
        }
        .atk-menu-icon {
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
        .atk-menu-item:hover {
            color: var(--atk-primary-dark);
            background: var(--atk-primary-softer);
        }
        .atk-menu-item.active {
            color: var(--atk-primary-dark);
            background: var(--atk-primary-soft);
            font-weight: 600;
        }
        .atk-menu-item.active .atk-menu-icon { opacity: 1; }

        .atk-menu-badge {
            margin-left: auto;
            background: var(--error);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 999px;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
        }

        .atk-sidebar-footer {
            padding: 10px 12px;
            border-top: 1px solid var(--atk-border-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .atk-user {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex: 1;
        }
        .atk-user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: var(--atk-primary-soft);
            color: var(--atk-primary-dark);
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .atk-user-info { min-width: 0; }
        .atk-user-name {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--atk-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .atk-user-role {
            font-size: 11px;
            color: var(--atk-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .atk-logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 32px;
            padding: 0 10px;
            border: 1px solid var(--atk-border);
            border-radius: 8px;
            background: var(--atk-surface);
            color: var(--atk-text-secondary);
            font: inherit;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            flex-shrink: 0;
            transition: all .15s ease;
        }
        .atk-logout-btn:hover {
            background: #FEF2F2;
            border-color: #FECACA;
            color: #B91C1C;
        }

        /* ========== MAIN & TOPBAR ========== */
        .atk-main { min-width: 0; min-height: 100dvh; }
        .atk-topbar {
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
            border-bottom: 1px solid var(--atk-border);
            backdrop-filter: blur(8px);
        }
        .atk-burger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border: 1px solid var(--atk-border);
            border-radius: 9px;
            background: var(--atk-surface);
            color: var(--atk-text);
            cursor: pointer;
        }
        .atk-burger svg { width: 18px; height: 18px; }
        .atk-topbar-title { min-width: 0; flex: 1; }
        .atk-topbar-title strong {
            display: block;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.25;
        }
        .atk-topbar-title span { display: none; }

        .atk-content {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 14px 14px 72px;
        }

        /* ========== SHARED UI ELEMENTS ========== */
        .atk-header {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
            flex-direction: column;
        }
        .atk-title { margin: 0; font-size: 18px; font-weight: 700; letter-spacing: -0.01em; overflow-wrap: anywhere; }
        .atk-subtitle { margin: 2px 0 0; color: var(--atk-muted); font-size: 12.5px; }

        .atk-card {
            background: var(--atk-surface);
            border: 1px solid var(--atk-border);
            border-radius: 12px;
            padding: 14px;
            box-shadow: var(--atk-shadow);
        }
        .atk-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .atk-table-wrap { overflow-x: auto; background: var(--atk-surface); border: 1px solid var(--atk-border); border-radius: 12px; }
        .atk-table { width: 100%; border-collapse: collapse; min-width: 720px; }
        .atk-table th, .atk-table td { padding: 10px 14px; border-bottom: 1px solid var(--atk-border-soft); text-align: left; font-size: 13px; }
        .atk-table th { color: var(--atk-muted); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; background: var(--atk-primary-softer); border-bottom: 1px solid var(--atk-border); }
        .atk-table tr:last-child td { border-bottom: 0; }

        .atk-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 38px;
            padding: 0 14px;
            border: 0;
            border-radius: 10px;
            text-decoration: none;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
        }
        .atk-btn:focus-visible,
        .atk-input:focus,
        .atk-select:focus,
        .atk-textarea:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(109, 40, 217, .14);
            border-color: var(--atk-primary);
        }
        .atk-btn-primary { color: #fff; background: var(--atk-primary); }
        .atk-btn-primary:hover { background: var(--atk-primary-dark); }
        .atk-btn-secondary { color: var(--atk-primary-dark); background: var(--atk-primary-soft); }
        .atk-btn-secondary:hover { background: #E9DFFA; }
        .atk-btn-muted { color: var(--atk-text-secondary); background: #F3F4F6; }
        .atk-btn-danger { color: #fff; background: var(--error); }
        .atk-btn[disabled] { cursor: not-allowed; opacity: .55; }

        .atk-cart-shortcut {
            position: relative;
            width: 36px;
            min-height: 36px;
            padding: 0;
            border-radius: 9px;
        }
        .atk-cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border: 2px solid var(--atk-surface);
            border-radius: 999px;
            background: var(--error);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            line-height: 14px;
            text-align: center;
        }

        .atk-input, .atk-select, .atk-textarea {
            width: 100%;
            min-height: 38px;
            border: 1.5px solid var(--atk-border);
            border-radius: 10px;
            padding: 0 12px;
            font: inherit;
            font-size: 13px;
            background: #fff;
            color: var(--atk-text);
        }
        .atk-input[type="file"] { padding: 7px 12px; }
        .atk-textarea { padding: 10px 12px; min-height: 96px; resize: vertical; }
        .atk-label { display: block; margin-bottom: 5px; font-size: 12px; color: var(--atk-text-secondary); font-weight: 600; }
        .atk-form-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }

        .atk-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        .atk-badge-success { color: #15803D; background: rgba(34,197,94,.12); }
        .atk-badge-warning { color: #B45309; background: rgba(245,158,11,.13); }
        .atk-badge-error { color: #B91C1C; background: rgba(239,68,68,.12); }
        .atk-badge-neutral { color: #4B5563; background: #F3F4F6; }
        .atk-badge-brand { color: var(--atk-primary-dark); background: var(--atk-primary-soft); }

        .atk-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .atk-alert { padding: 10px 14px; border-radius: 10px; margin-bottom: 12px; font-size: 13px; font-weight: 600; }
        .atk-alert-success { color: #15803D; background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.25); }
        .atk-alert-warning { color: #B45309; background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.25); }
        .atk-empty { text-align: center; padding: 32px 18px; color: var(--atk-muted); font-size: 13px; }

        .atk-product { display: grid; gap: 10px; padding: 12px; }
        .atk-product-media {
            aspect-ratio: 4 / 3;
            border-radius: 10px;
            background: #F8F7FC;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #9CA3AF;
            font-weight: 700;
        }
        .atk-product-media img { width: 100%; height: 100%; object-fit: cover; }
        .atk-product-title { margin: 0; font-size: 13.5px; font-weight: 700; line-height: 1.35; }
        .atk-product-meta { margin: 0; color: var(--atk-muted); font-size: 12px; }
        .atk-product-stock { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .atk-qty-form { display: grid; grid-template-columns: 92px minmax(0, 1fr); gap: 8px; }
        .atk-stat-card strong { display: block; font-size: 24px; line-height: 1; margin-bottom: 6px; }
        .atk-section-title { margin: 0 0 10px; font-size: 15px; font-weight: 700; }

        .atk-shell .hrd-page-btn--active {
            background: var(--atk-primary);
            border-color: var(--atk-primary);
            color: #fff;
        }

        @media (min-width: 640px) {
            .atk-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .atk-form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .atk-header { align-items: center; flex-direction: row; }
        }

        @media (min-width: 1024px) {
            .atk-shell { display: flex; }
            .atk-sidebar {
                width: 244px;
                height: 100dvh;
                position: sticky;
                top: 0;
                inset: auto;
                transform: none;
                flex-shrink: 0;
            }
            .atk-backdrop,
            .atk-topbar {
                display: none;
            }
            .atk-menu {
                display: block;
                overflow-x: visible;
                overflow-y: auto;
            }
            .atk-main { flex: 1; height: 100dvh; overflow-y: auto; }
            .atk-content { padding: 22px 26px 72px; }
            .atk-grid { grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); }
        }
    </style>
</head>
<body>
    <div class="atk-shell">
        <div class="atk-backdrop" id="atkBackdrop" aria-hidden="true"></div>
        <aside class="atk-sidebar" id="atkSidebar">
            <svg class="atk-icon-sprite" aria-hidden="true">
                <symbol id="atk-icon-catalog" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></symbol>
                <symbol id="atk-icon-cart" viewBox="0 0 24 24"><path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.5 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></symbol>
                <symbol id="atk-icon-request" viewBox="0 0 24 24"><path d="M9 5h6"/><path d="M9 3h6a2 2 0 0 1 2 2v1h2v15H5V6h2V5a2 2 0 0 1 2-2Z"/><path d="M9 11h6M9 15h6"/></symbol>
                <symbol id="atk-icon-history" viewBox="0 0 24 24"><path d="M4 7h16v13H4z"/><path d="M3 4h18v3H3zM9 11h6"/></symbol>
                <symbol id="atk-icon-add" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></symbol>
                <symbol id="atk-icon-inbox" viewBox="0 0 24 24"><path d="M4 4h16l2 10v6H2v-6L4 4Z"/><path d="M2 14h6l2 3h4l2-3h6"/></symbol>
                <symbol id="atk-icon-restock" viewBox="0 0 24 24"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/><path d="M12 11v10M16 14h4M18 12v4"/></symbol>
                <symbol id="atk-icon-items" viewBox="0 0 24 24"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></symbol>
                <symbol id="atk-icon-stock" viewBox="0 0 24 24"><path d="M7 3v15M3 7l4-4 4 4M17 21V6M13 17l4 4 4-4"/></symbol>
                <symbol id="atk-icon-report" viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></symbol>
                <symbol id="atk-icon-access" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M16 11h6M19 8v6"/></symbol>
                <symbol id="atk-icon-portal" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
                <symbol id="atk-icon-hrd" viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h1M14 10h1M9 14h1M14 14h1M10 21v-3h4v3"/></symbol>
            </svg>
            <div class="atk-brand">
                <div class="atk-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#atk-icon-items"/>
                    </svg>
                </div>
                <div>
                    <h1 class="atk-brand-title">Kebutuhan Kantor</h1>
                    <p class="atk-brand-subtitle">Modul ATK</p>
                </div>
            </div>
            <nav class="atk-menu">
                <div class="atk-menu-title">Menu</div>
                <a class="atk-menu-item {{ request()->routeIs('v2.atk.catalog') ? 'active' : '' }}" href="{{ route('v2.atk.catalog') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-catalog"/></svg><span>Katalog</span></a>
                <a class="atk-menu-item {{ request()->routeIs('v2.atk.cart.*') ? 'active' : '' }}" href="{{ route('v2.atk.cart.show') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-cart"/></svg><span>Keranjang</span></a>
                <a class="atk-menu-item {{ request()->routeIs('v2.atk.requests.*') ? 'active' : '' }}" href="{{ route('v2.atk.requests.index') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-request"/></svg><span>Pengajuan Saya</span></a>
                <a class="atk-menu-item {{ request()->routeIs('v2.atk.need-requests.index') ? 'active' : '' }}" href="{{ route('v2.atk.need-requests.index') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-history"/></svg><span>Pengajuan Barang Saya</span></a>
                <a class="atk-menu-item {{ request()->routeIs('v2.atk.need-requests.create') ? 'active' : '' }}" href="{{ route('v2.atk.need-requests.create') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-add"/></svg><span>Ajukan Barang</span></a>

                @if(auth()->user()->canManageAtk())
                    <div class="atk-menu-title">Admin ATK</div>
                    <a class="atk-menu-item {{ request()->routeIs('v2.atk.admin.items.*') ? 'active' : '' }}" href="{{ route('v2.atk.admin.items.index') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-items"/></svg><span>Master Barang</span></a>
                    <a class="atk-menu-item {{ request()->routeIs('v2.atk.admin.requests.*') ? 'active' : '' }}" href="{{ route('v2.atk.admin.requests.index') }}">
                        <svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-inbox"/></svg>
                        <span>Request Masuk</span>
                        @if($atkPendingRequestCount > 0)
                            <span class="atk-menu-badge">{{ $atkPendingRequestCount > 99 ? '99+' : $atkPendingRequestCount }}</span>
                        @endif
                    </a>
                    <a class="atk-menu-item {{ request()->routeIs('v2.atk.admin.need-requests.*') ? 'active' : '' }}" href="{{ route('v2.atk.admin.need-requests.index') }}">
                        <svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-restock"/></svg>
                        <span>Pengajuan Barang</span>
                        @if($atkPendingNeedRequestCount > 0)
                            <span class="atk-menu-badge">{{ $atkPendingNeedRequestCount > 99 ? '99+' : $atkPendingNeedRequestCount }}</span>
                        @endif
                    </a>
                    <a class="atk-menu-item {{ request()->routeIs('v2.atk.admin.stock-movements.*') ? 'active' : '' }}" href="{{ route('v2.atk.admin.stock-movements.index') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-stock"/></svg><span>Riwayat Stok</span></a>
                    <a class="atk-menu-item {{ request()->routeIs('v2.atk.admin.reports.*') ? 'active' : '' }}" href="{{ route('v2.atk.admin.reports.index') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-report"/></svg><span>Rekap PT</span></a>
                    <a class="atk-menu-item {{ request()->routeIs('v2.atk.admin.access.*') ? 'active' : '' }}" href="{{ route('v2.atk.admin.access.index') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-access"/></svg><span>Akses</span></a>
                @endif

                <div class="atk-menu-title">Pindah Layanan</div>
                <a class="atk-menu-item" href="{{ route('v2.access') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-portal"/></svg><span>Pilih Layanan</span></a>
                <a class="atk-menu-item" href="{{ route('dashboard') }}"><svg class="atk-menu-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#atk-icon-hrd"/></svg><span>HRD System</span></a>
            </nav>
            <div class="atk-sidebar-footer">
                <div class="atk-user">
                    <div class="atk-user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="atk-user-info">
                        <div class="atk-user-name">{{ auth()->user()->name }}</div>
                        <div class="atk-user-role">{{ auth()->user()->role->label() }}</div>
                    </div>
                </div>
                <button class="atk-logout-btn" type="button" data-modal-target="confirm-logout" aria-haspopup="dialog" aria-controls="confirm-logout">Keluar</button>
            </div>
        </aside>
        <main class="atk-main">
            <div class="atk-topbar">
                <button class="atk-burger" id="atkBurger" type="button" aria-label="Buka menu ATK" aria-controls="atkSidebar" aria-expanded="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="atk-topbar-title">
                    <strong>Kebutuhan Kantor</strong>
                    <span>Modul ATK</span>
                </div>
                @php($atkCartCount = array_sum(session('atk_cart', [])))
                <a class="atk-btn atk-btn-secondary atk-cart-shortcut" href="{{ route('v2.atk.cart.show') }}" aria-label="Buka keranjang, {{ $atkCartCount }} item" title="Keranjang">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/>
                        <circle cx="10" cy="20" r="1"/>
                        <circle cx="18" cy="20" r="1"/>
                    </svg>
                    @if($atkCartCount > 0)
                        <span class="atk-cart-badge" aria-hidden="true">{{ $atkCartCount > 99 ? '99+' : $atkCartCount }}</span>
                    @endif
                </a>
            </div>
            <div class="atk-content">
                @if(session('success'))
                    <div class="atk-alert atk-alert-success">{{ session('success') }}</div>
                @endif
                @if(session('warning'))
                    <div class="atk-alert atk-alert-warning">{{ session('warning') }}</div>
                @endif
                @if($errors->any())
                    <div class="atk-alert atk-alert-warning">{{ $errors->first() }}</div>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>
    <x-modal
        id="confirm-logout"
        title="Keluar dari Sistem?"
        type="confirm"
        variant="danger"
        confirmLabel="Ya, Keluar"
        cancelLabel="Batal"
        :confirmFormAction="route('logout')"
        confirmFormMethod="POST">
        <p style="margin:0;">Apakah Anda yakin ingin mengakhiri sesi ini?</p>
    </x-modal>
    <script>
        (function () {
            const sidebar = document.getElementById('atkSidebar');
            const burger = document.getElementById('atkBurger');
            const backdrop = document.getElementById('atkBackdrop');
            const logoutModal = document.getElementById('confirm-logout');
            const logoutTrigger = document.querySelector('[data-modal-target="confirm-logout"]');

            if (!sidebar || !burger || !backdrop) return;

            function setOpen(open) {
                sidebar.classList.toggle('open', open);
                backdrop.classList.toggle('show', open);
                burger.setAttribute('aria-expanded', open ? 'true' : 'false');
                document.body.style.overflow = open ? 'hidden' : '';
            }

            function setLogoutOpen(open) {
                if (!logoutModal) return;
                logoutModal.style.display = open ? 'flex' : 'none';
                document.body.style.overflow = open ? 'hidden' : '';
            }

            burger.addEventListener('click', () => setOpen(!sidebar.classList.contains('open')));
            backdrop.addEventListener('click', () => setOpen(false));
            sidebar.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => setOpen(false));
            });
            logoutTrigger?.addEventListener('click', () => {
                setOpen(false);
                setLogoutOpen(true);
            });
            logoutModal?.addEventListener('click', (event) => {
                if (event.target === logoutModal || event.target.closest('[data-modal-close]')) setLogoutOpen(false);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    setOpen(false);
                    setLogoutOpen(false);
                }
            });
        })();
    </script>
</body>
</html>
