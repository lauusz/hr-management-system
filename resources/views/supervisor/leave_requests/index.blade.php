{{-- resources/views/supervisor/leave_requests/index.blade.php --}}
@php
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Enums\LeaveType;
@endphp

<x-app title="Pengajuan Menunggu Supervisor">

  @if(session('success'))
  <div class="alert-success">
    {{ session('success') }}
  </div>
  @endif
  
  @if ($errors->any())
  <div class="alert-danger">
    {{ $errors->first() }}
  </div>
  @endif

  <div class="card">
    <div class="card-header-simple">
        <h4 class="card-title-sm">Menunggu Persetujuan</h4>
        <p class="card-subtitle-sm">
            Menampilkan {{ $leaves->firstItem() ?? 0 }}–{{ $leaves->lastItem() ?? 0 }} dari {{ $leaves->total() }} pengajuan.
        </p>
    </div>

    <div class="table-wrapper">
      <table class="custom-table">
        <thead>
          <tr>
            <th style="min-width: 200px;">Karyawan</th>
            <th>Jenis</th>
            <th style="min-width: 180px;">Periode Izin</th>
            <th style="min-width: 220px;">Alasan</th>
            <th class="text-right" style="width: 100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($leaves as $lv)
            @php
                // Logic Warna Badge Jenis (Opsional: sesuaikan dengan Enum/Value di sistem Anda)
                $type = $lv->type;
                $badgeClass = 'badge-gray';
                
                // Contoh logika warna sederhana berdasarkan string/enum
                if ($type === 'CUTI_TAHUNAN' || $type === 'CUTI') {
                    $badgeClass = 'badge-blue';
                } elseif ($type === 'SAKIT') {
                    $badgeClass = 'badge-yellow';
                } elseif ($type === 'IZIN_TELAT' || $type === 'IZIN_PULANG_CEPAT') {
                    $badgeClass = 'badge-orange';
                } elseif ($type === 'CUTI_KHUSUS') {
                    $badgeClass = 'badge-purple';
                }
            @endphp

            <tr>
                <td>
                    <div class="user-info">
                        <span class="fw-bold">{{ $lv->user->name ?? '—' }}</span>
                        </div>
                </td>

                <td>
                    <span class="badge-type {{ $badgeClass }}">
                        {{ $lv->type_label ?? $lv->type }}
                    </span>
                </td>

                <td>
                    <span class="text-date">
                        {{ $lv->start_date->format('d M Y') }}
                        @if($lv->end_date && $lv->end_date->ne($lv->start_date))
                        – {{ $lv->end_date->format('d M Y') }}
                        @endif
                    </span>
                </td>

                <td>
                    <div class="text-truncate" title="{{ $lv->reason }}">
                        {{ Str::limit($lv->reason, 80) }}
                    </div>
                </td>

                <td class="text-right">
                    <a href="{{ route('supervisor.leave.show', $lv) }}" class="btn-action">
                        Detail
                    </a>
                </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="empty-state">
                Tidak ada pengajuan yang perlu diproses.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div style="margin-top:20px;">
    {{ $leaves->links() }}
  </div>

  <style>
    /* --- UTILITY --- */
    .fw-bold { font-weight: 600; color: #111827; }
    .text-muted { color: #6b7280; font-size: 13px; }
    .text-right { text-align: right; }
    .text-date { font-weight: 500; color: #1f2937; font-size: 13.5px; }
    
    .text-truncate {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 13.5px;
        color: #4b5563;
    }

    /* --- ALERTS --- */
    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid #a7f3d0;
        margin-bottom: 16px;
        font-size: 14px;
    }
    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid #fecaca;
        margin-bottom: 16px;
        font-size: 14px;
    }

    /* --- CARD --- */
    .card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        border: 1px solid #f3f4f6;
        overflow: hidden;
        padding: 0;
    }

    .card-header-simple {
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .card-title-sm { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
    .card-subtitle-sm { margin: 4px 0 0; font-size: 13px; color: #6b7280; }

    /* --- TABLE --- */
    .table-wrapper { width: 100%; overflow-x: auto; }
    .custom-table { width: 100%; border-collapse: collapse; min-width: 800px; }

    .custom-table th {
        background: #f9fafb;
        padding: 12px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e5e7eb;
    }

    .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13.5px;
        color: #1f2937;
        vertical-align: middle;
    }
    .custom-table tr:last-child td { border-bottom: none; }
    .custom-table tr:hover td { background: #fdfdfd; }

    /* --- BADGES --- */
    .badge-type {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .badge-blue { background: #eff6ff; color: #1d4ed8; }   
    .badge-yellow { background: #fefce8; color: #a16207; } 
    .badge-orange { background: #fff7ed; color: #c2410c; } 
    .badge-purple { background: #f3e8ff; color: #7e22ce; }
    .badge-gray { background: #f3f4f6; color: #374151; }

    /* --- ACTION BUTTONS --- */
    .btn-action {
        padding: 6px 14px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-action:hover { background: #f3f4f6; border-color: #9ca3af; }

    .empty-state { padding: 40px; text-align: center; color: #9ca3af; font-style: italic; }
  </style>

</x-app>