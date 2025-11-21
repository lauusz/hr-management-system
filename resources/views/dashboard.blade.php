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
      <div class="card">
        <div class="label">Divisi</div>
        <div class="value">{{ auth()->user()->division?->name ?? '-' }}</div>
      </div>
    </div>
  </div>

  <style>
    .dashboard{
      display:flex;
      flex-direction:column;
      gap:24px;
    }

    .grid-cards{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
      gap:16px;
    }

    .card{
      background:#fff;
      border-radius:12px;
      box-shadow:0 4px 14px rgba(0,0,0,.04);
      padding:20px;
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
      font-size:14px;
      color:#666;
      margin-bottom:6px;
    }

    .value{
      font-size:18px;
      font-weight:600;
      color:#1e4a8d;
    }

    @media (max-width:600px){
      .card{ padding:16px; }
      .value{ font-size:16px; }
    }
  </style>
</x-app>