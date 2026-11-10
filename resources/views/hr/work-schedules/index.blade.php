<x-app title="Jadwal Kerja">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Jadwal Kerja</h1>
                <p class="section-subtitle">Penetapan jadwal shift khusus atau libur berdasarkan rentang tanggal</p>
            </div>
        </div>
    </x-slot>

    @php
        $monthCarbon = \Carbon\Carbon::parse($month . '-01')->locale('id');
        $monthLabel = $monthCarbon->translatedFormat('F Y');
        $monthStart = $month . '-01';
        $monthEnd = $monthCarbon->copy()->endOfMonth()->toDateString();

        $currentYear = (int) now()->year;
        $selectedYear = (int) ($monthCarbon->year ?: $currentYear);
        $selectedMonthNum = $monthCarbon->format('m');

        $monthOptions = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $yearOptions = collect(range($currentYear - 2, $currentYear + 3))
            ->push($selectedYear)
            ->unique()
            ->sort()
            ->values();

        $selectedUsers = (array) old('users', []);
        $selectedUsers = array_map('intval', $selectedUsers);

        $initialRanges = old('ranges', [['start' => '', 'end' => '', 'shift' => '']]);

        $totalSchedules = $schedules->count();
        $totalScheduledUsers = $schedules->pluck('user_id')->unique()->count();
        $totalWorkingDays = $schedules->where('is_holiday', false)->count();
        $totalHolidayDays = $schedules->where('is_holiday', true)->count();
    @endphp

    {{-- Alerts --}}
    @if(session('success'))
        <div class="lm-alert lm-alert--success alert-success" role="status">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

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

    @unless($schemaReady)
        <div role="alert" class="lm-alert lm-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Pembaruan Jadwal Kerja belum lengkap. Hubungi HR atau IT.</span>
        </div>
    @endunless

    {{-- Top Bar: Month Filter & Related Menu Shortcuts --}}
    <div class="ws-top-card">
        <form method="GET" action="{{ route('hr.work-schedules.index') }}" class="ws-month-form" id="ws-month-form">
            <input type="hidden" name="month" id="ws-month-input" value="{{ $month }}">

            <div class="ws-period-controls">
                <div class="ws-month-field">
                    <label for="ws-month-select" class="ws-field-label">Bulan</label>
                    <select id="ws-month-select" class="ws-select ws-select--month" aria-label="Pilih Bulan">
                        @foreach($monthOptions as $num => $label)
                            <option value="{{ $num }}" @selected($selectedMonthNum === (string) $num)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ws-month-field">
                    <label for="ws-year-select" class="ws-field-label">Tahun</label>
                    <select id="ws-year-select" class="ws-select ws-select--year" aria-label="Pilih Tahun">
                        @foreach($yearOptions as $yr)
                            <option value="{{ $yr }}" @selected($selectedYear === (int) $yr)>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ws-month-submit">
                    <button type="submit" class="ws-btn ws-btn--primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Lihat
                    </button>
                </div>
            </div>

            <div class="ws-related-links">
                <a href="{{ route('hr.schedules.index') }}" class="ws-link-chip">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Jadwal Reguler Karyawan
                </a>
                <a href="{{ route('hr.shifts.index') }}" class="ws-link-chip">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Master Shift Kerja
                </a>
            </div>
        </form>
    </div>

    {{-- Stats Summary --}}
    <div class="lm-stats">
        <div class="lm-stat">
            <div class="lm-stat-value">{{ $totalSchedules }}</div>
            <div class="lm-stat-label">Jadwal Khusus ({{ $monthLabel }})</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--primary">{{ $totalScheduledUsers }}</div>
            <div class="lm-stat-label">Karyawan Terjadwal</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--success">{{ $totalWorkingDays }}</div>
            <div class="lm-stat-label">Hari Kerja</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--neutral">{{ $totalHolidayDays }}</div>
            <div class="lm-stat-label">Hari Libur</div>
        </div>
    </div>

    @if($schemaReady)
        {{-- Main Builder Card --}}
        <form method="POST" action="{{ route('hr.work-schedules.store') }}" class="ws-card ws-form-card" id="ws-builder-form">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">

            @if(session('work_schedule_conflicts'))
                <dialog id="ws-conflict-dialog" class="ws-conflict-dialog" aria-modal="true" aria-labelledby="ws-conflict-title" aria-describedby="ws-conflict-description" data-signature="{{ session('work_schedule_conflicts.signature') }}">
                    <h2 id="ws-conflict-title">Ubah Jadwal Kerja?</h2>
                    <p id="ws-conflict-description">Jadwal berikut sudah tersimpan dan akan diganti.</p>
                    <div class="ws-conflict-list" tabindex="0" aria-label="Rincian jadwal yang akan diganti">
                        <ul>@foreach(session('work_schedule_conflicts.dates') as $conflict)<li>{{ $conflict }}</li>@endforeach</ul>
                    </div>
                    <div class="ws-conflict-actions">
                        <button type="button" id="ws-conflict-cancel" class="ws-btn ws-btn--outline" autofocus>Batal</button>
                        <button type="button" id="ws-conflict-confirm" class="ws-btn ws-btn--submit">Ubah</button>
                    </div>
                </dialog>
            @endif

            <div class="ws-card-header">
                <div>
                    <h2 class="ws-card-title">Atur Jadwal Khusus — {{ $monthLabel }}</h2>
                    <p class="ws-card-subtitle">Pilih satu atau beberapa karyawan, lalu tentukan rentang tanggal dan shift atau libur eksplisit.</p>
                </div>
                <span class="ws-period-badge">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ \Carbon\Carbon::parse($monthStart)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($monthEnd)->format('d/m/Y') }}
                </span>
            </div>

            <div class="ws-builder-grid">
                {{-- Step 1: Pilih Karyawan --}}
                <div class="ws-step-section">
                    <div class="ws-step-header">
                        <div class="ws-step-title-wrap">
                            <span class="ws-step-number">1</span>
                            <div>
                                <label class="ws-step-title" for="work-users">Karyawan (pilih satu atau beberapa)</label>
                                <p class="ws-step-desc">Centang karyawan yang akan diberikan jadwal khusus pada periode ini.</p>
                            </div>
                        </div>
                        <span class="ws-selected-badge" id="ws-selected-count">{{ count($selectedUsers) }} dipilih</span>
                    </div>

                    {{-- Native multi-select kept in DOM for form submission & accessibility/test compatibility --}}
                    <select id="work-users" name="users[]" multiple required class="ws-sr-only" tabindex="-1" aria-hidden="true">
                        @foreach(($allUsers ?? $users) as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, $selectedUsers, true))>{{ $user->name }}</option>
                        @endforeach
                    </select>

                    <div class="ws-user-picker">
                        <div class="ws-user-picker-toolbar">
                            <div class="ws-user-search-wrap">
                                <svg class="ws-user-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    type="search"
                                    id="ws-user-search"
                                    name="q"
                                    value="{{ old('q', $search ?? '') }}"
                                    placeholder="Cari nama karyawan..."
                                    autocomplete="off"
                                    aria-label="Cari karyawan"
                                    data-schedule-live-search
                                    class="ws-user-search-input">
                            </div>
                            <div class="ws-user-bulk-btns">
                                <button type="button" id="ws-select-all-users" class="ws-btn-ghost">Pilih Semua</button>
                                <button type="button" id="ws-clear-all-users" class="ws-btn-ghost">Reset</button>
                            </div>
                        </div>

                        <div data-schedule-results aria-live="polite" aria-busy="false">
                            <div class="ws-user-list" id="ws-user-list" role="group" aria-label="Daftar karyawan">
                                @forelse($users as $user)
                                    @php
                                        $isChecked = in_array($user->id, $selectedUsers, true);
                                    @endphp
                                    <label class="ws-user-item {{ $isChecked ? 'is-selected' : '' }}" data-user-name="{{ strtolower($user->name) }}">
                                        <input
                                            type="checkbox"
                                            value="{{ $user->id }}"
                                            class="ws-user-checkbox user-schedule-checkbox"
                                            @checked($isChecked)>
                                        <span class="ws-user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        <span class="ws-user-name">{{ $user->name }}</span>
                                    </label>
                                @empty
                                    <div class="ws-user-empty">
                                        {{ ($search ?? '') !== '' ? 'Tidak ditemukan karyawan yang cocok dengan pencarian.' : 'Tidak ada data karyawan aktif.' }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Rentang Tanggal & Shift --}}
                <div class="ws-step-section">
                    <div class="ws-step-header">
                        <div class="ws-step-title-wrap">
                            <span class="ws-step-number">2</span>
                            <div>
                                <h3 class="ws-step-title">Rentang Tanggal &amp; Shift</h3>
                                <p class="ws-step-desc">Tambahkan satu atau beberapa rentang tanggal dalam bulan {{ $monthLabel }}.</p>
                            </div>
                        </div>
                        <button type="button" id="work-add" class="ws-btn ws-btn--outline">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah rentang
                        </button>
                    </div>

                    <label class="ws-field-label" for="work-group-name">Nama Kelompok</label>
                    <input type="text" id="work-group-name" name="group_name" value="{{ old('group_name') }}" maxlength="255" required class="ws-input" placeholder="Nama kelompok">

                    <div class="ws-info-banner">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>
                            Tambahkan rentang tanpa tumpang tindih. Untuk menyesuaikan satu hari, buat rentang khusus tanggal tersebut. Hari libur master ditampilkan sebagai libur; untuk bekerja pilih shift lain yang memiliki jam pada hari itu.
                        </p>
                    </div>

                    <div id="work-ranges" class="ws-ranges-list">
                        @foreach($initialRanges as $index => $range)
                            @php
                                $startVal = $range['start'] ?? '';
                                $endVal = $range['end'] ?? '';
                                $rangeDisplayVal = '';
                                if ($startVal !== '' && $endVal !== '') {
                                    $rangeDisplayVal = $startVal === $endVal
                                        ? $startVal
                                        : ($startVal . ' sampai ' . $endVal);
                                } elseif ($startVal !== '') {
                                    $rangeDisplayVal = $startVal;
                                }
                            @endphp
                            <div class="work-range ws-range-row">
                                <div class="ws-range-field ws-range-field--dates">
                                    <label class="ws-field-label">
                                        Rentang Tanggal
                                        <input
                                            type="text"
                                            value="{{ $rangeDisplayVal }}"
                                            placeholder="Pilih rentang tanggal"
                                            autocomplete="off"
                                            required
                                            class="ws-input ws-range-picker">
                                    </label>
                                    <input
                                        type="hidden"
                                        name="ranges[{{ $index }}][start]"
                                        value="{{ $startVal }}"
                                        class="ws-range-start">
                                    <input
                                        type="hidden"
                                        name="ranges[{{ $index }}][end]"
                                        value="{{ $endVal }}"
                                        class="ws-range-end">
                                </div>

                                <div class="ws-range-field ws-range-field--shift">
                                    <label class="ws-field-label">
                                        Shift
                                        <select name="ranges[{{ $index }}][shift]" required class="ws-select">
                                            <option value="">Pilih Shift / Libur</option>
                                            <option value="holiday" @selected(($range['shift'] ?? '') === 'holiday')>Libur</option>
                                            @foreach($shifts as $shift)
                                                <option value="{{ $shift->id }}" @selected(($range['shift'] ?? '') == $shift->id)>{{ $shift->name }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>

                                <div class="ws-range-action">
                                    <button type="button" class="work-remove ws-btn-remove" title="Hapus rentang ini">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="ws-form-footer">
                        <div class="ws-form-hint">
                            Bentrokan akan ditampilkan jika jadwal tersimpan perlu diganti.
                        </div>
                        <button type="submit" id="ws-save-schedule" class="ws-btn ws-btn--submit">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Saved Schedules Table Card --}}
        <div class="ws-card ws-saved-card">
            <div class="ws-saved-header">
                <div>
                    <h2 class="ws-card-title">Jadwal tersimpan</h2>
                    <p class="ws-card-subtitle">Daftar jadwal khusus karyawan pada bulan {{ $monthLabel }}</p>
                </div>

                <div class="ws-saved-filters">
                    <div class="ws-saved-search-wrap">
                        <svg class="ws-user-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="search"
                            id="ws-table-search"
                            name="schedule_q"
                            value="{{ $scheduleSearch ?? '' }}"
                            placeholder="Cari kelompok / karyawan / shift..."
                            autocomplete="off"
                            data-saved-schedule-live-search
                            class="ws-user-search-input">
                    </div>
                    <select id="ws-table-status" name="status" class="ws-select ws-select--compact" aria-label="Filter status jadwal">
                        <option value="">Semua Status</option>
                        <option value="kerja" @selected(($statusFilter ?? '') === 'kerja')>Kerja</option>
                        <option value="libur" @selected(($statusFilter ?? '') === 'libur')>Libur</option>
                    </select>
                </div>
            </div>

            <div class="lm-table-wrap" data-saved-schedule-results aria-live="polite" aria-busy="false">
                @if($scheduleSearch !== '' || in_array($statusFilter, ['kerja', 'libur'], true))
                    <p class="ws-card-subtitle">Jumlah karyawan dan tanggal unik menunjukkan jadwal yang sesuai pencarian dan filter status.</p>
                @endif
                <table class="lm-data-table" id="ws-saved-table">
                    <thead>
                        <tr>
                            <th>Nama Kelompok</th>
                            <th>Jumlah Karyawan</th>
                            <th>Jumlah Tanggal Terjadwal</th>
                            <th>Lihat Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules->groupBy(fn ($schedule) => $schedule->work_schedule_group_id ?? 'ungrouped') as $groupId => $groupSchedules)
                            @php
                                $groupName = $groupSchedules->first()->group?->name ?? 'Tanpa Kelompok';
                                $employeeCount = $groupSchedules->pluck('user_id')->unique()->count();
                                $dateCount = $groupSchedules->map(fn ($schedule) => $schedule->work_date->toDateString())->unique()->count();
                            @endphp
                            <tr>
                                <td><div class="lm-employee-name">{{ $groupName }}</div></td>
                                <td>{{ $employeeCount }} karyawan</td>
                                <td>{{ $dateCount }} tanggal</td>
                                <td><a href="{{ route('hr.work-schedules.preview', ['month' => $month, 'group_id' => $groupId]) }}">Lihat Detail</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="lm-empty">
                                        <div class="lm-empty-icon">
                                            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        @if($scheduleSearch !== '' || in_array($statusFilter, ['kerja', 'libur'], true))
                                            <div class="lm-empty-title">Tidak ada jadwal yang sesuai pencarian atau filter.</div>
                                            <div class="lm-empty-sub">Coba ubah pencarian atau filter status pada bulan ini.</div>
                                        @else
                                            <div class="lm-empty-title">Belum ada jadwal khusus bulan ini.</div>
                                            <div class="lm-empty-sub">Karyawan yang tidak memiliki jadwal khusus pada tanggal tertentu akan mengikuti jadwal reguler.</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
    <script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
    <script>
        (() => {
            const monthForm = document.getElementById('ws-month-form');
            const monthInput = document.getElementById('ws-month-input');
            const monthSelect = document.getElementById('ws-month-select');
            const yearSelect = document.getElementById('ws-year-select');

            if (monthInput && monthSelect && yearSelect) {
                const syncPeriodValue = () => {
                    monthInput.value = `${yearSelect.value}-${monthSelect.value.padStart(2, '0')}`;
                };
                monthForm?.addEventListener('submit', syncPeriodValue);
            }
        })();
    </script>

    @if($schemaReady)
        <script>
            (() => {
                const conflictDialog = document.getElementById('ws-conflict-dialog');
                if (conflictDialog) {
                    const form = document.getElementById('ws-builder-form');
                    const cancel = document.getElementById('ws-conflict-cancel');
                    const confirm = document.getElementById('ws-conflict-confirm');
                    let submitting = false;

                    conflictDialog.addEventListener('cancel', (event) => {
                        if (submitting) event.preventDefault();
                    });
                    conflictDialog.addEventListener('click', (event) => {
                        if (event.target === conflictDialog && !submitting) conflictDialog.close();
                    });
                    conflictDialog.addEventListener('close', () => {
                        document.getElementById('ws-save-schedule')?.focus();
                    });
                    cancel.addEventListener('click', () => conflictDialog.close());
                    confirm.addEventListener('click', () => {
                        if (submitting || !form.reportValidity()) return;
                        submitting = true;
                        confirm.disabled = true;
                        cancel.disabled = true;
                        for (const [name, value] of [['replace', '1'], ['conflict_confirmation', conflictDialog.dataset.signature]]) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = value;
                            form.appendChild(input);
                        }
                        form.requestSubmit();
                    });
                    conflictDialog.showModal();
                }

                // 1. Dynamic Date Ranges Builder with Flatpickr Range (consistent with /hr/leave/master)
                const container = document.getElementById('work-ranges');
                const addBtn = document.getElementById('work-add');
                const monthStart = @json($monthStart);
                const monthEnd = @json($monthEnd);

                if (container && addBtn) {
                    let next = Math.max(
                        ...Array.from(container.querySelectorAll('input[name], select[name]'), (el) => {
                            const match = el.name.match(/\[(\d+)\]/);
                            return match ? Number(match[1]) : 0;
                        }),
                        0
                    ) + 1;

                    const syncHiddenRangeFromText = (row) => {
                        const pickerInput = row.querySelector('.ws-range-picker');
                        const startInput = row.querySelector('.ws-range-start');
                        const endInput = row.querySelector('.ws-range-end');
                        if (!pickerInput || !startInput || !endInput) return;

                        const raw = pickerInput.value.trim();
                        if (!raw) {
                            startInput.value = '';
                            endInput.value = '';
                            return;
                        }

                        const parts = raw.split(/\s+sampai\s+|\s+to\s+/i).map((s) => s.trim()).filter(Boolean);
                        startInput.value = parts[0] || '';
                        endInput.value = parts[1] || parts[0] || '';
                    };

                    const initRangePicker = (row) => {
                        const pickerInput = row.querySelector('.ws-range-picker');
                        const startInput = row.querySelector('.ws-range-start');
                        const endInput = row.querySelector('.ws-range-end');
                        if (!pickerInput || !startInput || !endInput) return;

                        pickerInput.addEventListener('input', () => syncHiddenRangeFromText(row));
                        pickerInput.addEventListener('change', () => syncHiddenRangeFromText(row));

                        if (typeof window.flatpickr === 'function') {
                            const startVal = startInput.value.trim();
                            const endVal = endInput.value.trim();
                            const defaultDates = startVal && endVal
                                ? (startVal === endVal ? [startVal] : [startVal, endVal])
                                : (startVal ? [startVal] : undefined);

                            window.flatpickr(pickerInput, {
                                mode: 'range',
                                dateFormat: 'Y-m-d',
                                minDate: monthStart,
                                maxDate: monthEnd,
                                defaultDate: defaultDates,
                                allowInput: true,
                                locale: { rangeSeparator: ' sampai ' },
                                onChange(selectedDates, _dateStr, instance) {
                                    if (selectedDates.length === 2) {
                                        startInput.value = instance.formatDate(selectedDates[0], 'Y-m-d');
                                        endInput.value = instance.formatDate(selectedDates[1], 'Y-m-d');
                                    } else if (selectedDates.length === 1) {
                                        const single = instance.formatDate(selectedDates[0], 'Y-m-d');
                                        startInput.value = single;
                                        endInput.value = single;
                                    } else {
                                        startInput.value = '';
                                        endInput.value = '';
                                    }
                                },
                                onClose(selectedDates, _dateStr, instance) {
                                    if (selectedDates.length === 1) {
                                        const single = instance.formatDate(selectedDates[0], 'Y-m-d');
                                        startInput.value = single;
                                        endInput.value = single;
                                        pickerInput.value = single;
                                    }
                                },
                            });
                        }
                    };

                    const updateRemoveButtonsState = () => {
                        const rows = container.querySelectorAll('.work-range');
                        rows.forEach((row) => {
                            const btn = row.querySelector('.work-remove');
                            if (btn) {
                                btn.disabled = rows.length <= 1;
                                btn.classList.toggle('is-disabled', rows.length <= 1);
                            }
                        });
                    };

                    // Keep a clean template before Flatpickr mutates DOM inputs
                    const rowTemplate = container.firstElementChild?.cloneNode(true);

                    Array.from(container.querySelectorAll('.work-range')).forEach((row) => {
                        initRangePicker(row);
                    });

                    addBtn.addEventListener('click', () => {
                        const sourceRow = rowTemplate || container.firstElementChild;
                        if (!sourceRow) return;
                        const row = sourceRow.cloneNode(true);
                        row.querySelectorAll('input, select').forEach((el) => {
                            if (el.name) {
                                el.name = el.name.replace(/ranges\[\d+\]/, `ranges[${next}]`);
                            }
                            el.value = '';
                        });
                        container.appendChild(row);
                        initRangePicker(row);
                        next++;
                        updateRemoveButtonsState();
                    });

                    container.addEventListener('click', (event) => {
                        const removeBtn = event.target.closest('.work-remove');
                        if (removeBtn && container.children.length > 1) {
                            const row = removeBtn.closest('.work-range');
                            const pickerInput = row?.querySelector('.ws-range-picker');
                            if (pickerInput && pickerInput._flatpickr) {
                                pickerInput._flatpickr.destroy();
                            }
                            row?.remove();
                            updateRemoveButtonsState();
                        }
                    });

                    document.getElementById('ws-builder-form')?.addEventListener('submit', () => {
                        container.querySelectorAll('.work-range').forEach((row) => {
                            const startInput = row.querySelector('.ws-range-start');
                            const endInput = row.querySelector('.ws-range-end');
                            if (startInput && endInput && (!startInput.value || !endInput.value)) {
                                syncHiddenRangeFromText(row);
                            }
                        });
                    });

                    updateRemoveButtonsState();
                }

                // 2. Employee Multi-Select Sync & AJAX Live Search (like /hr/schedules)
                const selectEl = document.getElementById('work-users');
                const results = document.querySelector('[data-schedule-results]');
                const savedResults = document.querySelector('[data-saved-schedule-results]');
                const searchInput = document.querySelector('[data-schedule-live-search]');
                const tableSearch = document.querySelector('[data-saved-schedule-live-search]');
                const tableStatus = document.getElementById('ws-table-status');
                const countBadge = document.getElementById('ws-selected-count');
                const selectAllBtn = document.getElementById('ws-select-all-users');
                const clearAllBtn = document.getElementById('ws-clear-all-users');
                const indexBaseUrl = @json(route('hr.work-schedules.index'));
                const displayedMonth = @json($month);

                if (selectEl && results) {
                    const optionsByValue = new Map(
                        Array.from(selectEl.options).map((opt) => [String(opt.value), opt])
                    );

                    const selectedUserIds = new Set(
                        Array.from(selectEl.selectedOptions, (opt) => String(opt.value))
                    );

                    let searchTimer;
                    let searchRequest;

                    const syncSelection = () => {
                        const checkboxes = Array.from(results.querySelectorAll('.user-schedule-checkbox'));
                        checkboxes.forEach((cb) => {
                            const isChecked = selectedUserIds.has(String(cb.value));
                            cb.checked = isChecked;
                            cb.closest('.ws-user-item')?.classList.toggle('is-selected', isChecked);
                        });

                        optionsByValue.forEach((opt, val) => {
                            opt.selected = selectedUserIds.has(val);
                        });

                        if (countBadge) {
                            countBadge.textContent = `${selectedUserIds.size} dipilih`;
                        }
                    };

                    results.addEventListener('change', (event) => {
                        if (event.target.classList.contains('user-schedule-checkbox')) {
                            const val = String(event.target.value);
                            if (event.target.checked) {
                                selectedUserIds.add(val);
                            } else {
                                selectedUserIds.delete(val);
                            }
                            syncSelection();
                        }
                    });

                    selectEl.addEventListener('change', () => {
                        selectedUserIds.clear();
                        Array.from(selectEl.selectedOptions).forEach((opt) => {
                            selectedUserIds.add(String(opt.value));
                        });
                        syncSelection();
                    });

                    selectAllBtn?.addEventListener('click', () => {
                        results.querySelectorAll('.user-schedule-checkbox').forEach((cb) => {
                            selectedUserIds.add(String(cb.value));
                        });
                        syncSelection();
                    });

                    clearAllBtn?.addEventListener('click', () => {
                        selectedUserIds.clear();
                        syncSelection();
                    });

                    async function loadScheduleResults(targetContainer = results) {
                        const url = new URL(indexBaseUrl, window.location.origin);
                        url.searchParams.set('month', displayedMonth);

                        const qVal = (searchInput?.value || '').trim();
                        if (qVal !== '') {
                            url.searchParams.set('q', qVal);
                        }

                        const scheduleQVal = (tableSearch?.value || '').trim();
                        if (scheduleQVal !== '') {
                            url.searchParams.set('schedule_q', scheduleQVal);
                        }

                        const statusVal = (tableStatus?.value || '').trim();
                        if (statusVal !== '') {
                            url.searchParams.set('status', statusVal);
                        }

                        searchRequest?.abort();
                        const currentRequest = new AbortController();
                        searchRequest = currentRequest;

                        targetContainer?.classList.add('is-loading');
                        targetContainer?.setAttribute('aria-busy', 'true');

                        try {
                            const response = await fetch(url, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                signal: currentRequest.signal,
                            });
                            if (!response.ok) throw new Error('Pencarian karyawan gagal');

                            const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
                            const nextUserResults = documentResult.querySelector('[data-schedule-results]');
                            const nextSavedResults = documentResult.querySelector('[data-saved-schedule-results]');

                            if (nextUserResults && results) {
                                results.innerHTML = nextUserResults.innerHTML;
                            }
                            if (nextSavedResults && savedResults) {
                                savedResults.innerHTML = nextSavedResults.innerHTML;
                            }

                            window.history.replaceState(null, '', url);
                            syncSelection();
                        } catch (error) {
                            if (error.name !== 'AbortError') console.error(error);
                        } finally {
                            if (searchRequest === currentRequest) {
                                targetContainer?.classList.remove('is-loading');
                                targetContainer?.setAttribute('aria-busy', 'false');
                            }
                        }
                    }

                    searchInput?.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            clearTimeout(searchTimer);
                            loadScheduleResults(results);
                        }
                    });

                    searchInput?.addEventListener('input', () => {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(() => loadScheduleResults(results), 350);
                    });

                    tableSearch?.addEventListener('input', () => {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(() => loadScheduleResults(savedResults), 350);
                    });

                    tableStatus?.addEventListener('change', () => {
                        clearTimeout(searchTimer);
                        loadScheduleResults(savedResults);
                    });

                    syncSelection();
                }
            })();
        </script>
    @endif

    <style>
        .ws-conflict-dialog {
            width: min(560px, calc(100% - 32px));
            max-height: min(80vh, 720px);
            border: 1px solid var(--border-light, #e2e8f0);
            border-radius: 16px;
            padding: 24px;
            color: var(--text-primary, #0f172a);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
        }
        .ws-conflict-dialog::backdrop { background: rgba(15, 23, 42, 0.55); }
        .ws-conflict-dialog h2 { margin: 0 0 8px; font-size: 1.1rem; }
        .ws-conflict-dialog p { margin: 0 0 16px; color: var(--text-secondary, #475569); }
        .ws-conflict-list { max-height: 45vh; overflow-y: auto; }
        .ws-conflict-list ul { margin: 0; padding-left: 22px; }
        .ws-conflict-list li { margin-bottom: 8px; overflow-wrap: anywhere; }
        .ws-conflict-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }
        /* ========================================== */
        /* SECTION HEADER                             */
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
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #64748b);
            font-weight: 500;
            line-height: 1.35;
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .lm-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
        }
        .lm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #15803d;
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* TOP BAR & STATS                            */
        /* ========================================== */
        .ws-top-card {
            background: var(--white, #fff);
            border: 1px solid var(--border-light, #e2e8f0);
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .ws-month-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .ws-period-controls {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: end;
        }
        .ws-month-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .ws-month-submit {
            grid-column: 1 / -1;
            display: flex;
        }
        .ws-month-submit .ws-btn {
            width: 100%;
        }
        .ws-select--month {
            min-width: 150px;
        }
        .ws-select--year {
            min-width: 110px;
        }
        .ws-related-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .ws-link-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid var(--border, #cbd5e1);
            background: var(--gray-50, #f8fafc);
            color: var(--text-secondary, #334155);
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.18s ease;
        }
        .ws-link-chip:hover {
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
            background: rgba(20, 93, 160, 0.05);
        }

        .lm-stats {
            display: flex;
            align-items: stretch;
            gap: 1px;
            background: var(--border-light, #e2e8f0);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #e2e8f0);
        }
        .lm-stat {
            flex: 1;
            background: var(--white, #fff);
            padding: 12px 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 0;
        }
        .lm-stat-value {
            font-size: 1.125rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
            line-height: 1;
        }
        .lm-stat-value--primary { color: var(--primary, #145da0); }
        .lm-stat-value--success { color: #16a34a; }
        .lm-stat-value--neutral { color: #d97706; }
        .lm-stat-label {
            font-size: 0.625rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            line-height: 1.2;
        }
        .lm-stat-divider { display: none; }

        /* ========================================== */
        /* CARDS & BUILDER                            */
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
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(255, 255, 255, 1));
        }
        .ws-card-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
        }
        .ws-card-subtitle {
            margin: 2px 0 0;
            font-size: 0.8rem;
            color: var(--text-muted, #64748b);
        }
        .ws-period-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            align-self: flex-start;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            font-size: 0.75rem;
            font-weight: 700;
        }

        .ws-builder-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 18px 20px;
        }

        .ws-step-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .ws-step-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .ws-step-title-wrap {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .ws-step-number {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: var(--primary-dark, #0a3d62);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .ws-step-title {
            margin: 0;
            display: block;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-primary, #0f172a);
        }
        .ws-step-desc {
            margin: 2px 0 0;
            font-size: 0.75rem;
            color: var(--text-muted, #64748b);
        }
        .ws-selected-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(20, 93, 160, 0.1);
            color: var(--primary, #145da0);
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Screen-reader / hidden select for form submission */
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

        /* Employee Picker */
        [data-schedule-results],
        [data-saved-schedule-results] {
            transition: opacity 0.15s ease;
        }
        [data-schedule-results].is-loading,
        [data-saved-schedule-results].is-loading {
            opacity: 0.55;
            pointer-events: none;
        }
        .ws-user-picker {
            border: 1.5px solid var(--border, #cbd5e1);
            border-radius: 14px;
            overflow: hidden;
            background: var(--white, #fff);
        }
        .ws-user-picker-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: var(--gray-50, #f8fafc);
            border-bottom: 1px solid var(--border-light, #e2e8f0);
        }
        .ws-user-search-wrap {
            position: relative;
            flex: 1;
            min-width: 180px;
        }
        .ws-user-search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted, #64748b);
            pointer-events: none;
        }
        .ws-user-search-input {
            width: 100%;
            padding: 8px 12px 8px 34px;
            border: 1px solid var(--border, #cbd5e1);
            border-radius: 10px;
            font-size: 0.8125rem;
            color: var(--text-primary, #0f172a);
            background: var(--white, #fff);
            outline: none;
        }
        .ws-user-search-input:focus {
            border-color: var(--primary, #145da0);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .ws-user-bulk-btns {
            display: flex;
            gap: 6px;
        }
        .ws-btn-ghost {
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid var(--border, #cbd5e1);
            background: var(--white, #fff);
            color: var(--text-secondary, #334155);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .ws-btn-ghost:hover {
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
        }

        .ws-user-list {
            max-height: 290px;
            overflow-y: auto;
            padding: 8px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 6px;
        }
        .ws-user-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid transparent;
            background: var(--gray-50, #f8fafc);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .ws-user-item:hover {
            border-color: rgba(20, 93, 160, 0.3);
            background: rgba(20, 93, 160, 0.04);
        }
        .ws-user-item.is-selected {
            border-color: var(--primary, #145da0);
            background: rgba(20, 93, 160, 0.08);
        }
        .ws-user-checkbox {
            width: 16px;
            height: 16px;
            accent-color: var(--primary, #145da0);
            cursor: pointer;
            flex-shrink: 0;
        }
        .ws-user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.12);
            color: var(--primary, #145da0);
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ws-user-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary, #0f172a);
            line-height: 1.25;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .ws-user-empty {
            padding: 20px;
            text-align: center;
            font-size: 0.8125rem;
            color: var(--text-muted, #64748b);
        }

        /* Info Banner */
        .ws-info-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(20, 93, 160, 0.06);
            border: 1px solid rgba(20, 93, 160, 0.16);
            color: var(--primary-dark, #0a3d62);
            font-size: 0.78rem;
            line-height: 1.45;
        }
        .ws-info-banner svg {
            flex-shrink: 0;
            margin-top: 1px;
            color: var(--primary, #145da0);
        }
        .ws-info-banner p {
            margin: 0;
        }

        /* Date Ranges List */
        .ws-ranges-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ws-range-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid var(--border-light, #e2e8f0);
            background: var(--gray-50, #f8fafc);
            align-items: end;
        }
        .ws-field-label {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-secondary, #334155);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .ws-input,
        .ws-select {
            width: 100%;
            min-height: 40px;
            padding: 8px 12px;
            border: 1.5px solid var(--border, #cbd5e1);
            border-radius: 10px;
            background: var(--white, #fff);
            color: var(--text-primary, #0f172a);
            font-size: 0.84rem;
            font-weight: 500;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            box-sizing: border-box;
        }
        .ws-input:focus,
        .ws-select:focus {
            border-color: var(--primary, #145da0);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .ws-input--month {
            max-width: 220px;
        }
        .ws-select--compact {
            width: auto;
            min-width: 145px;
        }

        /* Buttons */
        .ws-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 40px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s ease;
            border: none;
            text-decoration: none;
            white-space: nowrap;
        }
        .ws-btn--primary {
            background: var(--primary, #145da0);
            color: #fff;
        }
        .ws-btn--primary:hover {
            background: var(--primary-dark, #0a3d62);
        }
        .ws-btn--outline {
            background: var(--white, #fff);
            color: var(--primary, #145da0);
            border: 1.5px solid rgba(20, 93, 160, 0.35);
        }
        .ws-btn--outline:hover {
            background: rgba(20, 93, 160, 0.06);
            border-color: var(--primary, #145da0);
        }
        .ws-btn--submit {
            background: linear-gradient(135deg, var(--primary-dark, #0a3d62), var(--primary, #145da0));
            color: #fff;
            padding: 11px 22px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .ws-btn--submit:hover {
            box-shadow: 0 6px 18px rgba(10, 61, 98, 0.3);
            transform: translateY(-1px);
        }
        .ws-btn-remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            width: 100%;
            min-height: 40px;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #dc2626;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .ws-btn-remove:hover:not(:disabled) {
            background: #fee2e2;
            border-color: #fca5a5;
        }
        .ws-btn-remove.is-disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .ws-form-footer {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding-top: 12px;
            border-top: 1px solid var(--border-light, #e2e8f0);
            margin-top: 4px;
        }
        .ws-form-hint {
            font-size: 0.75rem;
            color: var(--text-muted, #64748b);
        }

        /* ========================================== */
        /* SAVED TABLE                                */
        /* ========================================== */
        .ws-saved-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light, #e2e8f0);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .ws-saved-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .ws-saved-search-wrap {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .lm-table-wrap {
            overflow-x: auto;
        }
        .lm-data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 680px;
        }
        .lm-data-table th {
            text-align: left;
            padding: 13px 16px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--gray-50, #f8fafc);
            border-bottom: 1px solid var(--border-light, #e2e8f0);
        }
        .lm-data-table td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border-light, #e2e8f0);
            vertical-align: middle;
            font-size: 0.8125rem;
            color: var(--text-primary, #0f172a);
        }
        .lm-data-table tr:last-child td {
            border-bottom: none;
        }
        .lm-data-table tbody tr:hover td {
            background: var(--gray-50, #f8fafc);
        }

        .ws-date-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .ws-date-main {
            font-weight: 700;
            color: var(--text-primary, #0f172a);
        }
        .ws-date-sub {
            font-size: 0.72rem;
            color: var(--text-muted, #64748b);
        }

        .lm-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .lm-employee-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            font-size: 0.78rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .lm-employee-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary, #0f172a);
        }

        .ws-shift-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            font-size: 0.75rem;
            font-weight: 700;
        }
        .ws-time-cell {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .ws-time-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            background: var(--gray-100, #f1f5f9);
            color: var(--text-primary, #0f172a);
        }
        .ws-overnight-tag {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
            background: rgba(124, 58, 237, 0.1);
            color: #6d28d9;
        }
        .ws-status-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .lm-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .lm-badge--success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }
        .lm-badge--holiday {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }
        .ws-tz-badge {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 3px 7px;
            border-radius: 6px;
            background: var(--gray-100, #f1f5f9);
            color: var(--text-secondary, #475569);
        }
        .ws-text-muted {
            color: var(--text-muted, #94a3b8);
        }

        .lm-empty {
            text-align: center;
            padding: 36px 20px;
        }
        .lm-empty-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: var(--gray-50, #f8fafc);
            color: var(--text-muted, #94a3b8);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        .lm-empty-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary, #0f172a);
            margin-bottom: 4px;
        }
        .lm-empty-sub {
            font-size: 0.78rem;
            color: var(--text-muted, #64748b);
            max-width: 420px;
            margin: 0 auto;
        }

        /* ========================================== */
        /* RESPONSIVE BREAKPOINTS                     */
        /* ========================================== */
        @media (min-width: 640px) {
            .ws-month-form {
                flex-direction: row;
                align-items: flex-end;
                justify-content: space-between;
            }
            .ws-period-controls {
                display: flex;
                align-items: flex-end;
                gap: 10px;
            }
            .ws-month-submit {
                grid-column: auto;
            }
            .ws-month-submit .ws-btn {
                width: auto;
            }
            .ws-card-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .ws-range-row {
                grid-template-columns: 1.5fr 1.2fr auto;
            }
            .ws-btn-remove {
                width: auto;
            }
            .ws-form-footer {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .ws-saved-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        @media (min-width: 768px) {
            .section-icon {
                width: 40px;
                height: 40px;
                border-radius: 12px;
            }
            .section-title {
                font-size: 1.2rem;
            }
            .lm-stats {
                border-radius: 14px;
            }
            .lm-stat {
                padding: 16px 14px;
            }
            .lm-stat-value {
                font-size: 1.4rem;
            }
            .lm-stat-label {
                font-size: 0.72rem;
            }
        }
    </style>

</x-app>
