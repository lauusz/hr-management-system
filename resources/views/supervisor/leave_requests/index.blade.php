{{-- resources/views/supervisor/leave_requests/index.blade.php --}}
@php
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Enums\LeaveType;
use App\Enums\UserRole;

// --- 1. LOGIC PEMBEDA (MANAGER vs SUPERVISOR) ---
$user = auth()->user();
// Ambil role sebagai string (handle jika Enum)
$roleVal = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
$roleStr = strtoupper((string)$roleVal);

// Default Variables
$pageTitle = 'Inbox Approval';
$subTitle  = 'Daftar pengajuan yang membutuhkan persetujuan Anda.';
$roleBadge = 'Atasan';

if ($roleStr === 'MANAGER') {
    $pageTitle = 'Inbox Approval Manager';
    $subTitle  = 'Daftar pengajuan dari Supervisor yang membutuhkan persetujuan Anda.';
    $roleBadge = 'Manager';
} elseif ($roleStr === 'SUPERVISOR' || $roleStr === 'SPV') {
    $pageTitle = 'Inbox Approval Supervisor';
    $subTitle  = 'Daftar pengajuan dari Staff yang membutuhkan persetujuan Anda.';
    $roleBadge = 'Supervisor';
}

$isApprover = $isApprover ?? false; 
@endphp

<x-app :title="$pageTitle">

  @if(session('success'))
  <div class="alert-success">
    {{ session('success') }}
  </div>
  @endif
  
  @if(session('error'))
  <div class="alert-danger">
    {{ session('error') }}
  </div>
  @endif

  @if ($errors->any())
  <div class="alert-danger">
    {{ $errors->first() }}
  </div>
  @endif

  <div class="card">
    <div class="card-header-simple">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h4 class="card-title-sm">
                    Daftar Pengajuan Masuk
                    <span class="role-indicator">{{ $roleBadge }} Area</span>
                </h4>
                <p class="card-subtitle-sm">
                    {{ $subTitle }}
                </p>
            </div>
            <div style="text-align:right; font-size:12px; color:#6b7280;">
                Total: <strong>{{ $leaves->total() }}</strong> Pengajuan
            </div>
        </div>
    </div>

    <div class="table-wrapper">
      <table class="custom-table">
        <thead>
          <tr>
            <th style="min-width: 220px;">Pemohon</th>
            <th>Jenis</th>
            <th>Status</th>
            <th style="min-width: 170px;">Periode Izin</th>
            <th style="min-width: 220px;">Alasan</th>
            <th class="text-right" style="width: 100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($leaves as $lv)
            @php
                // --- 2. LOGIC WARNA BADGE JENIS CUTI ---
                $type = $lv->type;
                $badgeClass = 'badge-gray';
                
                if (in_array($type, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
                    $badgeClass = 'badge-blue';
                } elseif ($type === \App\Enums\LeaveType::SAKIT->value) {
                    $badgeClass = 'badge-yellow';
                } elseif (in_array($type, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value])) {
                    $badgeClass = 'badge-orange';
                } elseif ($type === \App\Enums\LeaveType::DINAS_LUAR->value) {
                    $badgeClass = 'badge-purple';
                }

                // --- 3. LOGIC STATUS BADGE (KONSISTEN) ---
                $statusBadge = 'badge-gray';
                $statusLabel = $lv->status;

                if ($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                    // Status ini muncul di inbox berarti menunggu action user ini
                    $statusBadge = 'badge-yellow';
                    $statusLabel = '⏳ Menunggu Persetujuan Anda';
                } 
                elseif ($lv->status == \App\Models\LeaveRequest::PENDING_HR) {
                    // Sudah di-approve user ini, lanjut ke HR
                    $statusBadge = 'badge-teal';
                    $statusLabel = '✅ Atasan Mengetahui';
                } 
                elseif ($lv->status == \App\Models\LeaveRequest::STATUS_APPROVED) {
                    $statusBadge = 'badge-green';
                    $statusLabel = 'Disetujui Final (HR)';
                } 
                elseif ($lv->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                    $statusBadge = 'badge-red';
                    $statusLabel = 'Ditolak';
                }
            @endphp

            <tr>
                <td>
                    <div class="employee-info">
                        <span class="fw-bold">{{ $lv->user->name ?? '—' }}</span>
                        <div class="sub-info">
                            <span class="chip-role">{{ $lv->user->role }}</span>
                            <span class="text-muted">• {{ $lv->user->division->name ?? 'Divisi -' }}</span>
                        </div>
                    </div>
                </td>

                <td>
                    <span class="badge-type {{ $badgeClass }}">
                        {{ $lv->type_label ?? $lv->type }}
                    </span>
                </td>

                <td>
                    <span class="badge-type {{ $statusBadge }}">
                        {{ $statusLabel }}
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
                        {{ Str::limit($lv->reason, 60) }}
                    </div>
                </td>

                <td class="text-right">
                    {{-- Tombol Aksi: Muncul HANYA jika status masih Pending --}}
                    @if($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                        <a href="{{ route('approval.show', $lv) }}" class="btn-action btn-action-primary">
                            Proses
                        </a>
                    @else
                        <a href="{{ route('approval.show', $lv) }}" class="btn-action">
                            Detail
                        </a>
                    @endif
                </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="empty-state">
                <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                    <svg width="40" height="40" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Tidak ada pengajuan yang perlu diproses saat ini.</span>
                </div>
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
    
    .employee-info { display: flex; flex-direction: column; gap: 2px; }
    .sub-info { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    
    .chip-role { 
        background: #f3f4f6; color: #374151; 
        padding: 2px 6px; border-radius: 4px; 
        font-size: 10px; font-weight: 700; 
        text-transform: uppercase; letter-spacing: 0.03em;
    }

    .role-indicator {
        font-size: 11px; background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 12px; margin-left: 8px; vertical-align: middle; font-weight: 600; text-transform: uppercase;
    }

    .text-truncate {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 13.5px;
        color: #4b5563;
    }

    /* --- ALERTS --- */
    .alert-success { background: #ecfdf5; color: #065f46; padding: 12px 16px; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 14px; }
    .alert-danger { background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 16px; font-size: 14px; }

    /* --- CARD --- */
    .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); border: 1px solid #f3f4f6; overflow: hidden; padding: 0; }
    .card-header-simple { padding: 16px 24px; border-bottom: 1px solid #f3f4f6; background: #fff; }
    .card-title-sm { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
    .card-subtitle-sm { margin: 4px 0 0; font-size: 13px; color: #6b7280; }

    /* --- TABLE --- */
    .table-wrapper { width: 100%; }
    .custom-table { width: 100%; border-collapse: collapse; }

    .custom-table th { background: #f9fafb; padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb; }
    .custom-table td { padding: 14px 16px; border-bottom: 1px solid #f3f4f6; font-size: 13.5px; color: #1f2937; vertical-align: top; }
    .custom-table tr:last-child td { border-bottom: none; }
    .custom-table tr:hover td { background: #fdfdfd; }

    /* --- BADGES --- */
    .badge-type { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .badge-blue { background: #eff6ff; color: #1d4ed8; }   
    .badge-yellow { background: #fefce8; color: #a16207; } 
    .badge-orange { background: #fff7ed; color: #c2410c; } 
    .badge-purple { background: #f3e8ff; color: #7e22ce; }
    .badge-gray { background: #f3f4f6; color: #374151; }
    .badge-green { background: #dcfce7; color: #166534; }
    .badge-red { background: #fee2e2; color: #991b1b; }
    
    /* Badge Teal untuk "Atasan Mengetahui" */
    .badge-teal { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }

    /* --- ACTION BUTTONS --- */
    .btn-action { padding: 6px 14px; border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 20px; font-size: 12px; font-weight: 500; text-decoration: none; display: inline-block; transition: all 0.2s; white-space: nowrap; }
    .btn-action:hover { background: #f3f4f6; border-color: #9ca3af; }

    .btn-action-primary { background: #1e4a8d; color: #fff; border-color: #1e4a8d; }
    .btn-action-primary:hover { background: #163a75; border-color: #163a75; color: #fff; }

    .empty-state { padding: 60px 20px; text-align: center; color: #9ca3af; font-style: italic; }

    /* --- RESPONSIVE CARD VIEW --- */
    @media screen and (max-width: 768px) {
        .table-wrapper {
            background: transparent;
        }
        
        .custom-table, 
        .custom-table tbody, 
        .custom-table tr, 
        .custom-table td {
            display: block;
            width: 100%;
        }

        .custom-table thead { display: none; }

        .custom-table tr {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 12px;
            border: 1px solid #f3f4f6;
            padding: 16px;
            position: relative;
        }

        .custom-table td {
            padding: 4px 0;
            border: none;
            text-align: left;
        }

        /* Rewrite Layout for Card */
        
        /* 1. Header Card: Pemohon (Top Row) */
        .custom-table td:nth-child(1) { /* Pemohon */
            margin-bottom: 8px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 12px;
        }
        .custom-table td:nth-child(1) .employee-info .fw-bold { font-size: 15px; }

        /* 2. Status & Type - Flex Row */
        .custom-table td:nth-child(2), /* Jenis */
        .custom-table td:nth-child(3) { /* Status */
            display: inline-block;
            width: auto;
            margin-right: 8px;
            margin-bottom: 8px;
        }

        /* 3. Date & Reason */
        .custom-table td:nth-child(4) { /* Periode */
            display: block;
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 8px;
        }
        .custom-table td:nth-child(4)::before { content: '📅 '; }

        .custom-table td:nth-child(5) { /* Alasan */
            margin-top: 4px;
            font-style: italic;
            color: #6b7280;
            font-size: 13px;
            padding: 8px 12px;
            background: #fefce8;
            border-radius: 8px;
            border: 1px dashed #fcd34d;
        }
        .text-truncate { max-width: none; white-space: normal; }

        /* 5. Action Button */
        .custom-table td:last-child {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
            text-align: right;
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            flex: 1;
            text-align: center;
            justify-content: center;
            display: flex;
            align-items: center;
        }
        
        /* Empty State */
        .custom-table tr:has(.empty-state) {
            text-align: center;
            padding: 40px 20px;
        }
    }
  </style>

</x-app>