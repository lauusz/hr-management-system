<x-app title="Approval Absensi">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy" aria-hidden="true">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Approval Absensi</h1>
                <p class="section-subtitle">Validasi kehadiran Dinas Luar yang menunggu keputusan</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="aa-alert aa-alert--success" role="status">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="aa-alert aa-alert--error" role="alert">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                <path stroke-linecap="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="aa-summary" aria-label="Ringkasan antrean approval">
        <div class="aa-summary-icon" aria-hidden="true">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <span class="aa-summary-value">{{ $pendingAttendances->count() }}</span>
            <span class="aa-summary-label">Menunggu Keputusan</span>
        </div>
    </div>

    <div class="aa-table-card">
        @if($pendingAttendances->isEmpty())
            <div class="aa-empty">
                <div class="aa-empty-icon" aria-hidden="true">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="aa-empty-title">Semua Pengajuan Sudah Diproses</h2>
                <p class="aa-empty-description">Tidak ada absensi Dinas Luar yang menunggu keputusan saat ini.</p>
            </div>
        @else
            <div class="aa-table-wrap">
                <table class="aa-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Waktu &amp; Tanggal</th>
                            <th>Bukti Validasi</th>
                            <th>Lokasi Maps</th>
                            <th>Keperluan / Notes</th>
                            <th class="aa-cell-actions">Keputusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingAttendances as $item)
                            @php
                                $roleLabel = $item->user->role instanceof \App\Enums\UserRole
                                    ? $item->user->role->label()
                                    : $item->user->role;
                            @endphp
                            <tr>
                                <td>
                                    <div class="aa-employee">
                                        <div class="aa-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($item->user->name, 0, 1)) }}</div>
                                        <div class="aa-employee-info">
                                            <span class="aa-employee-name" title="{{ $item->user->name }}">{{ $item->user->name }}</span>
                                            <span class="aa-employee-role">{{ $roleLabel ?: 'Karyawan' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="aa-date-cell">
                                        <span class="aa-time">{{ $item->clock_in_at?->format('H:i') ?? '-' }}</span>
                                        <span class="aa-date">{{ $item->date?->translatedFormat('j F Y') ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($item->clock_in_photo)
                                        <button type="button"
                                            class="aa-link-button aa-link-button--photo"
                                            data-image-viewer-src="{{ asset('storage/'.$item->clock_in_photo) }}"
                                            data-image-viewer-alt="Foto absensi {{ $item->user->name }}">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Lihat Foto
                                        </button>
                                    @else
                                        <span class="aa-empty-value">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->clock_in_lat && $item->clock_in_lng)
                                        <a href="https://www.google.com/maps/search/?api=1&amp;query={{ $item->clock_in_lat }},{{ $item->clock_in_lng }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="aa-link-button aa-link-button--location">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Cek Lokasi
                                        </a>
                                    @else
                                        <span class="aa-empty-value">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->notes)
                                        <button type="button"
                                            class="aa-note-button"
                                            data-notes="{{ $item->notes }}"
                                            aria-label="Lihat detail keperluan"
                                            onclick="openNotesModal(this.dataset.notes)">
                                            <span class="aa-note-text">{{ $item->notes }}</span>
                                        </button>
                                    @else
                                        <span class="aa-note-empty">Tidak ada keterangan</span>
                                    @endif
                                </td>
                                <td class="aa-cell-actions">
                                    <div class="aa-actions">
                                        <button type="button"
                                            class="aa-action aa-action--approve"
                                            data-approve-url="{{ route('hr.approval_attendance.approve', $item) }}"
                                            data-user-name="{{ $item->user->name }}"
                                            onclick="openApproveModal(this.dataset.approveUrl, this.dataset.userName)">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Terima
                                        </button>
                                        <button type="button"
                                            class="aa-action aa-action--reject"
                                            data-reject-url="{{ route('hr.approval_attendance.reject', $item) }}"
                                            data-user-name="{{ $item->user->name }}"
                                            onclick="openRejectModal(this.dataset.rejectUrl, this.dataset.userName)">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="aa-mobile-list">
                @foreach($pendingAttendances as $item)
                    @php
                        $mobileRoleLabel = $item->user->role instanceof \App\Enums\UserRole
                            ? $item->user->role->label()
                            : $item->user->role;
                    @endphp
                    <article class="aa-mobile-card">
                        <header class="aa-mobile-card-header">
                            <div class="aa-employee">
                                <div class="aa-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($item->user->name, 0, 1)) }}</div>
                                <div class="aa-employee-info">
                                    <span class="aa-employee-name" title="{{ $item->user->name }}">{{ $item->user->name }}</span>
                                    <span class="aa-employee-role">{{ $mobileRoleLabel ?: 'Karyawan' }}</span>
                                </div>
                            </div>
                            <div class="aa-mobile-time">
                                <span class="aa-time">{{ $item->clock_in_at?->format('H:i') ?? '-' }}</span>
                                <span class="aa-date">{{ $item->date?->translatedFormat('j M Y') ?? '-' }}</span>
                            </div>
                        </header>

                        <div class="aa-mobile-section">
                            <span class="aa-mobile-label">Keperluan / Notes</span>
                            @if($item->notes)
                                <button type="button"
                                    class="aa-note-button"
                                    data-notes="{{ $item->notes }}"
                                    aria-label="Lihat detail keperluan"
                                    onclick="openNotesModal(this.dataset.notes)">
                                    <span class="aa-note-text">{{ $item->notes }}</span>
                                </button>
                            @else
                                <span class="aa-note-empty">Tidak ada keterangan</span>
                            @endif
                        </div>

                        <div class="aa-mobile-evidence">
                            @if($item->clock_in_photo)
                                <button type="button"
                                    class="aa-link-button aa-link-button--photo"
                                    data-image-viewer-src="{{ asset('storage/'.$item->clock_in_photo) }}"
                                    data-image-viewer-alt="Foto absensi {{ $item->user->name }}">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Lihat Foto
                                </button>
                            @else
                                <span class="aa-mobile-unavailable">Foto tidak tersedia</span>
                            @endif

                            @if($item->clock_in_lat && $item->clock_in_lng)
                                <a href="https://www.google.com/maps/search/?api=1&amp;query={{ $item->clock_in_lat }},{{ $item->clock_in_lng }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="aa-link-button aa-link-button--location">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Cek Lokasi
                                </a>
                            @else
                                <span class="aa-mobile-unavailable">Lokasi tidak tersedia</span>
                            @endif
                        </div>

                        <div class="aa-mobile-actions">
                            <button type="button"
                                class="aa-action aa-action--approve"
                                data-approve-url="{{ route('hr.approval_attendance.approve', $item) }}"
                                data-user-name="{{ $item->user->name }}"
                                onclick="openApproveModal(this.dataset.approveUrl, this.dataset.userName)">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                Terima
                            </button>
                            <button type="button"
                                class="aa-action aa-action--reject"
                                data-reject-url="{{ route('hr.approval_attendance.reject', $item) }}"
                                data-user-name="{{ $item->user->name }}"
                                onclick="openRejectModal(this.dataset.rejectUrl, this.dataset.userName)">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Tolak
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <x-modal id="notesDetailModal" title="Detail Keperluan" type="info" cancelLabel="Tutup">
        <p id="notesDetailText" class="aa-notes-detail"></p>
    </x-modal>

    <x-modal
        id="approveModal"
        title="Terima Pengajuan Ini?"
        type="confirm"
        variant="success"
        confirmLabel="Ya, Terima"
        cancelLabel="Batal"
        confirmFormAction="#"
        confirmFormMethod="POST">
        <p class="aa-modal-copy">
            Pengajuan Dinas Luar dari <strong id="approveUserName"></strong> akan disetujui dan tercatat sebagai <strong>Hadir</strong>.
        </p>
    </x-modal>

    <x-modal id="rejectModal" title="Tolak Pengajuan Ini?" type="form" variant="danger">
        <form id="rejectForm" method="POST" class="aa-reject-form">
            @csrf
            <p class="aa-modal-copy">
                Berikan alasan penolakan untuk pengajuan dari <strong id="rejectUserName"></strong>.
            </p>
            <div class="aa-form-group">
                <label for="rejection_note" class="aa-form-label">Alasan Penolakan <span aria-hidden="true">*</span></label>
                <textarea id="rejection_note"
                    name="rejection_note"
                    class="aa-textarea"
                    rows="3"
                    maxlength="255"
                    placeholder="Contoh: foto tidak jelas atau lokasi tidak sesuai"
                    required></textarea>
            </div>
            <div class="aa-modal-actions">
                <button type="button" class="aa-modal-button aa-modal-button--secondary" data-modal-close="true">Batal</button>
                <button type="submit" class="aa-modal-button aa-modal-button--danger">Ya, Tolak</button>
            </div>
        </form>
    </x-modal>

    <style>
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 8px;
        }

        .section-icon.icon-navy {
            color: var(--primary);
            background: rgba(20, 93, 160, 0.08);
        }

        .section-title {
            margin: 0;
            color: var(--text-primary);
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .section-subtitle {
            margin: 2px 0 0;
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        .aa-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            padding: 12px 16px;
            border: 1px solid;
            border-radius: 12px;
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .aa-alert svg { flex-shrink: 0; }
        .aa-alert--success { color: #166534; background: #f0fdf4; border-color: #bbf7d0; }
        .aa-alert--error { color: #991b1b; background: #fef2f2; border-color: #fecaca; }

        .aa-summary {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding: 12px 16px;
            border: 1px solid var(--border-light);
            border-radius: 14px;
            background: var(--white);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .aa-summary-icon {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(245, 158, 11, 0.1);
            color: #b45309;
        }

        .aa-summary-value {
            display: block;
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .aa-summary-label {
            display: block;
            margin-top: 3px;
            color: var(--text-muted);
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .aa-table-card {
            overflow: hidden;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            background: var(--white);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .aa-table-wrap { overflow-x: auto; }
        .aa-mobile-list { display: none; }

        .aa-table {
            width: 100%;
            min-width: 960px;
            border-collapse: collapse;
        }

        .aa-table th {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            background: var(--gray-50);
            color: var(--text-muted);
            font-size: 0.6875rem;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .aa-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-secondary);
            font-size: 0.8125rem;
            vertical-align: middle;
        }

        .aa-table tbody tr:last-child td { border-bottom: 0; }
        .aa-table tbody tr:hover td { background: var(--gray-50); }

        .aa-employee {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 190px;
        }

        .aa-avatar {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 10px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.8125rem;
            font-weight: 700;
        }

        .aa-employee-info {
            min-width: 0;
            max-width: 190px;
        }

        .aa-employee-name {
            display: block;
            overflow: hidden;
            color: var(--text-primary);
            font-size: 0.8125rem;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .aa-employee-role {
            display: block;
            margin-top: 2px;
            overflow: hidden;
            color: var(--text-muted);
            font-size: 0.6875rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .aa-date-cell { line-height: 1.35; }
        .aa-time { display: block; color: var(--text-primary); font-size: 0.875rem; font-weight: 700; }
        .aa-date { display: block; margin-top: 2px; color: var(--text-muted); font-size: 0.75rem; white-space: nowrap; }

        .aa-link-button,
        .aa-action,
        .aa-modal-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid transparent;
            border-radius: 9px;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .aa-link-button { padding: 8px 10px; }
        .aa-link-button--photo { color: var(--primary); background: rgba(20, 93, 160, 0.07); border-color: rgba(20, 93, 160, 0.1); }
        .aa-link-button--location { color: #0369a1; background: #f0f9ff; border-color: #e0f2fe; }
        .aa-link-button--photo:hover { background: rgba(20, 93, 160, 0.13); }
        .aa-link-button--location:hover { background: #e0f2fe; }

        .aa-note-button {
            display: block;
            width: 100%;
            max-width: 260px;
            padding: 7px 9px;
            overflow: hidden;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: var(--text-secondary);
            font-family: inherit;
            font-size: 0.8125rem;
            line-height: 1.45;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .aa-note-button:hover {
            border-color: var(--border);
            background: var(--white);
            color: var(--primary);
        }

        .aa-note-button:focus-visible {
            outline: 3px solid rgba(20, 93, 160, 0.22);
            outline-offset: 2px;
        }

        .aa-note-text {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .aa-note-empty {
            color: var(--text-muted);
            font-size: 0.8125rem;
        }

        .aa-notes-detail {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.7;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .aa-empty-value { color: var(--text-light); }
        .aa-cell-actions { text-align: right !important; }

        .aa-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .aa-action { padding: 8px 11px; }
        .aa-action--approve { color: #166534; background: #dcfce7; border-color: #bbf7d0; }
        .aa-action--reject { color: #991b1b; background: #fee2e2; border-color: #fecaca; }
        .aa-action--approve:hover { background: #bbf7d0; }
        .aa-action--reject:hover { background: #fecaca; }

        .aa-link-button:focus-visible,
        .aa-action:focus-visible,
        .aa-modal-button:focus-visible,
        .aa-textarea:focus-visible {
            outline: 3px solid rgba(20, 93, 160, 0.22);
            outline-offset: 2px;
        }

        .aa-empty {
            padding: 52px 24px;
            text-align: center;
        }

        .aa-empty-icon {
            width: 68px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: var(--gray-50);
            color: var(--text-light);
        }

        .aa-empty-title { margin: 0; color: var(--text-secondary); font-size: 0.9375rem; font-weight: 600; }
        .aa-empty-description { max-width: 340px; margin: 6px auto 0; color: var(--text-muted); font-size: 0.8125rem; line-height: 1.5; }

        .aa-modal-copy { margin: 0; color: var(--text-secondary); font-size: 0.875rem; line-height: 1.6; }
        .aa-reject-form { margin: 0; }
        .aa-form-group { margin-top: 16px; }

        .aa-form-label {
            display: block;
            margin-bottom: 7px;
            color: var(--text-secondary);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .aa-form-label span { color: var(--error); }

        .aa-textarea {
            width: 100%;
            min-height: 92px;
            resize: vertical;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            background: var(--white);
            font-family: inherit;
            font-size: 0.8125rem;
            line-height: 1.5;
        }

        .aa-textarea:focus { border-color: var(--primary); outline: none; }

        .aa-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .aa-modal-button { padding: 10px 16px; }
        .aa-modal-button--secondary { color: var(--text-secondary); background: var(--white); border-color: var(--border); }
        .aa-modal-button--danger { color: var(--white); background: var(--error); }
        .aa-modal-button--secondary:hover { background: var(--gray-50); }
        .aa-modal-button--danger:hover { background: #dc2626; }

        @media (max-width: 767px) {
            .section-subtitle { max-width: 260px; }
            .aa-summary { width: 100%; }
            .aa-table-card { overflow: visible; border: 0; background: transparent; box-shadow: none; }
            .aa-table-wrap { display: none; }
            .aa-mobile-list { display: grid; gap: 12px; }
            .aa-mobile-card { overflow: hidden; border: 1px solid var(--border-light); border-radius: 14px; background: var(--white); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); }
            .aa-mobile-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 14px; border-bottom: 1px solid var(--border-light); }
            .aa-mobile-card .aa-employee { min-width: 0; }
            .aa-mobile-card .aa-employee-info { max-width: min(48vw, 190px); }
            .aa-mobile-time { flex-shrink: 0; text-align: right; }
            .aa-mobile-section { min-width: 0; padding: 12px 14px; }
            .aa-mobile-label { display: block; margin-bottom: 5px; color: var(--text-muted); font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
            .aa-mobile-card .aa-note-button { max-width: none; padding: 7px 0; }
            .aa-mobile-evidence { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 8px; padding: 0 14px 14px; }
            .aa-mobile-evidence .aa-link-button { width: 100%; min-height: 40px; }
            .aa-mobile-unavailable { display: flex; align-items: center; justify-content: center; min-height: 40px; padding: 7px; border: 1px dashed var(--border); border-radius: 9px; color: var(--text-light); font-size: 0.6875rem; text-align: center; }
            .aa-mobile-actions { display: flex; gap: 8px; padding: 12px 14px; border-top: 1px solid var(--border-light); background: var(--gray-50); }
            .aa-mobile-actions .aa-action { flex: 1; min-height: 42px; }
            .aa-modal-actions { flex-direction: column-reverse; }
            .aa-modal-button { width: 100%; min-height: 42px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .aa-link-button,
            .aa-action,
            .aa-modal-button { transition: none; }
        }
    </style>

    <script>
        function openApproveModal(url, name) {
            const modal = document.getElementById('approveModal');
            const form = modal?.querySelector('form');
            const userName = document.getElementById('approveUserName');

            if (!modal || !form || !userName) return;

            form.action = url;
            userName.textContent = name;
            modal.style.display = 'flex';
        }

        function openRejectModal(url, name) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            const userName = document.getElementById('rejectUserName');

            if (!modal || !form || !userName) return;

            form.action = url;
            userName.textContent = name;
            form.querySelector('textarea').value = '';
            modal.style.display = 'flex';
            window.setTimeout(() => form.querySelector('textarea').focus(), 0);
        }

        function openNotesModal(notes) {
            const modal = document.getElementById('notesDetailModal');
            const detail = document.getElementById('notesDetailText');

            if (!modal || !detail) return;

            detail.textContent = notes;
            modal.style.display = 'flex';
        }
    </script>
</x-app>
