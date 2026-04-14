<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  {{-- PWA Meta Tags --}}
  <meta name="theme-color" content="#1e4a8d">
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
      --navy: #1e4a8d;
      --navy-dark: #163a75;
      --navy-light: #eef4ff;
      --navy-muted: #3b82f6;
      --bg: #f3f4f6;
      --bg-white: #ffffff;
      --text: #1f2937;
      --text-muted: #6b7280;
      --text-light: #9ca3af;
      --border: #e5e7eb;
      --border-light: #f3f4f6;
      --sidebar-width: 272px;
      --sidebar-bg: #ffffff;
      --sidebar-header-height: 64px;
      --danger: #ef4444;
      --danger-light: #fef2f2;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background: var(--bg);
      color: var(--text);
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
      transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Brand Header */
    .sidebar-header {
      height: var(--sidebar-header-height);
      display: flex;
      align-items: center;
      padding: 0 20px;
      border-bottom: 1px solid var(--border-light);
      flex-shrink: 0;
      gap: 12px;
    }

    .brand-icon {
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, var(--navy) 0%, var(--navy-muted) 100%);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .brand-icon svg {
      color: #fff;
    }

    .brand-text {
      display: flex;
      flex-direction: column;
    }

    .brand-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--navy);
      letter-spacing: -0.02em;
      line-height: 1.2;
    }

    .brand-subtitle {
      font-size: 0.7rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    /* Scrollable Menu */
    .sidebar-menu {
      flex: 1;
      overflow-y: auto;
      padding: 12px 12px;
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
      margin-bottom: 4px;
    }

    .menu-section-title {
      font-size: 0.65rem;
      font-weight: 700;
      color: var(--text-light);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin: 16px 12px 6px;
    }

    .menu-section:first-child .menu-section-title {
      margin-top: 0;
    }

    /* Menu Items */
    .menu-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 12px;
      margin-bottom: 2px;
      border-radius: 10px;
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.15s ease;
      cursor: pointer;
      border: none;
      background: transparent;
      width: 100%;
      text-align: left;
    }

    .menu-item:hover {
      background: var(--navy-light);
      color: var(--navy);
    }

    .menu-item.active {
      background: var(--navy-light);
      color: var(--navy);
      font-weight: 600;
    }

    .menu-item.active .menu-icon {
      color: var(--navy);
    }

    .menu-icon {
      width: 20px;
      height: 20px;
      flex-shrink: 0;
      color: var(--text-light);
      transition: color 0.15s ease;
    }

    .menu-item:hover .menu-icon {
      color: var(--navy);
    }

    .menu-text {
      flex: 1;
      min-width: 0;
    }

    .menu-badge {
      background: var(--danger);
      color: #fff;
      font-size: 0.65rem;
      font-weight: 700;
      padding: 2px 6px;
      border-radius: 9999px;
      min-width: 18px;
      text-align: center;
      line-height: 1.4;
    }

    /* Collapsible Submenu */
    .menu-group-btn {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 12px;
      margin-bottom: 2px;
      border-radius: 10px;
      color: var(--text-muted);
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.15s ease;
      cursor: pointer;
      border: none;
      background: transparent;
      width: 100%;
      text-align: left;
    }

    .menu-group-btn:hover {
      background: var(--navy-light);
      color: var(--navy);
    }

    .menu-group-btn.open {
      background: var(--navy-light);
      color: var(--navy);
    }

    .menu-group-icon {
      width: 20px;
      height: 20px;
      flex-shrink: 0;
      color: var(--text-light);
      transition: transform 0.2s ease;
    }

    .menu-group-btn.open .menu-group-icon {
      transform: rotate(180deg);
      color: var(--navy);
    }

    .menu-group-label {
      flex: 1;
    }

    .submenu-panel {
      display: none;
      padding-left: 44px;
      margin: 2px 0 8px;
      position: relative;
    }

    .submenu-panel::before {
      content: '';
      position: absolute;
      left: 20px;
      top: 0;
      bottom: 12px;
      width: 1px;
      background: var(--border);
    }

    .submenu-panel.open {
      display: block;
    }

    .submenu-item {
      display: block;
      padding: 8px 12px;
      margin-bottom: 1px;
      border-radius: 8px;
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.15s ease;
    }

    .submenu-item:hover {
      background: var(--navy-light);
      color: var(--navy);
    }

    .submenu-item.active {
      color: var(--navy);
      font-weight: 600;
    }

    /* Sidebar Footer */
    .sidebar-footer {
      flex-shrink: 0;
      padding: 12px;
      border-top: 1px solid var(--border-light);
      background: var(--sidebar-bg);
    }

    .btn-logout {
      width: 100%;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid #fecaca;
      background: var(--danger-light);
      color: #b91c1c;
      font-weight: 600;
      font-size: 0.85rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.15s ease;
      font-family: inherit;
    }

    .btn-logout:hover {
      background: #fee2e2;
      border-color: #fca5a5;
    }

    .btn-logout svg {
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
      background: var(--bg);
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
      color: #111827;
      letter-spacing: -0.02em;
    }

    .user-chip {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-left: auto;
      padding: 6px 12px 6px 6px;
      background: var(--bg-white);
      border-radius: 9999px;
      border: 1px solid var(--border);
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .user-avatar {
      width: 32px;
      height: 32px;
      background: linear-gradient(135deg, var(--navy) 0%, var(--navy-muted) 100%);
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.8rem;
    }

    .user-details {
      display: flex;
      flex-direction: column;
    }

    .user-name {
      font-size: 0.85rem;
      font-weight: 600;
      color: #111827;
      line-height: 1.2;
    }

    .user-role {
      font-size: 0.7rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    /* Mobile Burger */
    .burger {
      display: none;
      border: none;
      background: var(--bg-white);
      padding: 10px;
      border-radius: 10px;
      cursor: pointer;
      color: var(--navy);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
      border: 1px solid var(--border);
      transition: all 0.15s ease;
    }

    .burger:hover {
      background: var(--navy-light);
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
      }

      .sidebar.open {
        transform: translateX(0);
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
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
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 2000;
      padding: 20px;
      backdrop-filter: blur(2px);
    }

    .modal-content {
      background: var(--bg-white);
      padding: 24px;
      border-radius: 16px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
      animation: modalPop 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
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
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <div class="brand-text">
          <span class="brand-title">HRD System</span>
          <span class="brand-subtitle">Triguna Samudratrans</span>
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

          {{-- Keuangan Group --}}
          <button type="button" class="menu-group-btn {{ $hrLoanOpen ? 'open' : '' }}" data-menu-group="keuangan">
            <svg class="menu-group-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span class="menu-group-label">Keuangan</span>
            <svg class="menu-group-icon" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div class="submenu-panel {{ $hrLoanOpen ? 'open' : '' }}" data-menu-panel="keuangan">
            <a href="{{ route('hr.loan_requests.index') }}" class="submenu-item {{ request()->routeIs('hr.loan_requests.*') ? 'active' : '' }}">Pengajuan Hutang</a>
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

          <h2 class="page-title">{{ $title ?? 'Dashboard' }}</h2>

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
    variant="dark"
    confirmLabel="Ya, Keluar"
    cancelLabel="Batal"
    :confirmFormAction="route('logout')"
    confirmFormMethod="POST">
    <p style="margin:0; color:#374151;">Apakah Anda yakin ingin mengakhiri sesi ini?</p>
  </x-modal>

  <script>
    const sidebar = document.getElementById('sidebar');
    const burger = document.getElementById('burger');
    const backdrop = document.getElementById('backdrop');

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
        });
      });
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
