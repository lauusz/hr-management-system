<x-app title="Dashboard">
  <div class="dashboard">

    <div class="grid-cards">
      <div class="card">
        <div class="label">Selamat datang</div>
        <div class="value">{{ auth()->user()->name }}!</div>
      </div>
      <div class="card">
        <div class="label">Role</div>
        <div class="value">{{ auth()->user()->role }}</div>
      </div>
      <divdiv class="card">
        <div class="label">Divisi</div>
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
  .dashboard{
    display:flex;
    flex-direction:column;
    gap:20px;
  }

  .grid-cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:14px;
    margin-bottom:12px;
  }

  .card{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 14px rgba(0,0,0,.04);
    padding:18px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    transition:transform .15s ease, box-shadow .15s ease;
  }

  .card:hover{
    transform:translateY(-2px);
    box-shadow:0 6px 18px rgba(0,0,0,.08);
  }

  .label{
    font-size:13px;
    color:#666;
    margin-bottom:6px;
  }

  .value{
    font-size:18px;
    font-weight:700;
    color:#1e4a8d;
    line-height:1.2;
  }

  .quick-wrap{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 14px rgba(0,0,0,.04);
    padding:18px;
    margin-top:14px;
  }

  .quick-head{
    margin-bottom:12px;
  }

  .quick-title{
    font-size:15px;
    font-weight:800;
    color:#1e4a8d;
    line-height:1.2;
  }

  .quick-sub{
    margin-top:4px;
    font-size:13px;
    color:#666;
  }

  .quick-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:12px;
  }

  .qbtn{
    display:flex;
    align-items:center;
    gap:10px;
    text-decoration:none;
    color:inherit;
    background:#f6f7fb;
    border:1px solid rgba(30,74,141,.10);
    border-radius:14px;
    padding:12px 12px;
    min-height:60px;
    transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
  }

  .qbtn:hover{
    transform:translateY(-1px);
    border-color:rgba(30,74,141,.20);
    box-shadow:0 6px 16px rgba(0,0,0,.06);
  }

  .qbtn:active{
    transform:scale(.99);
  }

  .qicon{
    width: 42px;
    height: 42px;
    display: grid;
    place-items: center;
    flex: 0 0 42px;
  }

  .qicon img{
    width: 42px;
    height: 42px;
    max-width: 42px;
    max-height: 42px;
    object-fit: contain;
    display: block;
  }

  .qtext{
    min-width:0;
    flex:1;
  }

  .qname{
    font-weight:800;
    font-size:13px;
    color:#1e4a8d;
    line-height:1.2;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  .qdesc{
    margin-top:3px;
    font-size:12px;
    color:#666;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  .qgo{
    font-size:18px;
    color:#1e4a8d;
    opacity:.55;
    flex:0 0 auto;
  }

  @media (max-width:600px){
    .dashboard{
      gap:18px;
    }

    .grid-cards{
      grid-template-columns:1fr;
      gap:12px;
      margin-bottom:2px;
    }

    .card{
      padding:16px;
    }

    .value{
      font-size:16px;
    }

    .quick-wrap{
      padding:16px;
      margin-top:0;
    }

    .quick-grid{
      grid-template-columns:1fr;
      gap:10px;
    }

    .qbtn{
      min-height:58px;
    }
  }
</style>

</x-app>
