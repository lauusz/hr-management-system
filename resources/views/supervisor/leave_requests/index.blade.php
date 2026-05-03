{{-- resources/views/supervisor/leave_requests/index.blade.php --}}
@php
use Illuminate\Support\Str;
use App\Enums\LeaveType;

$user = auth()->user();
$roleVal = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
$roleStr = strtoupper((string)$roleVal);

$pageTitle = 'Inbox Approval';
$subTitle  = 'Daftar pengajuan yang membutuhkan persetujuan Anda.';

if ($roleStr === 'MANAGER') {
    $pageTitle = 'Inbox Approval Manager';
    $subTitle  = 'Daftar pengajuan dari Supervisor yang membutuhkan persetujuan Anda.';
} elseif ($roleStr === 'SUPERVISOR' || $roleStr === 'SPV') {
    $pageTitle = 'Inbox Approval Supervisor';
    $subTitle  = 'Daftar pengajuan dari Staff yang membutuhkan persetujuan Anda.';
}
@endphp

<x-app :title="$pageTitle">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">{{ $pageTitle }}</h1>
                <p class="section-subtitle">{{ $subTitle }}</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="apv-alert apv-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="apv-alert apv-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="apv-alert apv-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Request List --}}
    <div class="apv-list">
        @forelse($leaves as $lv)
            @php
                $type = $lv->type;
                $typeClass = 'apv-type--default';

                if (in_array($type?->value, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
                    $typeClass = 'apv-type--cuti';
                } elseif ($type?->value === \App\Enums\LeaveType::SAKIT->value) {
                    $typeClass = 'apv-type--sakit';
                } elseif (in_array($type?->value, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
                    $typeClass = 'apv-type--izin';
                } elseif ($type?->value === \App\Enums\LeaveType::DINAS_LUAR->value) {
                    $typeClass = 'apv-type--dinas';
                }

                $typeLabel = Str::contains($lv->type_label, 'Cuti Khusus') ? 'Cuti Khusus' : $lv->type_label;

                $isPending = $lv->status === \App\Models\LeaveRequest::PENDING_SUPERVISOR;
            @endphp

            <a href="{{ route('approval.show', $lv) }}" class="apv-card">
                <div class="apv-card-top">
                    <span class="apv-type {{ $typeClass }}">
                        @if($type?->value === \App\Enums\LeaveType::CUTI->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @elseif($type?->value === \App\Enums\LeaveType::CUTI_KHUSUS->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        @elseif($type?->value === \App\Enums\LeaveType::SAKIT->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        @else
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                        {{ $typeLabel }}
                    </span>

                    <span class="apv-badge apv-badge--warning">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Menunggu Approval
                    </span>
                </div>

                <div class="apv-card-employee">
                    <div class="apv-avatar">{{ substr($lv->user->name, 0, 1) }}</div>
                    <div class="apv-employee-info">
                        <span class="apv-employee-name">{{ $lv->user->name }}</span>
                        <span class="apv-employee-detail">{{ $lv->user->position->name ?? '-' }} — {{ $lv->user->division->name ?? '-' }}</span>
                    </div>
                </div>

                <div class="apv-card-date">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $lv->start_date->translatedFormat('l, j F Y') }}</span>
                    @if($lv->end_date && $lv->end_date->ne($lv->start_date))
                        <span class="apv-card-date-sep">—</span>
                        <span>{{ $lv->end_date->translatedFormat('l, j F Y') }}</span>
                    @endif
                </div>

                @if($lv->reason)
                    <div class="apv-card-note">
                        {{ Str::limit($lv->reason, 100) }}
                    </div>
                @endif

                <div class="apv-card-footer">
                    <div class="apv-card-meta">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $lv->created_at->translatedFormat('l, j F Y') }} · {{ $lv->created_at->format('H:i') }}</span>
                    </div>
                    <div class="apv-card-action">
                        <span>Proses</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="apv-empty">
                <div class="apv-empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="apv-empty-title">Tidak Ada Pengajuan</h3>
                <p class="apv-empty-desc">Tidak ada pengajuan yang perlu diproses saat ini.</p>
            </div>
        @endforelse
    </div>

    @if($leaves->hasPages())
    <div class="apv-pagination">
        <x-pagination :items="$leaves" />
    </div>
    @endif

    <style>
        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .apv-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .apv-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .apv-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* SECTION HEADER (x-slot)                    */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #111827);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark, #0A3D62); }

        /* ========================================== */
        /* REQUEST LIST & CARDS                       */
        /* ========================================== */
        .apv-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .apv-card {
            display: block;
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        .apv-card:hover {
            border-color: rgba(20, 93, 160, 0.35);
            box-shadow: 0 4px 12px rgba(20, 93, 160, 0.08);
            transform: translateY(-2px);
        }
        .apv-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        /* Type badge (rounded, not pill) */
        .apv-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .apv-type--default { background: #F8FAFC; color: var(--text-secondary, #374151); }
        .apv-type--cuti    { background: rgba(59, 130, 246, 0.1); color: var(--info, #3B82F6); }
        .apv-type--sakit   { background: rgba(245, 158, 11, 0.1); color: #b45309; }
        .apv-type--izin    { background: rgba(20, 93, 160, 0.08); color: var(--primary, #145DA0); }
        .apv-type--dinas   { background: rgba(147, 51, 234, 0.1); color: var(--purple, #9333EA); }

        /* Status badge (pill) */
        .apv-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .apv-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }

        /* Employee info */
        .apv-card-employee {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .apv-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145DA0);
            font-size: 0.875rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .apv-employee-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .apv-employee-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .apv-employee-detail {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Date */
        .apv-card-date {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            margin-bottom: 8px;
        }
        .apv-card-date svg {
            color: var(--text-muted, #6B7280);
            flex-shrink: 0;
        }
        .apv-card-date-sep {
            color: var(--text-muted, #6B7280);
            font-weight: 400;
        }

        /* Note */
        .apv-card-note {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            line-height: 1.5;
            margin-bottom: 12px;
        }

        /* Footer */
        .apv-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid var(--border-light, #E5E7EB);
            gap: 8px;
        }
        .apv-card-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }
        .apv-card-meta svg {
            flex-shrink: 0;
        }
        .apv-card-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }
        .apv-card-action svg {
            transition: transform 0.2s ease;
        }
        .apv-card:hover .apv-card-action svg {
            transform: translateX(3px);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .apv-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50, #F5F7FA);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light, #9CA3AF);
        }
        .apv-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin: 0 0 6px;
        }
        .apv-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .apv-pagination {
            margin-top: 24px;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .apv-card {
                padding: 18px 20px;
            }
        }

        @media (min-width: 768px) {
            .apv-card {
                padding: 20px;
            }
        }

        @media (min-width: 1024px) {
            .apv-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .apv-empty {
                grid-column: 1 / -1;
            }
        }
    </style>
</x-app>
