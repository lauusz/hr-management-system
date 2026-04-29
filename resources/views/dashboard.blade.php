<x-app title="Dashboard">
  <div class="dashboard">

    @php
      $roleItemCount = 0;
      if (auth()->user()->isSupervisor() || auth()->user()->isManager()) $roleItemCount++;
      if (auth()->user()->isHR()) $roleItemCount += 8;
      if (auth()->user()->isManager() && !auth()->user()->isHR()) $roleItemCount++;
    @endphp

    {{-- ============================================ --}}
    {{-- HERO / GREETING                              --}}
    {{-- ============================================ --}}
    <div class="hero">
      <div class="hero-content">
        <div class="hero-greeting">
          @php
            $hour = now()->hour;
            $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
          @endphp
          {{ $greeting }}, <span class="hero-name">{{ auth()->user()->name }}</span>
          <span class="hero-role-badge">
            @if(auth()->user()->isHR())
              HRD
            @elseif(auth()->user()->isSupervisor())
              Supervisor
            @elseif(auth()->user()->isManager())
              Manager
            @else
              Karyawan
            @endif
          </span>
        </div>
        <div class="hero-date">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span>{{ now()->translatedFormat('l, j F Y') }}</span>
        </div>
      </div>
    </div>

    {{-- ============================================ --}}
    {{-- SUMMARY BAR                                  --}}
    {{-- ============================================ --}}
    <div class="summary-bar">
      <div class="summary-item">
        <div class="summary-icon icon-navy">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <div class="summary-meta">
          <div class="summary-label">Sisa Cuti</div>
          <div class="summary-value">
            {{ rtrim(rtrim(sprintf('%.1f', auth()->user()->leave_balance ?? 0), '0'), '.') }}
            <span class="summary-unit">hari</span>
          </div>
        </div>
      </div>

      <div class="summary-item">
        <div class="summary-icon icon-teal">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div class="summary-meta">
          <div class="summary-label">Divisi</div>
          <div class="summary-value">{{ auth()->user()->division?->name ?? '-' }}</div>
        </div>
      </div>

    </div>

    {{-- ============================================ --}}
    {{-- PRIMARY QUICK ACTIONS — 2-Column Grid        --}}
    {{-- ============================================ --}}
    <section class="dash-section dash-section--primary">
      <div class="section-header">
        <h2 class="section-title">Aksi Cepat</h2>
        <p class="section-subtitle">Fitur yang sering digunakan</p>
      </div>
      <div class="action-grid">

        <a class="action-card" href="{{ route('attendance.clockIn.form') }}">
          <div class="action-card__top">
            <div class="action-card__icon icon-green">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
              </svg>
            </div>
            <div class="action-card__arrow">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
          <div class="action-card__body">
            <span class="action-card__name">Clock In</span>
            <span class="action-card__desc">Masuk kerja</span>
          </div>
        </a>

        <a class="action-card" href="{{ route('attendance.clockOut.form') }}">
          <div class="action-card__top">
            <div class="action-card__icon icon-red">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
            </div>
            <div class="action-card__arrow">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
          <div class="action-card__body">
            <span class="action-card__name">Clock Out</span>
            <span class="action-card__desc">Pulang kerja</span>
          </div>
        </a>

        <a class="action-card" href="{{ route('leave-requests.create') }}">
          <div class="action-card__top">
            <div class="action-card__icon icon-navy">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div class="action-card__arrow">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
          <div class="action-card__body">
            <span class="action-card__name">Buat Izin</span>
            <span class="action-card__desc">Ajukan izin / cuti</span>
          </div>
        </a>

        <a class="action-card" href="{{ route('leave-requests.index') }}">
          <div class="action-card__top">
            <div class="action-card__icon icon-teal">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
              </svg>
            </div>
            <div class="action-card__arrow">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
          <div class="action-card__body">
            <span class="action-card__name">Riwayat Izin</span>
            <span class="action-card__desc">Pantau status</span>
          </div>
        </a>

        <a class="action-card" href="{{ route('remote-attendance.index') }}">
          <div class="action-card__top">
            <div class="action-card__icon icon-purple">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <div class="action-card__arrow">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
          <div class="action-card__body">
            <span class="action-card__name">Dinas Luar</span>
            <span class="action-card__desc">Remote attendance</span>
          </div>
        </a>

        <a class="action-card" href="{{ route('overtime-requests.index') }}">
          <div class="action-card__top">
            <div class="action-card__icon icon-orange">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="action-card__arrow">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
          <div class="action-card__body">
            <span class="action-card__name">Lembur</span>
            <span class="action-card__desc">Ajukan lembur</span>
          </div>
        </a>

      </div>
    </section>

    {{-- ============================================ --}}
    {{-- ROLE ACCESS — Collapsible on mobile          --}}
    {{-- ============================================ --}}
    @if(auth()->user()->isHR() || auth()->user()->isSupervisor() || auth()->user()->isManager())
    <section class="dash-section dash-section--secondary">
      <div class="section-header">
        <div class="section-header-row">
          <h2 class="section-title">Akses Role</h2>
          @if(auth()->user()->isHR())
            <span class="role-pill role-pill-hrd">HRD</span>
          @elseif(auth()->user()->isSupervisor())
            <span class="role-pill role-pill-supervisor">Supervisor</span>
          @else
            <span class="role-pill role-pill-manager">Manager</span>
          @endif
        </div>
        <p class="section-subtitle">Menu sesuai tanggung jawab Anda</p>
      </div>

      <div class="role-grid" id="roleGrid">

        @if(auth()->user()->isSupervisor() || auth()->user()->isManager())
          <a class="action-card action-card--muted" href="{{ route('approval.index') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-yellow">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Mengetahui Pengajuan</span>
              <span class="action-card__desc">Approve izin / lembur</span>
            </div>
          </a>
        @endif

        @if(auth()->user()->isHR())
          <a class="action-card action-card--muted" href="{{ route('hr.leave.index') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Approval Izin/Cuti</span>
              <span class="action-card__desc">Verifikasi pengajuan</span>
            </div>
          </a>

          <a class="action-card action-card--muted" href="{{ route('hr.approval_attendance.index') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-teal">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Approval Absensi</span>
              <span class="action-card__desc">Verifikasi absensi</span>
            </div>
          </a>

          <a class="action-card action-card--muted" href="{{ route('hr.overtime-requests.index') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-orange">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Approval Lembur</span>
              <span class="action-card__desc">Verifikasi lembur</span>
            </div>
          </a>

          <a class="action-card action-card--muted" href="{{ route('hr.employees.index') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-blue">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Data Karyawan</span>
              <span class="action-card__desc">Kelola data karyawan</span>
            </div>
          </a>

          <a class="action-card action-card--muted" href="{{ route('hr.attendances.index') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-green">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Rekap Absensi</span>
              <span class="action-card__desc">Monitor kehadiran</span>
            </div>
          </a>

          <a class="action-card action-card--muted" href="{{ route('hr.loan_requests.index') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-amber">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Approval Pinjaman</span>
              <span class="action-card__desc">Kelola pengajuan hutang</span>
            </div>
          </a>

          <a class="action-card action-card--muted" href="{{ route('hr.organization') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-purple">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Organisasi</span>
              <span class="action-card__desc">Divisi & jabatan</span>
            </div>
          </a>

          <a class="action-card action-card--muted" href="{{ route('hr.supervisors.index') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-teal">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Data Supervisor</span>
              <span class="action-card__desc">Kelola supervisor</span>
            </div>
          </a>
        @endif

        @if(auth()->user()->isManager() && !auth()->user()->isHR())
          <a class="action-card action-card--muted" href="{{ route('hr.employees.index') }}">
            <div class="action-card__top">
              <div class="action-card__icon icon-blue">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
              </div>
              <div class="action-card__arrow">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </div>
            </div>
            <div class="action-card__body">
              <span class="action-card__name">Data Karyawan</span>
              <span class="action-card__desc">Kelola data karyawan</span>
            </div>
          </a>
        @endif
      </div>

      @if($roleItemCount > 4)
        <button type="button" class="expand-btn" id="roleExpandBtn" aria-expanded="false">
          <span class="expand-more">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            Lihat semua menu
          </span>
          <span class="expand-less">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
            Sembunyikan
          </span>
        </button>
      @endif
    </section>
    @endif

    {{-- ============================================ --}}
    {{-- PENGATURAN                                   --}}
    {{-- ============================================ --}}
    <section class="dash-section dash-section--secondary">
      <div class="section-header">
        <h2 class="section-title">Pengaturan</h2>
        <p class="section-subtitle">Kelola akun dan preferensi</p>
      </div>
      <div class="settings-grid">

        <a class="action-card action-card--muted" href="{{ route('settings.password') }}">
          <div class="action-card__top">
            <div class="action-card__icon icon-gray">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
              </svg>
            </div>
            <div class="action-card__arrow">
              <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
          <div class="action-card__body">
            <span class="action-card__name">Ubah Password</span>
            <span class="action-card__desc">Ganti password akun</span>
          </div>
        </a>

        <a class="action-card action-card--muted" href="{{ route('employee.loan_requests.index') }}">
          <div class="action-card__top">
            <div class="action-card__icon icon-gray">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
            </div>
            <div class="action-card__arrow">
              <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
          <div class="action-card__body">
            <span class="action-card__name">Pengajuan Hutang</span>
            <span class="action-card__desc">Riwayat pengajuan hutang</span>
          </div>
        </a>

      </div>
    </section>

  </div>

  <style>
    /* ============================================= */
    /* DASHBOARD ROOT                                */
    /* ============================================= */
    .dashboard {
      display: flex;
      flex-direction: column;
      gap: 10px;
      padding-bottom: 8px;
    }

    /* Tighten title-to-content spacing on dashboard */
    .topbar {
      margin-bottom: 10px;
    }

    /* ============================================= */
    /* HERO — Compact, clean, personal               */
    /* ============================================= */
    .hero {
      position: relative;
      background: linear-gradient(135deg, var(--primary-dark, #0A3D62) 0%, var(--primary, #145DA0) 60%, var(--primary-light, #1E81B0) 100%);
      border-radius: var(--radius-xl, 16px);
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(10, 61, 98, 0.10);
    }

    .hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse 80% 60% at 90% 10%, rgba(212, 175, 55, 0.08) 0%, transparent 70%);
      pointer-events: none;
    }

    .hero-content {
      position: relative;
      z-index: 1;
      padding: 14px 16px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .hero-greeting {
      font-size: 0.9375rem;
      font-weight: 700;
      color: #fff;
      line-height: 1.35;
      letter-spacing: -0.01em;
    }

    .hero-name {
      font-weight: 800;
    }

    .hero-role-badge {
      display: inline-flex;
      align-items: center;
      padding: 2px 8px;
      border-radius: var(--radius-full, 9999px);
      font-size: 0.625rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.03em;
      background: rgba(255,255,255,0.15);
      color: #fff;
      border: 1px solid rgba(255,255,255,0.25);
      margin-left: 6px;
      vertical-align: middle;
    }

    .hero-date {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.8125rem;
      color: #fff;
      font-weight: 600;
      line-height: 1.3;
    }

    .hero-date svg {
      width: 14px;
      height: 14px;
      color: rgba(255,255,255,0.85);
      flex-shrink: 0;
    }

    /* ============================================= */
    /* SUMMARY BAR — 2 compact chips                 */
    /* ============================================= */
    .summary-bar {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 8px;
    }

    .summary-item {
      background: var(--white, #fff);
      border: 1px solid var(--border, #E5E7EB);
      border-radius: var(--radius-lg, 12px);
      padding: 10px 6px;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      gap: 6px;
      box-shadow: var(--shadow-sm, 0 1px 2px rgba(0,0,0,0.04));
      transition: all 0.2s ease;
    }

    .summary-item:hover {
      border-color: #D1D5DB;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .summary-icon {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .summary-icon svg {
      width: 14px;
      height: 14px;
    }

    .summary-label {
      font-size: 0.625rem;
      font-weight: 600;
      color: var(--text-muted, #6B7280);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      line-height: 1.2;
    }

    .summary-value {
      font-size: 0.875rem;
      font-weight: 800;
      color: var(--text-primary, #111827);
      line-height: 1.25;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
    }

    .summary-unit {
      font-size: 0.6875rem;
      font-weight: 500;
      color: var(--text-muted, #6B7280);
    }

    /* ============================================= */
    /* DASHBOARD SECTIONS                            */
    /* ============================================= */
    .dash-section {
      background: var(--white, #fff);
      border: 1px solid var(--border, #E5E7EB);
      border-radius: var(--radius-xl, 16px);
      padding: 12px;
      box-shadow: var(--shadow-sm, 0 1px 2px rgba(0,0,0,0.04));
    }

    .dash-section--secondary {
      background: linear-gradient(180deg, var(--gray-100, #F8FAFC) 0%, var(--white, #fff) 100%);
    }

    .section-header {
      margin-bottom: 14px;
    }

    .section-header-row {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .section-title {
      font-size: 0.9375rem;
      font-weight: 800;
      color: var(--text-primary, #111827);
      letter-spacing: -0.01em;
      line-height: 1.3;
      margin: 0;
    }

    .section-subtitle {
      font-size: 0.8125rem;
      color: var(--text-muted, #6B7280);
      margin: 2px 0 0;
      font-weight: 500;
      line-height: 1.4;
    }

    .role-pill {
      display: inline-flex;
      align-items: center;
      padding: 2px 8px;
      border-radius: 6px;
      font-size: 0.625rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .role-pill-hrd {
      background: rgba(20, 93, 160, 0.08);
      color: var(--primary, #145DA0);
    }

    .role-pill-supervisor {
      background: rgba(234, 179, 8, 0.10);
      color: #A16207;
    }

    .role-pill-manager {
      background: rgba(147, 51, 234, 0.08);
      color: #7C3AED;
    }

    /* ============================================= */
    /* ACTION GRID — Primary 2-col on mobile         */
    /* ============================================= */
    .action-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 8px;
    }

    .settings-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 8px;
    }

    /* ============================================= */
    /* ACTION CARD — Unified component               */
    /* ============================================= */
    .action-card {
      display: flex;
      flex-direction: column;
      gap: 8px;
      padding: 10px;
      background: var(--white, #fff);
      border: 1.5px solid var(--border-light, #F3F4F6);
      border-radius: var(--radius-lg, 12px);
      text-decoration: none;
      color: inherit;
      transition: all 0.2s ease;
      position: relative;
      overflow: hidden;
    }

    .action-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(20, 93, 160, 0.02) 0%, transparent 60%);
      opacity: 0;
      transition: opacity 0.2s ease;
    }

    .action-card:hover {
      border-color: rgba(20, 93, 160, 0.2);
      box-shadow: 0 4px 12px rgba(10, 61, 98, 0.06);
      transform: translateY(-1px);
    }

    .action-card:hover::before {
      opacity: 1;
    }

    .action-card:active {
      transform: scale(0.985);
    }

    .action-card--muted {
      background: var(--gray-50, #F5F7FA);
      border-color: var(--border, #E5E7EB);
    }

    .action-card--muted:hover {
      background: var(--white, #fff);
      border-color: rgba(20, 93, 160, 0.2);
    }

    .action-card__top {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .action-card__icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      position: relative;
      z-index: 1;
    }

    .action-card__icon svg {
      width: 16px;
      height: 16px;
    }

    .action-card__arrow {
      color: #D1D5DB;
      transition: all 0.2s ease;
      position: relative;
      z-index: 1;
    }

    .action-card:hover .action-card__arrow {
      color: var(--primary, #145DA0);
      transform: translateX(2px);
    }

    .action-card__body {
      display: flex;
      flex-direction: column;
      gap: 2px;
      min-width: 0;
      position: relative;
      z-index: 1;
    }

    .action-card__name {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--text-secondary, #374151);
      line-height: 1.3;
    }

    .action-card__desc {
      font-size: 0.6875rem;
      color: var(--text-muted, #6B7280);
      font-weight: 500;
      line-height: 1.35;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* ============================================= */
    /* SETTINGS GRID — Smaller cards                 */
    /* ============================================= */
    .settings-grid {
      gap: 6px;
    }

    .settings-grid .action-card {
      padding: 8px;
      gap: 6px;
    }

    .settings-grid .action-card__icon {
      width: 26px;
      height: 26px;
      border-radius: 7px;
    }

    .settings-grid .action-card__icon svg {
      width: 14px;
      height: 14px;
    }

    .settings-grid .action-card__name {
      font-size: 0.6875rem;
    }

    .settings-grid .action-card__desc {
      font-size: 0.625rem;
    }

    /* ============================================= */
    /* ROLE GRID — Collapsible on mobile             */
    /* ============================================= */
    .role-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
    }

    .role-grid .action-card:nth-child(n+5) {
      display: none;
    }

    .role-grid.is-expanded .action-card:nth-child(n+5) {
      display: flex;
    }

    /* ============================================= */
    /* EXPAND BUTTON — Explicit, clear               */
    /* ============================================= */
    .expand-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      background: var(--gray-50, #F5F7FA);
      border: 1px solid var(--border, #E5E7EB);
      border-radius: var(--radius-lg, 12px);
      color: var(--primary, #145DA0);
      font-weight: 600;
      font-size: 0.8125rem;
      cursor: pointer;
      transition: all 0.2s ease;
      font-family: inherit;
    }

    .expand-btn:hover {
      background: #F3F4F6;
      border-color: #D1D5DB;
    }

    .expand-more,
    .expand-less {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .expand-btn[aria-expanded="true"] .expand-more,
    .expand-btn:not([aria-expanded="true"]) .expand-less {
      display: none;
    }

    /* ============================================= */
    /* ICON COLOR SYSTEM                             */
    /* ============================================= */
    .icon-navy   { background: rgba(20, 93, 160, 0.08);  color: #145DA0; }
    .icon-teal   { background: rgba(30, 129, 176, 0.08); color: #1E81B0; }
    .icon-blue   { background: rgba(59, 130, 246, 0.08); color: #2563EB; }
    .icon-green  { background: rgba(34, 197, 94, 0.08);  color: #16A34A; }
    .icon-red    { background: rgba(239, 68, 68, 0.08);  color: #DC2626; }
    .icon-purple { background: rgba(147, 51, 234, 0.08); color: #9333EA; }
    .icon-orange { background: rgba(249, 115, 22, 0.08); color: #EA580C; }
    .icon-amber  { background: rgba(245, 158, 11, 0.08); color: #D97706; }
    .icon-yellow { background: rgba(234, 179, 8, 0.08);  color: #CA8A04; }
    .icon-gray   { background: rgba(107, 114, 128, 0.08); color: #4B5563; }

    /* ============================================= */
    /* TABLET & DESKTOP (768px+)                     */
    /* ============================================= */
    @media (min-width: 768px) {
      .dashboard {
        gap: 14px;
      }

      .topbar {
        margin-bottom: 12px;
      }

      /* Hero */
      .hero {
        border-radius: var(--radius-2xl, 20px);
      }

      .hero-content {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 24px;
      }

      .hero-greeting {
        font-size: 1.125rem;
      }

      .hero-role-badge {
        font-size: 0.6875rem;
        padding: 3px 10px;
      }

      .hero-date {
        font-size: 0.875rem;
        gap: 8px;
      }

      .hero-date svg {
        width: 15px;
        height: 15px;
      }

      /* Summary */
      .summary-bar {
        gap: 12px;
      }

      .summary-item {
        flex-direction: row;
        align-items: center;
        text-align: left;
        padding: 14px;
        gap: 10px;
      }

      .summary-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
      }

      .summary-icon svg {
        width: 16px;
        height: 16px;
      }

      .summary-label {
        font-size: 0.6875rem;
      }

      .summary-value {
        font-size: 1rem;
      }

      .summary-unit {
        font-size: 0.75rem;
      }

      /* Sections */
      .dash-section {
        padding: 16px;
        border-radius: var(--radius-2xl, 20px);
      }

      .section-header {
        margin-bottom: 18px;
      }

      .section-title {
        font-size: 1.0625rem;
      }

      .section-subtitle {
        font-size: 0.875rem;
        margin-top: 4px;
      }

      /* Action Grid */
      .action-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
      }

      .settings-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
      }

      .settings-grid .action-card {
        padding: 10px;
        gap: 8px;
      }

      .settings-grid .action-card__icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
      }

      .settings-grid .action-card__icon svg {
        width: 14px;
        height: 14px;
      }

      .settings-grid .action-card__name {
        font-size: 0.75rem;
      }

      .settings-grid .action-card__desc {
        font-size: 0.6875rem;
      }

      .action-card {
        padding: 12px;
        gap: 10px;
      }

      .action-card__icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
      }

      .action-card__icon svg {
        width: 16px;
        height: 16px;
      }

      .action-card__name {
        font-size: 0.8125rem;
      }

      .action-card__desc {
        font-size: 0.75rem;
      }

      /* Role Grid — always expanded on tablet+ */
      .role-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
      }

      .role-grid .action-card:nth-child(n+5) {
        display: flex;
      }

      .expand-btn {
        display: none;
      }
    }

    /* ============================================= */
    /* DESKTOP WIDE (1024px+)                        */
    /* ============================================= */
    @media (min-width: 1024px) {
      .hero-content {
        padding: 24px 28px;
      }

      .hero-greeting {
        font-size: 1.25rem;
      }

      .action-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      }

      .role-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      }

      .settings-grid {
        grid-template-columns: repeat(2, minmax(200px, 1fr));
      }
    }
  </style>

  @if($roleItemCount > 4)
  <script>
    (function() {
      const btn = document.getElementById('roleExpandBtn');
      const grid = document.getElementById('roleGrid');
      if (!btn || !grid) return;
      btn.addEventListener('click', function() {
        const expanded = grid.classList.toggle('is-expanded');
        btn.setAttribute('aria-expanded', expanded);
      });
    })();
  </script>
  @endif
</x-app>
