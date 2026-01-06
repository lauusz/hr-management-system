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

  <style>
    :root {
      --navy: #1e4a8d;
      --bg: #f6f7fb;
      --text: #222;
      --muted: #eef3ff;
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: system-ui, Arial, sans-serif;
      background: var(--bg);
      color: var(--text);
    }

    .app {
      display: flex;
      min-height: 100vh;
      /* Mobile fix: gunakan dvh jika support untuk hindari address bar browser hp */
      min-height: 100dvh;
    }

    /* --- SIDEBAR STRUCTURE --- */
    .sidenav {
      background: #fff;
      border-right: 1px solid #eee;
      width: 260px; /* Sedikit dilebarkan agar lebih lega */
      flex-shrink: 0;
      display: flex;
      flex-direction: column; /* Susunan vertikal: Brand -> Menu -> Logout */
      z-index: 1000;
      position: sticky;
      top: 0;
      height: 100vh;
      height: 100dvh;
      transition: transform .3s ease;
    }

    .brand {
      padding: 20px;
      font-size: 18px;
      font-weight: 700;
      color: var(--navy);
      border-bottom: 1px solid #f0f0f0;
      flex-shrink: 0; /* Jangan mengecil */
    }

    /* Bagian Menu (Scrollable Area) */
    .menu {
      flex: 1; /* Mengisi sisa ruang antara brand dan logout */
      overflow-y: auto; /* Hanya bagian ini yang scroll */
      padding: 12px;
      -webkit-overflow-scrolling: touch;
      overscroll-behavior: contain; /* Mencegah scroll body saat menu mentok */
    }

    /* Styling Item Menu */
    .menu a {
      display: flex;
      align-items: center;
      padding: 11px 12px;
      margin: 2px 0;
      border-radius: 8px;
      color: #4b5563;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s;
    }

    .menu a.active,
    .menu a:hover {
      background: var(--muted);
      color: var(--navy);
      font-weight: 600;
    }

    .menu h3 {
      font-size: 11px;
      font-weight: 700;
      color: #9ca3af;
      text-transform: uppercase;
      letter-spacing: .05em;
      margin: 20px 12px 8px;
    }

    /* Accordion / Dropdown Menu */
    .menu-group {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 11px 12px;
      margin: 2px 0;
      border-radius: 8px;
      border: none;
      background: transparent;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      color: #4b5563;
      transition: background 0.2s;
    }

    .menu-group:hover {
      background: #f9fafb;
      color: var(--navy);
    }

    .menu-group.open {
      background: #f8faff;
      color: var(--navy);
      font-weight: 600;
    }

    .menu-group-label {
      flex: 1;
      text-align: left;
    }

    .menu-group-icon {
      font-size: 10px;
      transition: transform 0.2s;
    }
    
    .menu-group.open .menu-group-icon {
      transform: rotate(180deg);
    }

    .submenu {
      display: none;
      padding-left: 12px; /* Indentasi submenu */
      margin-bottom: 4px;
    }

    .submenu.show {
      display: block;
      animation: slideDown 0.2s ease-out;
    }
    
    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-5px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .submenu a {
      font-size: 13.5px;
      padding: 8px 12px;
      border-left: 2px solid transparent;
      border-radius: 0 8px 8px 0;
    }
    
    .submenu a.active {
      border-left-color: var(--navy);
      background: white; 
    }

    /* Bagian Logout (Fixed Bottom) */
    .logout {
      flex-shrink: 0; /* Jangan mengecil */
      padding: 16px;
      border-top: 1px solid #eee;
      background: #fff;
      /* Safe area untuk iPhone X ke atas */
      padding-bottom: calc(16px + env(safe-area-inset-bottom));
    }

    .btn-logout {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #e5e7eb;
      background: #fff;
      color: #ef4444; /* Merah soft untuk logout */
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 8px;
    }

    .btn-logout:hover {
      background: #fef2f2;
      border-color: #fecaca;
    }

    /* --- CONTENT AREA --- */
    .content {
      flex: 1;
      padding: 24px;
      min-width: 0; /* Mencegah overflow flex child */
      height: 100vh;
      height: 100dvh;
      overflow-y: auto; /* Content scroll terpisah dari sidebar */
    }

    .container {
      max-width: 1200px;
      margin-inline: auto;
      padding-bottom: 40px;
    }

    .topbar {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 24px;
      background: #fff;
      padding: 12px 16px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }
    
    .page-title {
        font-size: 1.25rem;
        margin: 0;
        font-weight: 700;
        flex: 1;
    }

    .userchip {
      font-size: 13px;
      color: #6b7280;
      background: #f3f4f6;
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 500;
    }

    .burger {
      display: none; /* Hidden on Desktop */
      border: none;
      background: transparent;
      padding: 4px;
      cursor: pointer;
      color: var(--navy);
    }

    /* --- MOBILE RESPONSIVE --- */
    @media (max-width: 960px) {
      .sidenav {
        position: fixed;
        left: 0;
        transform: translateX(-100%);
        box-shadow: none;
        width: 80%; /* Lebar sidebar di HP */
        max-width: 300px;
      }

      .sidenav.open {
        transform: translateX(0);
        box-shadow: 4px 0 24px rgba(0,0,0,0.15);
      }

      .content {
        padding: 16px;
      }
      
      .topbar {
        padding: 10px;
        margin-bottom: 16px;
      }

      .burger {
        display: block;
      }

      .backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
        z-index: 990; /* Di bawah sidebar (1000) */
        backdrop-filter: blur(2px);
      }

      .backdrop.show {
        opacity: 1;
        pointer-events: auto;
      }
    }

    /* Components */
    .card {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      border: 1px solid #f3f4f6;
    }

    .modal-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 2000;
      padding: 20px;
    }
    
    .modal-content {
        background: #fff;
        padding: 24px;
        border-radius: 12px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
  </style>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>
  <div class="app">
    <div class="backdrop" id="backdrop"></div>

    <aside class="sidenav" id="sidenav" aria-label="Sidenav">
      
      <div class="brand">HRD System</div>
      
      <nav class="menu" role="navigation">
        <h3>General</h3>
        <a href="{{ route('dashboard') }}"
          class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
          Dashboard
        </a>

        <a href="{{ route('leave-requests.index') }}"
          class="{{ request()->routeIs('leave-requests.*') ? 'active' : '' }}">
          Izin / Cuti
        </a>

        <a href="{{ route('attendance.dashboard') }}"
          class="{{ request()->routeIs('attendance.dashboard') ? 'active' : '' }}">
          Absensi
        </a>

        <a href="{{ route('employee.loan_requests.index') }}"
          class="{{ request()->routeIs('employee.loan_requests.*') ? 'active' : '' }}">
          Hutang Karyawan
        </a>

        <a href="{{ route('settings.password') }}"
          class="{{ request()->routeIs('settings.password') ? 'active' : '' }}">
          Pengaturan Akun
        </a>

        @if(auth()->user()->isSupervisor())
        <h3>Supervisor</h3>
        <a href="{{ route('supervisor.leave.index') }}">
          Mengetahui Pengajuan
        </a>
        @endif

        @if(auth()->user()->isHR())
        @php
        $hrEmployeesOpen = request()->routeIs('hr.employees.*','hr.organization','hr.divisions.*','hr.positions.*','hr.pts.*');
        $hrPresensiOpen = request()->routeIs('hr.attendances.*','hr.shifts.*','hr.locations.*','hr.schedules.*');
        $hrLeaveMasterOpen = request()->routeIs('hr.leave.master');
        $hrLoanOpen = request()->routeIs('hr.loan_requests.*');
        @endphp

        <h3>HRD Panel</h3>

        <a href="{{ route('hr.leave.index') }}"
          class="{{ request()->routeIs('hr.leave.index','hr.leave.show','hr.leave.approve','hr.leave.reject') ? 'active' : '' }}">
          Daftar Pengajuan Izin/Cuti
        </a>

        <button type="button"
          class="menu-group {{ $hrEmployeesOpen ? 'open' : '' }}"
          data-menu-group="employees">
          <span class="menu-group-label">Karyawan</span>
          <span class="menu-group-icon">▾</span>
        </button>
        <div class="submenu {{ $hrEmployeesOpen ? 'show' : '' }}" data-menu-panel="employees">
          <a href="{{ route('hr.employees.index') }}"
            class="{{ request()->routeIs('hr.employees.*') ? 'active' : '' }}">
            Daftar Karyawan
          </a>

          <a href="{{ route('hr.organization') }}"
            class="{{ request()->routeIs('hr.organization','hr.divisions.*','hr.positions.*') ? 'active' : '' }}">
            Divisi &amp; Jabatan
          </a>

          <a href="{{ route('hr.pts.index') }}"
            class="{{ request()->routeIs('hr.pts.*') ? 'active' : '' }}">
            Master PT
          </a>
        </div>

        <button type="button"
          class="menu-group {{ $hrPresensiOpen ? 'open' : '' }}"
          data-menu-group="presensi">
          <span class="menu-group-label">Presensi &amp; Shift</span>
          <span class="menu-group-icon">▾</span>
        </button>
        <div class="submenu {{ $hrPresensiOpen ? 'show' : '' }}" data-menu-panel="presensi">
          <a href="{{ route('hr.attendances.index') }}"
            class="{{ request()->routeIs('hr.attendances.*') ? 'active' : '' }}">
            Master Absensi
          </a>

          <a href="{{ route('hr.shifts.index') }}"
            class="{{ request()->routeIs('hr.shifts.*') ? 'active' : '' }}">
            Master Shift
          </a>

          <a href="{{ route('hr.locations.index') }}"
            class="{{ request()->routeIs('hr.locations.*') ? 'active' : '' }}">
            Master Lokasi Presensi
          </a>

          <a href="{{ route('hr.schedules.index') }}"
            class="{{ request()->routeIs('hr.schedules.*') ? 'active' : '' }}">
            Master Jadwal Karyawan
          </a>
        </div>

        <button type="button"
          class="menu-group {{ $hrLeaveMasterOpen ? 'open' : '' }}"
          data-menu-group="izin">
          <span class="menu-group-label">Izin &amp; Cuti</span>
          <span class="menu-group-icon">▾</span>
        </button>
        <div class="submenu {{ $hrLeaveMasterOpen ? 'show' : '' }}" data-menu-panel="izin">
          <a href="{{ route('hr.leave.master') }}"
            class="{{ request()->routeIs('hr.leave.master') ? 'active' : '' }}">
            Master Izin/Cuti
          </a>
        </div>

        <button type="button"
          class="menu-group {{ $hrLoanOpen ? 'open' : '' }}"
          data-menu-group="keuangan">
          <span class="menu-group-label">Keuangan Karyawan</span>
          <span class="menu-group-icon">▾</span>
        </button>
        <div class="submenu {{ $hrLoanOpen ? 'show' : '' }}" data-menu-panel="keuangan">
          <a href="{{ route('hr.loan_requests.index') }}"
            class="{{ request()->routeIs('hr.loan_requests.*') ? 'active' : '' }}">
            Hutang Karyawan
          </a>
        </div>
        @endif
        
        <div style="height: 20px;"></div>

      </nav> <div class="logout">
        <button class="btn-logout" type="button" data-modal-target="confirm-logout">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          Logout
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
          
          <div class="userchip">
             {{ auth()->user()->name }}
             <span style="opacity:0.6; margin-left:4px;">({{ auth()->user()->role }})</span>
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
    confirmLabel="Logout"
    cancelLabel="Batal"
    :confirmFormAction="route('logout')"
    confirmFormMethod="POST">
    <p style="margin:0 0 4px 0;">Yakin ingin keluar dari sistem?</p>
    <p style="margin:0;font-size:0.85rem;opacity:.8;">Sesi Anda akan diakhiri dan perlu login kembali.</p>
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

    burger?.addEventListener('click', toggleMobile);
    backdrop?.addEventListener('click', toggleMobile);

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

      // Tutup Modal (Klik Backdrop atau Tombol Close)
      document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
          if (e.target === modal || e.target.closest('[data-modal-close]')) {
            toggleModal(modal.id, false);
          }
        });
      });

      // Logic Dropdown Menu Sidebar
      document.querySelectorAll('.menu-group').forEach(btn => {
        // Set icon awal berdasarkan class open
        const icon = btn.querySelector('.menu-group-icon');
        if(btn.classList.contains('open')) {
             if(icon) icon.style.transform = 'rotate(180deg)';
        }

        btn.addEventListener('click', function() {
          const group = this.getAttribute('data-menu-group');
          const panel = document.querySelector(`[data-menu-panel="${group}"]`);
          
          this.classList.toggle('open');
          
          if (this.classList.contains('open')) {
            panel.classList.add('show');
            if(icon) icon.style.transform = 'rotate(180deg)';
          } else {
            panel.classList.remove('show');
            if(icon) icon.style.transform = 'rotate(0deg)';
          }
        });
      });
    });
  </script>

  @stack('scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>
</html>