<x-app title="Divisi & Jabatan">

  <div class="tabs-container">
    <button class="tab-btn active" data-target="#tab-divisi">
      Divisi
    </button>
    <button class="tab-btn" data-target="#tab-jabatan">
      Jabatan
    </button>
  </div>

  <div id="tab-divisi" class="tab-content active">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Divisi</h3>
        <a href="{{ route('hr.divisions.create') }}" class="btn-add">
          + Tambah Divisi
        </a>
      </div>

      <div class="table-wrapper">
        <table class="custom-table">
          <thead>
            <tr>
              <th>Nama Divisi</th>
              <th>Supervisor</th>
              <th class="text-center" style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($divisions as $division)
            <tr>
              <td class="fw-bold">{{ $division->name }}</td>
              <td>
                @if($division->supervisor)
                  <div class="user-info">
                    <span>{{ $division->supervisor->name }}</span>
                  </div>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                <div class="action-buttons">
                  <a href="{{ route('hr.divisions.edit', $division->id) }}" class="btn-action edit">
                    Edit
                  </a>
                  
                  <form method="POST" action="{{ route('hr.divisions.destroy', $division->id) }}" onsubmit="return confirm('Hapus divisi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action delete">
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="empty-state">
                Belum ada data divisi.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="tab-jabatan" class="tab-content" style="display: none;">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Jabatan</h3>
        <a href="{{ route('hr.positions.create') }}" class="btn-add">
          + Tambah Jabatan
        </a>
      </div>

      <div class="table-wrapper">
        <table class="custom-table">
          <thead>
            <tr>
              <th>Nama Jabatan</th>
              <th>Divisi</th>
              <th class="text-center" style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($positions as $position)
            <tr>
              <td class="fw-bold">{{ $position->name }}</td>
              <td>
                <span class="badge-divisi">{{ $position->division?->name ?? '-' }}</span>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="{{ route('hr.positions.edit', $position->id) }}" class="btn-action edit">
                    Edit
                  </a>

                  <form method="POST" action="{{ route('hr.positions.destroy', $position->id) }}" onsubmit="return confirm('Hapus jabatan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-action delete">
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="empty-state">
                Belum ada data jabatan.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <style>
    /* --- TABS --- */
    .tabs-container {
      display: flex;
      gap: 24px;
      border-bottom: 1px solid #e5e7eb;
      margin-bottom: 20px;
    }

    .tab-btn {
      padding: 12px 4px;
      background: none;
      border: none;
      font-size: 15px;
      cursor: pointer;
      color: #6b7280;
      font-weight: 500;
      border-bottom: 3px solid transparent;
      transition: all 0.2s;
    }

    .tab-btn:hover {
      color: #1e4a8d;
    }

    .tab-btn.active {
      color: #1e4a8d;
      font-weight: 700;
      border-bottom-color: #1e4a8d;
    }

    /* --- CARD & HEADER --- */
    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
      border: 1px solid #f3f4f6;
      overflow: hidden; /* Supaya sudut tabel ikut rounded */
    }

    .card-header {
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #f3f4f6;
      background: #fff;
    }

    .card-title {
      margin: 0;
      font-size: 16px;
      font-weight: 700;
      color: #1f2937;
    }

    .btn-add {
      padding: 8px 16px;
      border-radius: 8px;
      background: #1e4a8d;
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.2s;
    }

    .btn-add:hover {
      background: #163a75;
    }

    /* --- TABLE STYLING --- */
    .table-wrapper {
      width: 100%;
      overflow-x: auto; /* Agar bisa discroll di HP */
    }

    .custom-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 600px; /* Min width agar tabel tidak gepeng di HP */
    }

    .custom-table th,
    .custom-table td {
      padding: 14px 20px;
      text-align: left;
      border-bottom: 1px solid #f3f4f6;
      font-size: 14px;
    }

    .custom-table th {
      background: #f9fafb;
      font-weight: 600;
      color: #4b5563;
      text-transform: uppercase;
      font-size: 12px;
      letter-spacing: 0.05em;
    }

    .custom-table tr:last-child td {
      border-bottom: none;
    }
    
    .custom-table tr:hover td {
      background: #fdfdfd;
    }

    .fw-bold {
      font-weight: 600;
      color: #1f2937;
    }

    .text-muted {
      color: #9ca3af;
      font-style: italic;
    }
    
    .text-center {
        text-align: center !important;
    }

    .badge-divisi {
        background: #eef2ff;
        color: #1e4a8d;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    /* --- ACTION BUTTONS --- */
    .action-buttons {
      display: flex;
      justify-content: flex-end; /* Tombol rata kanan */
      gap: 8px;
    }

    .btn-action {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      display: inline-block;
      border: 1px solid transparent;
      transition: all 0.2s;
    }

    .btn-action.edit {
      background: #fff;
      border-color: #d1d5db;
      color: #374151;
    }

    .btn-action.edit:hover {
      background: #f3f4f6;
      border-color: #9ca3af;
    }

    .btn-action.delete {
      background: #fee2e2;
      border-color: #fecaca;
      color: #b91c1c;
    }

    .btn-action.delete:hover {
      background: #fecaca;
    }

    .empty-state {
      text-align: center;
      padding: 40px !important;
      color: #9ca3af;
    }
  </style>

  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const tabs = document.querySelectorAll('.tab-btn');
      const contents = document.querySelectorAll('.tab-content');

      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          // Reset Active State
          tabs.forEach(t => t.classList.remove('active'));
          contents.forEach(c => c.style.display = 'none');

          // Set New Active State
          tab.classList.add('active');
          const targetId = tab.getAttribute('data-target');
          const targetContent = document.querySelector(targetId);
          
          if(targetContent) {
              targetContent.style.display = 'block';
              // Add slight fade in effect
              targetContent.style.opacity = 0;
              setTimeout(() => targetContent.style.opacity = 1, 50);
          }
        });
      });
      
      // Init fade effect style
      contents.forEach(c => {
          c.style.transition = 'opacity 0.2s ease';
          if(c.style.display === 'none') c.style.opacity = 0;
      });
    });
  </script>
  @endpush

</x-app>