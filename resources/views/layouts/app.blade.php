<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  {{-- PWA Meta Tags --}}
  <meta name="theme-color" content="#0A3D62">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="HRD System">

  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

  <title>{{ $title ?? 'HRD System' }}</title>

  <link rel="icon" href="{{ asset('images/logo-triguna-clean.png') }}" type="image/png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-dark: #0A3D62;
      --primary: #145DA0;
      --primary-light: #1E81B0;
      --accent: #D4AF37;
      --accent-light: #E6C65C;

      --white: #FFFFFF;
      --gray-50: #F5F7FA;
      --gray-100: #F8FAFC;
      --border: #E5E7EB;
      --border-light: #F3F4F6;

      --text-primary: #111827;
      --text-secondary: #374151;
      --text-muted: #6B7280;
      --text-light: #9CA3AF;

      --success: #22C55E;
      --warning: #F59E0B;
      --error: #EF4444;
      --info: #3B82F6;

      /* Legacy mappings for child view compatibility */
      --navy: var(--primary);
      --navy-dark: var(--primary-dark);
      --navy-light: rgba(20, 93, 160, 0.08);
      --navy-muted: var(--primary-light);
      --bg: var(--gray-50);
      --bg-white: var(--white);
      --text: var(--text-primary);
      --danger: var(--error);
      --danger-light: #FEF2F2;

      --sidebar-width: 264px;
      --sidebar-bg: var(--white);
      --sidebar-header-height: 60px;
    }

    * {
      box-sizing: border-box;
    }

    svg:not([width]) {
      width: 1em;
    }

    svg:not([height]) {
      height: 1em;
    }

    body {
      margin: 0;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background: var(--gray-50);
      color: var(--text-primary);
      -webkit-font-smoothing: antialiased;
    }

    .app {
      display: flex;
      min-height: 100vh;
      min-height: 100dvh;
    }

    /* --- SIDEBAR --- */
    /* Flat, quiet navigation: one surface, one accent, no gradients or glow. */
    .sidebar {
      --sb-text: #344054;
      --sb-text-strong: #101828;
      --sb-icon: #667085;
      --sb-label: #98A2B3;
      --sb-hover: #F2F4F7;
      --sb-active-bg: #EAF1F8;
      --sb-active-text: var(--primary-dark);
      --sb-divider: #EAECF0;

      width: var(--sidebar-width);
      flex-shrink: 0;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--sb-divider);
      display: flex;
      flex-direction: column;
      z-index: 1000;
      position: sticky;
      top: 0;
      height: 100vh;
      height: 100dvh;
      overflow: hidden;
      transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1),
                  width .3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Brand Header */
    .sidebar-header {
      height: var(--sidebar-header-height);
      display: flex;
      align-items: center;
      padding: 0 20px;
      flex-shrink: 0;
      gap: 10px;
      min-width: var(--sidebar-width);
    }

    .brand-icon {
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .brand-icon img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .brand-text {
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .brand-title {
      font-size: 0.875rem;
      font-weight: 700;
      color: var(--sb-text-strong);
      letter-spacing: -0.01em;
      line-height: 1.25;
      white-space: nowrap;
    }

    .brand-subtitle {
      font-size: 0.6875rem;
      color: var(--sb-icon);
      font-weight: 500;
      line-height: 1.3;
    }

    /* Scrollable Menu */
    .sidebar-menu {
      flex: 1;
      overflow-y: auto;
      overscroll-behavior: contain;
      padding: 4px 12px 16px;
      min-width: var(--sidebar-width);
      scrollbar-width: thin;
      scrollbar-color: transparent transparent;
    }

    .sidebar-menu:hover {
      scrollbar-color: #D0D5DD transparent;
    }

    .sidebar-menu::-webkit-scrollbar {
      width: 6px;
    }

    .sidebar-menu::-webkit-scrollbar-track {
      background: transparent;
    }

    .sidebar-menu::-webkit-scrollbar-thumb {
      background-color: transparent;
      border-radius: 6px;
    }

    .sidebar-menu:hover::-webkit-scrollbar-thumb {
      background-color: #D0D5DD;
    }

    /* Section */
    .menu-section {
      padding-top: 6px;
    }

    .menu-section + .menu-section {
      margin-top: 10px;
      padding-top: 12px;
      border-top: 1px solid var(--sb-divider);
    }

    .menu-section-title {
      font-size: 0.6875rem;
      font-weight: 600;
      color: var(--sb-label);
      letter-spacing: 0.02em;
      margin: 0 10px 6px;
      line-height: 1.4;
    }

    /* Menu Items (shared by links and group toggles) */
    .menu-item,
    .menu-group-btn {
      display: flex;
      align-items: center;
      gap: 12px;
      width: 100%;
      min-height: 36px;
      padding: 7px 10px;
      margin: 0 0 2px;
      border: none;
      border-radius: 8px;
      background: transparent;
      color: var(--sb-text);
      font-family: inherit;
      font-size: 0.8125rem;
      font-weight: 500;
      line-height: 1.35;
      text-align: left;
      text-decoration: none;
      cursor: pointer;
      transition: background-color .12s ease, color .12s ease;
    }

    .menu-item:hover,
    .menu-group-btn:hover {
      background: var(--sb-hover);
      color: var(--sb-text-strong);
    }

    .menu-item:focus-visible,
    .menu-group-btn:focus-visible,
    .submenu-item:focus-visible,
    .footer-action:focus-visible,
    .btn-logout:focus-visible {
      outline: 2px solid var(--primary);
      outline-offset: -2px;
    }

    .menu-item.active {
      background: var(--sb-active-bg);
      color: var(--sb-active-text);
      font-weight: 600;
    }

    .menu-icon,
    .menu-group-icon {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
      color: var(--sb-icon);
      transition: color .12s ease;
    }

    .menu-icon path,
    .menu-icon rect,
    .menu-icon circle,
    .menu-group-icon path {
      stroke-width: 1.75;
    }

    .menu-item:hover .menu-icon,
    .menu-group-btn:hover .menu-group-icon {
      color: var(--sb-text-strong);
    }

    .menu-item.active .menu-icon {
      color: var(--sb-active-text);
    }

    .menu-text,
    .menu-group-label {
      flex: 1;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* Count badge: calm tint, tabular digits, no glow ring */
    .menu-badge {
      flex-shrink: 0;
      min-width: 22px;
      height: 20px;
      padding: 0 6px;
      border-radius: 6px;
      background: #FEE4E2;
      color: #B42318;
      font-size: 0.6875rem;
      font-weight: 600;
      line-height: 20px;
      text-align: center;
      font-variant-numeric: tabular-nums;
    }

    .menu-item.active .menu-badge {
      background: #FFFFFF;
    }

    /* Collapsible Group */
    .menu-group-btn.has-active {
      color: var(--sb-active-text);
      font-weight: 600;
    }

    .menu-group-btn.has-active .menu-group-icon {
      color: var(--sb-active-text);
    }

    .menu-chevron {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
      color: var(--sb-label);
      transition: transform .2s ease;
    }

    .menu-group-btn.open .menu-chevron {
      transform: rotate(180deg);
    }

    .submenu-panel {
      display: none;
      position: relative;
      margin: 0 0 4px;
      padding-left: 30px;
    }

    /* Guide line sits under the parent icon's centre */
    .submenu-panel::before {
      content: '';
      position: absolute;
      left: 18px;
      top: 2px;
      bottom: 2px;
      width: 1px;
      background: var(--sb-divider);
    }

    .submenu-panel.open {
      display: block;
    }

    .submenu-item {
      display: flex;
      align-items: center;
      gap: 8px;
      min-height: 32px;
      padding: 6px 10px;
      margin-bottom: 1px;
      border-radius: 6px;
      color: var(--sb-text);
      text-decoration: none;
      font-size: 0.8125rem;
      font-weight: 500;
      line-height: 1.35;
      transition: background-color .12s ease, color .12s ease;
    }

    .submenu-text {
      flex: 1;
      min-width: 0;
    }

    .submenu-item:hover {
      background: var(--sb-hover);
      color: var(--sb-text-strong);
    }

    .submenu-item.active {
      background: var(--sb-active-bg);
      color: var(--sb-active-text);
      font-weight: 600;
    }

    /* Sidebar Footer */
    .sidebar-footer {
      flex-shrink: 0;
      padding: 12px;
      border-top: 1px solid var(--sb-divider);
      background: var(--sidebar-bg);
      min-width: var(--sidebar-width);
    }

    .sidebar-user {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 4px 6px 10px;
      min-width: 0;
    }

    .sidebar-user-avatar {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      background: var(--primary-dark);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8125rem;
      font-weight: 700;
      flex-shrink: 0;
    }

    .sidebar-user-details {
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .sidebar-user-name {
      color: var(--sb-text-strong);
      font-size: 0.8125rem;
      font-weight: 600;
      line-height: 1.3;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .sidebar-user-role {
      color: var(--sb-icon);
      font-size: 0.75rem;
      font-weight: 500;
      line-height: 1.3;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .sidebar-footer-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
    }

    .footer-action,
    .btn-logout {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      min-height: 34px;
      padding: 6px 8px;
      border: 1px solid var(--sb-divider);
      border-radius: 8px;
      background: var(--white);
      color: var(--sb-text);
      font-family: inherit;
      font-size: 0.75rem;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      white-space: nowrap;
      transition: background-color .12s ease, border-color .12s ease, color .12s ease;
    }

    .footer-action svg,
    .btn-logout svg {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
    }

    .footer-action:hover {
      background: var(--sb-hover);
      color: var(--sb-text-strong);
    }

    .footer-action.active {
      background: var(--sb-active-bg);
      border-color: transparent;
      color: var(--sb-active-text);
    }

    .btn-logout {
      color: #B42318;
    }

    .btn-logout:hover {
      background: #FEF3F2;
      border-color: #FECDCA;
      color: #912018;
    }

    /* Sidebar Toggle Button */
    .sidebar-toggle {
      display: none;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: var(--white);
      color: var(--primary-dark);
      cursor: pointer;
      transition: background-color .12s ease, border-color .12s ease;
      flex-shrink: 0;
    }

    .sidebar-toggle:hover {
      background: var(--gray-50);
      border-color: #D0D5DD;
    }

    .sidebar-toggle svg {
      width: 18px;
      height: 18px;
    }


    /* --- MAIN CONTENT --- */
    .main-content {
      flex: 1;
      min-width: 0;
      height: 100vh;
      height: 100dvh;
      overflow-y: auto;
      background:
        radial-gradient(circle 250px at 100% 0%, rgba(20, 93, 160, 0.07) 0 99%, transparent 100%),
        radial-gradient(circle 190px at 0% 95%, rgba(20, 93, 160, 0.05) 0 99%, transparent 100%),
        radial-gradient(circle 90px at 92% 62%, rgba(30, 129, 176, 0.05) 0 99%, transparent 100%),
        var(--gray-50);
    }

    .content-wrapper {
      padding: 28px 32px;
      max-width: 1200px;
      margin: 0 auto;
    }

    /* Topbar */
    .topbar {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 28px;
    }

    .page-title {
      font-size: 1.35rem;
      margin: 0;
      font-weight: 700;
      color: var(--text-primary);
      letter-spacing: -0.02em;
    }

    .user-chip {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-left: auto;
      padding: 4px 10px 4px 4px;
      background: var(--bg-white);
      border-radius: 9999px;
      border: 1px solid var(--border);
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
      min-width: 0;
      flex-shrink: 0;
    }

    .user-avatar {
      width: 26px;
      height: 26px;
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.75rem;
      flex-shrink: 0;
    }

    .user-details {
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .user-name {
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--text-primary);
      line-height: 1.2;
      white-space: nowrap;
    }

    .user-role {
      font-size: 0.625rem;
      color: var(--text-muted);
      font-weight: 500;
      line-height: 1.2;
      white-space: nowrap;
    }

    /* Mobile Burger */
    .burger {
      display: none;
      border: none;
      background: var(--white);
      padding: 10px;
      border-radius: 10px;
      cursor: pointer;
      color: var(--primary-dark);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
      border: 1px solid var(--border);
      transition: all 0.15s ease;
    }

    .burger:hover {
      background: var(--gray-50);
    }

    .burger svg {
      display: block;
    }

    /* Mobile Backdrop */
    .backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
      z-index: 999;
      backdrop-filter: blur(3px);
    }

    .backdrop.show {
      opacity: 1;
      pointer-events: auto;
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 1024px) {
      .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        transform: translateX(-100%);
        height: 100vh;
        height: 100dvh;
        width: min(86vw, 320px);
      }

      .sidebar.open {
        transform: translateX(0);
        box-shadow: 0 0 0 1px rgba(16, 24, 40, 0.04), 8px 0 24px rgba(16, 24, 40, 0.12);
      }

      .sidebar-header,
      .sidebar-menu,
      .sidebar-footer {
        min-width: 0;
      }

      .sidebar-header {
        height: 64px;
      }

      .brand-title {
        font-size: 0.9375rem;
      }

      .brand-subtitle {
        font-size: 0.75rem;
      }

      .menu-section-title {
        font-size: 0.75rem;
      }

      /* 44px touch targets */
      .menu-item,
      .menu-group-btn {
        min-height: 44px;
        padding: 10px 12px;
        font-size: 0.9375rem;
        gap: 12px;
      }

      .menu-icon,
      .menu-group-icon {
        width: 20px;
        height: 20px;
      }

      .submenu-panel {
        padding-left: 34px;
      }

      .submenu-panel::before {
        left: 22px;
      }

      .submenu-item {
        min-height: 40px;
        padding: 9px 12px;
        font-size: 0.875rem;
      }

      .sidebar-footer {
        padding: 12px 14px;
        padding-bottom: max(12px, env(safe-area-inset-bottom));
      }

      .footer-action,
      .btn-logout {
        min-height: 44px;
        font-size: 0.875rem;
      }

      .main-content {
        height: 100vh;
        height: 100dvh;
      }

      .content-wrapper {
        padding: 20px 16px;
      }

      .topbar {
        margin-bottom: 20px;
        align-items: center;
        gap: 12px;
      }

      .page-title {
        font-size: 1.15rem;
        flex: 1;
      }

      .user-chip {
        display: none;
      }

      .burger {
        display: flex;
        align-items: center;
        justify-content: center;
      }
    }

    /* Desktop: collapse support */
    @media (min-width: 1025px) {
      .app.sidebar-collapsed .sidebar {
        width: 0;
        border-right-color: transparent;
      }

      .sidebar-toggle {
        display: flex;
      }
    }

    /* Components */
    .card {
      background: var(--bg-white);
      padding: 24px;
      border-radius: 16px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--border-light);
    }

    .modal-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.35);
      justify-content: center;
      align-items: center;
      z-index: 2000;
      padding: 20px;
      backdrop-filter: blur(2px);
    }

    .modal-content {
      background: var(--white);
      padding: 24px;
      border-radius: 16px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
      animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes modalPop {
      from {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }
.toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
    .toast { pointer-events: auto; padding: 14px 20px; border-radius: 12px; font-size: 14px; font-weight: 500; color: #fff; box-shadow: 0 8px 30px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 10px; min-width: 280px; max-width: 400px; animation: toastSlideIn 0.35s ease-out; border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(4px); }
    .toast.hiding { animation: toastSlideOut 0.25s ease-in forwards; }
    .toast-success { background: linear-gradient(135deg, #22C55E, #16A34A); }
    .toast-error { background: linear-gradient(135deg, #EF4444, #DC2626); }
    .toast-warning { background: linear-gradient(135deg, #F59E0B, #D97706); }
    .toast-info { background: linear-gradient(135deg, #3B82F6, #2563EB); }
    .toast-icon { width: 22px; height: 22px; flex-shrink: 0; }
    .toast-close { margin-left: auto; background: none; border: none; color: rgba(255,255,255,0.8); font-size: 18px; line-height: 1; cursor: pointer; padding: 0 0 0 8px; }
    .toast-close:hover { color: #fff; }
    @keyframes toastSlideIn { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes toastSlideOut { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(40px); } }
    @media (max-width: 480px) { .toast-container { top: 10px; right: 10px; left: 10px; } .toast { min-width: unset; max-width: unset; width: 100%; } }

    /* ================= TOAST END ================= */

  </style>

</head>

<body>
  <div class="app">
    <div class="backdrop" id="backdrop"></div>

    <aside class="sidebar" id="sidebar" aria-label="Sidebar">
      @php
      $sidebarUser = auth()->user();
      $formatSidebarBadge = static fn (int $count): string => $count > 99 ? '99+' : (string) $count;
      @endphp

      <!-- Header / Brand -->
      <div class="sidebar-header">
        <div class="brand-icon">
          <img src="{{ asset('images/logo-triguna-clean.png') }}" alt="Triguna Samudratrans" width="30" height="30">
        </div>
        <div class="brand-text">
          <span class="brand-title">Triguna Samudratrans</span>
          <span class="brand-subtitle">HRD System</span>
        </div>
      </div>

      <!-- Menu Navigation -->
      <nav class="sidebar-menu" role="navigation" aria-label="Menu utama">

        {{-- ============================================== --}}
        {{-- BERANDA (All Users) --}}
        {{-- ============================================== --}}
        <div class="menu-section">
          <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="menu-text">Dashboard</span>
          </a>

          <a href="{{ route('v2.access') }}" class="menu-item {{ request()->routeIs('v2.access') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/>
            </svg>
            <span class="menu-text">Pilih Layanan</span>
          </a>
        </div>

        {{-- ============================================== --}}
        {{-- AKTIVITAS SAYA (All Users) — urut dari yang paling sering dipakai --}}
        {{-- ============================================== --}}
        <div class="menu-section">
          <div class="menu-section-title">Aktivitas Saya</div>

          <a href="{{ route('attendance.dashboard') }}" class="menu-item {{ request()->routeIs('attendance.dashboard') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="menu-text">Absensi</span>
          </a>

          <a href="{{ route('leave-requests.index') }}" class="menu-item {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="menu-text">Izin &amp; Cuti</span>
          </a>

          <a href="{{ route('overtime-requests.index') }}" class="menu-item {{ request()->routeIs('overtime-requests.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span class="menu-text">Lembur</span>
          </a>

          <a href="{{ route('remote-attendance.index') }}" class="menu-item {{ request()->routeIs('remote-attendance.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="menu-text">Dinas Luar</span>
          </a>

          @if($sidebarUser->isEligibleForEmployeeLoan())
          <a href="{{ route('employee.loan_requests.index') }}" class="menu-item {{ request()->routeIs('employee.loan_requests.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span class="menu-text">Pengajuan Hutang</span>
          </a>
          @endif
        </div>

        {{-- ============================================== --}}
        {{-- APPROVER PANEL --}}
        {{-- ============================================== --}}
        @if($sidebarUser->isEmployee() && $sidebarUser->hasLeaveApprovalAssignments())
        @php
        $pendingLeaveApprovalCount = app(\App\Services\LeaveApprovalAssignmentService::class)
            ->queryPendingFor($sidebarUser)
            ->count();
        @endphp
        <div class="menu-section">
          <div class="menu-section-title">Approver Panel</div>

          <a href="{{ route('approval.index') }}" class="menu-item {{ request()->routeIs('approval.index', 'approval.show', 'approval.edit') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="menu-text">Approval Izin/Cuti</span>
            @if($pendingLeaveApprovalCount > 0)
            <span class="menu-badge">{{ $formatSidebarBadge($pendingLeaveApprovalCount) }}</span>
            @endif
          </a>
        </div>
        @endif

        {{-- ============================================== --}}
        {{-- MANAGER AREA --}}
        {{-- ============================================== --}}
        @if($sidebarUser->isSupervisor() || $sidebarUser->isManager())
        @php
        $pendingAssignedOvertimeCount = \App\Models\OvertimeRequest::where('status', \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
            ->whereHas('user.overtimeApproverAssignment', function ($q) {
                $q->where('supervisor_id', auth()->id())->orWhere('manager_id', auth()->id());
            })
            ->count();
        @endphp
        @endif
        @if($sidebarUser->isManager())
        <div class="menu-section">
          <div class="menu-section-title">Persetujuan Manager</div>

          <a href="{{ route('approval.index') }}" class="menu-item {{ request()->routeIs('approval.index', 'approval.show') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="menu-text">Approval Izin/Cuti</span>
            @if(isset($notifCount) && $notifCount > 0)
            <span class="menu-badge">{{ $formatSidebarBadge((int) $notifCount) }}</span>
            @endif
          </a>

          <a href="{{ route('overtime-approvals.index') }}" class="menu-item {{ request()->routeIs('overtime-approvals.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="14" r="8" stroke-width="2"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 2h4m-2 0v4m0 8 3-3"/>
            </svg>
            <span class="menu-text">Approval Lembur</span>
            @if($pendingAssignedOvertimeCount > 0)
            <span class="menu-badge" title="Pengajuan ditugaskan yang berstatus menunggu">{{ $formatSidebarBadge($pendingAssignedOvertimeCount) }}</span>
            @endif
          </a>

          <a href="{{ route('supervisor.leave.master') }}" class="menu-item {{ request()->routeIs('supervisor.leave.master') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span class="menu-text">Rekap Izin/Cuti Tim</span>
          </a>
        </div>
        @endif

        {{-- ============================================== --}}
        {{-- SUPERVISOR AREA --}}
        {{-- ============================================== --}}
        @php
        $hasDirectSupervisorOvertimeSubordinates = \App\Models\User::where('direct_supervisor_id', auth()->id())->exists();
        @endphp

        @if($sidebarUser->isSupervisor() || $hasDirectSupervisorOvertimeSubordinates)
        <div class="menu-section">
          <div class="menu-section-title">Persetujuan Supervisor</div>

          @if($sidebarUser->isSupervisor())
          <a href="{{ route('approval.index') }}" class="menu-item {{ request()->routeIs('approval.index', 'approval.show') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="menu-text">Approval Izin/Cuti</span>
            @if(isset($notifCount) && $notifCount > 0)
            <span class="menu-badge">{{ $formatSidebarBadge((int) $notifCount) }}</span>
            @endif
          </a>
          @endif

          @if(!$sidebarUser->isManager())
          @php
          $pendingOvertimeCount = $sidebarUser->isSupervisor() ? $pendingAssignedOvertimeCount : \App\Models\OvertimeRequest::where('status', \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
              ->whereHas('user', function ($q) {
                  $q->where('direct_supervisor_id', auth()->id());
              })
              ->count();
          @endphp
          <a href="{{ route($sidebarUser->isSupervisor() ? 'overtime-approvals.index' : 'supervisor.overtime-requests.index') }}" class="menu-item {{ request()->routeIs('overtime-approvals.*', 'supervisor.overtime-requests.index', 'supervisor.overtime-requests.show') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="14" r="8" stroke-width="2"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 2h4m-2 0v4m0 8 3-3"/>
            </svg>
            <span class="menu-text">Approval Lembur</span>
            @if($pendingOvertimeCount > 0)
            <span class="menu-badge" title="Pengajuan berstatus menunggu">{{ $formatSidebarBadge($pendingOvertimeCount) }}</span>
            @endif
          </a>
          @endif

          @if($sidebarUser->isSupervisor())
          <a href="{{ route('supervisor.leave.master') }}" class="menu-item {{ request()->routeIs('supervisor.leave.master') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="menu-text">Rekap Izin/Cuti Tim</span>
          </a>
          @endif

          <a href="{{ route('supervisor.overtime-requests.master') }}" class="menu-item {{ request()->routeIs('supervisor.overtime-requests.master') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="menu-text">Rekap Lembur Tim</span>
          </a>
        </div>
        @endif

        {{-- ============================================== --}}
        {{-- HRD PANEL — dibagi per tugas: persetujuan → rekap → kepegawaian → pengaturan --}}
        {{-- ============================================== --}}
        @if(!$sidebarUser->isHR() && $sidebarUser->canViewTeamAttendance())
        <div class="menu-section">
          <a href="{{ route('supervisor.attendances.index') }}" class="menu-item {{ request()->routeIs('supervisor.attendances.index') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="menu-text">Daftar Absen</span>
          </a>
        </div>
        @endif

        @if($sidebarUser->isHR())
        @php
        $hrEmployeesOpen = request()->routeIs('hr.employees.*','hr.approvers.*','hr.organization','hr.divisions.*','hr.positions.*','hr.pts.*');
        $hrPresensiOpen = request()->routeIs('hr.shifts.*','hr.shift-rotations.*','hr.locations.*','hr.schedules.*','hr.office-holidays.*');
        $hrAssetOpen = request()->routeIs('hr.assets.*');
        $pendingAttendanceApprovalCount = \Illuminate\Support\Facades\Schema::hasColumn('attendance_events', 'is_flexible')
          && \Illuminate\Support\Facades\Schema::hasColumn('attendance_events', 'is_outside_reference_location')
          ? \App\Models\AttendanceEvent::query()->pendingApproval()->where('type', 'WFO')->count()
          : 0;
        $pendingLoanCount = \App\Models\LoanRequest::where('status', 'PENDING_HRD')->count();
        @endphp

        {{-- HRD: Persetujuan (semua item yang butuh tindakan, lengkap dengan badge) --}}
        <div class="menu-section">
          <div class="menu-section-title">Persetujuan HRD</div>

          <a href="{{ route('hr.leave.index') }}" class="menu-item {{ request()->routeIs('hr.leave.index','hr.leave.show','hr.leave.approve','hr.leave.reject') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13l2 2 4-4"/>
            </svg>
            <span class="menu-text">Approval Izin/Cuti</span>
            @if(isset($notifCount) && $notifCount > 0)
            <span class="menu-badge">{{ $formatSidebarBadge((int) $notifCount) }}</span>
            @endif
          </a>

          <a href="{{ route('hr.approval_attendance.index') }}" class="menu-item {{ request()->routeIs('hr.approval_attendance.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="menu-text">Approval Absensi</span>
            @if($pendingAttendanceApprovalCount > 0)
            <span class="menu-badge" data-attendance-approval-badge aria-label="{{ $pendingAttendanceApprovalCount }} karyawan menunggu persetujuan absensi">{{ $formatSidebarBadge($pendingAttendanceApprovalCount) }}</span>
            @endif
          </a>

          <a href="{{ route('hr.loan_requests.index') }}" class="menu-item {{ request()->routeIs('hr.loan_requests.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span class="menu-text">Approval Hutang</span>
            @if($pendingLoanCount > 0)
            <span class="menu-badge" aria-label="{{ $pendingLoanCount }} pengajuan hutang menunggu HRD">{{ $formatSidebarBadge($pendingLoanCount) }}</span>
            @endif
          </a>
        </div>

        {{-- HRD: Master data transaksi --}}
        <div class="menu-section">
          <div class="menu-section-title">Master Data</div>

          <a href="{{ route('hr.attendances.index') }}" class="menu-item {{ request()->routeIs('hr.attendances.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="menu-text">Master Absensi</span>
          </a>

          <a href="{{ route('hr.leave.master') }}" class="menu-item {{ request()->routeIs('hr.leave.master') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6"/>
            </svg>
            <span class="menu-text">Master Izin/Cuti</span>
          </a>

          <a href="{{ route('hr.overtime-requests.master') }}" class="menu-item {{ request()->routeIs('hr.overtime-requests.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <rect x="3" y="5" width="18" height="16" rx="2" stroke-width="2"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3v4m10-4v4M3 10h18m-6 4v3l2 1"/>
            </svg>
            <span class="menu-text">Master Lembur</span>
          </a>
        </div>

        {{-- HRD: Kepegawaian --}}
        <div class="menu-section">
          <div class="menu-section-title">Kepegawaian</div>

          <button type="button" class="menu-group-btn {{ $hrEmployeesOpen ? 'open has-active' : '' }}" data-menu-group="employees" aria-expanded="{{ $hrEmployeesOpen ? 'true' : 'false' }}" aria-controls="menu-panel-employees">
            <svg class="menu-group-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="menu-group-label">Karyawan</span>
            <svg class="menu-chevron" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div class="submenu-panel {{ $hrEmployeesOpen ? 'open' : '' }}" id="menu-panel-employees" data-menu-panel="employees">
            <a href="{{ route('hr.employees.index') }}" class="submenu-item {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}">Daftar Karyawan</a>
            <a href="{{ route('hr.approvers.index') }}" class="submenu-item {{ request()->routeIs('hr.approvers.*') ? 'active' : '' }}">Daftar Approver</a>
            <a href="{{ route('hr.organization') }}" class="submenu-item {{ request()->routeIs('hr.organization','hr.divisions.*','hr.positions.*') ? 'active' : '' }}">Divisi &amp; Jabatan</a>
            <a href="{{ route('hr.pts.index') }}" class="submenu-item {{ request()->routeIs('hr.pts.*') ? 'active' : '' }}">Daftar PT</a>
          </div>

          <a href="{{ route('hr.supervisors.index') }}" class="menu-item {{ request()->routeIs('hr.supervisors.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span class="menu-text">Data Supervisor</span>
          </a>

          <a href="{{ route('hr.assets.index') }}" class="menu-item {{ $hrAssetOpen ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4m16 0v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7m16 0l-2-3H6L4 7m8 4v4"/>
            </svg>
            <span class="menu-text">Inventaris Karyawan</span>
          </a>
        </div>

        {{-- HRD: Pengaturan (jarang diubah, ditaruh paling bawah) --}}
        <div class="menu-section">
          <div class="menu-section-title">Pengaturan HRD</div>

          <button type="button" class="menu-group-btn {{ $hrPresensiOpen ? 'open has-active' : '' }}" data-menu-group="presensi" aria-expanded="{{ $hrPresensiOpen ? 'true' : 'false' }}" aria-controls="menu-panel-presensi">
            <svg class="menu-group-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="menu-group-label">Jadwal &amp; Shift</span>
            <svg class="menu-chevron" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div class="submenu-panel {{ $hrPresensiOpen ? 'open' : '' }}" id="menu-panel-presensi" data-menu-panel="presensi">
            <a href="{{ route('hr.schedules.index') }}" class="submenu-item {{ request()->routeIs('hr.schedules.*') ? 'active' : '' }}">Jadwal Karyawan</a>
            <a href="{{ route('hr.shifts.index') }}" class="submenu-item {{ request()->routeIs('hr.shifts.*') ? 'active' : '' }}">Jam Kerja (Shift)</a>
            <a href="{{ route('hr.shift-rotations.index') }}" class="submenu-item {{ request()->routeIs('hr.shift-rotations.*') ? 'active' : '' }}">Jadwal Rotasi Kerja</a>
            <a href="{{ route('hr.locations.index') }}" class="submenu-item {{ request()->routeIs('hr.locations.*') ? 'active' : '' }}">Lokasi Absensi</a>
            <a href="{{ route('hr.office-holidays.index') }}" class="submenu-item {{ request()->routeIs('hr.office-holidays.*') ? 'active' : '' }}">Hari Libur Kantor</a>
          </div>

          @can('manage-payroll')
          <a href="{{ route('hr.payroll.index') }}" class="menu-item {{ request()->routeIs('hr.payroll.*') ? 'active' : '' }}">
            <svg class="menu-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
            </svg>
            <span class="menu-text">Slip Gaji Karyawan</span>
          </a>
          @endcan
        </div>
        @endif

      </nav>

      <!-- Footer / Akun -->
      <div class="sidebar-footer">
        <div class="sidebar-user">
          <div class="sidebar-user-avatar" aria-hidden="true">
            {{ mb_strtoupper(mb_substr($sidebarUser->name, 0, 1)) }}
          </div>
          <div class="sidebar-user-details">
            <span class="sidebar-user-name">{{ $sidebarUser->name }}</span>
            <span class="sidebar-user-role">{{ $sidebarUser->role->label() }}</span>
          </div>
        </div>

        <div class="sidebar-footer-actions">
          <a href="{{ route('settings.password') }}" class="footer-action {{ request()->routeIs('settings.password') ? 'active' : '' }}" title="Pengaturan Akun">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Pengaturan
          </a>
          <button class="btn-logout" type="button" data-modal-target="confirm-logout">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Keluar
          </button>
        </div>
      </div>

    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="content-wrapper">
        <div class="topbar">
          <button class="burger" id="burger" aria-label="Toggle menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" title="Toggle sidebar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          @isset($header)
            {{ $header }}
          @else
            <h2 class="page-title">{{ $title ?? 'Dashboard' }}</h2>
          @endisset

        </div>

        {{ $slot }}
        <div class="toast-container" id="toastContainer"></div>
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
    const sidebar = document.getElementById('sidebar');
    const burger = document.getElementById('burger');
    const backdrop = document.getElementById('backdrop');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const appRoot = document.querySelector('.app');
    const sidebarMenu = document.querySelector('.sidebar-menu');
    const sidebarScrollStorageKey = 'sidebar-scroll-{{ auth()->id() }}';

    if (sidebarMenu) {
      const savedScrollTop = Number(sessionStorage.getItem(sidebarScrollStorageKey));
      if (Number.isFinite(savedScrollTop)) sidebarMenu.scrollTop = savedScrollTop;

      function persistSidebarScroll() {
        sessionStorage.setItem(sidebarScrollStorageKey, String(sidebarMenu.scrollTop));
      }

      sidebarMenu.addEventListener('scroll', persistSidebarScroll, { passive: true });
    }

    // Mobile Menu Toggle
    function toggleMobile() {
      const isOpen = sidebar.classList.contains('open');
      if (isOpen) {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
        document.body.style.overflow = '';
      } else {
        sidebar.classList.add('open');
        backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
      }
    }

    if (burger) burger.addEventListener('click', toggleMobile);
    if (backdrop) backdrop.addEventListener('click', toggleMobile);

    // Desktop Sidebar Collapse Toggle
    function toggleDesktopSidebar() {
      const isCollapsed = appRoot.classList.contains('sidebar-collapsed');
      if (isCollapsed) {
        appRoot.classList.remove('sidebar-collapsed');
        if (sidebarToggle) sidebarToggle.setAttribute('title', 'Sembunyikan sidebar');
      } else {
        appRoot.classList.add('sidebar-collapsed');
        if (sidebarToggle) sidebarToggle.setAttribute('title', 'Tampilkan sidebar');
      }
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', toggleDesktopSidebar);

    document.addEventListener('change', function(event) {
      const input = event.target.closest('input[type="file"][data-max-file-size]');
      if (!input || !input.files || input.files.length === 0) return;

      const file = input.files[0];
      const maxBytes = Number(input.dataset.maxFileSize || 0);
      const maxLabel = input.dataset.maxFileLabel || '8 MB';

      if (maxBytes > 0 && file.size > maxBytes) {
        window.showToast(`Ukuran file "${file.name}" melebihi batas maksimal ${maxLabel}. Silakan pilih file yang lebih kecil.`, 'warning');
        input.value = '';
      }
    }, true);

    // Modal Logic
    document.addEventListener('DOMContentLoaded', function() {
      function toggleModal(id, show) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = show ? 'flex' : 'none';
        const sidebarOpen = sidebar && sidebar.classList.contains('open');
        if (!sidebarOpen) {
          document.body.style.overflow = show ? 'hidden' : '';
        }
      }

      document.querySelectorAll('[data-modal-target]').forEach(btn => {
        btn.addEventListener('click', () => toggleModal(btn.dataset.modalTarget, true));
      });

      document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
          if (e.target === modal || e.target.closest('[data-modal-close]')) {
            toggleModal(modal.id, false);
          }
        });
      });

      // Sidebar collapsible groups (independent; state reflected via aria-expanded)
      function setMenuGroupOpen(btn, open) {
        const panel = document.querySelector(`[data-menu-panel="${btn.dataset.menuGroup}"]`);
        btn.classList.toggle('open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (panel) panel.classList.toggle('open', open);
      }

      document.querySelectorAll('.menu-group-btn').forEach(btn => {
        btn.addEventListener('click', () => setMenuGroupOpen(btn, !btn.classList.contains('open')));
      });
    });

    // ================= TOAST NOTIFICATION =================
    const toastContainer = document.getElementById('toastContainer');
    const toastIcons = {
      success: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
      error: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
      warning: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
      info: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    };

    function showToast(message, type = 'info', duration = 4000) {
      if (!toastContainer) return;
      const toast = document.createElement('div');
      toast.className = 'toast toast-' + type;
      toast.innerHTML = toastIcons[type] + '<span style="flex:1">' + escapeHtml(message) + '</span>' +
        '<button class="toast-close" onclick="this.parentElement.remove()">&times;</button>';
      toastContainer.appendChild(toast);
      setTimeout(() => {
        toast.classList.add('hiding');
        toast.addEventListener('animationend', () => toast.remove());
      }, duration);
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // Global toast helper for inline scripts
    window.showToast = showToast;

    function requiredFieldLabel(field) {
      const label = field.id
        ? Array.from(document.querySelectorAll('label')).find(item => item.getAttribute('for') === field.id)
        : null;
      const wrapperLabel = label || field.closest('.form-group, .field, .lrc-field, .lr-field, div')?.querySelector('label');
      const text = wrapperLabel?.textContent || field.getAttribute('aria-label') || field.getAttribute('placeholder') || field.name || 'Field ini';

      return text.replace(/\*/g, '').replace(/\s+/g, ' ').trim() || 'Field ini';
    }

    let lastRequiredToastAt = 0;
    document.addEventListener('invalid', function(event) {
      const field = event.target;
      if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) return;
      if (!field.validity.valueMissing) return;

      event.preventDefault();

      const now = Date.now();
      if (now - lastRequiredToastAt < 300) return;
      lastRequiredToastAt = now;

      showToast(requiredFieldLabel(field) + ' wajib diisi.', 'warning', 5000);
      field.scrollIntoView({ block: 'center', behavior: 'smooth' });
      field.focus({ preventScroll: true });
    }, true);

    // Handle Laravel flash messages from session
    @if(session('success'))
      showToast('{{ addslashes(session('success')) }}', 'success', 4000);
    @endif
    @if(session('error'))
      showToast('{{ addslashes(session('error')) }}', 'error', 5000);
    @endif
    @if(session('warning'))
      showToast('{{ addslashes(session('warning')) }}', 'warning', 5000);
    @endif
    @if(session('info'))
      showToast('{{ addslashes(session('info')) }}', 'info', 4000);
    @endif

    // Handle validation errors (convert to toast)
    @if($errors->any())
      @foreach($errors->all() as $error)
        showToast('{{ addslashes($error) }}', 'warning', 6000);
      @endforeach
    @endif
  </script>

  <x-image-viewer />

  @stack('scripts')
  {{-- PWA dinonaktifkan sementara; bersihkan Service Worker dan cache lama. --}}
  <script type="module" src="{{ asset('js/disable-pwa.js') }}"></script>

</body>

</html>
