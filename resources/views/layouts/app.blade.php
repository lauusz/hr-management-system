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
    }

    .sidenav {
      background: #fff;
      border-right: 1px solid #eee;
      width: 248px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      z-index: 1000;
      transform: translateX(0);
      transition: transform .3s ease;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }

    .brand {
      padding: 16px 18px;
      font-weight: 700;
      color: var(--navy);
      border-bottom: 1px solid #eee;
    }

    .menu {
      padding: 8px 12px;
      flex: 1;
    }

    .menu a {
      display: block;
      padding: 10px 12px;
      margin: 4px 0;
      border-radius: 10px;
      color: #333;
      text-decoration: none;
      font-size: 15px;
    }

    .menu a.active,
    .menu a:hover {
      background: var(--muted);
      color: var(--navy);
      font-weight: 600;
    }

    .menu h3 {
      font-size: 12px;
      font-weight: 600;
      color: #9ca3af;
      text-transform: uppercase;
      letter-spacing: .04em;
      margin: 14px 8px 4px;
    }

    .menu-group {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
      padding: 10px 12px;
      margin: 4px 0;
      border-radius: 10px;
      border: none;
      background: transparent;
      cursor: pointer;
      font-size: 15px;
      color: #333;
    }

    .menu-group-label {
      text-align: left;
      flex: 1;
    }

    .menu-group-icon {
      font-size: 11px;
      opacity: .7;
    }

    .menu-group.open {
      background: var(--muted);
      color: var(--navy);
      font-weight: 600;
    }

    .submenu {
      display: none;
      padding-left: 8px;
      margin-left: 4px;
      border-left: 1px solid #eef0f5;
    }

    .submenu.show {
      display: block;
    }

    .submenu a {
      font-size: 14px;
      padding: 8px 12px;
      margin: 2px 0;
    }

    .logout {
      padding: 10px 12px;
      border-top: 1px solid #f0f0f0;
    }

    .btn {
      width: 100%;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid #ddd;
      background: #fff;
      color: #222;
      cursor: pointer;
      font-weight: 500;
    }

    .btn:hover {
      background: #f7f7f7;
    }

    .content {
      flex: 1;
      padding: 20px 24px;
      min-width: 0;
    }

    .container {
      max-width: 1200px;
      margin-inline: auto;
    }

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 18px;
    }

    .userchip {
      font-size: 14px;
      color: #555;
    }

    .burger {
      display: none;
      border: 1px solid #ddd;
      background: #fff;
      border-radius: 10px;
      padding: 8px 10px;
      line-height: 0;
      cursor: pointer;
    }

    .burger svg {
      stroke: var(--navy);
    }

    @media (max-width: 960px) {
      .sidenav {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        transform: translateX(-100%);
        box-shadow: 0 8px 20px rgba(0, 0, 0, .15);
      }

      .sidenav.open {
        transform: translateX(0);
      }

      .content {
        padding: 18px;
      }

      .burger {
        display: inline-block;
      }

      .backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .25);
        opacity: 0;
        pointer-events: none;
        transition: opacity .25s;
        z-index: 900;
      }

      .backdrop.show {
        opacity: 1;
        pointer-events: auto;
      }
    }

    .card {
      background: #fff;
      padding: 16px;
      border-radius: 12px;
      box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
    }

    th,
    td {
      padding: 12px 14px;
      border-bottom: 1px solid #f0f0f0;
      text-align: left;
    }

    th {
      background: #fafbff;
      color: #333;
    }

    .modal-backdrop {
      display: none;
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
          <span class="menu-group-icon">{{ $hrEmployeesOpen ? '▴' : '▾' }}</span>
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
          <span class="menu-group-icon">{{ $hrPresensiOpen ? '▴' : '▾' }}</span>
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
          <span class="menu-group-icon">{{ $hrLeaveMasterOpen ? '▴' : '▾' }}</span>
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
          <span class="menu-group-icon">{{ $hrLoanOpen ? '▴' : '▾' }}</span>
        </button>
        <div class="submenu {{ $hrLoanOpen ? 'show' : '' }}" data-menu-panel="keuangan">
          <a href="{{ route('hr.loan_requests.index') }}"
            class="{{ request()->routeIs('hr.loan_requests.*') ? 'active' : '' }}">
            Hutang Karyawan
          </a>
        </div>
        @endif

      </nav>

      <div class="logout">
        <button class="btn" type="button" data-modal-target="confirm-logout">Logout</button>
      </div>
    </aside>

    <main class="content">
      <div class="container">
        <div class="topbar">
          <button class="burger" id="burger" aria-label="Toggle menu" aria-controls="sidenav" aria-expanded="false">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
              <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <h2 style="margin:0">{{ $title ?? 'Dashboard' }}</h2>
          <div class="userchip">{{ auth()->user()->name }} • {{ auth()->user()->role }}</div>
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
    <p style="margin:0;font-size:0.85rem;opacity:.8;">Sesi Anda akan diakhiri dan perlu login kembali untuk mengakses HRD System.</p>
  </x-modal>

  <script>
    const sidenav = document.getElementById('sidenav');
    const burger = document.getElementById('burger');
    const backdrop = document.getElementById('backdrop');

    function isMobile() {
      return window.innerWidth <= 960;
    }

    function openMobile() {
      if (!isMobile()) return;
      sidenav.classList.add('open');
      backdrop.classList.add('show');
      burger.setAttribute('aria-expanded', 'true');
    }

    function closeMobile() {
      sidenav.classList.remove('open');
      backdrop.classList.remove('show');
      burger.setAttribute('aria-expanded', 'false');
    }

    burger?.addEventListener('click', () => (sidenav.classList.contains('open') ? closeMobile() : openMobile()));
    backdrop?.addEventListener('click', closeMobile);
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') closeMobile();
    });
    window.addEventListener('resize', () => {
      if (!isMobile()) closeMobile();
    });

    document.addEventListener('DOMContentLoaded', function() {
      function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      }

      function closeModal(modal) {
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
      }

      document.querySelectorAll('[data-modal-target]').forEach(function(trigger) {
        trigger.addEventListener('click', function() {
          var targetId = this.getAttribute('data-modal-target');
          if (targetId) {
            openModal(targetId);
          }
        });
      });

      document.querySelectorAll('.modal-backdrop').forEach(function(backdropEl) {
        backdropEl.addEventListener('click', function(e) {
          if (e.target === backdropEl) {
            closeModal(backdropEl);
          }
        });
      });

      document.addEventListener('click', function(e) {
        var closeButton = e.target.closest('[data-modal-close]');
        if (!closeButton) return;
        var modal = closeButton.closest('.modal-backdrop');
        closeModal(modal);
      });

      document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.modal-backdrop').forEach(function(modal) {
          if (modal.style.display === 'flex') {
            closeModal(modal);
          }
        });
      });

      document.querySelectorAll('.menu-group').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var group = this.getAttribute('data-menu-group');
          var panel = document.querySelector('[data-menu-panel="' + group + '"]');
          var icon = this.querySelector('.menu-group-icon');
          var isOpen = this.classList.contains('open');

          if (isOpen) {
            this.classList.remove('open');
            if (panel) panel.classList.remove('show');
            if (icon) icon.textContent = '▾';
          } else {
            this.classList.add('open');
            if (panel) panel.classList.add('show');
            if (icon) icon.textContent = '▴';
          }
        });
      });
    });
  </script>

  <script>
    (function() {
      if (!('serviceWorker' in navigator)) return;

      const isLocalhost = location.hostname === 'localhost' || location.hostname === '127.0.0.1';
      const isSecure = location.protocol === 'https:' || isLocalhost;

      if (!isSecure) return;

      window.addEventListener('load', function() {
        navigator.serviceWorker.register('{{ asset('
          service - worker.js ') }}');
      });
    })();
  </script>

  @stack('scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>

</html>