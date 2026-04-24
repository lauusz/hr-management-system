{{-- resources/views/supervisor/leave_requests/index.blade.php --}}
@php
use Illuminate\Support\Str;
use App\Enums\LeaveType;
use App\Enums\UserRole;

$user = auth()->user();
$roleVal = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
$roleStr = strtoupper((string)$roleVal);

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

    <div class="approval-container">

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="flash flash-success">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="flash flash-error">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if ($errors->any())
        <div class="flash flash-error">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        {{-- Page Header --}}
        <div class="page-header">
            <div class="page-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="page-header-text">
                <h1 class="page-title">{{ $pageTitle }}</h1>
                <p class="page-subtitle">{{ $subTitle }}</p>
            </div>
            <div class="page-header-meta">
                <span class="role-indicator role-{{ strtolower($roleBadge) }}">{{ $roleBadge }}</span>
                <span class="total-badge">{{ $leaves->total() }} Pengajuan</span>
            </div>
        </div>

        {{-- Cards List --}}
        @if($leaves->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>Tidak ada pengajuan yang perlu diproses saat ini.</p>
        </div>
        @else
        <div class="cards-list">
            @foreach($leaves as $lv)
            @php
                $type = $lv->type;
                $badgeClass = 'badge-basic';
                $badgeBg = 'var(--bg-body)';
                $badgeColor = 'var(--text-main)';

                if (in_array($type, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
                    $badgeClass = 'badge-cuti';
                    $badgeBg = 'var(--blue-light)';
                    $badgeColor = 'var(--blue-text)';
                } elseif ($type === \App\Enums\LeaveType::SAKIT->value) {
                    $badgeClass = 'badge-sakit';
                    $badgeBg = '#fce7f3';
                    $badgeColor = '#be185d';
                } elseif (in_array($type, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value])) {
                    $badgeClass = 'badge-izin';
                    $badgeBg = 'var(--warning-bg)';
                    $badgeColor = 'var(--warning-text)';
                } elseif ($type === \App\Enums\LeaveType::DINAS_LUAR->value) {
                    $badgeClass = 'badge-dinas';
                    $badgeBg = 'var(--purple-light)';
                    $badgeColor = 'var(--purple-text)';
                } elseif ($type === \App\Enums\LeaveType::OFF_SPV->value) {
                    $badgeClass = 'badge-offspv';
                    $badgeBg = '#f3e8ff';
                    $badgeColor = '#6b21a8';
                }

                $statusBg = 'var(--bg-body)';
                $statusColor = 'var(--text-muted)';
                $statusLabel = $lv->status;
                $isPending = false;

                if ($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                    $statusBg = 'var(--warning-bg)';
                    $statusColor = 'var(--warning-text)';
                    $statusLabel = 'Menunggu Approval';
                    $isPending = true;
                } elseif ($lv->status == \App\Models\LeaveRequest::PENDING_HR) {
                    $statusBg = 'var(--teal-bg)';
                    $statusColor = 'var(--teal-text)';
                    $statusLabel = 'Atasan Mengetahui';
                } elseif ($lv->status == \App\Models\LeaveRequest::STATUS_APPROVED) {
                    $statusBg = 'var(--success-bg)';
                    $statusColor = 'var(--success-text)';
                    $statusLabel = 'Disetujui';
                } elseif ($lv->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                    $statusBg = 'var(--danger-bg)';
                    $statusColor = 'var(--danger-text)';
                    $statusLabel = 'Ditolak';
                }
            @endphp

            <div class="approval-card {{ $isPending ? 'card-pending' : '' }}">
                {{-- Card Header --}}
                <div class="card-header">
                    <div class="card-header-left">
                        <div class="employee-avatar">{{ substr($lv->user->name, 0, 1) }}</div>
                        <div class="employee-info">
                            <span class="employee-name">{{ $lv->user->name }}</span>
                            <span class="employee-detail">{{ $lv->user->position->name ?? '-' }} — {{ $lv->user->division->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="card-header-right">
                        <span class="badge-status" style="background: {{ $statusBg }}; color: {{ $statusColor }};">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="card-body">
                    <div class="card-row">
                        <div class="card-item">
                            <span class="card-label">Jenis</span>
                            <span class="badge" style="background: {{ $badgeBg }}; color: {{ $badgeColor }};">
                                {{ $lv->type_label ?? $lv->type }}
                            </span>
                        </div>
                        <div class="card-item">
                            <span class="card-label">Pengajuan</span>
                            <span class="card-value">{{ $lv->created_at->translatedFormat('j F Y') }}</span>
                        </div>
                    </div>

                    <div class="card-item card-item-full">
                        <span class="card-label">Periode Izin</span>
                        <span class="card-value card-value-date">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            {{ $lv->start_date->translatedFormat('j F Y') }}
                            @if($lv->end_date && $lv->end_date->ne($lv->start_date))
                                — {{ $lv->end_date->translatedFormat('j F Y') }}
                            @endif
                        </span>
                    </div>

                    @if($lv->reason)
                    <div class="card-item card-item-full">
                        <span class="card-label">Alasan</span>
                        <span class="card-value card-value-reason">{{ $lv->reason }}</span>
                    </div>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="card-footer">
                    @if($isPending)
                        <a href="{{ route('approval.show', $lv) }}" class="btn btn-primary btn-full">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
                            Proses Sekarang
                        </a>
                    @else
                        <a href="{{ route('approval.show', $lv) }}" class="btn btn-secondary btn-full">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Lihat Detail
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Pagination --}}
        @if($leaves->hasPages())
        <div class="pagination-wrap">
            {{ $leaves->links() }}
        </div>
        @endif

    </div>

    <style>
        /* === BASE VARIABLES === */
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success-bg: #f0fdf4;
            --success-text: #15803d;
            --success-border: #bbf7d0;
            --danger-bg: #fef2f2;
            --danger-text: #b91c1c;
            --danger-border: #fecaca;
            --warning-bg: #fffbeb;
            --warning-text: #c2410c;
            --warning-border: #fed7aa;
            --blue-light: #eff6ff;
            --blue-text: #1d4ed8;
            --purple-light: #faf5ff;
            --purple-text: #7e22ce;
            --teal-bg: #ccfbf1;
            --teal-text: #0f766e;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        /* === RESET & BASE === */
        .approval-container {
            max-width: 680px;
            margin: 0 auto;
            padding: 20px 16px 40px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            color: var(--text-main);
        }

        /* === FLASH MESSAGES === */
        .flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .flash-success { background: var(--success-bg); color: var(--success-text); border: 1px solid var(--success-border); }
        .flash-error { background: var(--danger-bg); color: var(--danger-text); border: 1px solid var(--danger-border); }
        .flash-icon { width: 18px; height: 18px; flex-shrink: 0; }

        /* === PAGE HEADER === */
        .page-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .page-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            color: #fff;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .page-icon svg { width: 24px; height: 24px; }
        .page-header-text { flex: 1; min-width: 200px; }
        .page-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .page-subtitle {
            margin: 4px 0 0;
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        .page-header-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }
        .role-indicator {
            display: inline-flex;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .role-indicator.role-supervisor {
            background: var(--warning-bg);
            color: var(--warning-text);
        }
        .role-indicator.role-manager {
            background: var(--purple-light);
            color: var(--purple-text);
        }
        .role-indicator.role-atasan {
            background: var(--blue-light);
            color: var(--blue-text);
        }
        .total-badge {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* === CARDS LIST === */
        .cards-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* === APPROVAL CARD === */
        .approval-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .approval-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .approval-card.card-pending {
            border-left: 4px solid var(--primary);
        }

        /* Card Header */
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            gap: 12px;
        }
        .card-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }
        .card-header-right {
            flex-shrink: 0;
        }
        .employee-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            background: var(--blue-light);
            color: var(--blue-text);
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .employee-info {
            min-width: 0;
        }
        .employee-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .employee-detail {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Card Body */
        .card-body {
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .card-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .card-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .card-item-full {
            width: 100%;
        }
        .card-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .card-value {
            font-size: 0.875rem;
            color: var(--text-main);
        }
        .card-value-date {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        .card-value-date svg {
            width: 14px;
            height: 14px;
            color: var(--text-muted);
        }
        .card-value-reason {
            color: var(--text-muted);
            line-height: 1.5;
            font-size: 0.8125rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* === BADGES === */
        .badge {
            display: inline-flex;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.02em;
        }
        .badge-basic { background: var(--bg-body); color: var(--text-main); border: 1px solid var(--border); }
        .badge-cuti { }
        .badge-sakit { }
        .badge-izin { }
        .badge-dinas { }
        .badge-offspv { }

        .badge-status {
            display: inline-flex;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        /* === CARD FOOTER === */
        .card-footer {
            padding: 12px 16px;
            border-top: 1px solid var(--border);
            background: var(--bg-body);
        }

        /* === BUTTONS === */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn svg { width: 16px; height: 16px; }
        .btn-full { width: 100%; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: var(--bg-card); color: var(--text-main); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--border); }

        /* === EMPTY STATE === */
        .empty-state {
            padding: 60px 24px;
            text-align: center;
            color: var(--text-muted);
        }
        .empty-state svg { width: 56px; height: 56px; margin-bottom: 16px; opacity: 0.3; }
        .empty-state p { font-size: 0.95rem; margin: 0; }

        /* === PAGINATION === */
        .pagination-wrap {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        /* === MOBILE RESPONSIVE === */
        @media (max-width: 640px) {
            .page-header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            .page-icon { margin: 0 auto; }
            .page-header-meta {
                align-items: center;
                width: 100%;
            }
            .total-badge { font-size: 0.85rem; }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .card-header-right {
                align-self: flex-start;
                margin-top: 8px;
            }

            .card-row {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                padding: 14px 20px;
            }
        }
    </style>

</x-app>
