<x-app :title="'Detail Akses Absensi - ' . $viewer->name">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Akses Absensi</h1>
                <p class="section-subtitle">Atur daftar karyawan yang rekap absensinya dapat dilihat oleh {{ $viewer->name }}</p>
            </div>
        </div>
    </x-slot>

    @php
        $pts = $employees->pluck('profile.pt')->filter()->unique('id')->sortBy('name')->values();
        $divisions = $employees->pluck('division')->filter()->unique('id')->sortBy('name')->values();
        $initialSelectedCount = count($selectedIds);
    @endphp

    <div class="apv-detail-page">

        {{-- Back Button --}}
        <a
            href="{{ route('hr.attendance-access.index') }}"
            class="back-btn"
            id="ava-back-to-list"
            aria-label="Kembali ke daftar penerima akses">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if (session('success'))
            <div class="apv-alert apv-alert--success" role="status">
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

        <div class="apv-detail-body">

            {{-- Summary Header Card (selaras dengan /hr/leave-requests/show & /hr/positions/edit) --}}
            <div class="apv-card">
                <div class="apv-card-header">
                    <div class="apv-avatar apv-avatar--lg" aria-hidden="true">{{ mb_strtoupper(mb_substr($viewer->name, 0, 1)) }}</div>
                    <div class="apv-card-header-info">
                        <span class="apv-entity-label">Penerima Akses Absensi</span>
                        <h2 class="apv-employee-name">{{ $viewer->name }}</h2>
                        <div class="apv-employee-meta">
                            <span class="apv-role-chip">{{ $viewer->role->label() }}</span>
                            @if ($viewer->position?->name)
                                <span class="apv-meta-dot">•</span>
                                <span>{{ $viewer->position->name }}</span>
                            @endif
                            <span class="apv-meta-dot">•</span>
                            <span>Divisi: {{ $viewer->division?->name ?? '-' }}</span>
                            <span class="apv-meta-dot">•</span>
                            <span>PT: {{ $viewer->profile?->pt?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="apv-card-header-footer">
                    @if ($initialSelectedCount > 0)
                        <span class="apv-badge apv-badge--success">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Memiliki Akses · {{ $initialSelectedCount }} Karyawan
                        </span>
                    @else
                        <span class="apv-badge apv-badge--gray">
                            Belum Memiliki Akses
                        </span>
                    @endif
                    <span class="apv-submit-time">ID Pengguna #{{ $viewer->id }}</span>
                </div>
            </div>

            {{-- Form Pengaturan Cakupan Karyawan --}}
            <form method="POST" action="{{ route('hr.attendance-access.update', $viewer) }}" id="attendance-access-form" class="apv-card">
                @csrf
                <input type="hidden" name="employee_ids_json" id="attendance-access-values" value="">

                <div class="apv-section-title-bar">
                    <div class="apv-section-title-inline">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Karyawan yang Dapat Dilihat</span>
                    </div>
                    <span class="ava-selected-pill" id="ava-selected-count">{{ $initialSelectedCount }} dipilih</span>
                </div>

                <div class="ava-info-banner">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16v-4m0-4h.01"/>
                    </svg>
                    <span>Centang karyawan yang rekap absensinya boleh dilihat oleh <strong>{{ $viewer->name }}</strong>. Menghapus seluruh centang lalu menyimpan akan mencabut akses pengguna ini.</span>
                </div>

                <div class="ava-filters-grid">
                    <div class="edit-form-group">
                        <label for="ava-search" class="edit-form-label">Cari Karyawan</label>
                        <div class="ava-search-wrap">
                            <svg class="ava-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                type="search"
                                id="ava-search"
                                class="edit-form-input ava-search-input"
                                placeholder="Ketik nama karyawan..."
                                autocomplete="off">
                        </div>
                    </div>

                    <div class="edit-form-group">
                        <label for="ava-pt" class="edit-form-label">Perusahaan (PT)</label>
                        <select id="ava-pt" class="edit-form-select">
                            <option value="">Semua PT</option>
                            @foreach ($pts as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="edit-form-group">
                        <label for="ava-division" class="edit-form-label">Divisi</label>
                        <select id="ava-division" class="edit-form-select">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ava-toolbar-row">
                    <label class="ava-select-all-label">
                        <input type="checkbox" id="ava-select-visible">
                        <span>Centang semua karyawan yang tampil</span>
                    </label>
                    <span class="ava-visible-meta" id="ava-visible-count"></span>
                </div>

                <div class="ava-list" id="ava-list">
                    @foreach ($employees as $employee)
                        @php
                            $isChecked = in_array($employee->id, $selectedIds);
                            $employeeMetaParts = array_values(array_filter([
                                $employee->profile?->pt?->name ?? '-',
                                $employee->division?->name ?? '-',
                                $employee->position?->name,
                            ]));
                        @endphp
                        <label
                            class="ava-employee {{ $isChecked ? 'is-checked' : '' }}"
                            data-name="{{ mb_strtolower($employee->name) }}"
                            data-pt="{{ $employee->profile?->pt_id }}"
                            data-division="{{ $employee->division_id }}">
                            <input type="checkbox" value="{{ $employee->id }}" @checked($isChecked)>
                            <span class="ava-employee-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($employee->name, 0, 1)) }}</span>
                            <span class="ava-employee-info">
                                <span class="ava-employee-name-row">
                                    <strong>{{ $employee->name }}</strong>
                                    @if (! $employee->isActive())
                                        <span class="ava-inactive-badge">Nonaktif</span>
                                    @endif
                                </span>
                                <small>{{ implode(' · ', $employeeMetaParts) }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="ava-empty-filter" id="ava-empty-filter" hidden>
                    Tidak ada karyawan yang cocok dengan kata kunci atau filter yang dipilih.
                </div>

                <noscript>
                    <p class="apv-noscript-error">Aktifkan JavaScript untuk menyimpan pilihan akses.</p>
                </noscript>
            </form>
        </div>
    </div>

    {{-- FIXED ACTION BAR (selaras dengan /hr/leave-requests/show & /hr/positions/edit) --}}
    <div class="apv-action-bar" aria-label="Aksi pengaturan akses absensi">
        <div class="apv-action-dock">
            <div class="apv-action-main">
                <a
                    href="{{ route('hr.attendance-access.index') }}"
                    class="apv-action-btn apv-action-btn--secondary"
                    id="ava-cancel-btn">
                    Batal
                </a>
                <button type="submit" form="attendance-access-form" class="apv-action-btn apv-action-btn--primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Akses
                </button>
            </div>
        </div>
    </div>

    <script data-attendance-unsaved-guard>
        (() => {
            const hasChanges = () => [...document.querySelectorAll('.ava-employee input')]
                .some((input) => input.checked !== input.defaultChecked);

            const guardNavigation = (event) => {
                if (hasChanges() && !confirm('Pilihan karyawan belum disimpan. Kembali ke daftar penerima akses?')) {
                    event.preventDefault();
                }
            };

            document.getElementById('ava-back-to-list')?.addEventListener('click', guardNavigation);
            document.getElementById('ava-cancel-btn')?.addEventListener('click', guardNavigation);
        })();
    </script>

    <script data-attendance-selection>
        (() => {
            const form = document.getElementById('attendance-access-form');
            const rows = [...document.querySelectorAll('.ava-employee')];
            const search = document.getElementById('ava-search');
            const pt = document.getElementById('ava-pt');
            const division = document.getElementById('ava-division');
            const selectVisible = document.getElementById('ava-select-visible');
            const count = document.getElementById('ava-selected-count');
            const visibleCount = document.getElementById('ava-visible-count');
            const emptyFilter = document.getElementById('ava-empty-filter');

            function update() {
                rows.forEach((row) => {
                    const checkbox = row.querySelector('input');
                    row.hidden = !row.dataset.name.includes(search.value.trim().toLocaleLowerCase())
                        || (pt.value && row.dataset.pt !== pt.value)
                        || (division.value && row.dataset.division !== division.value);
                    row.classList?.toggle('is-checked', Boolean(checkbox.checked));
                });

                const visible = rows.filter((row) => !row.hidden);
                const checked = visible.filter((row) => row.querySelector('input').checked).length;

                selectVisible.checked = visible.length > 0 && checked === visible.length;
                selectVisible.indeterminate = checked > 0 && checked < visible.length;
                selectVisible.disabled = visible.length === 0;
                count.textContent = rows.filter((row) => row.querySelector('input').checked).length + ' dipilih';

                if (visibleCount) {
                    visibleCount.textContent = visible.length + ' dari ' + rows.length + ' karyawan tampil';
                }
                if (emptyFilter && rows.length > 0) {
                    emptyFilter.hidden = visible.length > 0;
                }
            }

            [search, pt, division].forEach((input) => input.addEventListener('input', update));
            rows.forEach((row) => row.querySelector('input').addEventListener('change', update));
            selectVisible.addEventListener('change', () => {
                rows.filter((row) => !row.hidden).forEach((row) => {
                    row.querySelector('input').checked = selectVisible.checked;
                });
                update();
            });
            form.addEventListener('submit', () => {
                document.getElementById('attendance-access-values').value = JSON.stringify(
                    rows.filter((row) => row.querySelector('input').checked).map((row) => Number(row.querySelector('input').value))
                );
            });

            update();
        })();
    </script>

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
            box-sizing: border-box;
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
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            margin-bottom: 16px;
            box-sizing: border-box;
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
            box-sizing: border-box;
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
        /* CARDS (selaras /hr/leave-requests/show)    */
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
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
        /* SECTION HEADER & INFO BANNER               */
        /* ========================================== */
        .apv-section-title-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-section-title-inline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .apv-section-title-inline svg {
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }
        .ava-selected-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 11px;
            border-radius: 99px;
            background: rgba(20, 93, 160, 0.08);
            border: 1px solid rgba(20, 93, 160, 0.18);
            color: var(--primary, #145DA0);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        .ava-info-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            margin-bottom: 16px;
            border-radius: 12px;
            background: rgba(20, 93, 160, 0.05);
            border: 1px solid rgba(20, 93, 160, 0.14);
            color: var(--text-secondary, #374151);
            font-size: 12.5px;
            line-height: 1.5;
            box-sizing: border-box;
        }
        .ava-info-banner svg {
            color: var(--primary, #145DA0);
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* ========================================== */
        /* FILTERS & CHECKLIST                        */
        /* ========================================== */
        .ava-filters-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }
        .edit-form-group {
            margin: 0;
        }
        .edit-form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 6px;
        }
        .ava-search-wrap {
            position: relative;
        }
        .ava-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }
        .edit-form-input,
        .edit-form-select {
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            font-size: 13.5px;
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
            transition: all 0.2s ease;
            font-family: inherit;
            box-sizing: border-box;
            outline: none;
        }
        .ava-search-input {
            padding-left: 36px;
        }
        .edit-form-input:focus,
        .edit-form-select:focus {
            border-color: var(--primary, #145DA0);
            background: var(--white, #FFFFFF);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .ava-toolbar-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 10px 14px;
            margin-bottom: 10px;
            background: var(--gray-50, #F5F7FA);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            box-sizing: border-box;
        }
        .ava-select-all-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary, #111827);
            cursor: pointer;
        }
        .ava-select-all-label input[type="checkbox"] {
            accent-color: var(--primary, #145DA0);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        .ava-visible-meta {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted, #6B7280);
        }
        .ava-list {
            max-height: 440px;
            overflow-y: auto;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
            background: var(--white, #FFFFFF);
        }
        .ava-employee {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 14px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            cursor: pointer;
            transition: background 0.15s ease;
            box-sizing: border-box;
        }
        .ava-employee:last-child {
            border-bottom: none;
        }
        .ava-employee:hover {
            background: var(--gray-50, #F5F7FA);
        }
        .ava-employee.is-checked {
            background: rgba(20, 93, 160, 0.05);
        }
        .ava-employee[hidden] {
            display: none;
        }
        .ava-employee input[type="checkbox"] {
            accent-color: var(--primary, #145DA0);
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            cursor: pointer;
        }
        .ava-employee-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .ava-employee-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            flex: 1;
        }
        .ava-employee-name-row {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .ava-employee-name-row strong {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary, #111827);
            line-height: 1.3;
        }
        .ava-inactive-badge {
            display: inline-flex;
            align-items: center;
            padding: 1px 7px;
            border-radius: 99px;
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .ava-employee-info small {
            font-size: 11.5px;
            color: var(--text-muted, #6B7280);
            line-height: 1.35;
        }
        .ava-empty-filter {
            padding: 28px 16px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted, #6B7280);
            border: 1px dashed var(--border-light, #E5E7EB);
            border-radius: 12px;
            margin-top: 10px;
        }
        .ava-empty-filter[hidden] {
            display: none;
        }
        .apv-noscript-error {
            margin: 12px 0 0;
            padding: 12px;
            border-radius: 10px;
            background: #fef2f2;
            color: #991b1b;
            font-size: 13px;
        }

        /* ========================================== */
        /* FIXED ACTION BAR (selaras /hr/leave-requests/show) */
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
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            pointer-events: auto;
            box-sizing: border-box;
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
            box-sizing: border-box;
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
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.04);
                padding: 10px;
                max-width: 1200px;
                margin: 0 auto;
            }
        }
        @media (max-width: 768px) {
            .ava-filters-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 420px) {
            .apv-card-header { align-items: flex-start; }
            .apv-avatar--lg { width: 44px; height: 44px; border-radius: 12px; font-size: 17px; }
            .apv-employee-name { font-size: 1rem; }
        }
    </style>
</x-app>
