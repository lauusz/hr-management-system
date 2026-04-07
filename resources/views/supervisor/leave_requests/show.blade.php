<x-app title="Detail Approval">

    @php
        $typeValue = $item->type;
        if ($typeValue instanceof \App\Enums\LeaveType) {
            $typeValue = $typeValue->value;
        }
        $typeValue = (string) $typeValue;

        $backUrl = url()->previous();
        if ($backUrl === url()->current()) {
            $backUrl = route('approval.index');
        }

        $showActionButtons = isset($canApprove) && $canApprove;
        $isDirectSuper = isset($isApprover) && $isApprover;

        $statusBg = 'var(--bg-body)';
        $statusColor = 'var(--text-muted)';
        $statusLabel = $item->status;

        if ($item->status === \App\Models\LeaveRequest::STATUS_APPROVED) {
            $statusBg = 'var(--success-bg)';
            $statusColor = 'var(--success-text)';
            $statusLabel = 'Disetujui Final (HR)';
        } elseif ($item->status === \App\Models\LeaveRequest::STATUS_REJECTED) {
            $statusBg = 'var(--danger-bg)';
            $statusColor = 'var(--danger-text)';
            $statusLabel = 'Ditolak';
        } elseif ($item->status === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
            $statusBg = 'var(--warning-bg)';
            $statusColor = 'var(--warning-text)';
            $statusLabel = $showActionButtons ? 'Menunggu Approval' : 'Menunggu Atasan';
        } elseif ($item->status === \App\Models\LeaveRequest::PENDING_HR) {
            $statusBg = 'var(--teal-bg)';
            $statusColor = 'var(--teal-text)';
            $statusLabel = 'Atasan Mengetahui';
        }

        $typeBadgeBg = 'var(--bg-body)';
        $typeBadgeColor = 'var(--text-main)';
        if (in_array($typeValue, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
            $typeBadgeBg = 'var(--blue-light)';
            $typeBadgeColor = 'var(--blue-text)';
        } elseif ($typeValue === \App\Enums\LeaveType::SAKIT->value) {
            $typeBadgeBg = '#fce7f3';
            $typeBadgeColor = '#be185d';
        } elseif (in_array($typeValue, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value])) {
            $typeBadgeBg = 'var(--warning-bg)';
            $typeBadgeColor = 'var(--warning-text)';
        } elseif ($typeValue === \App\Enums\LeaveType::DINAS_LUAR->value) {
            $typeBadgeBg = 'var(--purple-light)';
            $typeBadgeColor = 'var(--purple-text)';
        } elseif ($typeValue === \App\Enums\LeaveType::OFF_SPV->value) {
            $typeBadgeBg = '#f3e8ff';
            $typeBadgeColor = '#6b21a8';
        }
    @endphp

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

    @if($errors->any())
    <div class="flash flash-error">
        <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <div class="detail-container">

        {{-- Header Card --}}
        <div class="header-card">
            <a href="{{ $backUrl }}" class="back-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Kembali
            </a>

            <div class="header-main">
                <div class="header-avatar">{{ substr($item->user->name, 0, 1) }}</div>
                <div class="header-info">
                    <h1 class="header-name">{{ $item->user->name }}</h1>
                    <div class="header-meta">
                        <span class="chip-role">{{ $item->user->role }}</span>
                        <span class="meta-dot">•</span>
                        <span>{{ $item->user->position->name ?? '-' }}</span>
                        <span class="meta-dot">•</span>
                        <span>{{ $item->user->division->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="header-footer">
                <span class="badge-status" style="background: {{ $statusBg }}; color: {{ $statusColor }};">
                    {{ $statusLabel }}
                </span>
                <span class="submit-time">Diajukan {{ $item->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>

        {{-- Info Card: Jenis & Periode --}}
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Jenis Pengajuan</span>
                <span class="badge" style="background: {{ $typeBadgeBg }}; color: {{ $typeBadgeColor }};">
                    {{ $item->type_label ?? $item->type }}
                </span>
            </div>

            @if($typeValue === 'CUTI_KHUSUS' && $item->special_leave_category)
                @php
                    $catMap = [
                        'NIKAH_KARYAWAN'   => 'Menikah (4 Hari)',
                        'ISTRI_MELAHIRKAN' => 'Istri Melahirkan (2 Hari)',
                        'ISTRI_KEGUGURAN'  => 'Istri Keguguran (2 Hari)',
                        'KHITANAN_ANAK'    => 'Khitanan Anak (2 Hari)',
                        'PEMBAPTISAN_ANAK' => 'Pembaptisan Anak (2 Hari)',
                        'NIKAH_ANAK'       => 'Pernikahan Anak (2 Hari)',
                        'DEATH_EXTENDED'   => 'Kematian Adik/Kakak/Ipar (2 Hari)',
                        'DEATH_CORE'       => 'Kematian Inti (2 Hari)',
                        'DEATH_HOUSE'      => 'Kematian Anggota Rumah (1 Hari)',
                        'HAJI'             => 'Ibadah Haji (40 Hari)',
                        'UMROH'            => 'Ibadah Umroh (14 Hari)',
                    ];
                    $catLabel = $catMap[$item->special_leave_category] ?? $item->special_leave_category;
                @endphp
                <div class="info-row">
                    <span class="info-label">Detail Cuti Khusus</span>
                    <span class="cuti-khusus-badge">{{ $catLabel }}</span>
                </div>
            @endif

            <div class="info-row">
                <span class="info-label">Periode Izin</span>
                <div class="date-display">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ $item->start_date->format('d M Y') }}
                    @if($item->end_date && $item->end_date->ne($item->start_date))
                        — {{ $item->end_date->format('d M Y') }}
                    @endif
                </div>
            </div>

            @php
                $startTimeLabel = $item->start_time ? $item->start_time->format('H:i') : null;
                $endTimeLabel   = $item->end_time ? $item->end_time->format('H:i') : null;
            @endphp

            @if($startTimeLabel)
            <div class="info-row">
                <span class="info-label">
                    @if($endTimeLabel)
                        Jam Izin
                    @elseif($typeValue === 'IZIN_TELAT')
                        Estimasi Jam Tiba
                    @elseif($typeValue === 'IZIN_PULANG_AWAL')
                        Jam Pulang Awal
                    @else
                        Jam Mulai
                    @endif
                </span>
                <span class="time-display">{{ $startTimeLabel }}{{ $endTimeLabel ? ' — ' . $endTimeLabel : '' }}</span>
            </div>
            @endif
        </div>

        {{-- Info Card: Alasan --}}
        <div class="info-card">
            <div class="info-row info-row-full">
                <span class="info-label">Alasan / Keterangan</span>
                <div class="reason-box">{{ $item->reason }}</div>
            </div>

            @if($item->substitute_pic)
            <div class="info-row info-row-full">
                <span class="info-label">PIC Pengganti</span>
                <div class="pic-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ $item->substitute_pic }}</span>
                    @if($item->substitute_phone)
                        <span class="pic-phone">{{ $item->substitute_phone }}</span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Info Card: Foto & Lokasi --}}
        @php
            $url = $item->photo
                ? asset('storage/leave_photos/' . ltrim($item->photo, '/'))
                : null;
        @endphp

        @if($url || ($item->latitude && $item->longitude))
        <div class="info-card">
            @if($url)
            <div class="info-row info-row-full">
                <span class="info-label">Bukti Pendukung</span>
                <div class="photo-preview js-view-photo" data-url="{{ $url }}">
                    <img src="{{ $url }}" alt="Bukti Izin">
                    <div class="photo-overlay">
                        <svg width="20" height="20" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Lihat Foto</span>
                    </div>
                </div>
            </div>
            @endif

            @if($item->latitude && $item->longitude)
            <div class="info-row info-row-full">
                <span class="info-label">Lokasi Pengajuan <span class="accuracy-label">(±{{ (int)$item->accuracy_m }}m)</span></span>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}&z=16&output=embed" loading="lazy" allowfullscreen></iframe>
                </div>
                <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" target="_blank" class="link-map">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Buka di Google Maps
                </a>
            </div>
            @endif
        </div>
        @endif

        {{-- Info Card: Status & Approval --}}
        @if($item->approved_by || $item->notes)
        <div class="info-card">
            @if($item->approved_by)
            <div class="info-row info-row-full">
                <span class="info-label">Diputuskan Oleh</span>
                <div class="approver-box">
                    <div class="approver-avatar">{{ substr($item->approver?->name ?? '—', 0, 1) }}</div>
                    <div class="approver-info">
                        <span class="approver-name">{{ $item->approver?->name ?? '-' }}</span>
                        @if($item->approved_at)
                            <span class="approver-time">{{ $item->approved_at->format('d M Y, H:i') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($item->notes)
            <div class="info-row info-row-full">
                <span class="info-label">Catatan Sistem / Revisi</span>
                <div class="notes-box">{!! nl2br(e($item->notes)) !!}</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Bottom Spacer for Fixed Actions --}}
        <div class="bottom-spacer"></div>

    </div>

    {{-- Fixed Action Bar --}}
    <div class="action-bar">
        <div class="action-main">
            @if($isDirectSuper)
                <a href="{{ route('approval.edit', $item->id) }}" class="action-btn action-btn-edit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </a>

                @if(!in_array($item->status, ['BATAL', 'REJECTED']))
                    <button type="button" data-modal-target="modal-delete" class="action-btn action-btn-batal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Batal
                    </button>
                @endif
            @endif

            @if($showActionButtons)
                <button type="button" data-modal-target="modal-reject" class="action-btn action-btn-tolak">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    Tolak
                </button>
                <button type="button" data-modal-target="modal-approve" class="action-btn action-btn-setuju">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                    Setujui
                </button>
            @endif
        </div>
    </div>

    {{-- Photo Viewer Overlay --}}
    <div id="simple-viewer" class="simple-viewer-overlay" style="display: none;">
        <button type="button" id="btn-close-simple" class="btn-close-simple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="simple-viewer-img" src="" alt="Full Preview">
    </div>

    {{-- Modals --}}
    <x-modal id="modal-delete" title="Ajukan Pembatalan?" type="confirm" variant="danger" confirmLabel="Ya, Ajukan" cancelLabel="Batal" :confirmFormAction="route('approval.destroy', $item->id)" confirmFormMethod="DELETE">
        <p style="margin:0; color:#374151;">Anda akan mengajukan permintaan pembatalan untuk pengajuan ini ke HRD.</p>
    </x-modal>

    @if($showActionButtons)
    <x-modal id="modal-reject" title="Tolak Pengajuan Ini?" type="confirm" variant="danger" confirmLabel="Ya, Tolak" cancelLabel="Batal" :confirmFormAction="route('approval.reject', $item->id)" confirmFormMethod="POST">
        <p style="margin:0; color:#374151;">Yakin menolak pengajuan <strong>{{ $item->user->name }}</strong>?</p>
    </x-modal>

    <x-modal id="modal-approve" title="Terima Pengajuan Ini?" type="confirm" variant="success" confirmLabel="Ya, Terima" cancelLabel="Batal" :confirmFormAction="route('approval.approve', $item->id)" confirmFormMethod="POST">
        <p style="margin:0; color:#374151;">Setujui pengajuan izin ini?</p>
    </x-modal>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const viewer = document.getElementById('simple-viewer');
            const viewerImg = document.getElementById('simple-viewer-img');
            const closeBtn = document.getElementById('btn-close-simple');

            document.querySelectorAll('.js-view-photo').forEach(el => {
                el.addEventListener('click', () => {
                    const url = el.getAttribute('data-url');
                    if (url && viewer && viewerImg) {
                        viewerImg.src = url;
                        viewer.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    }
                });
            });

            function closeViewer() {
                if (viewer) viewer.style.display = 'none';
                if (viewerImg) viewerImg.src = '';
                document.body.style.overflow = '';
            }

            if (closeBtn) closeBtn.addEventListener('click', closeViewer);
            if (viewer) viewer.addEventListener('click', (e) => { if (e.target === viewer) closeViewer(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && viewer.style.display === 'flex') closeViewer(); });
        });
    </script>

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
        .detail-container {
            max-width: 680px;
            margin: 0 auto;
            padding: 16px 16px 100px;
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
            margin-bottom: 12px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .flash-success { background: var(--success-bg); color: var(--success-text); border: 1px solid var(--success-border); }
        .flash-error { background: var(--danger-bg); color: var(--danger-text); border: 1px solid var(--danger-border); }
        .flash-icon { width: 18px; height: 18px; flex-shrink: 0; }

        /* === HEADER CARD === */
        .header-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 16px;
            margin-bottom: 12px;
            position: relative;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            background: var(--bg-body);
            color: var(--text-muted);
            margin-bottom: 12px;
            transition: background 0.2s;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        .back-btn:hover { background: var(--border); color: var(--primary); }
        .back-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

        .header-main {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .header-avatar {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            background: var(--blue-light);
            color: var(--blue-text);
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .header-info { flex: 1; min-width: 0; }

        .header-name {
            margin: 0 0 4px;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .header-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .chip-role {
            background: var(--bg-body);
            color: var(--text-muted);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .meta-dot { color: var(--border); }

        .header-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .badge-status {
            display: inline-flex;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .submit-time {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* === INFO CARD === */
        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 16px;
            margin-bottom: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
            gap: 12px;
        }
        .info-row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-row:first-child { padding-top: 0; }

        .info-row-full {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }

        .accuracy-label {
            font-weight: 400;
            color: var(--text-muted);
        }

        /* === BADGES === */
        .badge {
            display: inline-flex;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.02em;
        }

        .cuti-khusus-badge {
            font-size: 0.75rem;
            font-weight: 600;
            background: var(--blue-light);
            color: var(--blue-text);
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #dbeafe;
        }

        /* === DATE & TIME === */
        .date-display {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
        }
        .date-display svg { width: 16px; height: 16px; color: var(--text-muted); }

        .time-display {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
        }

        /* === REASON BOX === */
        .reason-box {
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px;
            font-size: 0.875rem;
            color: var(--text-main);
            line-height: 1.6;
            width: 100%;
            box-sizing: border-box;
        }

        /* === PIC BOX === */
        .pic-box {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-main);
        }
        .pic-box svg { width: 16px; height: 16px; color: var(--text-muted); }
        .pic-phone { font-size: 0.75rem; color: var(--text-muted); }

        /* === APPROVER BOX === */
        .approver-box {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }
        .approver-avatar {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--teal-bg);
            color: var(--teal-text);
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .approver-info { display: flex; flex-direction: column; gap: 2px; }
        .approver-name { font-size: 0.875rem; font-weight: 600; color: var(--text-main); }
        .approver-time { font-size: 0.75rem; color: var(--text-muted); }

        /* === NOTES BOX === */
        .notes-box {
            background: var(--warning-bg);
            border: 1px solid var(--warning-border);
            border-radius: var(--radius-sm);
            padding: 12px;
            font-size: 0.8125rem;
            color: var(--warning-text);
            line-height: 1.5;
            width: 100%;
            box-sizing: border-box;
        }

        /* === PHOTO === */
        .photo-preview {
            position: relative;
            width: 100%;
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid var(--border);
            cursor: pointer;
        }
        .photo-preview img { width: 100%; height: auto; display: block; max-height: 300px; object-fit: cover; }
        .photo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .photo-preview:hover .photo-overlay { opacity: 1; }
        .photo-overlay span { color: #fff; font-size: 0.75rem; font-weight: 600; }

        /* === MAP === */
        .map-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            width: 100%;
            box-sizing: border-box;
        }
        .map-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }

        .link-map {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            margin-top: 8px;
        }
        .link-map:hover { color: var(--primary-dark); }
        .link-map svg { width: 14px; height: 14px; }

        /* === FIXED ACTION BAR === */
        .action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 50;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.06);
        }

        .action-btn-back {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            background: var(--bg-body);
            color: var(--text-muted);
            flex-shrink: 0;
            transition: background 0.2s;
            text-decoration: none;
        }
        .action-btn-back:hover { background: var(--border); color: var(--primary); }
        .action-btn-back svg { width: 18px; height: 18px; }

        .action-main {
            flex: 1;
            display: flex;
            gap: 8px;
            min-width: 0;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            text-decoration: none;
            white-space: nowrap;
        }
        .action-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

        .action-btn-edit {
            background: var(--bg-body);
            color: var(--warning-text);
            border: 1px solid var(--warning-border);
        }
        .action-btn-edit:hover { background: var(--warning-bg); }

        .action-btn-batal {
            background: var(--bg-body);
            color: var(--danger-text);
            border: 1px solid var(--danger-border);
        }
        .action-btn-batal:hover { background: var(--danger-bg); }

        .action-btn-tolak {
            background: var(--bg-card);
            color: var(--danger-text);
            border: 1px solid var(--danger-border);
            flex: 1;
        }
        .action-btn-tolak:hover { background: var(--danger-bg); }

        .action-btn-setuju {
            background: var(--primary);
            color: #fff;
            flex: 1;
        }
        .action-btn-setuju:hover { background: var(--primary-dark); }

        .bottom-spacer { height: 80px; }

        /* === PHOTO VIEWER === */
        .simple-viewer-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0,0,0,0.92);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-close-simple {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            z-index: 100000;
        }
        .btn-close-simple:hover { background: rgba(255,255,255,0.25); }
        #simple-viewer-img { max-width: 95vw; max-height: 95vh; object-fit: contain; border-radius: 4px; }

        /* === MODAL OVERRIDE === */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-backdrop.show { display: flex; }

        /* === MOBILE RESPONSIVE === */
        @media (max-width: 640px) {
            .detail-container { padding: 12px 12px 90px; }

            .header-card, .info-card { padding: 14px; }

            .action-btn-setuju, .action-btn-tolak { flex: 1; padding: 12px 10px; }
        }

        @media (min-width: 768px) {
            .action-bar {
                max-width: 680px;
                left: 50%;
                transform: translateX(-50%);
                border-radius: var(--radius-md) var(--radius-md) 0 0;
            }
        }
    </style>

</x-app>
