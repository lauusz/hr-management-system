<x-app title="Edit Jadwal Kerja">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy" aria-hidden="true">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Jadwal Kerja</h1>
            </div>
        </div>
    </x-slot>

    @php
        $monthLabel = \Carbon\Carbon::parse($month.'-01')->locale('id')->translatedFormat('F Y');
        $groupName = $group?->name ?? 'Tanpa Kelompok';
        $employeeCount = $records->pluck('user_id')->unique()->count();
        $dateCount = $records->pluck('work_date')->map->toDateString()->unique()->count();
        $back = route('hr.work-schedules.preview', ['month' => $month, 'group_id' => $groupId]);
        $shiftKey = fn ($record) => $record->shift_id ? (string) $record->shift_id : 'holiday';
        $describe = fn ($record) => $record->shift_name.' ('.($record->is_holiday ? 'Libur' : substr($record->start_time, 0, 5).'–'.substr($record->end_time, 0, 5)).($record->is_wita ? ' WITA' : ' WIB').($record->is_overnight ? ', lintas hari' : '').')';
    @endphp

    @if($errors->any())
        <div role="alert" class="lm-alert lm-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" stroke-linecap="round"/>
                <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="ws-top-bar">
        <a class="ws-back-btn" href="{{ $back }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <form method="POST" action="{{ route('hr.work-schedules.update') }}" class="ws-card" id="ws-edit-form">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="group_id" value="{{ $groupId }}">
        <input type="hidden" name="ids" value="{{ $idsParam }}">
        <input type="hidden" name="fingerprint" value="{{ $fingerprint }}">

        {{-- Card Header --}}
        <div class="ws-card-header">
            <div class="ws-header-main">
                <div class="ws-title-row">
                    <h2 class="ws-card-title">{{ $groupName }}</h2>
                    <span class="ws-period-badge">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $monthLabel }}
                    </span>
                </div>
                <div class="ws-quick-stats">
                    <span class="ws-stat-chip"><strong>{{ $employeeCount }}</strong> Karyawan</span>
                    <span class="ws-stat-chip"><strong>{{ $dateCount }}</strong> Tanggal</span>
                </div>
            </div>
        </div>

        {{-- Top Controls: Group Name & Bulk Shift --}}
        <div class="ws-controls-bar">
            @if($group)
                <div class="ws-control-box">
                    <label class="ws-control-label" for="ws-edit-group-name">Nama Kelompok</label>
                    <input
                        type="text"
                        id="ws-edit-group-name"
                        name="group_name"
                        maxlength="255"
                        required
                        value="{{ old('group_name', $group->name) }}"
                        class="ws-edit-input"
                        placeholder="Masukkan nama kelompok...">
                </div>
            @endif

            <div class="ws-control-box ws-control-box--highlight">
                <label class="ws-control-label" for="ws-edit-bulk-shift">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Ubah Semua Shift Sekaligus
                </label>
                <select id="ws-edit-bulk-shift" class="ws-edit-input">
                    <option value="">— Pilih untuk menerapkan ke semua baris —</option>
                    <option value="holiday">Libur</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Schedule Rows Table --}}
        <div class="ws-edit-table-wrap">
            <table class="ws-edit-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Jadwal Saat Ini</th>
                        <th>Ubah Shift / Libur</th>
                        <th>Status</th>
                        <th style="text-align: right;">Tindakan Baris</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                        @php
                            $key = $shiftKey($record);
                            $empName = $record->user?->name ?? '—';
                            $dateCarbon = $record->work_date->copy()->locale('id');
                            $dayName = $dateCarbon->translatedFormat('l');
                            $dateFormatted = $dateCarbon->translatedFormat('d M Y');
                            $timeRange = $record->is_holiday
                                ? 'Libur'
                                : substr((string) $record->start_time, 0, 5).'–'.substr((string) $record->end_time, 0, 5).' '.($record->is_wita ? 'WITA' : 'WIB');
                        @endphp
                        <tr data-ws-row data-current="{{ $key }}" data-current-label="{{ $describe($record) }}" class="ws-row">
                            <td>
                                <div class="ws-emp-cell">
                                    <span class="ws-emp-avatar" aria-hidden="true">{{ strtoupper(substr($empName, 0, 1)) }}</span>
                                    <span class="ws-emp-name">{{ $empName }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="ws-date-cell">
                                    <span class="ws-date-day">{{ $dayName }}</span>
                                    <span class="ws-date-full">{{ $dateFormatted }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="ws-current-cell">
                                    <span class="ws-shift-pill {{ $record->is_holiday ? 'ws-shift-pill--holiday' : '' }}">
                                        {{ $record->shift_name }}
                                    </span>
                                    @unless($record->is_holiday)
                                        <span class="ws-time-badge">{{ $timeRange }}</span>
                                    @endunless
                                </div>
                            </td>
                            <td>
                                <select
                                    name="rows[{{ $record->id }}][shift]"
                                    class="ws-edit-input ws-edit-shift"
                                    aria-label="Shift baru {{ $empName }} {{ $record->work_date->format('d/m/Y') }}">
                                    <option value="holiday" @selected(old('rows.'.$record->id.'.shift', $key) === 'holiday')>Libur</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" @selected((string) old('rows.'.$record->id.'.shift', $key) === (string) $shift->id)>{{ $shift->name }}</option>
                                    @endforeach
                                    @unless($key === 'holiday' || $shifts->contains('id', $record->shift_id))
                                        <option value="{{ $key }}" selected>{{ $record->shift_name }} (nonaktif, tetap)</option>
                                    @endunless
                                </select>
                            </td>
                            <td>
                                <span class="ws-row-badge ws-edit-change">Tetap</span>
                            </td>
                            <td style="text-align: right;">
                                <input type="hidden" name="rows[{{ $record->id }}][include]" value="0">
                                <label class="ws-toggle-btn" title="Klik untuk menonaktifkan atau mengaktifkan kembali tanggal ini">
                                    <input
                                        type="checkbox"
                                        name="rows[{{ $record->id }}][include]"
                                        value="1"
                                        class="ws-edit-include ws-sr-only"
                                        checked
                                        aria-label="Pertahankan jadwal {{ $empName }} {{ $record->work_date->format('d/m/Y') }}">
                                    <span class="ws-toggle-btn__text" data-toggle-label>Nonaktifkan</span>
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer Bar --}}
        <div class="ws-edit-footer">
            <div class="ws-summary-bar" aria-live="polite">
                <span class="ws-summary-pill ws-summary-pill--changed"><strong id="ws-count-changed">0</strong> Diubah</span>
                <span class="ws-summary-pill ws-summary-pill--removed"><strong id="ws-count-removed">0</strong> Dinonaktifkan</span>
                <span class="ws-summary-pill ws-summary-pill--kept"><strong id="ws-count-kept">{{ count($records) }}</strong> Tetap</span>
                <span id="ws-edit-summary" class="ws-sr-only"></span>
            </div>

            <div class="ws-edit-actions">
                <a class="ws-btn ws-btn--outline" href="{{ $back }}">Batal</a>
                <button type="submit" class="ws-btn ws-btn--submit" id="ws-edit-submit">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </form>

    <script>
        (() => {
            const form = document.getElementById('ws-edit-form');
            const rows = Array.from(form.querySelectorAll('[data-ws-row]'));
            const summary = document.getElementById('ws-edit-summary');
            const countChangedEl = document.getElementById('ws-count-changed');
            const countRemovedEl = document.getElementById('ws-count-removed');
            const countKeptEl = document.getElementById('ws-count-kept');

            const refresh = () => {
                let changed = 0, removed = 0;
                rows.forEach((row) => {
                    const select = row.querySelector('.ws-edit-shift');
                    const include = row.querySelector('.ws-edit-include').checked;
                    const badge = row.querySelector('.ws-edit-change');
                    const toggleLabel = row.querySelector('[data-toggle-label]');
                    const toggleBtn = row.querySelector('.ws-toggle-btn');

                    select.disabled = !include;
                    row.classList.remove('is-changed', 'is-removed');
                    badge.classList.remove('ws-row-badge--changed', 'ws-row-badge--removed');
                    toggleBtn?.classList.toggle('is-restore', !include);

                    if (!include) {
                        removed++;
                        row.classList.add('is-removed');
                        badge.classList.add('ws-row-badge--removed');
                        badge.textContent = 'Dinonaktifkan';
                        if (toggleLabel) toggleLabel.textContent = 'Batalkan Nonaktif';
                        return;
                    }

                    if (toggleLabel) toggleLabel.textContent = 'Nonaktifkan';

                    if (select.value !== row.dataset.current) {
                        changed++;
                        row.classList.add('is-changed');
                        badge.classList.add('ws-row-badge--changed');
                        badge.textContent = `Diubah → ${select.selectedOptions[0].textContent}`;
                    } else {
                        badge.textContent = 'Tetap';
                    }
                });

                const kept = rows.length - changed - removed;
                if (countChangedEl) countChangedEl.textContent = String(changed);
                if (countRemovedEl) countRemovedEl.textContent = String(removed);
                if (countKeptEl) countKeptEl.textContent = String(kept);
                if (summary) summary.textContent = `${changed} diubah · ${removed} dikeluarkan · ${kept} tetap`;
            };

            form.addEventListener('change', refresh);
            document.getElementById('ws-edit-bulk-shift').addEventListener('change', (event) => {
                if (!event.target.value) return;
                rows.forEach((row) => {
                    const select = row.querySelector('.ws-edit-shift');
                    if (row.querySelector('.ws-edit-include').checked && Array.from(select.options).some((o) => o.value === event.target.value)) {
                        select.value = event.target.value;
                    }
                });
                refresh();
            });
            form.addEventListener('submit', () => {
                document.getElementById('ws-edit-submit').disabled = true;
            });
            refresh();
        })();
    </script>

    <style>
        /* ========================================== */
        /* SECTION HEADER & TOP BAR                   */
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
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0a3d62);
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .lm-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 14px;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }
        .ws-top-bar {
            display: flex;
            align-items: center;
            margin-bottom: 14px;
        }
        .ws-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 13px;
            border-radius: 9px;
            border: 1.5px solid var(--border, #cbd5e1);
            background: var(--white, #fff);
            color: var(--text-secondary, #334155);
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            box-sizing: border-box;
            transition: all 0.15s ease;
        }
        .ws-back-btn:hover {
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
            background: var(--gray-50, #f8fafc);
        }

        /* ========================================== */
        /* CARD & HEADER                              */
        /* ========================================== */
        .ws-card {
            background: var(--white, #fff);
            border: 1px solid var(--border-light, #e2e8f0);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .ws-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light, #e2e8f0);
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(255, 255, 255, 1));
        }
        .ws-header-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .ws-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .ws-card-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
        }
        .ws-period-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            font-size: 0.75rem;
            font-weight: 700;
        }
        .ws-quick-stats {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ws-stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .ws-stat-chip strong {
            color: #0f172a;
            font-weight: 800;
        }

        /* ========================================== */
        /* CONTROLS BAR (GROUP NAME & BULK APPLY)     */
        /* ========================================== */
        .ws-controls-bar {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 16px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .ws-control-box {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .ws-control-box--highlight {
            background: rgba(20, 93, 160, 0.05);
            border: 1px solid rgba(20, 93, 160, 0.18);
            padding: 10px 12px;
            border-radius: 12px;
        }
        .ws-control-label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .ws-control-box--highlight .ws-control-label {
            color: var(--primary, #145da0);
        }
        .ws-edit-input {
            width: 100%;
            min-height: 38px;
            border: 1.5px solid #cbd5e1;
            border-radius: 9px;
            padding: 7px 11px;
            font: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .ws-edit-input:focus {
            border-color: var(--primary, #145da0);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .ws-edit-input:disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }

        /* ========================================== */
        /* TABLE                                      */
        /* ========================================== */
        .ws-edit-table-wrap {
            overflow-x: auto;
        }
        .ws-edit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
            min-width: 720px;
        }
        .ws-edit-table th {
            padding: 11px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .ws-edit-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .ws-row {
            transition: background 0.15s ease;
        }
        .ws-row:hover {
            background: #fafbfc;
        }
        .ws-row.is-changed {
            background: rgba(20, 93, 160, 0.04);
        }
        .ws-row.is-removed {
            background: #fef2f2;
        }
        .ws-row.is-removed .ws-emp-name,
        .ws-row.is-removed .ws-date-day {
            text-decoration: line-through;
            color: #94a3b8;
        }

        .ws-emp-cell {
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .ws-emp-avatar {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.1);
            color: var(--primary, #145da0);
            font-size: 0.72rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ws-emp-name {
            font-weight: 700;
            color: #0f172a;
        }

        .ws-date-cell {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .ws-date-day {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.8125rem;
        }
        .ws-date-full {
            font-size: 0.72rem;
            color: #64748b;
            font-weight: 600;
        }

        .ws-current-cell {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }
        .ws-shift-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 7px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            font-size: 0.74rem;
            font-weight: 700;
        }
        .ws-shift-pill--holiday {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }
        .ws-time-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 7px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #334155;
        }

        .ws-edit-table .ws-edit-shift {
            min-width: 210px;
        }

        /* Status Badge & Row Action Toggle */
        .ws-row-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            background: #f1f5f9;
            color: #64748b;
            white-space: nowrap;
        }
        .ws-row-badge--changed {
            background: rgba(20, 93, 160, 0.12);
            color: var(--primary, #145da0);
        }
        .ws-row-badge--removed {
            background: #fee2e2;
            color: #dc2626;
        }

        .ws-sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .ws-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 5px 11px;
            border-radius: 8px;
            font-size: 0.74rem;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
            box-sizing: border-box;
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            color: #dc2626;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .ws-toggle-btn:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }
        .ws-toggle-btn.is-restore {
            background: #fff;
            border-color: #cbd5e1;
            color: #334155;
        }
        .ws-toggle-btn.is-restore:hover {
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
        }

        /* ========================================== */
        /* FOOTER & SUMMARY PILLS                     */
        /* ========================================== */
        .ws-edit-footer {
            padding: 16px 20px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .ws-summary-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .ws-summary-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
        }
        .ws-summary-pill strong {
            font-weight: 800;
        }
        .ws-summary-pill--changed strong {
            color: var(--primary, #145da0);
        }
        .ws-summary-pill--removed strong {
            color: #dc2626;
        }
        .ws-summary-pill--kept strong {
            color: #16a34a;
        }

        .ws-edit-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ws-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 40px;
            padding: 9px 18px;
            border-radius: 10px;
            font: inherit;
            font-size: 0.8125rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s ease;
            border: none;
            text-decoration: none;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .ws-btn--outline {
            background: #fff;
            color: #334155;
            border: 1.5px solid #cbd5e1;
        }
        .ws-btn--outline:hover {
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
        }
        .ws-btn--submit {
            background: linear-gradient(135deg, var(--primary-dark, #0a3d62), var(--primary, #145da0));
            color: #fff;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.2);
        }
        .ws-btn--submit:hover {
            box-shadow: 0 6px 16px rgba(10, 61, 98, 0.28);
            transform: translateY(-1px);
        }
        .ws-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        @media (min-width: 768px) {
            .ws-controls-bar {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                align-items: end;
            }
            .section-icon {
                width: 40px;
                height: 40px;
                border-radius: 12px;
            }
            .section-title {
                font-size: 1.2rem;
            }
        }
    </style>
</x-app>
