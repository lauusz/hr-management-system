<x-app title="Approval Lembur">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="14" r="8" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 2h4m-2 0v4m0 8 3-3"/></svg>
            </div>
            <div>
                <h1 class="section-title">Approval Lembur</h1>
                <p class="section-subtitle">Pengajuan yang ditugaskan kepada Anda.</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="apv-alert apv-alert--success" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="apv-alert apv-alert--error" role="alert">{{ session('error') }}</div>
    @endif

    <div class="apv-list">
        @forelse($overtimes as $overtime)
            @php
                $badgeClass = match ($overtime->status) {
                    \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR => 'apv-badge--warning',
                    \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD => 'apv-badge--success',
                    \App\Models\OvertimeRequest::STATUS_REJECTED => 'apv-badge--error',
                    default => 'apv-badge--gray',
                };
            @endphp
            <article class="apv-card">
                <div class="apv-card-top">
                    <span class="apv-type">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Lembur
                    </span>
                    <span class="apv-badge {{ $badgeClass }}">{{ $overtime->status_label }}</span>
                </div>

                <div class="apv-card-employee">
                    <div class="apv-avatar">{{ substr($overtime->user->name, 0, 1) }}</div>
                    <div class="apv-employee-info"><span class="apv-employee-name">{{ $overtime->user->name }}</span></div>
                </div>

                <div class="apv-card-date">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $overtime->overtime_date->translatedFormat('l, j F Y') }}</span>
                </div>
                <div class="apv-card-time">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $overtime->start_time->format('H:i') }} – {{ $overtime->end_time->format('H:i') }}</span>
                    <span class="apv-duration">{{ $overtime->duration_human }}</span>
                </div>

                <div class="apv-approval">
                    @include('overtime_requests._approval_status', ['overtime' => $overtime])
                </div>

                <div class="apv-card-footer">
                    <div class="apv-card-meta">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $overtime->created_at->translatedFormat('l, j F Y') }} · {{ $overtime->created_at->format('H:i') }}</span>
                    </div>
                    <a href="{{ route('overtime-approvals.show', $overtime) }}" class="apv-card-action">
                        Lihat detail
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="apv-empty">
                <div class="apv-empty-icon"><svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <h3 class="apv-empty-title">Tidak Ada Pengajuan</h3>
                <p class="apv-empty-desc">Belum ada pengajuan lembur yang ditugaskan.</p>
            </div>
        @endforelse
    </div>

    @if($overtimes->hasPages())
        <div class="apv-pagination"><x-pagination :items="$overtimes" /></div>
    @endif

    <style>
        .section-header-inline { display: flex; align-items: center; gap: 10px; }
        .section-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .icon-navy { background: rgba(10,61,98,.08); color: var(--primary-dark, #0A3D62); }
        .section-title { margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-primary, #111827); letter-spacing: -.01em; line-height: 1.25; }
        .section-subtitle { margin: 0; font-size: .8125rem; color: var(--text-muted, #6B7280); font-weight: 500; line-height: 1.35; }
        .apv-alert { display: flex; align-items: center; padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 500; }
        .apv-alert--success { background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.25); color: #16a34a; }
        .apv-alert--error { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.25); color: #dc2626; }
        .apv-list { display: flex; flex-direction: column; gap: 10px; }
        .apv-card { position: relative; min-width: 0; background: var(--white, #fff); border-radius: 16px; padding: 16px; border: 1px solid var(--border-light, #E5E7EB); box-shadow: 0 1px 3px rgba(0,0,0,.04); transition: border-color .2s, box-shadow .2s; }
        .apv-card:hover, .apv-card:focus-within { border-color: rgba(20,93,160,.35); box-shadow: 0 4px 12px rgba(20,93,160,.08); }
        .apv-card-top { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .apv-type { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 8px; font-size: .75rem; font-weight: 600; background: rgba(20,93,160,.08); color: var(--primary, #145DA0); }
        .apv-badge { display: inline-flex; align-items: center; max-width: 100%; padding: 5px 10px; border-radius: 9999px; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; overflow-wrap: anywhere; }
        .apv-badge--warning { background: rgba(245,158,11,.1); color: #a16207; }
        .apv-badge--success { background: rgba(34,197,94,.1); color: #15803d; }
        .apv-badge--error { background: rgba(239,68,68,.1); color: #b91c1c; }
        .apv-badge--gray { background: var(--gray-50, #F8FAFC); color: var(--text-secondary, #374151); }
        .apv-card-employee { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .apv-avatar { width: 36px; height: 36px; border-radius: 8px; background: rgba(20,93,160,.08); color: var(--primary, #145DA0); font-size: .875rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .apv-employee-info { min-width: 0; }
        .apv-employee-name { font-size: .875rem; font-weight: 600; color: var(--text-primary, #111827); overflow-wrap: anywhere; }
        .apv-card-date, .apv-card-time { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; font-size: .875rem; color: var(--text-primary, #111827); }
        .apv-card-date { font-weight: 600; }
        .apv-card-date svg, .apv-card-time svg { flex-shrink: 0; color: var(--text-muted, #6B7280); }
        .apv-duration { padding: 3px 8px; border-radius: 6px; font-size: .75rem; background: var(--gray-50, #F5F7FA); color: var(--text-secondary, #374151); }
        .apv-approval { color: var(--text-secondary, #374151); overflow-wrap: anywhere; }
        .apv-approval > div > strong, .apv-approval > div > div { font-size: 11px; }
        .apv-approval details { position: relative; z-index: 1; }
        .apv-approval summary { cursor: pointer; }
        .apv-card-footer { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding-top: 8px; border-top: 1px solid var(--border-light, #E5E7EB); }
        .apv-card-meta { display: flex; align-items: center; gap: 5px; min-width: 0; font-size: .75rem; color: var(--text-muted, #6B7280); }
        .apv-card-meta svg { flex-shrink: 0; }
        .apv-card-action { display: inline-flex; align-items: center; justify-content: flex-end; gap: 4px; min-height: 44px; font-size: .8125rem; font-weight: 600; color: var(--primary, #145DA0); text-decoration: none; flex-shrink: 0; }
        .apv-card-action::after { content: ''; position: absolute; inset: 0; border-radius: inherit; }
        .apv-card-action:hover { text-decoration: underline; }
        .apv-empty { text-align: center; padding: 48px 24px; background: var(--white, #fff); border-radius: 16px; border: 1px solid var(--border-light, #E5E7EB); }
        .apv-empty-icon { width: 72px; height: 72px; margin: 0 auto 16px; background: var(--gray-50, #F5F7FA); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-light, #9CA3AF); }
        .apv-empty-title { font-size: .9375rem; font-weight: 600; color: var(--text-secondary, #374151); margin: 0 0 6px; }
        .apv-empty-desc { font-size: .8125rem; color: var(--text-muted, #6B7280); margin: 0 auto; max-width: 280px; line-height: 1.5; }
        .apv-pagination { margin-top: 24px; }
        @media (min-width: 480px) { .apv-card { padding: 18px 20px; } }
        @media (min-width: 768px) { .apv-card { padding: 20px; } }
        @media (min-width: 1024px) { .apv-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; } .apv-empty { grid-column: 1 / -1; } }
    </style>
</x-app>
