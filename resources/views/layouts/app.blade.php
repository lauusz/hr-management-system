<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $title ?? 'HRD System' }}</title>
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
  </style>
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

        {{-- 🔑 Pengaturan Akun / Ganti Password --}}
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
        <h3>HRD Panel</h3>

        <a href="{{ route('hr.leave.index') }}"
          class="{{ request()->routeIs('hr.leave.*') ? 'active' : '' }}">
          Daftar Pengajuan Izin/Cuti
        </a>

        <a href="{{ route('hr.shifts.index') }}"
          class="{{ request()->routeIs('hr.shifts.*') ? 'active' : '' }}">
          Master Shift
        </a>

        <a href="{{ route('hr.divisions.index') }}"
          class="{{ request()->routeIs('hr.divisions.*') ? 'active' : '' }}">
          Master Divisi
        </a>

        <a href="{{ route('hr.positions.index') }}"
          class="{{ request()->routeIs('hr.positions.*') ? 'active' : '' }}">
          Master Jabatan
        </a>


        <a href="{{ route('hr.employees.index') }}"
          class="{{ request()->routeIs('hr.employees.*') ? 'active' : '' }}">
          Daftar Karyawan
        </a>

        <a href="{{ route('hr.locations.index') }}"
          class="{{ request()->routeIs('hr.locations.*') ? 'active' : '' }}">
          Master Lokasi Presensi
        </a>

        <a href="{{ route('hr.schedules.index') }}"
          class="{{ request()->routeIs('hr.schedules.*') ? 'active' : '' }}">
          Master Jadwal Karyawan
        </a>

        <a href="{{ route('hr.attendances.index') }}"
          class="{{ request()->routeIs('hr.attendances.*') ? 'active' : '' }}">
          Master Absensi
        </a>
        @endif

      </nav>

      <div class="logout">
        <form class="inline" method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="btn" type="submit">Logout</button>
        </form>
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
  </script>
  @stack('scripts')

</body>

</html>