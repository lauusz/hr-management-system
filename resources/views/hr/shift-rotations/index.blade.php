<x-app title="Jadwal Rotasi Kerja">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Jadwal Rotasi Kerja</h1>
                <p class="section-subtitle">Atur pergantian shift otomatis untuk setiap kelompok karyawan</p>
            </div>
        </div>
    </x-slot>

    @php
        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $frequencyMeta = [
            'monthly' => [
                'label' => 'Bulanan',
                'title' => 'Rotasi Bulanan',
                'subtitle' => 'Pergantian shift otomatis setiap tanggal 1 awal bulan.',
                'empty_desc' => 'Belum ada kelompok rotasi bulanan yang terdaftar.',
            ],
            'weekly' => [
                'label' => 'Mingguan',
                'title' => 'Rotasi Mingguan',
                'subtitle' => 'Pergantian shift otomatis pada hari tertentu setiap minggu.',
                'empty_desc' => 'Belum ada kelompok rotasi mingguan yang terdaftar.',
            ],
            'days' => [
                'label' => 'Lainnya',
                'title' => 'Rotasi Interval Hari (Lainnya)',
                'subtitle' => 'Pergantian shift otomatis berdasarkan interval jumlah hari kerja.',
                'empty_desc' => 'Belum ada kelompok rotasi interval hari yang terdaftar.',
            ],
        ];

        $totalRotations = $rotations->count();
        $activeRotationsCount = $rotations->where('is_active', true)->count();
        $stoppedRotationsCount = $totalRotations - $activeRotationsCount;
        $totalActiveMembers = $members->flatten(1)->count();
        $userRotationMap = $userRotationNames ?? collect();
    @endphp

    <div class="rotation-page">
        {{-- Flash & Validation Alerts --}}
        @if (session('success'))
            <div class="lm-alert lm-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="lm-alert lm-alert--error" role="alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="lm-alert lm-alert--error" role="alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                </svg>
                <div class="sr-alert-content">
                    <strong>Data belum dapat disimpan:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (! $ready)
            <div class="lm-table-card">
                <div class="lm-empty">
                    <div class="lm-empty-icon lm-empty-icon--warning">
                        <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="lm-empty-title">Pengaturan Belum Tersedia</h3>
                    <p class="lm-empty-desc">Pengaturan rotasi belum tersedia. Hubungi HR atau IT.</p>
                </div>
            </div>
        @else
            {{-- Top Split Bar: Frequency Tabs + Primary CTA --}}
            <div class="lm-cta-bar lm-cta-bar--split">
                <div class="lm-tabs" role="tablist" aria-label="Filter frekuensi rotasi kerja">
                    <button
                        type="button"
                        class="lm-tab-btn active"
                        data-freq-tab="all"
                        role="tab"
                        aria-selected="true">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <span>Semua</span>
                        <span class="lm-tab-count">{{ $totalRotations }}</span>
                    </button>

                    <button
                        type="button"
                        class="lm-tab-btn"
                        data-freq-tab="monthly"
                        role="tab"
                        aria-selected="false">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        <span>Bulanan</span>
                        <span class="lm-tab-count">{{ $rotations->where('frequency', 'monthly')->count() }}</span>
                    </button>

                    <button
                        type="button"
                        class="lm-tab-btn"
                        data-freq-tab="weekly"
                        role="tab"
                        aria-selected="false">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Mingguan</span>
                        <span class="lm-tab-count">{{ $rotations->where('frequency', 'weekly')->count() }}</span>
                    </button>

                    <button
                        type="button"
                        class="lm-tab-btn"
                        data-freq-tab="days"
                        role="tab"
                        aria-selected="false">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 3"/>
                        </svg>
                        <span>Lainnya</span>
                        <span class="lm-tab-count">{{ $rotations->where('frequency', 'days')->count() }}</span>
                    </button>
                </div>

                <div class="lm-cta-actions">
                    <button
                        type="button"
                        id="rotation-top-add"
                        class="lm-btn-primary"
                        data-add-rotation="weekly">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Rotasi</span>
                    </button>
                </div>
            </div>

            {{-- Summary Stats --}}
            <div class="lm-stats" data-rotation-stats>
                <div class="lm-stat">
                    <div class="lm-stat-value">{{ $totalRotations }}</div>
                    <div class="lm-stat-label">Total Kelompok</div>
                </div>
                <div class="lm-stat-divider"></div>
                <div class="lm-stat">
                    <div class="lm-stat-value lm-stat-value--success">{{ $activeRotationsCount }}</div>
                    <div class="lm-stat-label">Rotasi Aktif</div>
                </div>
                <div class="lm-stat-divider"></div>
                <div class="lm-stat">
                    <div class="lm-stat-value lm-stat-value--neutral">{{ $stoppedRotationsCount }}</div>
                    <div class="lm-stat-label">Dihentikan</div>
                </div>
                <div class="lm-stat-divider"></div>
                <div class="lm-stat">
                    <div class="lm-stat-value lm-stat-value--info">{{ $totalActiveMembers }}</div>
                    <div class="lm-stat-label">Karyawan Ikut</div>
                </div>
            </div>

            {{-- Search & Filter Card --}}
            <div class="lm-filter-card">
                <div class="lm-search-row">
                    <div class="lm-search-input-wrap">
                        <svg class="lm-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="search"
                            id="rotation-page-search"
                            class="lm-search-input"
                            placeholder="Cari nama kelompok rotasi, urutan shift, atau nama karyawan..."
                            autocomplete="off"
                            aria-label="Cari kelompok rotasi"
                            data-rotation-live-search>
                    </div>

                    <button
                        type="button"
                        id="rotation-filter-reset"
                        class="lm-btn-reset"
                        style="display: none;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </button>

                    <button
                        type="button"
                        id="rotation-filter-toggle"
                        class="lm-btn-toggle-filter"
                        aria-expanded="false"
                        aria-controls="rotation-filter-panel">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                        <span class="lm-filter-badge" id="rotation-filter-badge" style="display: none;">1</span>
                    </button>
                </div>

                <div class="lm-filter-panel" id="rotation-filter-panel" style="display: none;">
                    <div class="lm-filter-grid">
                        <div class="lm-filter-group">
                            <label class="lm-filter-label" for="rotation-status-filter">Status Rotasi</label>
                            <select id="rotation-status-filter" class="lm-filter-input">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="stopped">Dihentikan</option>
                            </select>
                        </div>

                        <div class="lm-filter-actions">
                            <button type="button" id="rotation-filter-apply" class="lm-btn-apply">Terapkan Filter</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Frequency Sections --}}
            <div class="sr-sections-stack">
                @foreach ($frequencyMeta as $frequency => $meta)
                    @php
                        $groupRotations = $rotations->where('frequency', $frequency)->values();
                    @endphp
                    <section class="lm-table-card rotation-section" data-freq-section="{{ $frequency }}">
                        <div class="sr-section-header">
                            <div class="sr-section-title-group">
                                <div class="sr-section-icon sr-section-icon--{{ $frequency }}">
                                    @if ($frequency === 'monthly')
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01"/>
                                        </svg>
                                    @elseif ($frequency === 'weekly')
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    @else
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 3"/>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="sr-section-heading-row">
                                        <h2>{{ $meta['label'] }}</h2>
                                        <span class="sr-count-chip">{{ $groupRotations->count() }} Kelompok</span>
                                    </div>
                                    <p>{{ $meta['subtitle'] }}</p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="lm-btn-primary sr-btn-add"
                                data-add-rotation="{{ $frequency }}">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Tambah</span>
                            </button>
                        </div>

                        <div class="sr-section-body">
                            @forelse ($groupRotations as $rotation)
                                @php
                                    $orderedShiftIds = json_decode($rotation->shift_ids, true) ?: [];
                                    $orderedShiftLabels = collect($orderedShiftIds)
                                        ->map(fn ($id) => $shiftNames->get($id, 'Shift tidak ditemukan'))
                                        ->values();
                                    $rotationMembers = $members->get($rotation->id, collect());
                                    $rotationSnapshots = $snapshots->get($rotation->id, collect());

                                    if ($rotation->frequency === 'weekly') {
                                        $ruleText = 'Setiap hari ' . ($dayNames[$rotation->weekly_day] ?? 'Senin');
                                    } elseif ($rotation->frequency === 'monthly') {
                                        $ruleText = 'Tanggal 1 setiap bulan';
                                    } else {
                                        $ruleText = 'Setiap ' . $rotation->interval_days . ' hari';
                                    }

                                    $searchableBlob = mb_strtolower(implode(' ', [
                                        $rotation->name,
                                        $orderedShiftLabels->implode(' '),
                                        $rotationMembers->pluck('name')->implode(' '),
                                        $ruleText,
                                    ]));
                                @endphp

                                <details
                                    class="rotation-item {{ $rotation->is_active ? 'is-active' : 'is-stopped' }}"
                                    data-rotation-card
                                    data-rotation-status="{{ $rotation->is_active ? 'active' : 'stopped' }}"
                                    data-rotation-search="{{ $searchableBlob }}">
                                    <summary class="sr-item-summary">
                                        <div class="sr-item-left">
                                            <div class="lm-employee-avatar {{ $rotation->is_active ? '' : 'lm-employee-avatar--muted' }}">
                                                {{ mb_strtoupper(mb_substr($rotation->name, 0, 1)) }}
                                            </div>
                                            <div class="sr-item-title-wrap">
                                                <strong class="sr-item-name">{{ $rotation->name }}</strong>
                                                <div class="sr-item-meta">
                                                    <span class="sr-meta-item">
                                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/>
                                                        </svg>
                                                        Mulai {{ \Carbon\Carbon::parse($rotation->start_date)->format('d/m/Y') }}
                                                    </span>
                                                    <span class="sr-meta-dot">•</span>
                                                    <span class="sr-meta-item">
                                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                        </svg>
                                                        {{ $ruleText }}
                                                    </span>
                                                    <span class="sr-meta-dot">•</span>
                                                    <span class="sr-meta-item">
                                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                                                        </svg>
                                                        {{ $orderedShiftLabels->count() }} Shift
                                                    </span>
                                                    @if ($rotation->is_active)
                                                        <span class="sr-meta-dot">•</span>
                                                        <span class="sr-meta-item">
                                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            </svg>
                                                            {{ $rotationMembers->count() }} Karyawan
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="sr-item-right">
                                            @if ($rotation->is_active)
                                                <span class="lm-badge lm-badge--success">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="lm-badge lm-badge--neutral">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Dihentikan
                                                </span>
                                            @endif

                                            <span class="sr-chevron" aria-hidden="true">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </summary>

                                    <div class="rotation-detail">
                                        {{-- Panel 1: Urutan Siklus Shift --}}
                                        <div class="sr-detail-block">
                                            <div class="sr-detail-block-head">
                                                <span class="sr-detail-label">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                    </svg>
                                                    Urutan Siklus Shift
                                                </span>
                                                <span class="sr-detail-sub">Pergantian: {{ $ruleText }}</span>
                                            </div>

                                            <div class="sr-shift-chain">
                                                @foreach ($orderedShiftLabels as $idx => $shiftLabel)
                                                    <span class="sr-shift-pill">
                                                        <span class="sr-shift-pill-num">{{ $idx + 1 }}</span>
                                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                                                        </svg>
                                                        <span>{{ $shiftLabel }}</span>
                                                    </span>
                                                    @if (! $loop->last)
                                                        <svg class="sr-shift-arrow" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                        </svg>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Panel 2: Anggota & Aksi Hentikan (jika aktif) --}}
                                        @if ($rotation->is_active)
                                            <div class="sr-detail-block">
                                                <div class="sr-detail-block-head sr-detail-block-head--between">
                                                    <span class="sr-detail-label">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        Karyawan dan Shift Awal ({{ $rotationMembers->count() }})
                                                    </span>

                                                    <button
                                                        type="button"
                                                        class="lm-btn-action lm-btn-action--delete"
                                                        data-modal-target="stop-rotation-{{ $rotation->id }}">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                                            <rect x="9" y="9" width="6" height="6" rx="1" stroke-width="2"/>
                                                        </svg>
                                                        Hentikan Rotasi
                                                    </button>
                                                </div>

                                                @if ($rotationMembers->isEmpty())
                                                    <p class="sr-inline-muted">Tidak ada karyawan aktif pada kelompok ini.</p>
                                                @else
                                                    <div class="sr-member-grid">
                                                        @foreach ($rotationMembers as $member)
                                                            <div class="sr-member-card">
                                                                <div class="sr-member-person">
                                                                    <span class="sr-mini-avatar">{{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}</span>
                                                                    <span class="sr-member-name">{{ $member->name }}</span>
                                                                </div>
                                                                <span class="lm-type lm-type--izin">
                                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                        <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                                                                    </svg>
                                                                    {{ $member->shift_name }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Panel 3: Riwayat Jadwal --}}
                                        <div class="sr-detail-block">
                                            <div class="sr-detail-block-head sr-detail-block-head--between">
                                                <span class="sr-detail-label">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Riwayat Jadwal (200 Terbaru)
                                                </span>
                                                <span class="sr-detail-sub">{{ $rotationSnapshots->count() }} catatan</span>
                                            </div>

                                            <div class="lm-table-wrap rotation-history">
                                                <table class="lm-data-table sr-history-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Tanggal</th>
                                                            <th>Karyawan</th>
                                                            <th>Shift</th>
                                                            <th>Jam Kerja</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($rotationSnapshots as $snapshot)
                                                            <tr>
                                                                <td class="sr-td-date">
                                                                    <span class="sr-date-inline">
                                                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/>
                                                                        </svg>
                                                                        {{ \Carbon\Carbon::parse($snapshot->work_date)->format('d/m/Y') }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <div class="sr-history-user">
                                                                        <span class="sr-mini-avatar">{{ mb_strtoupper(mb_substr($snapshot->user_name, 0, 1)) }}</span>
                                                                        <span>{{ $snapshot->user_name }}</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="sr-history-shift">{{ $snapshot->shift_name }}</span>
                                                                </td>
                                                                <td>
                                                                    @if ($snapshot->is_holiday)
                                                                        <span class="lm-badge lm-badge--warning">
                                                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                                                            </svg>
                                                                            OFF
                                                                        </span>
                                                                    @else
                                                                        <span class="sr-time-chip">
                                                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                                                                            </svg>
                                                                            {{ substr($snapshot->start_time ?? '', 0, 5) }}–{{ substr($snapshot->end_time ?? '', 0, 5) }}
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="sr-history-empty">
                                                                    Belum ada riwayat jadwal yang terbentuk untuk kelompok ini.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </details>
                            @empty
                                <div class="lm-empty lm-empty--compact">
                                    <div class="lm-empty-icon">
                                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </div>
                                    <h3 class="lm-empty-title">Belum Ada Kelompok Rotasi</h3>
                                    <p class="lm-empty-desc">{{ $meta['empty_desc'] }}</p>
                                </div>
                            @endforelse

                            <div class="lm-empty lm-empty--compact sr-filter-empty" hidden>
                                <div class="lm-empty-icon">
                                    <svg width="30" height="30" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <h3 class="lm-empty-title">Tidak Ditemukan</h3>
                                <p class="lm-empty-desc">Tidak ada kelompok rotasi {{ strtolower($meta['label']) }} yang cocok dengan pencarian/filter.</p>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            {{-- Stop Rotation Confirmation Modals --}}
            @foreach ($rotations->where('is_active', true) as $activeRotation)
                <x-modal
                    id="stop-rotation-{{ $activeRotation->id }}"
                    title="Hentikan Rotasi Kerja?"
                    type="confirm"
                    variant="danger"
                    confirmLabel="Ya, Hentikan"
                    cancelLabel="Batal"
                    :confirmFormAction="route('hr.shift-rotations.stop', $activeRotation->id)"
                    confirmFormMethod="POST">
                    <p style="margin: 0 0 6px 0;">
                        Yakin ingin menghentikan pergantian shift otomatis untuk kelompok:
                    </p>
                    <p style="margin: 0; font-weight: 700; color: var(--text-primary, #111827);">
                        {{ $activeRotation->name }}
                    </p>
                    <p style="margin: 8px 0 0 0; font-size: 0.8125rem; color: var(--text-muted, #6B7280);">
                        Anggota kelompok akan dilepas dari rotasi aktif, namun seluruh riwayat jadwal tetap tersimpan.
                    </p>
                </x-modal>
            @endforeach

            {{-- Add Rotation Modal --}}
            <div class="rotation-modal" id="rotation-modal" hidden>
                <div class="rotation-modal-card" role="dialog" aria-modal="true" aria-labelledby="rotation-modal-title">
                    {{-- Modal Header --}}
                    <div class="sr-modal-header">
                        <div class="sr-modal-header-left">
                            <div class="sr-modal-icon">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            <div>
                                <h2 id="rotation-modal-title">Tambah Rotasi Mingguan</h2>
                                <p>Pilih aturan pergantian, urutan siklus shift, dan karyawan</p>
                            </div>
                        </div>

                        <button type="button" class="sr-modal-close" id="rotation-close" aria-label="Tutup">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form id="rotation-create-form" method="POST" action="{{ route('hr.shift-rotations.store') }}" class="sr-modal-form">
                        @csrf
                        <input type="hidden" name="frequency" id="rotation-frequency" value="{{ old('frequency', 'weekly') }}">

                        <div class="sr-modal-body">
                            {{-- Info Banner --}}
                            <div class="mlc-banner" role="note">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Kolom bertanda <strong>*</strong> wajib diisi. Seluruh shift dalam satu siklus wajib aktif dan menggunakan zona waktu (WIB/WITA) yang sama.</span>
                            </div>

                            {{-- Frequency Selector Pills inside Modal --}}
                            <div class="sr-freq-pills" role="group" aria-label="Pilih tipe frekuensi rotasi">
                                <button type="button" class="sr-freq-pill" data-modal-freq="monthly">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/>
                                    </svg>
                                    <span>Bulanan</span>
                                </button>
                                <button type="button" class="sr-freq-pill" data-modal-freq="weekly">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Mingguan</span>
                                </button>
                                <button type="button" class="sr-freq-pill" data-modal-freq="days">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 3"/>
                                    </svg>
                                    <span>Interval Hari</span>
                                </button>
                            </div>

                            {{-- Langkah 1: Informasi Kelompok & Jadwal Pergantian --}}
                            <div class="mlc-step">
                                <div class="mlc-step__header">
                                    <span class="mlc-step__num">1</span>
                                    <div class="mlc-step__headtext">
                                        <span class="mlc-step__title">Informasi &amp; Jadwal Pergantian</span>
                                        <span class="mlc-step__sub">Langkah 1 dari 3 · Tentukan nama kelompok, tanggal mulai, dan waktu pergantian</span>
                                    </div>
                                </div>

                                <div class="mlc-field">
                                    <label for="rotation-name-input" class="mlc-label">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Nama Kelompok <span class="mlc-required">*</span>
                                    </label>
                                    <input
                                        id="rotation-name-input"
                                        required
                                        type="text"
                                        name="name"
                                        maxlength="150"
                                        class="mlc-input"
                                        value="{{ old('name') }}"
                                        placeholder="Contoh: Aqua Pandaan, Tim Gudang Shift">
                                </div>

                                <div class="sr-grid-2">
                                    <div class="mlc-field">
                                        <label for="rotation-start-date" class="mlc-label">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/>
                                            </svg>
                                            Tanggal Mulai <span class="mlc-required">*</span>
                                        </label>
                                        <input
                                            id="rotation-start-date"
                                            required
                                            type="date"
                                            name="start_date"
                                            class="mlc-input"
                                            min="{{ now('Asia/Jakarta')->toDateString() }}"
                                            value="{{ old('start_date', now('Asia/Jakarta')->toDateString()) }}">
                                    </div>

                                    <div class="mlc-field" id="rotation-weekly">
                                        <label for="rotation-weekly-select" class="mlc-label">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Hari Pergantian <span class="mlc-required">*</span>
                                        </label>
                                        <select id="rotation-weekly-select" name="weekly_day" class="mlc-select">
                                            @foreach ($dayNames as $day => $dayName)
                                                <option value="{{ $day }}" @selected(old('weekly_day', 1) == $day)>{{ $dayName }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mlc-field" id="rotation-days" hidden>
                                        <label for="rotation-interval-input" class="mlc-label">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 3"/>
                                            </svg>
                                            Ganti Setiap Berapa Hari <span class="mlc-required">*</span>
                                        </label>
                                        <input
                                            id="rotation-interval-input"
                                            type="number"
                                            name="interval_days"
                                            class="mlc-input"
                                            min="1"
                                            max="365"
                                            value="{{ old('interval_days', 3) }}">
                                    </div>

                                    <div class="mlc-field" id="rotation-monthly" hidden>
                                        <span class="mlc-label">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Jadwal Pergantian
                                        </span>
                                        <div class="sr-monthly-note">
                                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/>
                                            </svg>
                                            <span>Pergantian setiap tanggal 1 awal bulan.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Langkah 2: Urutan Siklus Shift --}}
                            <div class="mlc-step">
                                <div class="mlc-step__header">
                                    <span class="mlc-step__num">2</span>
                                    <div class="mlc-step__headtext">
                                        <span class="mlc-step__title">Urutan Siklus Shift</span>
                                        <span class="mlc-step__sub">Langkah 2 dari 3 · Urutkan dari shift pertama ke shift berikutnya (setiap shift dipilih satu kali)</span>
                                    </div>
                                </div>

                                <div id="rotation-shifts" class="sr-shifts-list">
                                    @foreach (old('shift_ids', ['', '']) as $idx => $selected)
                                        <div class="rotation-shift-row">
                                            <span class="sr-order-num" aria-hidden="true">{{ $idx + 1 }}</span>
                                            <div class="sr-select-icon-wrap">
                                                <svg class="sr-select-icon" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                                                </svg>
                                                <select name="shift_ids[]" class="mlc-select sr-shift-select" aria-label="Urutan shift" required>
                                                    <option value="">Pilih shift...</option>
                                                    @foreach ($shifts as $shift)
                                                        @php
                                                            $monday = $shift->days->first();
                                                        @endphp
                                                        <option value="{{ $shift->id }}" @selected($selected == $shift->id)>
                                                            {{ $shift->name }} ({{ $shift->is_wita ? 'WITA' : 'WIB' }})
                                                            — Senin: @if (!$monday) belum diatur @elseif ($monday->is_holiday) libur @elseif ($monday->start_time && $monday->end_time) {{ $monday->start_time_label }}–{{ $monday->end_time_label }} @else jam belum lengkap @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="button" class="rotation-remove" aria-label="Hapus shift" title="Hapus urutan shift">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" id="rotation-add-shift" class="sr-btn-add-shift">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Tambah Urutan Shift</span>
                                </button>
                            </div>

                            {{-- Langkah 3: Karyawan & Shift Awal --}}
                            <div class="mlc-step">
                                <div class="mlc-step__header sr-step-header-between">
                                    <div class="sr-step-header-main">
                                        <span class="mlc-step__num">3</span>
                                        <div class="mlc-step__headtext">
                                            <span class="mlc-step__title">Pilih Karyawan</span>
                                            <span class="mlc-step__sub">Langkah 3 dari 3 · Pilih karyawan; shift awal mengikuti jadwal saat ini</span>
                                        </div>
                                    </div>
                                    <span class="sr-selected-counter" id="rotation-selected-counter">0 dipilih</span>
                                </div>

                                <div class="lm-search-input-wrap sr-modal-search">
                                    <svg class="lm-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input
                                        type="search"
                                        id="rotation-search"
                                        class="lm-search-input"
                                        placeholder="Cari nama karyawan...">
                                </div>

                                <div class="rotation-users">
                                    @forelse ($users as $user)
                                        @php
                                            $existingRotation = $userRotationMap->get($user->id);
                                            $isSelected = old('members.' . $user->id) !== null;
                                        @endphp
                                        <label
                                            class="rotation-user {{ $isSelected ? 'is-selected' : '' }} {{ $existingRotation ? 'is-busy' : '' }}"
                                            data-user-name="{{ mb_strtolower($user->name) }}">
                                            <div class="sr-user-info">
                                                <span class="sr-mini-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                                <div class="sr-user-text">
                                                    <span class="sr-user-name">{{ $user->name }}</span>
                                                    <span class="sr-user-shift">{{ $user->employeeShift?->shift?->name ?? 'Shift belum tersedia' }}</span>
                                                    @if ($existingRotation)
                                                        <span class="sr-user-busy-tag">
                                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Mengikuti: {{ $existingRotation }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <input type="checkbox"
                                                name="members[{{ $user->id }}]"
                                                value="1"
                                                class="sr-member-checkbox"
                                                aria-label="Pilih {{ $user->name }}"
                                                @checked($isSelected)
                                                @disabled($existingRotation)>
                                        </label>
                                    @empty
                                        <div class="sr-users-empty">
                                            Belum ada karyawan aktif yang memiliki jadwal kerja.
                                        </div>
                                    @endforelse

                                    <div class="sr-users-empty" id="rotation-users-no-match" hidden>
                                        Tidak ada nama karyawan yang cocok dengan pencarian.
                                    </div>
                                </div>

                                <p class="mlc-helper sr-helper-with-icon">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Shift pada jadwal karyawan yang dipilih harus ada dalam urutan siklus dan karyawan belum mengikuti rotasi lain.</span>
                                </p>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="sr-modal-footer">
                            <button type="button" id="rotation-cancel" class="apv-action-btn apv-action-btn--secondary">
                                Batal
                            </button>
                            <button type="submit" class="apv-action-btn apv-action-btn--primary">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Rotasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <template id="rotation-shift-template">
                <div class="rotation-shift-row">
                    <span class="sr-order-num" aria-hidden="true">1</span>
                    <div class="sr-select-icon-wrap">
                        <svg class="sr-select-icon" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l3 2"/>
                        </svg>
                        <select name="shift_ids[]" class="mlc-select sr-shift-select" aria-label="Urutan shift" required>
                            <option value="">Pilih shift...</option>
                            @foreach ($shifts as $shift)
                                @php
                                    $monday = $shift->days->first();
                                @endphp
                                <option value="{{ $shift->id }}">
                                    {{ $shift->name }} ({{ $shift->is_wita ? 'WITA' : 'WIB' }})
                                    — Senin: @if (!$monday) belum diatur @elseif ($monday->is_holiday) libur @elseif ($monday->start_time && $monday->end_time) {{ $monday->start_time_label }}–{{ $monday->end_time_label }} @else jam belum lengkap @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="rotation-remove" aria-label="Hapus shift" title="Hapus urutan shift">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </template>
        @endif
    </div>

    @if ($ready)
    <script>
        (() => {
            const modal = document.getElementById('rotation-modal');
            const frequencyInput = document.getElementById('rotation-frequency');
            const weeklyBlock = document.getElementById('rotation-weekly');
            const monthlyBlock = document.getElementById('rotation-monthly');
            const daysBlock = document.getElementById('rotation-days');
            const intervalInput = document.getElementById('rotation-interval-input');
            const modalTitle = document.getElementById('rotation-modal-title');
            const shiftsContainer = document.getElementById('rotation-shifts');
            const shiftTemplate = document.getElementById('rotation-shift-template');
            const memberSearch = document.getElementById('rotation-search');
            const noMatchMessage = document.getElementById('rotation-users-no-match');
            const selectedCounter = document.getElementById('rotation-selected-counter');
            const topAddBtn = document.getElementById('rotation-top-add');

            const frequencyLabels = {
                weekly: 'Mingguan',
                monthly: 'Bulanan',
                days: 'Lainnya',
            };

            function syncShiftRowNumbers() {
                const rows = shiftsContainer.querySelectorAll('.rotation-shift-row');
                rows.forEach((row, index) => {
                    const badge = row.querySelector('.sr-order-num');
                    if (badge) badge.textContent = String(index + 1);
                    const removeBtn = row.querySelector('.rotation-remove');
                    if (removeBtn) {
                        removeBtn.disabled = rows.length <= 2;
                    }
                });
            }

            function syncSelectedMemberCount() {
                let count = 0;
                document.querySelectorAll('.rotation-user').forEach((row) => {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    const selected = Boolean(checkbox && checkbox.checked);
                    row.classList.toggle('is-selected', selected);
                    if (selected) count += 1;
                });
                if (selectedCounter) {
                    selectedCounter.textContent = count + ' dipilih';
                    selectedCounter.classList.toggle('is-active', count > 0);
                }
            }

            function applyFrequency(kind) {
                const normalized = frequencyLabels[kind] ? kind : 'weekly';
                frequencyInput.value = normalized;
                weeklyBlock.hidden = normalized !== 'weekly';
                monthlyBlock.hidden = normalized !== 'monthly';
                daysBlock.hidden = normalized !== 'days';

                if (intervalInput) {
                    intervalInput.disabled = normalized !== 'days';
                    intervalInput.required = normalized === 'days';
                }

                modalTitle.textContent = 'Tambah Rotasi ' + frequencyLabels[normalized];

                document.querySelectorAll('[data-modal-freq]').forEach((pill) => {
                    pill.classList.toggle('active', pill.dataset.modalFreq === normalized);
                });
            }

            function openModal(kind) {
                applyFrequency(kind);
                syncShiftRowNumbers();
                syncSelectedMemberCount();
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.hidden = true;
                document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-add-rotation]').forEach((button) => {
                button.addEventListener('click', () => openModal(button.dataset.addRotation));
            });

            document.querySelectorAll('[data-modal-freq]').forEach((pill) => {
                pill.addEventListener('click', () => applyFrequency(pill.dataset.modalFreq));
            });

            document.getElementById('rotation-close').addEventListener('click', closeModal);
            document.getElementById('rotation-cancel').addEventListener('click', closeModal);

            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeModal();
                }
            });

            document.getElementById('rotation-add-shift').addEventListener('click', () => {
                shiftsContainer.append(shiftTemplate.content.cloneNode(true));
                syncShiftRowNumbers();
            });

            shiftsContainer.addEventListener('click', (event) => {
                const removeBtn = event.target.closest('.rotation-remove');
                if (removeBtn && shiftsContainer.querySelectorAll('.rotation-shift-row').length > 2) {
                    removeBtn.closest('.rotation-shift-row').remove();
                    syncShiftRowNumbers();
                }
            });

            memberSearch.addEventListener('input', (event) => {
                const query = event.target.value.trim().toLowerCase();
                let visibleCount = 0;

                document.querySelectorAll('.rotation-user').forEach((row) => {
                    const matches = !query || row.dataset.userName.includes(query);
                    row.hidden = !matches;
                    if (matches) visibleCount += 1;
                });

                if (noMatchMessage) {
                    noMatchMessage.hidden = visibleCount > 0 || document.querySelectorAll('.rotation-user').length === 0;
                }
            });

            document.querySelectorAll('.rotation-user input[type="checkbox"]').forEach((checkbox) => {
                checkbox.addEventListener('change', syncSelectedMemberCount);
            });

            // Page Tabs, Search & Status Filter
            let activeFreqTab = 'all';
            let appliedStatus = '';
            const pageSearch = document.getElementById('rotation-page-search');
            const statusSelect = document.getElementById('rotation-status-filter');
            const filterToggleBtn = document.getElementById('rotation-filter-toggle');
            const filterPanel = document.getElementById('rotation-filter-panel');
            const filterApplyBtn = document.getElementById('rotation-filter-apply');
            const filterResetBtn = document.getElementById('rotation-filter-reset');
            const filterBadge = document.getElementById('rotation-filter-badge');

            function filterPageRotations() {
                const q = (pageSearch?.value || '').trim().toLowerCase();
                const hasActiveFilter = Boolean(q || appliedStatus);

                if (filterResetBtn) {
                    filterResetBtn.style.display = hasActiveFilter ? 'inline-flex' : 'none';
                }
                if (filterBadge) {
                    filterBadge.style.display = appliedStatus ? 'inline-flex' : 'none';
                }
                if (filterToggleBtn) {
                    filterToggleBtn.classList.toggle('active', Boolean(appliedStatus) || filterPanel?.style.display !== 'none');
                }

                document.querySelectorAll('[data-freq-section]').forEach((section) => {
                    const sectionFreq = section.dataset.freqSection;
                    const tabVisible = activeFreqTab === 'all' || activeFreqTab === sectionFreq;
                    section.hidden = !tabVisible;

                    if (!tabVisible) return;

                    const cards = section.querySelectorAll('[data-rotation-card]');
                    const filterEmpty = section.querySelector('.sr-filter-empty');
                    if (cards.length === 0) {
                        if (filterEmpty) filterEmpty.hidden = true;
                        return;
                    }

                    let shown = 0;
                    cards.forEach((card) => {
                        const statusMatch = !appliedStatus || card.dataset.rotationStatus === appliedStatus;
                        const textMatch = !q || card.dataset.rotationSearch.includes(q);
                        const showCard = statusMatch && textMatch;
                        card.hidden = !showCard;
                        if (showCard) shown += 1;
                    });

                    if (filterEmpty) {
                        filterEmpty.hidden = shown > 0;
                    }
                });
            }

            document.querySelectorAll('[data-freq-tab]').forEach((tabBtn) => {
                tabBtn.addEventListener('click', () => {
                    activeFreqTab = tabBtn.dataset.freqTab;
                    document.querySelectorAll('[data-freq-tab]').forEach((btn) => {
                        const isCurrent = btn === tabBtn;
                        btn.classList.toggle('active', isCurrent);
                        btn.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
                    });
                    if (topAddBtn) {
                        topAddBtn.dataset.addRotation = activeFreqTab === 'all' ? 'weekly' : activeFreqTab;
                    }
                    filterPageRotations();
                });
            });

            pageSearch?.addEventListener('input', filterPageRotations);

            filterToggleBtn?.addEventListener('click', () => {
                const isHidden = filterPanel.style.display === 'none';
                filterPanel.style.display = isHidden ? '' : 'none';
                filterToggleBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                filterToggleBtn.classList.toggle('active', isHidden || Boolean(appliedStatus));
            });

            filterApplyBtn?.addEventListener('click', () => {
                appliedStatus = statusSelect?.value || '';
                filterPageRotations();
            });

            filterResetBtn?.addEventListener('click', () => {
                if (pageSearch) pageSearch.value = '';
                if (statusSelect) statusSelect.value = '';
                appliedStatus = '';
                filterPageRotations();
            });

            syncShiftRowNumbers();
            syncSelectedMemberCount();

            @if ($errors->any())
                openModal(frequencyInput.value);
            @endif
        })();
    </script>
    @endif

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

        .rotation-page {
            max-width: 100%;
            margin: 0 auto;
            padding-bottom: 32px;
        }

        /* ========================================== */
        /* ALERTS (selaras dengan /hr/leave/master)   */
        /* ========================================== */
        .lm-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.5;
        }
        .lm-alert svg { flex-shrink: 0; margin-top: 1px; }
        .lm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #15803d;
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }
        .sr-alert-content strong {
            display: block;
            margin-bottom: 4px;
            font-weight: 700;
        }
        .sr-alert-content ul {
            margin: 0;
            padding-left: 18px;
        }

        /* ========================================== */
        /* TOP BAR: TABS + PRIMARY CTA                */
        /* ========================================== */
        .lm-cta-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .lm-tabs {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px;
            background: var(--white, #FFFFFF);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            max-width: 100%;
            overflow-x: auto;
        }
        .lm-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 13px;
            border: none;
            border-radius: 9px;
            background: transparent;
            color: var(--text-muted, #6B7280);
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .lm-tab-btn:hover {
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
        }
        .lm-tab-btn.active {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #FFFFFF;
            box-shadow: 0 2px 6px rgba(10, 61, 98, 0.2);
        }
        .lm-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 18px;
            padding: 0 6px;
            border-radius: 99px;
            font-size: 0.6875rem;
            font-weight: 700;
            background: var(--gray-100, #F3F4F6);
            color: var(--text-secondary, #374151);
        }
        .lm-tab-btn.active .lm-tab-count {
            background: rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
        }
        .lm-cta-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .lm-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.18);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .lm-btn-primary:hover {
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.28);
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* STATS SUMMARY (selaras dengan .lm-stats)   */
        /* ========================================== */
        .lm-stats {
            display: flex;
            align-items: center;
            background: var(--white, #FFFFFF);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            gap: 16px;
        }
        .lm-stat {
            flex: 1;
            text-align: center;
            min-width: 0;
        }
        .lm-stat-value {
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--text-primary, #111827);
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
        }
        .lm-stat-value--success { color: #16a34a; }
        .lm-stat-value--neutral { color: #6B7280; }
        .lm-stat-value--info { color: var(--primary, #145DA0); }
        .lm-stat-label {
            margin-top: 3px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
        }
        .lm-stat-divider {
            width: 1px;
            height: 34px;
            background: var(--border-light, #E5E7EB);
            flex-shrink: 0;
        }

        /* ========================================== */
        /* SEARCH & FILTER CARD                       */
        /* ========================================== */
        .lm-filter-card {
            background: var(--white, #FFFFFF);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .lm-search-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .lm-search-input-wrap {
            position: relative;
            flex: 1;
            min-width: 220px;
        }
        .lm-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }
        .lm-search-input {
            width: 100%;
            height: 40px;
            padding: 0 14px 0 38px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            background: var(--gray-50, #F9FAFB);
            color: var(--text-primary, #111827);
            font-family: inherit;
            font-size: 0.8125rem;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .lm-search-input:focus {
            background: #FFFFFF;
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .lm-btn-reset,
        .lm-btn-toggle-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 40px;
            padding: 0 14px;
            border-radius: 10px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            background: var(--white, #FFFFFF);
            color: var(--text-secondary, #374151);
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .lm-btn-reset:hover,
        .lm-btn-toggle-filter:hover {
            background: var(--gray-50, #F5F7FA);
            border-color: #D1D5DB;
        }
        .lm-btn-toggle-filter.active {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: rgba(20, 93, 160, 0.06);
        }
        .lm-filter-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 99px;
            background: var(--primary, #145DA0);
            color: #FFFFFF;
            font-size: 0.6875rem;
            font-weight: 700;
        }
        .lm-filter-panel {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border-light, #E5E7EB);
        }
        .lm-filter-grid {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }
        .lm-filter-group {
            flex: 1;
            min-width: 200px;
        }
        .lm-filter-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 6px;
        }
        .lm-filter-input {
            width: 100%;
            height: 40px;
            padding: 0 12px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            background: #FFFFFF;
            color: var(--text-primary, #111827);
            font-family: inherit;
            font-size: 0.8125rem;
            outline: none;
            box-sizing: border-box;
        }
        .lm-filter-input:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .lm-btn-apply {
            height: 40px;
            padding: 0 18px;
            border: none;
            border-radius: 10px;
            background: var(--primary-dark, #0A3D62);
            color: #FFFFFF;
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .lm-btn-apply:hover {
            background: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* FREQUENCY SECTION CARDS                    */
        /* ========================================== */
        .sr-sections-stack {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .lm-table-card {
            background: var(--white, #FFFFFF);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .rotation-section[hidden],
        .rotation-item[hidden],
        .sr-filter-empty[hidden],
        .rotation-modal[hidden],
        .rotation-user[hidden],
        #rotation-users-no-match[hidden] {
            display: none !important;
        }

        .sr-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-light, #F3F4F6);
            background: linear-gradient(180deg, #FFFFFF 0%, #FAFBFC 100%);
        }
        .sr-section-title-group {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .sr-section-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sr-section-icon--monthly {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
        }
        .sr-section-icon--weekly {
            background: rgba(20, 93, 160, 0.1);
            color: var(--primary, #145DA0);
        }
        .sr-section-icon--days {
            background: rgba(245, 158, 11, 0.12);
            color: #B45309;
        }
        .sr-section-heading-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .sr-section-header h2 {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.3;
        }
        .sr-section-header p {
            margin: 2px 0 0;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted, #6B7280);
            line-height: 1.4;
        }
        .sr-count-chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 99px;
            background: var(--gray-100, #F3F4F6);
            color: var(--text-secondary, #374151);
            font-size: 0.6875rem;
            font-weight: 700;
        }
        .sr-btn-add {
            padding: 8px 14px;
            font-size: 0.75rem;
            border-radius: 9px;
            flex-shrink: 0;
        }

        .sr-section-body {
            padding: 16px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ========================================== */
        /* ROTATION ACCORDION ITEMS                   */
        /* ========================================== */
        .rotation-item {
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
            background: var(--white, #FFFFFF);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            overflow: hidden;
        }
        .rotation-item:hover {
            border-color: rgba(20, 93, 160, 0.35);
        }
        .rotation-item[open] {
            border-color: rgba(20, 93, 160, 0.45);
            box-shadow: 0 3px 12px rgba(10, 61, 98, 0.06);
        }
        .sr-item-summary {
            list-style: none;
            cursor: pointer;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            user-select: none;
        }
        .sr-item-summary::-webkit-details-marker { display: none; }
        .sr-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }
        .lm-employee-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .lm-employee-avatar--muted {
            background: var(--gray-200, #E5E7EB);
            color: var(--text-muted, #6B7280);
        }
        .sr-item-title-wrap {
            min-width: 0;
            flex: 1;
        }
        .sr-item-name {
            display: block;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.3;
            margin-bottom: 4px;
        }
        .sr-item-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            color: var(--text-muted, #6B7280);
            font-size: 0.75rem;
            font-weight: 500;
        }
        .sr-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .sr-meta-dot {
            color: #D1D5DB;
        }
        .sr-item-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .sr-chevron {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--gray-50, #F5F7FA);
            color: var(--text-muted, #6B7280);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, background 0.15s ease, color 0.15s ease;
        }
        .rotation-item[open] .sr-chevron {
            transform: rotate(180deg);
            background: rgba(20, 93, 160, 0.1);
            color: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* BADGES & ACTION CHIPS                      */
        /* ========================================== */
        .lm-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.6875rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .lm-badge--success {
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.22);
        }
        .lm-badge--neutral {
            background: var(--gray-100, #F3F4F6);
            color: var(--text-muted, #6B7280);
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .lm-badge--warning {
            background: rgba(245, 158, 11, 0.12);
            color: #B45309;
            border: 1px solid rgba(245, 158, 11, 0.24);
        }
        .lm-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .lm-type--izin {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary-dark, #0A3D62);
        }
        .lm-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }
        .lm-btn-action--delete {
            background: #FEF2F2;
            color: #DC2626;
            border-color: #FECACA;
        }
        .lm-btn-action--delete:hover {
            background: #FEE2E2;
            border-color: #FCA5A5;
        }

        /* ========================================== */
        /* ROTATION DETAIL CONTENT                    */
        /* ========================================== */
        .rotation-detail {
            padding: 16px;
            border-top: 1px solid var(--border-light, #F3F4F6);
            background: #FAFBFC;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .sr-detail-block {
            background: #FFFFFF;
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .sr-detail-block-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .sr-detail-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .sr-detail-label svg {
            color: var(--primary, #145DA0);
        }
        .sr-detail-sub {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
        }

        .sr-shift-chain {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .sr-shift-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 11px 6px 7px;
            border-radius: 9px;
            background: rgba(10, 61, 98, 0.06);
            border: 1px solid rgba(20, 93, 160, 0.16);
            color: var(--primary-dark, #0A3D62);
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .sr-shift-pill-num {
            width: 19px;
            height: 19px;
            border-radius: 50%;
            background: var(--primary-dark, #0A3D62);
            color: #FFFFFF;
            font-size: 0.6875rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sr-shift-arrow {
            color: var(--text-light, #9CA3AF);
            flex-shrink: 0;
        }

        .sr-member-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 8px;
        }
        .sr-member-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 8px 10px;
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            background: var(--gray-50, #F9FAFB);
        }
        .sr-member-person {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .sr-mini-avatar {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: rgba(20, 93, 160, 0.12);
            color: var(--primary-dark, #0A3D62);
            font-size: 0.6875rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sr-member-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sr-inline-muted {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* HISTORY TABLE                              */
        /* ========================================== */
        .rotation-history {
            max-height: 280px;
            overflow: auto;
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
        }
        .sr-history-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }
        .sr-history-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: var(--gray-50, #F5F7FA);
            padding: 10px 12px;
            text-align: left;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
        }
        .sr-history-table td {
            padding: 9px 12px;
            font-size: 0.8125rem;
            color: var(--text-primary, #111827);
            border-bottom: 1px solid var(--border-light, #F3F4F6);
            vertical-align: middle;
        }
        .sr-history-table tbody tr:last-child td { border-bottom: none; }
        .sr-history-table tbody tr:hover td { background: var(--gray-50, #F9FAFB); }
        .sr-date-inline {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--text-secondary, #374151);
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }
        .sr-date-inline svg { color: var(--text-light, #9CA3AF); }
        .sr-history-user {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        .sr-history-shift {
            font-weight: 600;
            color: var(--primary-dark, #0A3D62);
        }
        .sr-time-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 6px;
            background: var(--gray-100, #F3F4F6);
            color: var(--text-secondary, #374151);
            font-size: 0.75rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }
        .sr-history-empty {
            text-align: center;
            padding: 20px 12px !important;
            color: var(--text-muted, #6B7280) !important;
            font-size: 0.8125rem;
        }

        /* ========================================== */
        /* EMPTY STATES                               */
        /* ========================================== */
        .lm-empty {
            padding: 40px 20px;
            text-align: center;
        }
        .lm-empty--compact {
            padding: 26px 16px;
        }
        .lm-empty-icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 10px;
            border-radius: 14px;
            background: rgba(20, 93, 160, 0.07);
            color: var(--primary, #145DA0);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lm-empty-icon--warning {
            background: #FEF3C7;
            color: #D97706;
        }
        .lm-empty-title {
            margin: 0 0 4px;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
        }
        .lm-empty-desc {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* MODAL TAMBAH ROTASI (#rotation-modal)      */
        /* ========================================== */
        @keyframes srModalPop {
            0% { opacity: 0; transform: scale(0.97) translateY(8px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        .rotation-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            padding-bottom: calc(16px + env(safe-area-inset-bottom));
        }
        .rotation-modal-card {
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.16), 0 2px 6px rgba(15, 23, 42, 0.06);
            width: min(720px, 100%);
            max-height: calc(100vh - 32px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: srModalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .sr-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            background: #FFFFFF;
            flex-shrink: 0;
        }
        .sr-modal-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .sr-modal-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sr-modal-header h2 {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #111827);
            line-height: 1.25;
        }
        .sr-modal-header p {
            margin: 2px 0 0;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted, #6B7280);
            line-height: 1.35;
        }
        .sr-modal-close {
            width: 34px;
            height: 34px;
            border: 1px solid transparent;
            border-radius: 9px;
            background: transparent;
            color: var(--text-light, #9CA3AF);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .sr-modal-close:hover {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-primary, #111827);
            border-color: var(--border-light, #E5E7EB);
        }

        .sr-modal-form {
            display: flex;
            flex-direction: column;
            min-height: 0;
            flex: 1;
        }
        .sr-modal-body {
            padding: 18px 20px;
            overflow-y: auto;
            background: #F8FAFC;
            flex: 1;
        }

        /* Banner & Step Cards inside Modal (selaras dengan .mlc-*) */
        .mlc-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            background: #EFF6FF;
            border: 1px solid #DBEAFE;
            border-radius: 10px;
            margin-bottom: 12px;
            font-size: 0.78125rem;
            color: #1E40AF;
            line-height: 1.45;
        }
        .mlc-banner svg { flex-shrink: 0; margin-top: 1px; }

        .sr-freq-pills {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        .sr-freq-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 10px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            background: #FFFFFF;
            color: var(--text-secondary, #374151);
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .sr-freq-pill:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
        }
        .sr-freq-pill.active {
            border-color: var(--primary, #145DA0);
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary-dark, #0A3D62);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.08);
        }

        .mlc-step {
            background: #FFFFFF;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }
        .mlc-step:last-child { margin-bottom: 0; }
        .mlc-step__header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }
        .sr-step-header-between {
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .sr-step-header-main {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .mlc-step__num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary-dark, #0A3D62);
            color: #FFFFFF;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .mlc-step__headtext {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }
        .mlc-step__title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.3;
        }
        .mlc-step__sub {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted, #6B7280);
            line-height: 1.35;
        }

        .mlc-field { margin-bottom: 12px; }
        .mlc-field:last-child { margin-bottom: 0; }
        .mlc-field[hidden] { display: none !important; }
        .sr-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .sr-grid-2 .mlc-field { margin-bottom: 0; }

        .mlc-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78125rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 6px;
        }
        .mlc-label svg { color: var(--primary, #145DA0); flex-shrink: 0; }
        .mlc-required { color: #EF4444; font-weight: 700; }

        .mlc-input,
        .mlc-select {
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.875rem;
            color: var(--text-primary, #111827);
            background: #FFFFFF;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .mlc-input:focus,
        .mlc-select:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .sr-monthly-note {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 42px;
            padding: 9px 12px;
            border-radius: 10px;
            background: rgba(20, 93, 160, 0.06);
            border: 1px solid rgba(20, 93, 160, 0.18);
            color: var(--primary-dark, #0A3D62);
            font-size: 0.8125rem;
            font-weight: 600;
            box-sizing: border-box;
        }
        .sr-monthly-note svg { flex-shrink: 0; }

        /* Shift Order Rows inside Modal */
        .sr-shifts-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 10px;
        }
        .rotation-shift-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sr-order-num {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sr-select-icon-wrap {
            position: relative;
            flex: 1;
            min-width: 0;
        }
        .sr-select-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }
        .sr-shift-select {
            padding-left: 34px;
        }
        .rotation-remove {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #FECACA;
            background: #FEF2F2;
            color: #DC2626;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .rotation-remove:hover:not(:disabled) {
            background: #FEE2E2;
            border-color: #FCA5A5;
        }
        .rotation-remove:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: var(--gray-50, #F9FAFB);
            border-color: var(--border-light, #E5E7EB);
            color: var(--text-light, #9CA3AF);
        }
        .sr-btn-add-shift {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 9px 14px;
            border-radius: 10px;
            border: 1.5px dashed rgba(20, 93, 160, 0.35);
            background: rgba(20, 93, 160, 0.03);
            color: var(--primary, #145DA0);
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .sr-btn-add-shift:hover {
            background: rgba(20, 93, 160, 0.08);
            border-color: var(--primary, #145DA0);
        }

        /* Employee Picker inside Modal */
        .sr-selected-counter {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 99px;
            background: var(--gray-100, #F3F4F6);
            color: var(--text-muted, #6B7280);
            font-size: 0.75rem;
            font-weight: 700;
        }
        .sr-selected-counter.is-active {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }
        .sr-modal-search {
            margin-bottom: 10px;
        }
        .rotation-users {
            max-height: 250px;
            overflow-y: auto;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
            background: #FFFFFF;
        }
        .rotation-user {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 9px 12px;
            border-bottom: 1px solid var(--border-light, #F3F4F6);
            transition: background 0.15s ease;
        }
        .rotation-user:last-child { border-bottom: none; }
        .rotation-user:hover { background: var(--gray-50, #F9FAFB); }
        .rotation-user.is-selected {
            background: rgba(20, 93, 160, 0.05);
        }
        .sr-user-info {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            flex: 1;
        }
        .sr-user-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .sr-user-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sr-user-busy-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.6875rem;
            font-weight: 600;
            color: #B45309;
        }
        .sr-user-shift { font-size: 0.6875rem; color: var(--text-muted, #6B7280); }
        .sr-member-checkbox { width: 18px; height: 18px; flex-shrink: 0; accent-color: var(--primary, #145DA0); }
        .sr-users-empty {
            padding: 20px 14px;
            text-align: center;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
        }
        .mlc-helper {
            margin: 8px 0 0;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted, #6B7280);
            line-height: 1.45;
        }
        .sr-helper-with-icon {
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }
        .sr-helper-with-icon svg { flex-shrink: 0; margin-top: 2px; }

        /* Modal Footer (selaras dengan .apv-action-btn) */
        .sr-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 20px;
            border-top: 1px solid var(--border-light, #E5E7EB);
            background: #FFFFFF;
            flex-shrink: 0;
        }
        .apv-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 42px;
            padding: 10px 20px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }
        .apv-action-btn--secondary {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #6B7280);
            border: 1.5px solid var(--border-light, #E5E7EB);
        }
        .apv-action-btn--secondary:hover {
            background: var(--gray-100, #E5E7EB);
        }
        .apv-action-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #FFFFFF;
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .apv-action-btn--primary:hover {
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.3);
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (max-width: 767px) {
            .lm-stats {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                padding: 14px;
            }
            .lm-stat-divider { display: none; }
            .sr-grid-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 639px) {
            .lm-cta-bar { flex-direction: column; align-items: stretch; }
            .lm-cta-actions .lm-btn-primary { width: 100%; }
            .sr-section-header { flex-direction: column; align-items: flex-start; padding: 14px 16px; }
            .sr-btn-add { width: 100%; }
            .sr-section-body { padding: 12px 14px; }
            .sr-item-summary { flex-direction: column; align-items: flex-start; }
            .sr-item-right { width: 100%; justify-content: space-between; padding-left: 50px; }
            .sr-freq-pills { grid-template-columns: 1fr; }
            .sr-modal-footer { flex-direction: column-reverse; }
            .sr-modal-footer .apv-action-btn { width: 100%; }
        }
    </style>
</x-app>
