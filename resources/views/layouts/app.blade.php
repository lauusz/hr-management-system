<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $title ?? 'HRD System' }}</title>

  <meta name="theme-color" content="#1e4a8d">
  <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" href="{{ asset('pwa/icon-180.png') }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --navy: #1e4a8d;
      --navy-light: #eef4ff;
      --bg: #f3f4f6;
      --text: #1f2937;
      --text-muted: #6b7280;
      --border: #e5e7eb;
      --sidebar-width: 280px;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: var(--bg);
      color: var(--text);
      -webkit-font-smoothing: antialiased;
    }

    .app {
      display: flex;
      min-height: 100vh;
      min-height: 100dvh;
    }

    /* --- SIDEBAR MODERN --- */
    .sidenav {
      background: #fff;
      border-right: 1px solid var(--border);
      width: var(--sidebar-width);
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      z-index: 1000;
      position: sticky;
      top: 0;
      height: 100vh;
      height: 100dvh;
      transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .brand {
      height: 70px;
      display: flex;
      align-items: center;
      padding: 0 24px;
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--navy);
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
      letter-spacing: -0.025em;
    }

    /* Scrollable Menu Area */
    .menu {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      scrollbar-width: thin;
      scrollbar-color: #d1d5db transparent;
    }

    .menu::-webkit-scrollbar { width: 4px; }
    .menu::-webkit-scrollbar-track { background: transparent; }
    .menu::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 20px; }

    .menu h3 {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin: 24px 12px 8px;
    }
    .menu h3:first-child { margin-top: 0; }

    .menu a, .menu-group {
      display: flex;
      align-items: center;
      width: 100%;
      padding: 10px 12px;
      margin-bottom: 4px;
      border-radius: 8px;
      color: #4b5563;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.2s;
      border: 1px solid transparent;
      background: transparent;
      cursor: pointer;
    }

    .menu a:hover, .menu-group:hover {
      background-color: #f9fafb;
      color: #111827;
    }

    .menu a.active {
      background-color: var(--navy-light);
      color: var(--navy);
      font-weight: 600;
    }

    .menu-group { justify-content: space-between; }
    
    .menu-group.open {
      background-color: #f9fafb;
      color: #111827;
    }

    .menu-group-icon {
      width: 16px;
      height: 16px;
      transition: transform 0.2s;
      color: #9ca3af;
    }
    
    .menu-group.open .menu-group-icon {
      transform: rotate(180deg);
      color: var(--navy);
    }

    .submenu {
      display: none;
      padding-left: 22px;
      margin-top: 2px;
      margin-bottom: 8px;
      position: relative;
    }

    .submenu::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 10px;
        width: 1px;
        background: #e5e7eb;
    }

    .submenu.show { display: block; }

    .submenu a {
      font-size: 0.85rem;
      padding: 8px 12px;
      color: #6b7280;
      margin-bottom: 2px;
    }

    .submenu a:hover { color: #111827; background: transparent; }
    
    .submenu a.active {
      color: var(--navy);
      background: transparent;
      position: relative;
    }
    
    .submenu a.active::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 50%;
        transform: translateY(-50%);
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--navy);
    }

    .logout {
      flex-shrink: 0;
      padding: 16px;
      border-top: 1px solid var(--border);
      background: #fff;
      padding-bottom: calc(16px + env(safe-area-inset-bottom));
    }

    .btn-logout {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #fee2e2;
      background: #fef2f2;
      color: #b91c1c;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 8px;
      transition: all 0.2s;
    }

    .btn-logout:hover {
      background: #fee2e2;
      border-color: #fca5a5;
    }

    /* --- CONTENT --- */
    .content {
      flex: 1;
      padding: 32px;
      min-width: 0;
      height: 100vh;
      height: 100dvh;
      overflow-y: auto;
    }

    .container {
      max-width: 1100px;
      margin-inline: auto;
      padding-bottom: 60px;
    }

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 32px;
    }
    
    .page-title {
        font-size: 1.5rem;
        margin: 0;
        font-weight: 700;
        color: #111827;
        letter-spacing: -0.025em;
    }

    .user-info { display: flex; align-items: center; gap: 12px; }
    .userchip { text-align: right; }
    .user-name { font-size: 0.9rem; font-weight: 600; color: #111827; display: block; }
    .user-role { font-size: 0.75rem; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; display: block; }

    .burger {
      display: none;
      border: none;
      background: #fff;
      padding: 8px;
      border-radius: 8px;
      cursor: pointer;
      color: var(--navy);
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
      border: 1px solid var(--border);
    }

    /* --- MOBILE --- */
    @media (max-width: 1024px) {
      .sidenav { position: fixed; left: 0; transform: translateX(-100%); box-shadow: none; }
      .sidenav.open { transform: translateX(0); box-shadow: 10px 0 30px rgba(0,0,0,0.1); }
      .content { padding: 20px; }
      .topbar { margin-bottom: 24px; align-items: center; gap: 16px; justify-content: flex-start; }
      .page-title { font-size: 1.25rem; flex: 1; }
      .user-info { display: none; }
      .burger { display: block; }
      .backdrop { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.3); opacity: 0; pointer-events: none; transition: opacity 0.3s; z-index: 990; backdrop-filter: blur(2px); }
      .backdrop.show { opacity: 1; pointer-events: auto; }
    }

    /* Components */
    .card { background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f3f4f6; }
    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 2000; padding: 20px; backdrop-filter: blur(2px); }
    .modal-content { background: #fff; padding: 24px; border-radius: 16px; width: 100%; max-width: 420px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); animation: modalPop 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    @keyframes modalPop { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
  </style>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>
  <div class="app">
    <div class="backdrop" id="backdrop"></div>

    <aside class="sidenav" id="sidenav" aria-label="Sidenav">
      
      <div class="brand">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:10px; color:#1e4a8d;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          HRD System
      </div>
      
      <nav class="menu" role="navigation">
        <h3>General</h3>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <span style="margin-right:10px;"></span> Dashboard
        </a>

        <a href="{{ route('leave-requests.index') }}" class="{{ request()->routeIs('leave-requests.*') ? 'active' : '' }}">
          <span style="margin-right:10px;"></span> Izin / Cuti
        </a>

        <a href="{{ route('attendance.dashboard') }}" class="{{ request()->routeIs('attendance.dashboard') ? 'active' : '' }}">
          <span style="margin-right:10px;"></span> Absensi
        </a>

        <a href="{{ route('employee.loan_requests.index') }}" class="{{ request()->routeIs('employee.loan_requests.*') ? 'active' : '' }}">
          <span style="margin-right:10px;"></span> Pengajuan Hutang
        </a>

        <a href="{{ route('settings.password') }}" class="{{ request()->routeIs('settings.password') ? 'active' : '' }}">
          <span style="margin-right:10px;"></span> Pengaturan Akun
        </a>

        @if(auth()->user()->isSupervisor())
        <h3>Supervisor Area</h3>
        {{-- Menu ini muncul untuk user yang memiliki Role SPV / Manager --}}
        <a href="{{ route('approval.index') }}" class="{{ request()->routeIs('approval.*') ? 'active' : '' }}">
            <span style="margin-right:10px;"></span> Mengetahui Pengajuan
        </a>
        @endif

        @if(auth()->user()->isHR())
        @php
            // Logic Active State untuk Menu HRD
            $hrEmployeesOpen = request()->routeIs('hr.employees.*','hr.organization','hr.divisions.*','hr.positions.*','hr.pts.*');
            $hrPresensiOpen = request()->routeIs('hr.attendances.*','hr.shifts.*','hr.locations.*','hr.schedules.*');
            $hrLeaveMasterOpen = request()->routeIs('hr.leave.master');
            $hrLoanOpen = request()->routeIs('hr.loan_requests.*');
        @endphp

        <h3>HRD Panel</h3>

        <a href="{{ route('hr.leave.index') }}" class="{{ request()->routeIs('hr.leave.index','hr.leave.show','hr.leave.approve','hr.leave.reject') ? 'active' : '' }}">
          <span style="margin-right:10px;"></span> Approval Izin/Cuti
        </a>

        {{-- Menu Karyawan (Umum) --}}
        <button type="button" class="menu-group {{ ($hrEmployeesOpen) ? 'open' : '' }}" data-menu-group="employees">
          <span class="menu-group-label"><span style="margin-right:10px;"></span> Karyawan</span>
          <svg class="menu-group-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="submenu {{ ($hrEmployeesOpen) ? 'show' : '' }}" data-menu-panel="employees">
          <a href="{{ route('hr.employees.index') }}" class="{{ request()->routeIs('hr.employees.*') ? 'active' : '' }}">Daftar Karyawan</a>
          <a href="{{ route('hr.organization') }}" class="{{ request()->routeIs('hr.organization','hr.divisions.*','hr.positions.*') ? 'active' : '' }}">Divisi &amp; Jabatan</a>
          <a href="{{ route('hr.pts.index') }}" class="{{ request()->routeIs('hr.pts.*') ? 'active' : '' }}">Master PT</a>
        </div>

        {{-- MENU DATA SUPERVISOR (LINK LANGSUNG) --}}
        {{-- Ubah route ke 'hr.supervisors.index' (pake s) --}}
        <a href="{{ route('hr.supervisors.index') }}" class="{{ request()->routeIs('hr.supervisors.*') ? 'active' : '' }}">
          <span style="margin-right:10px;"></span> Data Supervisor
        </a>

        <button type="button" class="menu-group {{ $hrPresensiOpen ? 'open' : '' }}" data-menu-group="presensi">
          <span class="menu-group-label"><span style="margin-right:10px;"></span> Presensi &amp; Shift</span>
          <svg class="menu-group-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="submenu {{ $hrPresensiOpen ? 'show' : '' }}" data-menu-panel="presensi">
          <a href="{{ route('hr.attendances.index') }}" class="{{ request()->routeIs('hr.attendances.*') ? 'active' : '' }}">Master Absensi</a>
          <a href="{{ route('hr.shifts.index') }}" class="{{ request()->routeIs('hr.shifts.*') ? 'active' : '' }}">Master Shift</a>
          <a href="{{ route('hr.locations.index') }}" class="{{ request()->routeIs('hr.locations.*') ? 'active' : '' }}">Lokasi Presensi</a>
          <a href="{{ route('hr.schedules.index') }}" class="{{ request()->routeIs('hr.schedules.*') ? 'active' : '' }}">Jadwal Karyawan</a>
        </div>

        <button type="button" class="menu-group {{ $hrLeaveMasterOpen ? 'open' : '' }}" data-menu-group="izin">
          <span class="menu-group-label"><span style="margin-right:10px;"></span> Pengaturan Izin</span>
          <svg class="menu-group-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="submenu {{ $hrLeaveMasterOpen ? 'show' : '' }}" data-menu-panel="izin">
          <a href="{{ route('hr.leave.master') }}" class="{{ request()->routeIs('hr.leave.master') ? 'active' : '' }}">Master Jenis Izin</a>
        </div>

        <button type="button" class="menu-group {{ $hrLoanOpen ? 'open' : '' }}" data-menu-group="keuangan">
          <span class="menu-group-label"><span style="margin-right:10px;"></span> Keuangan</span>
          <svg class="menu-group-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div class="submenu {{ $hrLoanOpen ? 'show' : '' }}" data-menu-panel="keuangan">
          <a href="{{ route('hr.loan_requests.index') }}" class="{{ request()->routeIs('hr.loan_requests.*') ? 'active' : '' }}">Pengajuan Hutang</a>
        </div>
        @endif
        
        <div style="height: 20px;"></div>

      </nav> 
      
      <div class="logout">
        <button class="btn-logout" type="button" data-modal-target="confirm-logout">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          Keluar Sistem
        </button>
      </div>
      
    </aside>

    <main class="content">
      <div class="container">
        <div class="topbar">
          <button class="burger" id="burger" aria-label="Toggle menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <h2 class="page-title">{{ $title ?? 'Dashboard' }}</h2>
          
          <div class="user-info">
             <div class="userchip">
                 <span class="user-name">{{ auth()->user()->name }}</span>
                 {{-- Gunakan label() agar tampilan role rapi dan tanpa underscore --}}
                 <span class="user-role">
                    {{ auth()->user()->role instanceof \App\Enums\UserRole ? auth()->user()->role->label() : auth()->user()->role }}
                 </span>
             </div>
             <div style="width:36px; height:36px; background:#e0e7ff; color:#1e4a8d; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">
                 {{ substr(auth()->user()->name, 0, 1) }}
             </div>
          </div>
        </div>

        {{ $slot }}
      </div>
    </main>
  </div>

  <x-modal
    id="confirm-logout"
    title="Konfirmasi Logout"
    type="confirm"
    confirmLabel="Ya, Keluar"
    cancelLabel="Batal"
    :confirmFormAction="route('logout')"
    confirmFormMethod="POST">
    <p style="margin:0; color:#374151;">Apakah Anda yakin ingin mengakhiri sesi ini?</p>
  </x-modal>

  <script>
    const sidenav = document.getElementById('sidenav');
    const burger = document.getElementById('burger');
    const backdrop = document.getElementById('backdrop');

    // Logic Mobile Menu
    function toggleMobile() {
        const isOpen = sidenav.classList.contains('open');
        if (isOpen) {
            sidenav.classList.remove('open');
            backdrop.classList.remove('show');
        } else {
            sidenav.classList.add('open');
            backdrop.classList.add('show');
        }
    }

    if(burger) burger.addEventListener('click', toggleMobile);
    if(backdrop) backdrop.addEventListener('click', toggleMobile);

    // Logic Modal
    document.addEventListener('DOMContentLoaded', function() {
      function toggleModal(id, show) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = show ? 'flex' : 'none';
        document.body.style.overflow = show ? 'hidden' : '';
      }

      // Buka Modal
      document.querySelectorAll('[data-modal-target]').forEach(btn => {
        btn.addEventListener('click', () => toggleModal(btn.dataset.modalTarget, true));
      });

      // Tutup Modal
      document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
          if (e.target === modal || e.target.closest('[data-modal-close]')) {
            toggleModal(modal.id, false);
          }
        });
      });

      // Logic Dropdown Menu Sidebar (Accordion Style)
      const menuGroups = document.querySelectorAll('.menu-group');

      menuGroups.forEach(btn => {
        const icon = btn.querySelector('.menu-group-icon');
        
        // Initialize state
        if(btn.classList.contains('open') && icon) {
             icon.style.transform = 'rotate(180deg)';
        }

        btn.addEventListener('click', function() {
          const targetGroup = this.getAttribute('data-menu-group');
          const targetPanel = document.querySelector(`[data-menu-panel="${targetGroup}"]`);
          const isOpen = this.classList.contains('open');

          // 1. Close ALL other menus first
          menuGroups.forEach(otherBtn => {
              if (otherBtn !== this) {
                  otherBtn.classList.remove('open');
                  const otherIcon = otherBtn.querySelector('.menu-group-icon');
                  if(otherIcon) otherIcon.style.transform = 'rotate(0deg)';

                  const otherGroupAttr = otherBtn.getAttribute('data-menu-group');
                  const otherPanel = document.querySelector(`[data-menu-panel="${otherGroupAttr}"]`);
                  if(otherPanel) otherPanel.classList.remove('show');
              }
          });

          // 2. Toggle the CURRENT menu
          if (isOpen) {
              // Close if it was open
              this.classList.remove('open');
              if(targetPanel) targetPanel.classList.remove('show');
              if(icon) icon.style.transform = 'rotate(0deg)';
          } else {
              // Open if it was closed
              this.classList.add('open');
              if(targetPanel) targetPanel.classList.add('show');
              if(icon) icon.style.transform = 'rotate(180deg)';
          }
        });
      });
    });
  </script>

  @stack('scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>
</html>