<x-app title="Dashboard">
  <div class="dashboard">

    <div class="grid-cards">
      <div class="card card-welcome">
        <div class="label">Selamat datang</div>
        <div class="value">{{ auth()->user()->name }}!</div>
      </div>
      <div class="card">
        <div class="label">Role</div>
        <div class="value">{{ auth()->user()->role }}</div>
      </div>
      <div class="card"> <div class="label">Divisi</div>
        <div class="value">{{ auth()->user()->division?->name ?? '-' }}</div>
      </div>
    </div>

    <div class="quick-wrap">
      <div class="quick-head">
        <div class="quick-title">Quick Access</div>
        <div class="quick-sub">Akses cepat fitur utama</div>
      </div>

      <div class="quick-grid">

        <a class="qbtn" href="{{ route('attendance.clockIn.form') }}">
          <div class="qicon">
            <img src="{{ asset('icons/quick-access/clock-in.png') }}" alt="Clock In">
          </div>
          <div class="qtext">
            <div class="qname">Clock In</div>
            <div class="qdesc">Masuk kerja</div>
          </div>
          <div class="qgo">›</div>
        </a>

        <a class="qbtn" href="{{ route('attendance.clockOut.form') }}">
          <div class="qicon">
            <img src="{{ asset('icons/quick-access/clock-out.png') }}" alt="Clock Out">
          </div>
          <div class="qtext">
            <div class="qname">Clock Out</div>
            <div class="qdesc">Pulang kerja</div>
          </div>
          <div class="qgo">›</div>
        </a>

        <a class="qbtn" href="{{ route('leave-requests.create') }}">
          <div class="qicon">
            <img src="{{ asset('icons/quick-access/buat-izin.png') }}" alt="Buat Izin">
          </div>
          <div class="qtext">
            <div class="qname">Buat Izin</div>
            <div class="qdesc">Ajukan izin/cuti</div>
          </div>
          <div class="qgo">›</div>
        </a>

        <a class="qbtn" href="{{ route('leave-requests.index') }}">
          <div class="qicon">
            <img src="{{ asset('icons/quick-access/riwayat-izin.png') }}" alt="Riwayat Izin">
          </div>
          <div class="qtext">
            <div class="qname">Riwayat Izin</div>
            <div class="qdesc">Pantau status</div>
          </div>
          <div class="qgo">›</div>
        </a>

        <a class="qbtn" href="{{ route('employee.loan_requests.index') }}">
          <div class="qicon">
            <img src="{{ asset('icons/quick-access/pinjaman.png') }}" alt="Pinjaman">
          </div>
          <div class="qtext">
            <div class="qname">Pinjaman</div>
            <div class="qdesc">Daftar pengajuan</div>
          </div>
          <div class="qgo">›</div>
        </a>

        <a class="qbtn" href="{{ route('settings.password') }}">
          <div class="qicon">
            <img src="{{ asset('icons/quick-access/password.png') }}" alt="Password">
          </div>
          <div class="qtext">
            <div class="qname">Password</div>
            <div class="qdesc">Ganti password</div>
          </div>
          <div class="qgo">›</div>
        </a>
      </div>
    </div>

    @if (auth()->user()->role === 'HRD' || auth()->user()->role === 'SUPERVISOR')
      <div class="quick-wrap">
        <div class="quick-head">
          <div class="quick-title">Akses Role</div>
          <div class="quick-sub">Menu sesuai role kamu</div>
        </div>

        <div class="quick-grid">
          @if (auth()->user()->role === 'SUPERVISOR')
            <a class="qbtn" href="{{ route('supervisor.leave.index') }}">
              <div class="qicon">
                <img src="{{ asset('icons/quick-access/supervisor-approval.png') }}" alt="Ack Izin">
              </div>
              <div class="qtext">
                <div class="qname">Ack Izin</div>
                <div class="qdesc">Supervisor approval</div>
              </div>
              <div class="qgo">›</div>
            </a>
          @endif

          @if (auth()->user()->role === 'HRD')
            <a class="qbtn" href="{{ route('hr.leave.index') }}">
              <div class="qicon">
                <img src="{{ asset('icons/quick-access/approval-izin.png') }}" alt="Approval Izin">
              </div>
              <div class="qtext">
                <div class="qname">Approval Izin</div>
                <div class="qdesc">HRD approval</div>
              </div>
              <div class="qgo">›</div>
            </a>

            <a class="qbtn" href="{{ route('hr.employees.index') }}">
              <div class="qicon">
                <img src="{{ asset('icons/quick-access/karyawan.png') }}" alt="Karyawan">
              </div>
              <div class="qtext">
                <div class="qname">Karyawan</div>
                <div class="qdesc">Data & detail</div>
              </div>
              <div class="qgo">›</div>
            </a>

            <a class="qbtn" href="{{ route('hr.attendances.index') }}">
              <div class="qicon">
                <img src="{{ asset('icons/quick-access/rekap-absensi.png') }}" alt="Rekap Absensi">
              </div>
              <div class="qtext">
                <div class="qname">Rekap Absensi</div>
                <div class="qdesc">Monitor absensi</div>
              </div>
              <div class="qgo">›</div>
            </a>

            <a class="qbtn" href="{{ route('hr.loan_requests.index') }}">
              <div class="qicon">
                <img src="{{ asset('icons/quick-access/approval-pinjaman.png') }}" alt="Approval Pinjaman">
              </div>
              <div class="qtext">
                <div class="qname">Approval Pinjaman</div>
                <div class="qdesc">HRD review</div>
              </div>
              <div class="qgo">›</div>
            </a>

            <a class="qbtn" href="{{ route('hr.organization') }}">
              <div class="qicon">
                <img src="{{ asset('icons/quick-access/divisi.png') }}" alt="Organisasi">
              </div>
              <div class="qtext">
                <div class="qname">Organisasi</div>
                <div class="qdesc">Divisi & jabatan</div>
              </div>
              <div class="qgo">›</div>
            </a>
          @endif
        </div>
      </div>
    @endif

  </div>


  <style>
  /* --- LAYOUT UTAMA --- */
  .dashboard {
    display: flex;
    flex-direction: column;
    gap: 24px; /* Gap diperbesar agar antar section tidak mepet */
    padding-bottom: 24px;
  }

  /* --- GRID INFO CARDS --- */
  .grid-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Default desktop 3 kolom */
    gap: 20px;
  }

  .card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    transition: transform .2s ease, box-shadow .2s ease;
    border: 1px solid #f3f4f6;
  }

  .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
  }

  .label {
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.025em;
  }

  .value {
    font-size: 20px;
    font-weight: 700;
    color: #1e4a8d;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* --- WRAPPER QUICK ACCESS --- */
  .quick-wrap {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    padding: 24px;
    border: 1px solid #f3f4f6;
  }

  .quick-head {
    margin-bottom: 18px;
  }

  .quick-title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
  }

  .quick-sub {
    margin-top: 4px;
    font-size: 13.5px;
    color: #6b7280;
  }

  /* --- GRID BUTTONS --- */
  .quick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
  }

  .qbtn {
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    color: inherit;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 16px; /* Padding diperbesar agar tidak sesak */
    transition: all .2s ease;
    position: relative;
    overflow: hidden;
  }

  .qbtn:hover {
    background: #fff;
    border-color: #1e4a8d;
    box-shadow: 0 4px 12px rgba(30, 74, 141, 0.08);
    transform: translateY(-2px);
  }

  .qbtn:active {
    transform: scale(0.98);
  }

  .qicon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
  }

  .qicon img {
    width: 48px;
    height: 48px;
    object-fit: contain;
  }

  .qtext {
    flex: 1;
    min-width: 0;
  }

  .qname {
    font-weight: 700;
    font-size: 14px;
    color: #1f2937;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .qdesc {
    font-size: 12.5px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .qgo {
    font-size: 20px;
    color: #9ca3af;
    transition: transform 0.2s;
  }
  
  .qbtn:hover .qgo {
    color: #1e4a8d;
    transform: translateX(2px);
  }

  /* --- MOBILE RESPONSIVE --- */
  @media (max-width: 768px) {
    .dashboard {
      gap: 20px; /* Jarak antar container */
    }
    
    .grid-cards {
      /* Ubah layout kartu atas:
         Nama (full width)
         Role & Divisi (sebelahan)
      */
      grid-template-columns: 1fr 1fr;
    }

    .card-welcome {
      grid-column: span 2; /* Nama user memanjang penuh */
    }

    .card {
      padding: 16px; /* Sedikit compact tapi tetap lega */
    }

    .value {
      font-size: 16px; /* Font value sedikit dikecilkan agar muat */
    }

    .quick-wrap {
      padding: 20px 16px; /* Padding kiri-kanan container */
    }

    .quick-grid {
      grid-template-columns: 1fr; /* 1 Kolom ke bawah */
      gap: 12px;
    }

    .qbtn {
      padding: 14px;
    }
    
    .qicon {
        width: 42px;
        height: 42px;
    }
    
    .qicon img {
        width: 48px;
        height: 48px;
    }
  }
  </style>
</x-app>