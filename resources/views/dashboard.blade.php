<x-app title="Dashboard">
  <div class="dashboard">

    {{-- ============================================ --}}
    {{-- HEADER SECTION --}}
    {{-- ============================================ --}}
    <div class="welcome-section">
      <div class="welcome-left">
        <div class="welcome-greeting">
          @php
            $hour = now()->hour;
            $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
          @endphp
          {{ $greeting }}, {{ auth()->user()->name }}
        </div>
        <div class="welcome-sub">
          @if(auth()->user()->isHR())
            Anda login sebagai <span class="role-badge role-hrd">HRD</span>
          @elseif(auth()->user()->isSupervisor())
            Anda login sebagai <span class="role-badge role-supervisor">Supervisor</span>
          @elseif(auth()->user()->isManager())
            Anda login sebagai <span class="role-badge role-manager">Manager</span>
          @else
            Anda login sebagai <span class="role-badge role-employee">Karyawan</span>
          @endif
        </div>
      </div>
      <div class="welcome-date">
        <div class="date-main">{{ now()->translatedFormat('j F Y') }}</div>
        <div class="date-sub">
          @php
            $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
          @endphp
          {{ $dayNames[now()->dayOfWeek] }}
        </div>
      </div>
    </div>

    {{-- ============================================ --}}
    {{-- INFO CARDS ROW --}}
    {{-- ============================================ --}}
    <div class="info-cards">

      <div class="info-card">
        <div class="info-icon info-icon-navy">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <div class="info-content">
          <div class="info-label">Sisa Cuti</div>
          <div class="info-value">{{ rtrim(rtrim(sprintf('%.1f', auth()->user()->leave_balance ?? 0), '0'), '.') }} <span class="info-unit">hari</span></div>
        </div>
      </div>

      <div class="info-card">
        <div class="info-icon info-icon-teal">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div class="info-content">
          <div class="info-label">Divisi</div>
          <div class="info-value">{{ auth()->user()->division?->name ?? '-' }}</div>
        </div>
      </div>

      <div class="info-card">
        <div class="info-icon info-icon-blue">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
        </div>
        <div class="info-content">
          <div class="info-label">Role</div>
          <div class="info-value">
            @if(auth()->user()->role instanceof \App\Enums\UserRole)
              {{ auth()->user()->role->label() }}
            @else
              {{ auth()->user()->role }}
            @endif
          </div>
        </div>
      </div>

    </div>

    {{-- ============================================ --}}
    {{-- QUICK ACTIONS SECTION --}}
    {{-- ============================================ --}}
    <div class="section-wrap">
      <div class="section-header">
        <div class="section-title">Aksi Cepat</div>
        <div class="section-sub">Fitur yang sering digunakan</div>
      </div>

      <div class="quick-grid">

        <a class="quick-btn" href="{{ route('attendance.clockIn.form') }}">
          <div class="quick-icon quick-icon-green">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
          </div>
          <div class="quick-text">
            <div class="quick-name">Clock In</div>
            <div class="quick-desc">Masuk kerja</div>
          </div>
          <div class="quick-arrow">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>

        <a class="quick-btn" href="{{ route('attendance.clockOut.form') }}">
          <div class="quick-icon quick-icon-red">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </div>
          <div class="quick-text">
            <div class="quick-name">Clock Out</div>
            <div class="quick-desc">Pulang kerja</div>
          </div>
          <div class="quick-arrow">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>

        <a class="quick-btn" href="{{ route('leave-requests.create') }}">
          <div class="quick-icon quick-icon-navy">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div class="quick-text">
            <div class="quick-name">Buat Izin</div>
            <div class="quick-desc">Ajukan izin / cuti</div>
          </div>
          <div class="quick-arrow">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>

        <a class="quick-btn" href="{{ route('leave-requests.index') }}">
          <div class="quick-icon quick-icon-teal">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
          </div>
          <div class="quick-text">
            <div class="quick-name">Riwayat Izin</div>
            <div class="quick-desc">Pantau status pengajuan</div>
          </div>
          <div class="quick-arrow">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>

        <a class="quick-btn" href="{{ route('remote-attendance.index') }}">
          <div class="quick-icon quick-icon-purple">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <div class="quick-text">
            <div class="quick-name">Dinas Luar</div>
            <div class="quick-desc">Remote attendance</div>
          </div>
          <div class="quick-arrow">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>

        <a class="quick-btn" href="{{ route('overtime-requests.index') }}">
          <div class="quick-icon quick-icon-orange">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="quick-text">
            <div class="quick-name">Lembur</div>
            <div class="quick-desc">Ajukan lembur</div>
          </div>
          <div class="quick-arrow">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>

      </div>
    </div>

    {{-- ============================================ --}}
    {{-- HRD / SUPERVISOR ROLE ACCESS SECTION --}}
    {{-- ============================================ --}}
    @if(auth()->user()->isHR() || auth()->user()->isSupervisor() || auth()->user()->isManager())
    <div class="section-wrap">
      <div class="section-header">
        <div class="section-title">
          Akses Role
          @if(auth()->user()->isHR())
            <span class="role-indicator role-hrd">HRD</span>
          @elseif(auth()->user()->isSupervisor())
            <span class="role-indicator role-supervisor">Supervisor</span>
          @else
            <span class="role-indicator role-manager">Manager</span>
          @endif
        </div>
        <div class="section-sub">Menu sesuai dengan tanggung jawab Anda</div>
      </div>

      <div class="quick-grid">

        @if(auth()->user()->isSupervisor() || auth()->user()->isManager())
          <a class="quick-btn" href="{{ route('approval.index') }}">
            <div class="quick-icon quick-icon-yellow">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Mengetahui Pengajuan</div>
              <div class="quick-desc">Approve izin / lembur</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>
        @endif

        @if(auth()->user()->isHR())
          <a class="quick-btn" href="{{ route('hr.leave.index') }}">
            <div class="quick-icon quick-icon-navy">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Approval Izin/Cuti</div>
              <div class="quick-desc">Verifikasi pengajuan</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>

          <a class="quick-btn" href="{{ route('hr.approval_attendance.index') }}">
            <div class="quick-icon quick-icon-teal">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Approval Absensi</div>
              <div class="quick-desc">Verifikasi absensi</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>

          <a class="quick-btn" href="{{ route('hr.overtime-requests.index') }}">
            <div class="quick-icon quick-icon-orange">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Approval Lembur</div>
              <div class="quick-desc">Verifikasi lembur</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>

          <a class="quick-btn" href="{{ route('hr.employees.index') }}">
            <div class="quick-icon quick-icon-blue">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Data Karyawan</div>
              <div class="quick-desc">Kelola data karyawan</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>

          <a class="quick-btn" href="{{ route('hr.attendances.index') }}">
            <div class="quick-icon quick-icon-green">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Rekap Absensi</div>
              <div class="quick-desc">Monitor kehadiran</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>

          <a class="quick-btn" href="{{ route('hr.loan_requests.index') }}">
            <div class="quick-icon quick-icon-amber">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Approval Pinjaman</div>
              <div class="quick-desc">Kelola pengajuan hutang</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>

          <a class="quick-btn" href="{{ route('hr.organization') }}">
            <div class="quick-icon quick-icon-purple">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Organisasi</div>
              <div class="quick-desc">Divisi & jabatan</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>

          <a class="quick-btn" href="{{ route('hr.supervisors.index') }}">
            <div class="quick-icon quick-icon-teal">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Data Supervisor</div>
              <div class="quick-desc">Kelola supervisor</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>
        @endif

        @if(auth()->user()->isManager())
          <a class="quick-btn" href="{{ route('hr.employees.index') }}">
            <div class="quick-icon quick-icon-blue">
              <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
            <div class="quick-text">
              <div class="quick-name">Data Karyawan</div>
              <div class="quick-desc">Kelola data karyawan</div>
            </div>
            <div class="quick-arrow">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </a>
        @endif

      </div>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- PENGATURAN SECTION --}}
    {{-- ============================================ --}}
    <div class="section-wrap">
      <div class="section-header">
        <div class="section-title">Pengaturan</div>
        <div class="section-sub">Kelola akun dan preferensi</div>
      </div>

      <div class="quick-grid">

        <a class="quick-btn" href="{{ route('settings.password') }}">
          <div class="quick-icon quick-icon-gray">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
          </div>
          <div class="quick-text">
            <div class="quick-name">Ubah Password</div>
            <div class="quick-desc">Ganti password akun</div>
          </div>
          <div class="quick-arrow">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>

        <a class="quick-btn" href="{{ route('employee.loan_requests.index') }}">
          <div class="quick-icon quick-icon-gray">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
          </div>
          <div class="quick-text">
            <div class="quick-name">Pengajuan Hutang</div>
            <div class="quick-desc">Riwayat pengajuan hutang</div>
          </div>
          <div class="quick-arrow">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>

      </div>
    </div>

  </div>

  <style>
    /* ========================================== */
    /* DASHBOARD LAYOUT */
    /* ========================================== */
    .dashboard {
      display: flex;
      flex-direction: column;
      gap: 24px;
      padding-bottom: 24px;
    }

    /* ========================================== */
    /* WELCOME SECTION */
    /* ========================================== */
    .welcome-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(135deg, #1e4a8d 0%, #2563eb 100%);
      border-radius: 16px;
      padding: 24px 28px;
      color: #fff;
    }

    .welcome-greeting {
      font-size: 1.35rem;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .welcome-sub {
      font-size: 0.9rem;
      opacity: 0.85;
    }

    .role-badge {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 600;
      margin-left: 4px;
    }

    .role-hrd { background: rgba(255,255,255,0.2); }
    .role-supervisor { background: rgba(255,255,255,0.2); }
    .role-manager { background: rgba(255,255,255,0.2); }
    .role-employee { background: rgba(255,255,255,0.2); }

    .welcome-date {
      text-align: right;
    }

    .date-main {
      font-size: 1.1rem;
      font-weight: 700;
    }

    .date-sub {
      font-size: 0.85rem;
      opacity: 0.8;
    }

    /* ========================================== */
    /* INFO CARDS */
    /* ========================================== */
    .info-cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
    }

    .info-card {
      background: #fff;
      border-radius: 14px;
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 14px;
      border: 1px solid #f3f4f6;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
      transition: all 0.2s ease;
    }

    .info-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .info-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .info-icon-navy { background: #eef4ff; color: #1e4a8d; }
    .info-icon-teal { background: #ccfbf1; color: #0f766e; }
    .info-icon-blue { background: #dbeafe; color: #1d4ed8; }
    .info-icon-amber { background: #fef3c7; color: #d97706; }
    .info-icon-green { background: #dcfce7; color: #16a34a; }
    .info-icon-red { background: #fee2e2; color: #dc2626; }
    .info-icon-purple { background: #f3e8ff; color: #9333ea; }
    .info-icon-orange { background: #ffedd5; color: #ea580c; }
    .info-icon-yellow { background: #fef9c3; color: #ca8a04; }
    .info-icon-gray { background: #f3f4f6; color: #4b5563; }

    .info-content {
      flex: 1;
      min-width: 0;
    }

    .info-label {
      font-size: 0.75rem;
      color: #6b7280;
      font-weight: 500;
      margin-bottom: 2px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    .info-value {
      font-size: 1rem;
      font-weight: 700;
      color: #111827;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .info-unit {
      font-size: 0.8rem;
      font-weight: 500;
      color: #6b7280;
    }

    /* ========================================== */
    /* SECTION WRAPPER */
    /* ========================================== */
    .section-wrap {
      background: #fff;
      border-radius: 16px;
      padding: 24px;
      border: 1px solid #f3f4f6;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .section-header {
      margin-bottom: 18px;
    }

    .section-title {
      font-size: 1rem;
      font-weight: 700;
      color: #111827;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .role-indicator {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 6px;
      font-size: 0.7rem;
      font-weight: 700;
    }

    .role-hrd { background: #eef4ff; color: #1e4a8d; }
    .role-supervisor { background: #fef9c3; color: #854d0e; }
    .role-manager { background: #f3e8ff; color: #6b21a8; }

    .section-sub {
      font-size: 0.85rem;
      color: #6b7280;
      margin-top: 2px;
    }

    /* ========================================== */
    /* QUICK GRID */
    /* ========================================== */
    .quick-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 14px;
    }

    .quick-btn {
      display: flex;
      align-items: center;
      gap: 14px;
      text-decoration: none;
      color: inherit;
      background: #f9fafb;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 16px;
      transition: all 0.2s ease;
    }

    .quick-btn:hover {
      background: #fff;
      border-color: #1e4a8d;
      box-shadow: 0 4px 12px rgba(30, 74, 141, 0.08);
      transform: translateY(-2px);
    }

    .quick-btn:active {
      transform: scale(0.98);
    }

    .quick-icon {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .quick-icon-green { background: #dcfce7; color: #16a34a; }
    .quick-icon-red { background: #fee2e2; color: #dc2626; }
    .quick-icon-navy { background: #eef4ff; color: #1e4a8d; }
    .quick-icon-teal { background: #ccfbf1; color: #0f766e; }
    .quick-icon-purple { background: #f3e8ff; color: #9333ea; }
    .quick-icon-orange { background: #ffedd5; color: #ea580c; }
    .quick-icon-blue { background: #dbeafe; color: #1d4ed8; }
    .quick-icon-amber { background: #fef3c7; color: #d97706; }
    .quick-icon-yellow { background: #fef9c3; color: #ca8a04; }
    .quick-icon-gray { background: #f3f4f6; color: #4b5563; }

    .quick-text {
      flex: 1;
      min-width: 0;
    }

    .quick-name {
      font-weight: 700;
      font-size: 0.9rem;
      color: #1f2937;
      margin-bottom: 2px;
    }

    .quick-desc {
      font-size: 0.8rem;
      color: #6b7280;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .quick-arrow {
      color: #9ca3af;
      transition: all 0.2s ease;
    }

    .quick-btn:hover .quick-arrow {
      color: #1e4a8d;
      transform: translateX(3px);
    }

    /* ========================================== */
    /* RESPONSIVE */
    /* ========================================== */
    @media (max-width: 1024px) {
      .info-cards {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .dashboard {
        gap: 16px;
      }

      .welcome-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 20px;
      }

      .welcome-date {
        text-align: left;
      }

      .welcome-greeting {
        font-size: 1.15rem;
      }

      .info-cards {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
      }

      .info-card {
        padding: 16px;
      }

      .info-icon {
        width: 40px;
        height: 40px;
      }

      .info-value {
        font-size: 0.9rem;
      }

      .section-wrap {
        padding: 20px 16px;
      }

      .quick-grid {
        grid-template-columns: 1fr;
        gap: 10px;
      }

      .quick-btn {
        padding: 14px;
      }

      .quick-icon {
        width: 42px;
        height: 42px;
      }
    }

    @media (max-width: 480px) {
      .info-cards {
        grid-template-columns: 1fr;
      }

      .info-card {
        flex-direction: row;
      }
    }
  </style>
</x-app>
