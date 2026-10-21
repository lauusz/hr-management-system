<x-app title="Edit Divisi">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm14 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Divisi</h1>
                <p class="section-subtitle">Perbarui nama divisi dan supervisor yang bertanggung jawab</p>
            </div>
        </div>
    </x-slot>

    @php
        $currentSupervisor = $supervisors->firstWhere('id', $item->supervisor_id) ?? $item->supervisor;
    @endphp

    <div class="apv-detail-page">

        {{-- Back Button --}}
        <a href="{{ route('hr.organization') }}" class="back-btn" aria-label="Kembali ke Divisi dan Jabatan">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if (session('success'))
            <div class="apv-alert apv-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="apv-alert apv-alert--error" role="alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form id="division-edit-form" method="POST" action="{{ route('hr.divisions.update', $item->id) }}" class="apv-detail-body">
            @csrf
            @method('PUT')

            {{-- Summary Header Card (selaras dengan /hr/leave-requests/show) --}}
            <div class="apv-card">
                <div class="apv-card-header">
                    <div class="apv-avatar apv-avatar--lg">{{ strtoupper(substr($item->name, 0, 1)) }}</div>
                    <div class="apv-card-header-info">
                        <span class="apv-entity-label">Divisi Organisasi</span>
                        <h2 class="apv-employee-name">{{ $item->name }}</h2>
                        <div class="apv-employee-meta">
                            <span class="apv-role-chip">DIVISI</span>
                            <span class="apv-meta-dot">•</span>
                            <span>Supervisor: {{ $currentSupervisor?->name ?? 'Belum ditentukan' }}</span>
                        </div>
                    </div>
                </div>
                <div class="apv-card-header-footer">
                    @if($currentSupervisor)
                        <span class="apv-badge apv-badge--success">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Memiliki Supervisor
                        </span>
                    @else
                        <span class="apv-badge apv-badge--gray">
                            Tanpa Supervisor
                        </span>
                    @endif
                    <span class="apv-submit-time">ID Divisi #{{ $item->id }}</span>
                </div>
            </div>

            {{-- Form Detail Card --}}
            <div class="apv-card">
                <div class="apv-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Informasi &amp; Pengaturan Divisi
                </div>

                <div class="edit-fields">
                    <div class="edit-form-group">
                        <label for="name" class="edit-form-label">
                            Nama Divisi <span class="req">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            class="edit-form-input"
                            value="{{ old('name', $item->name) }}"
                            placeholder="Contoh: IT, Finance, Marketing"
                            required>
                    </div>

                    <div class="edit-form-group">
                        <label for="supervisor" class="edit-form-label">
                            Supervisor
                        </label>
                        <select id="supervisor" name="supervisor_id" class="edit-form-select">
                            <option value="">Tidak ada / Belum ditentukan</option>
                            @foreach ($supervisors as $sup)
                                <option
                                    value="{{ $sup->id }}"
                                    @selected((string) old('supervisor_id', $item->supervisor_id) === (string) $sup->id)
                                >
                                    {{ $sup->name }} {{ $sup->username ? '('.$sup->username.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-helper">Supervisor bertanggung jawab untuk menyetujui izin anggota divisi.</small>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- FIXED ACTION BAR (selaras dengan /hr/leave-requests/show) --}}
    <div class="apv-action-bar" aria-label="Aksi edit divisi">
        <div class="apv-action-dock">
            <div class="apv-action-main">
                <a href="{{ route('hr.organization') }}" class="apv-action-btn apv-action-btn--secondary">
                    Batal
                </a>
                <button type="submit" form="division-edit-form" class="apv-action-btn apv-action-btn--primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <style>
        /* ========================================== */
        /* HEADER (x-slot)                            */
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
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
        }

        /* ========================================== */
        /* PAGE & BACK BUTTON                         */
        /* ========================================== */
        .apv-detail-page {
            max-width: 100%;
            margin: 0 auto;
            padding-bottom: 120px;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white, #FFFFFF);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            color: var(--text-muted, #6B7280);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            margin-bottom: 16px;
        }
        .back-btn:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .back-btn svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .back-btn:hover svg {
            transform: translateX(-2px);
        }
        .back-btn-text {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .apv-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .apv-alert svg { flex-shrink: 0; }
        .apv-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #15803d;
        }
        .apv-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* CARDS (selaras dengan /hr/leave-requests/show) */
        /* ========================================== */
        .apv-detail-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .apv-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .apv-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .apv-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        .apv-avatar--lg {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            font-size: 20px;
        }
        .apv-card-header-info {
            flex: 1;
            min-width: 0;
        }
        .apv-entity-label {
            display: block;
            color: var(--text-muted, #6B7280);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .apv-employee-name {
            margin: 0 0 4px 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.25;
        }
        .apv-employee-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            font-size: 12px;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
        }
        .apv-role-chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145DA0);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .apv-meta-dot {
            color: var(--text-light, #9CA3AF);
        }
        .apv-card-header-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }
        .apv-badge--success {
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
        .apv-badge--gray {
            background: var(--gray-100, #F3F4F6);
            color: var(--text-muted, #6B7280);
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-submit-time {
            font-size: 12px;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
        }

        /* ========================================== */
        /* SECTION TITLE & FORM FIELDS                */
        /* ========================================== */
        .apv-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-section-title svg {
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }
        .edit-fields {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .edit-form-group {
            margin: 0;
        }
        .edit-form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 7px;
        }
        .req {
            color: var(--error, #EF4444);
            font-weight: 700;
        }
        .edit-form-input,
        .edit-form-select {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-primary, #111827);
            background: var(--white, #FFFFFF);
            transition: all 0.2s ease;
            font-family: inherit;
            box-sizing: border-box;
            outline: none;
        }
        .edit-form-input:focus,
        .edit-form-select:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .form-helper {
            display: block;
            font-size: 12px;
            color: var(--text-muted, #6B7280);
            margin-top: 6px;
            line-height: 1.45;
        }

        /* ========================================== */
        /* FIXED ACTION BAR (selaras dengan /hr/leave-requests/show) */
        /* ========================================== */
        .apv-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
            pointer-events: none;
            transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        right 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        bottom 0.35s ease;
        }
        .apv-action-dock {
            background: var(--white, #FFFFFF);
            border-top: 1px solid var(--border-light, #E5E7EB);
            border-radius: 18px 18px 0 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            pointer-events: auto;
        }
        .apv-action-main {
            display: flex;
            flex-direction: column-reverse;
            gap: 8px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .apv-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            font-family: inherit;
            min-height: 44px;
        }
        .apv-action-btn svg { flex-shrink: 0; }
        .apv-action-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: var(--white, #FFFFFF);
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .apv-action-btn--primary:hover {
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.3);
            transform: translateY(-1px);
        }
        .apv-action-btn--secondary {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #6B7280);
            border: 1.5px solid var(--border-light, #E5E7EB);
        }
        .apv-action-btn--secondary:hover {
            background: var(--gray-100, #E5E7EB);
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 640px) {
            .apv-action-main {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                gap: 12px;
                width: 100%;
            }
            .apv-action-btn {
                width: auto;
                flex: 1 1 0;
                min-width: 0;
                padding: 12px 24px;
                font-size: 14px;
            }
        }
        @media (min-width: 768px) {
            .apv-card { padding: 24px; }
        }
        @media (min-width: 1025px) {
            .apv-action-bar {
                left: calc(var(--sidebar-width) + 12px);
                right: 0;
                bottom: 16px;
                padding: 0 16px;
            }
            .app.sidebar-collapsed .apv-action-bar {
                left: 0;
            }
            .apv-action-dock {
                border: 1px solid var(--border-light, #E5E7EB);
                border-radius: 18px;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
                padding: 10px;
                max-width: 1200px;
                margin: 0 auto;
            }
        }
        @media (max-width: 420px) {
            .apv-card-header { align-items: flex-start; }
            .apv-avatar--lg { width: 44px; height: 44px; border-radius: 12px; font-size: 17px; }
            .apv-employee-name { font-size: 1rem; }
        }
    </style>
</x-app>
