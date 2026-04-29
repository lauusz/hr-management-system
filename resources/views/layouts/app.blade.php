<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  {{-- PWA Meta Tags --}}
  <meta name="theme-color" content="#0A3D62">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="HRD System">

  <link rel="manifest" href="/hrd/manifest.json">
  <link rel="apple-touch-icon" href="/hrd/images/icons/icon-192x192.png">

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
    .sidebar {
      width: var(--sidebar-width);
      flex-shrink: 0;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      z-index: 1000;
      position: sticky;
      top: 0;
      height: 100vh;
      height: 100dvh;
      transition: transform .35s cubic-bezier(0.4, 0, 0.2, 1),
                  width .35s cubic-bezier(0.4, 0, 0.2, 1),
                  margin .35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Brand Header */
    .sidebar-header {
      height: var(--sidebar-header-height);
      display: flex;
      align-items: center;
      padding: 0 16px;
      border-bottom: 1px solid var(--border-light);
      flex-shrink: 0;
      gap: 10px;
    }

    .brand-icon {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      border-radius: 8px;
      overflow: hidden;
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
      color: var(--text-primary);
      letter-spacing: -0.01em;
      line-height: 1.25;
    }

    .brand-subtitle {
      font-size: 0.6875rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    /* Scrollable Menu */
    .sidebar-menu {
      flex: 1;
      overflow-y: auto;
      padding: 8px 8px;
      scrollbar-width: thin;
      scrollbar-color: #d1d5db transparent;
    }

    .sidebar-menu::-webkit-scrollbar {
      width: 4px;
    }

    .sidebar-menu::-webkit-scrollbar-track {
      background: transparent;
    }

    .sidebar-menu::-webkit-scrollbar-thumb {
      background-color: #d1d5db;
      border-radius: 20px;
    }

    /* Section Label */
    .menu-section {
      margin-bottom: 2px;
    }

    .menu-section-title {
      font-size: 0.625rem;
      font-weight: 700;
      color: var(--text-light);
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin: 20px 14px 8px;
    }

    .menu-section:first-child .menu-section-title {
      margin-top: 4px;
    }

    /* Menu Items */
    .menu-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 12px;
      margin: 0 6px 2px;
      border-radius: 10px;
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.8125rem;
      font-weight: 500;
      transition: all 0.15s ease;
      cursor: pointer;
      border: none;
      background: transparent;
      width: calc(100% - 12px);
      text-align: left;
      position: relative;
    }

    .menu-item:hover {
      background: var(--gray-50);
      color: var(--primary);
    }

    .menu-item.active {
      background: rgba(20, 93, 160, 0.08);
      color: var(--primary-dark);
      font-weight: 600;
    }

    .menu-item.active::before {
      content: '';
      position: absolute;
      left: -6px;
      top: 50%;
      transform: translateY(-50%);
      width: 3px;
      height: 18px;
      background: var(--primary-dark);
      border-radius: 0 3px 3px 0;
    }

    .menu-item.active .menu-icon {
      color: var(--primary-dark);
    }

    .menu-icon {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
      color: var(--text-light);
      transition: color 0.15s ease;
    }

    .menu-item:hover .menu-icon {
      color: var(--primary);
    }

    .menu-text {
      flex: 1;
      min-width: 0;
    }

    .menu-badge {
      background: var(--error);
      color: #fff;
      font-size: 0.625rem;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 9999px;
      min-width: 18px;
      text-align: center;
      line-height: 1.4;
      box-shadow: 0 0 0 2px var(--sidebar-bg);
    }

    /* Collapsible Submenu */
    .menu-group-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 12px;
      margin: 0 6px 2px;
      border-radius: 10px;
      color: var(--text-muted);
      font-size: 0.8125rem;
      font-weight: 500;
      transition: all 0.15s ease;
      cursor: pointer;
      border: none;
      background: transparent;
      width: calc(100% - 12px);
      text-align: left;
      position: relative;
    }

    .menu-group-btn:hover {
      background: var(--gray-50);
      color: var(--primary);
    }

    .menu-group-btn.open {
      background: rgba(20, 93, 160, 0.06);
      color: var(--primary-dark);
      font-weight: 600;
    }

    .menu-group-icon {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
      color: var(--text-light);
      transition: transform 0.2s ease, color 0.15s ease;
    }

    .menu-group-btn:hover .menu-group-icon {
      color: var(--primary);
    }

    .menu-group-btn.open .menu-group-icon:first-child {
      color: var(--primary-dark);
    }

    .menu-group-btn .menu-group-icon:last-child {
      margin-left: auto;
      width: 16px;
      height: 16px;
    }

    .menu-group-btn.open .menu-group-icon:last-child {
      transform: rotate(180deg);
      color: var(--primary-dark);
    }

    .menu-group-label {
      flex: 1;
      min-width: 0;
    }

    .submenu-panel {
      display: none;
      padding-left: 40px;
      margin: 2px 6px 6px;
      position: relative;
    }

    .submenu-panel::before {
      content: '';
      position: absolute;
      left: 18px;
      top: 4px;
      bottom: 8px;
      width: 1.5px;
      background: var(--border);
      border-radius: 1px;
    }

    .submenu-panel.open {
      display: block;
    }

    .submenu-item {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      margin-bottom: 1px;
      border-radius: 8px;
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.78rem;
      font-weight: 500;
      transition: all 0.15s ease;
    }

    .submenu-text {
      flex: 1;
      min-width: 0;
    }

    .submenu-item:hover {
      background: var(--gray-50);
      color: var(--primary);
    }

    .submenu-item.active {
      color: var(--primary-dark);
      font-weight: 600;
      background: rgba(20, 93, 160, 0.06);
    }

    /* Sidebar Footer */
    .sidebar-footer {
      flex-shrink: 0;
      padding: 12px 16px;
      border-top: 1px solid var(--border-light);
      background: var(--sidebar-bg);
    }

    .btn-logout {
      width: 100%;
      padding: 8px 12px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: var(--white);
      color: var(--error);
      font-weight: 600;
      font-size: 0.8125rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.15s ease;
      font-family: inherit;
    }

    .btn-logout:hover {
      background: var(--danger-light);
      border-color: #FECACA;
      color: #B91C1C;
    }

    .btn-logout svg {
      width: 18px;
      height: 18px;
    }

    /* Sidebar Toggle Button */
    .sidebar-toggle {
      display: none;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: var(--white);
      color: var(--primary-dark);
      cursor: pointer;
      transition: all 0.15s ease;
      box-shadow: 0 1px 2px rgba(0,0,0,0.04);
      flex-shrink: 0;
    }

    .sidebar-toggle:hover {
      background: var(--gray-50);
      border-color: var(--primary);
      color: var(--primary);
    }

    .sidebar-toggle svg {
      width: 18px;
      height: 18px;
      transition: transform 0.3s ease;
    }



    /* --- MAIN CONTENT --- */
    .main-content {
      flex: 1;
      min-width: 0;
      height: 100vh;
      height: 100dvh;
      overflow-y: auto;
      background: var(--gray-50);
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
        box-shadow: none;
        height: 100vh;
        height: 100dvh;
        border-radius: 0 20px 20px 0;
        border-right: 1px solid var(--border);
        overflow: hidden;
      }

      .sidebar.open {
        transform: translateX(0);
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
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

    /* Desktop: rounded sidebar + collapse support */
    @media (min-width: 1025px) {
      .sidebar {
        margin: 12px 0 12px 12px;
        height: calc(100dvh - 24px);
        border-radius: 20px;
        border: 1px solid var(--border);
        box-shadow:
          0 4px 6px -1px rgba(0, 0, 0, 0.04),
          0 2px 4px -2px rgba(0, 0, 0, 0.04),
          0 10px 20px -4px rgba(0, 0, 0, 0.06);
        overflow: hidden;
      }

      .sidebar-header {
        border-bottom: 1px solid var(--border-light);
        border-radius: 20px 20px 0 0;
      }

      .sidebar-footer {
        border-radius: 0 0 20px 20px;
      }

      .sidebar-menu {
        padding: 10px;
      }

      .menu-item,
      .menu-group-btn {
        border-radius: 12px;
        margin: 0 4px 3px;
        width: calc(100% - 8px);
      }

      .menu-item.active::before {
        left: -4px;
        border-radius: 0 4px 4px 0;
      }

      .btn-logout {
        border-radius: 12px;
      }

      /* Collapsed state */
      .app.sidebar-collapsed .sidebar {
        width: 0;
        margin: 12px 0;
        border: none;
        box-shadow: none;
      }

      .app.sidebar-collapsed .main-content {
        /* content naturally expands via flex:1 */
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
  </style>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>
  <div class="app">
    <div class="backdrop" id="backdrop"></div>

    <aside class="sidebar" id="sidebar" aria-label="Sidebar">

      <!-- Header / Brand -->
      <div class="sidebar-header">
        <div class="brand-icon">
          <img src="{{ asset('images/logo-triguna-clean.png') }}" alt="Triguna Samudratrans">
        </div>
        <div class="brand-text">
          <span class="brand-title">Triguna Samudratrans</span>
          <span class="brand-subtitle">HRD System</span>
        </div>
      </div>

      <!-- Menu Navigation -->
      <nav class="sidebar-menu" role="navigation">

        {{-- ============================================== --}}
        {{-- GENERAL MENU (All Users) --}}
        {{-- ============================================== --}}
        <div class="menu-section">
          <div class="menu-section-title">Umum</div>

          <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="menu-text">Dashboard</span>
          </a>

          <a href="{{ route('leave-requests.index') }}" class="menu-item {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="menu-text">Izin & Cuti</span>
          </a>

          <a href="{{ route('attendance.dashboard') }}" class="menu-item {{ request()->routeIs('attendance.dashboard') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="menu-text">Absensi</span>
          </a>

          <a href="{{ route('remote-attendance.index') }}" class="menu-item {{ request()->routeIs('remote-attendance.*') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="menu-text">Dinas Luar</span>
          </a>

          <a href="{{ route('overtime-requests.index') }}" class="menu-item {{ request()->routeIs('overtime-requests.*') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v4"/>
            </svg>
            <span class="menu-text">Pengajuan Lembur</span>
          </a>

          <a href="{{ route('employee.loan_requests.index') }}" class="menu-item {{ request()->routeIs('employee.loan_requests.*') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span class="menu-text">Pengajuan Hutang</span>
          </a>

          <a href="{{ route('settings.password') }}" class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="menu-text">Pengaturan Akun</span>
          </a>
        </div>

        {{-- ============================================== --}}
        {{-- MANAGER AREA --}}
        {{-- ============================================== --}}
        @if(auth()->user()->isManager())
        <div class="menu-section">
          <div class="menu-section-title">Manager</div>

          <a href="{{ route('approval.index') }}" class="menu-item {{ request()->routeIs('approval.index', 'approval.show') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="menu-text">Approval Pengajuan</span>
            @if(isset($notifCount) && $notifCount > 0)
            <span class="menu-badge">{{ $notifCount }}</span>
            @endif
          </a>

          <a href="{{ route('supervisor.leave.master') }}" class="menu-item {{ request()->routeIs('supervisor.leave.master') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span class="menu-text">Daftar Pengajuan</span>
          </a>
        </div>
        @endif

        {{-- ============================================== --}}
        {{-- SUPERVISOR AREA --}}
        {{-- ============================================== --}}
        @if(auth()->user()->isSupervisor())
        <div class="menu-section">
          <div class="menu-section-title">Supervisor</div>

          <a href="{{ route('approval.index') }}" class="menu-item {{ request()->routeIs('approval.index', 'approval.show') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span class="menu-text">Mengetahui Pengajuan</span>
            @if(isset($notifCount) && $notifCount > 0)
            <span class="menu-badge">{{ $notifCount }}</span>
            @endif
          </a>

          <a href="{{ route('supervisor.overtime-requests.index') }}" class="menu-item {{ request()->routeIs('supervisor.overtime-requests.index', 'supervisor.overtime-requests.show') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
            </svg>
            <span class="menu-text">Approval Lembur</span>
            @php
            $supervisor = auth()->user();
            $myDivisionId = $supervisor->division_id;
            $myPtId = $supervisor->profile?->pt_id;
            $pendingOvertimeCount = \App\Models\OvertimeRequest::where('status', \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
            ->whereHas('user', function ($q) use ($myDivisionId) {
              $q->where('division_id', $myDivisionId);
            })
            ->when($myPtId, function ($q) use ($myPtId) {
              $q->whereHas('user.profile', function ($sq) use ($myPtId) {
                $sq->where('pt_id', $myPtId);
              });
            })
            ->count();
            @endphp
            @if($pendingOvertimeCount > 0)
            <span class="menu-badge">{{ $pendingOvertimeCount }}</span>
            @endif
          </a>

          <a href="{{ route('supervisor.leave.master') }}" class="menu-item {{ request()->routeIs('supervisor.leave.master') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="menu-text">Daftar Pengajuan</span>
          </a>

          <a href="{{ route('supervisor.overtime-requests.master') }}" class="menu-item {{ request()->routeIs('supervisor.overtime-requests.master') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="menu-text">Daftar Lembur</span>
          </a>
        </div>
        @endif

        {{-- ============================================== --}}
        {{-- HRD PANEL --}}
        {{-- ============================================== --}}
        @if(auth()->user()->isHR())
        @php
        $hrEmployeesOpen = request()->routeIs('hr.employees.*','hr.organization','hr.divisions.*','hr.positions.*','hr.pts.*');
        $hrPresensiOpen = request()->routeIs('hr.attendances.*','hr.shifts.*','hr.locations.*','hr.schedules.*', 'hr.overtime-requests.master');
        $hrLeaveMasterOpen = request()->routeIs('hr.leave.master');
        $hrLoanOpen = request()->routeIs('hr.loan_requests.*', 'hr.payroll.*');
        @endphp

        <div class="menu-section">
          <div class="menu-section-title">HRD Panel</div>

          <a href="{{ route('hr.leave.index') }}" class="menu-item {{ request()->routeIs('hr.leave.index','hr.leave.show','hr.leave.approve','hr.leave.reject') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
            </svg>
            <span class="menu-text">Approval Izin/Cuti</span>
            @if(isset($notifCount) && $notifCount > 0)
            <span class="menu-badge">{{ $notifCount }}</span>
            @endif
          </a>

          <a href="{{ route('hr.approval_attendance.index') }}" class="menu-item {{ request()->routeIs('hr.approval_attendance.*') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span class="menu-text">Approval Absensi</span>
            @php
            $pendingAttendanceCount = \App\Models\Attendance::where('approval_status', 'PENDING')->count();
            @endphp
            @if($pendingAttendanceCount > 0)
            <span class="menu-badge">{{ $pendingAttendanceCount }}</span>
            @endif
          </a>

          <a href="{{ route('hr.overtime-requests.index') }}" class="menu-item {{ request()->routeIs('hr.overtime-requests.index', 'hr.overtime-requests.show') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
            </svg>
            <span class="menu-text">Approval Lembur</span>
            @php
            $pendingHrOvertimeCount = \App\Models\OvertimeRequest::where('status', \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR)->count();
            @endphp
            @if($pendingHrOvertimeCount > 0)
            <span class="menu-badge">{{ $pendingHrOvertimeCount }}</span>
            @endif
          </a>

          {{-- Karyawan Group --}}
          <button type="button" class="menu-group-btn {{ $hrEmployeesOpen ? 'open' : '' }}" data-menu-group="employees">
            <svg class="menu-group-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="menu-group-label">Karyawan</span>
            <svg class="menu-group-icon" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div class="submenu-panel {{ $hrEmployeesOpen ? 'open' : '' }}" data-menu-panel="employees">
            <a href="{{ route('hr.employees.index') }}" class="submenu-item {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}">Daftar Karyawan</a>
            <a href="{{ route('hr.organization') }}" class="submenu-item {{ request()->routeIs('hr.organization','hr.divisions.*','hr.positions.*') ? 'active' : '' }}">Divisi & Jabatan</a>
            <a href="{{ route('hr.pts.index') }}" class="submenu-item {{ request()->routeIs('hr.pts.*') ? 'active' : '' }}">Master PT</a>
          </div>

          <a href="{{ route('hr.supervisors.index') }}" class="menu-item {{ request()->routeIs('hr.supervisors.*') ? 'active' : '' }}">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span class="menu-text">Data Supervisor</span>
          </a>

          {{-- Presensi & Shift Group --}}
          <button type="button" class="menu-group-btn {{ $hrPresensiOpen ? 'open' : '' }}" data-menu-group="presensi">
            <svg class="menu-group-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="menu-group-label">Presensi & Shift</span>
            <svg class="menu-group-icon" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div class="submenu-panel {{ $hrPresensiOpen ? 'open' : '' }}" data-menu-panel="presensi">
            <a href="{{ route('hr.attendances.index') }}" class="submenu-item {{ request()->routeIs('hr.attendances.*') ? 'active' : '' }}">Master Absensi</a>
            <a href="{{ route('hr.overtime-requests.master') }}" class="submenu-item {{ request()->routeIs('hr.overtime-requests.master') ? 'active' : '' }}">Master Lembur</a>
            <a href="{{ route('hr.shifts.index') }}" class="submenu-item {{ request()->routeIs('hr.shifts.*') ? 'active' : '' }}">Master Shift</a>
            <a href="{{ route('hr.locations.index') }}" class="submenu-item {{ request()->routeIs('hr.locations.*') ? 'active' : '' }}">Lokasi Presensi</a>
            <a href="{{ route('hr.schedules.index') }}" class="submenu-item {{ request()->routeIs('hr.schedules.*') ? 'active' : '' }}">Jadwal Karyawan</a>
          </div>

          {{-- Pengaturan Izin Group --}}
          <button type="button" class="menu-group-btn {{ $hrLeaveMasterOpen ? 'open' : '' }}" data-menu-group="izin">
            <svg class="menu-group-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="menu-group-label">Pengaturan Izin</span>
            <svg class="menu-group-icon" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div class="submenu-panel {{ $hrLeaveMasterOpen ? 'open' : '' }}" data-menu-panel="izin">
            <a href="{{ route('hr.leave.master') }}" class="submenu-item {{ request()->routeIs('hr.leave.master') ? 'active' : '' }}">Master Jenis Izin</a>
          </div>

          @php
          $pendingLoanCount = \App\Models\LoanRequest::where('status', 'PENDING_HRD')->count();
          @endphp

          {{-- Keuangan Group --}}
          <button type="button" class="menu-group-btn {{ $hrLoanOpen ? 'open' : '' }}" data-menu-group="keuangan">
            <svg class="menu-group-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span class="menu-group-label">Keuangan</span>
            @if($pendingLoanCount > 0)
            <span class="menu-badge" data-loan-badge="group">{{ $pendingLoanCount }}</span>
            @endif
            <svg class="menu-group-icon" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div class="submenu-panel {{ $hrLoanOpen ? 'open' : '' }}" data-menu-panel="keuangan">
            <a href="{{ route('hr.loan_requests.index') }}" class="submenu-item {{ request()->routeIs('hr.loan_requests.*') ? 'active' : '' }}">
              <span class="submenu-text">Pengajuan Hutang</span>
              @if($pendingLoanCount > 0)
              <span class="menu-badge" data-loan-badge="submenu">{{ $pendingLoanCount }}</span>
              @endif
            </a>
            @can('manage-payroll')
            <a href="{{ route('hr.payroll.index') }}" class="submenu-item {{ request()->routeIs('hr.payroll.*') ? 'active' : '' }}">Master Gaji Karyawan</a>
            @endcan
          </div>
        </div>
        @endif

      </nav>

      <!-- Footer / Logout -->
      <div class="sidebar-footer">
        <button class="btn-logout" type="button" data-modal-target="confirm-logout">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          Keluar Sistem
        </button>
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

          <div class="user-chip">
            <div class="user-avatar">
              {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="user-details">
              <span class="user-name">{{ auth()->user()->name }}</span>
              <span class="user-role">
                {{ auth()->user()->role instanceof \App\Enums\UserRole ? auth()->user()->role->label() : auth()->user()->role }}
              </span>
            </div>
          </div>
        </div>

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
    const sidebar = document.getElementById('sidebar');
    const burger = document.getElementById('burger');
    const backdrop = document.getElementById('backdrop');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const appRoot = document.querySelector('.app');

    // Mobile Menu Toggle
    function toggleMobile() {
      const isOpen = sidebar.classList.contains('open');
      if (isOpen) {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
      } else {
        sidebar.classList.add('open');
        backdrop.classList.add('show');
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

    // Modal Logic
    document.addEventListener('DOMContentLoaded', function() {
      function toggleModal(id, show) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = show ? 'flex' : 'none';
        document.body.style.overflow = show ? 'hidden' : '';
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

      // Sidebar Accordion Menu
      const menuGroupBtns = document.querySelectorAll('.menu-group-btn');

      menuGroupBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const targetGroup = this.getAttribute('data-menu-group');
          const targetPanel = document.querySelector(`[data-menu-panel="${targetGroup}"]`);
          const isOpen = this.classList.contains('open');

          // Close all other panels
          menuGroupBtns.forEach(otherBtn => {
            if (otherBtn !== this) {
              otherBtn.classList.remove('open');
              const otherGroupAttr = otherBtn.getAttribute('data-menu-group');
              const otherPanel = document.querySelector(`[data-menu-panel="${otherGroupAttr}"]`);
              if (otherPanel) otherPanel.classList.remove('open');
            }
          });

          // Toggle current panel
          if (isOpen) {
            this.classList.remove('open');
            if (targetPanel) targetPanel.classList.remove('open');
          } else {
            this.classList.add('open');
            if (targetPanel) targetPanel.classList.add('open');
          }

          // Dynamic loan badge: move between group button and submenu item
          if (targetGroup === 'keuangan') {
            const groupBadge = this.querySelector('[data-loan-badge="group"]');
            const submenuBadge = targetPanel ? targetPanel.querySelector('[data-loan-badge="submenu"]') : null;
            if (!isOpen) {
              // Opening: hide group badge, show submenu badge
              if (groupBadge) groupBadge.style.display = 'none';
              if (submenuBadge) submenuBadge.style.display = '';
            } else {
              // Closing: show group badge, hide submenu badge
              if (groupBadge) groupBadge.style.display = '';
              if (submenuBadge) submenuBadge.style.display = 'none';
            }
          }
        });
      });

      // Init loan badge visibility on page load
      const keuanganBtn = document.querySelector('[data-menu-group="keuangan"]');
      if (keuanganBtn) {
        const isOpen = keuanganBtn.classList.contains('open');
        const groupBadge = keuanganBtn.querySelector('[data-loan-badge="group"]');
        const targetPanel = document.querySelector('[data-menu-panel="keuangan"]');
        const submenuBadge = targetPanel ? targetPanel.querySelector('[data-loan-badge="submenu"]') : null;
        if (groupBadge) groupBadge.style.display = isOpen ? 'none' : '';
        if (submenuBadge) submenuBadge.style.display = isOpen ? '' : 'none';
      }
    });
  </script>

  @stack('scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  {{-- Service Worker Registration for PWA --}}
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        navigator.serviceWorker.register('/hrd/sw.js')
          .then(function(registration) {
            console.log('SW registered:', registration.scope);
          })
          .catch(function(error) {
            console.log('SW registration failed:', error);
          });
      });
    }
  </script>

</body>

</html>
