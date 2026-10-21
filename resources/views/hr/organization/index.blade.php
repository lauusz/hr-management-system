<x-app title="Divisi & Jabatan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm14 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Divisi &amp; Jabatan</h1>
                <p class="section-subtitle">Kelola struktur organisasi: data divisi dan jabatan karyawan</p>
            </div>
        </div>
    </x-slot>

    @php
        $resolvedTab = strtolower((string) ($activeTab ?? request('tab', 'divisi')));
        $isJabatanTab = in_array($resolvedTab, ['positions', 'jabatan'], true);

        $totalDivisions = $divisions->count();
        $divisionsWithSupervisor = $divisions->whereNotNull('supervisor_id')->count();
        $divisionsWithoutSupervisor = $totalDivisions - $divisionsWithSupervisor;

        $totalPositions = $positions->count();
        $positionsWithDivision = $positions->whereNotNull('division_id')->count();
        $positionsWithoutDivision = $totalPositions - $positionsWithDivision;
    @endphp

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="lm-alert lm-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="lm-alert lm-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Top Bar: Dual-Layout Switcher + Context CTA --}}
    <div class="lm-cta-bar lm-cta-bar--split">
        <div class="lm-tabs org-tabs" role="tablist" aria-label="Pilih tampilan struktur organisasi">
            <button
                type="button"
                class="lm-tab-btn org-tab-btn {{ !$isJabatanTab ? 'active' : '' }}"
                data-tab="divisi"
                role="tab"
                aria-selected="{{ !$isJabatanTab ? 'true' : 'false' }}"
                aria-controls="tab-divisi">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
                <span>Divisi</span>
                <span class="lm-tab-count org-tab-count">{{ $totalDivisions }}</span>
            </button>

            <button
                type="button"
                class="lm-tab-btn org-tab-btn {{ $isJabatanTab ? 'active' : '' }}"
                data-tab="jabatan"
                role="tab"
                aria-selected="{{ $isJabatanTab ? 'true' : 'false' }}"
                aria-controls="tab-jabatan">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                </svg>
                <span>Jabatan</span>
                <span class="lm-tab-count org-tab-count">{{ $totalPositions }}</span>
            </button>
        </div>

        <div class="lm-cta-actions">
            <a
                href="{{ route('hr.divisions.create') }}"
                class="lm-btn-primary"
                data-org-cta="divisi"
                style="{{ $isJabatanTab ? 'display: none;' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Divisi
            </a>

            <a
                href="{{ route('hr.positions.create') }}"
                class="lm-btn-primary"
                data-org-cta="jabatan"
                style="{{ !$isJabatanTab ? 'display: none;' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Jabatan
            </a>
        </div>
    </div>

    {{-- ====================================================== --}}
    {{-- LAYOUT 1: TAB DIVISI                                   --}}
    {{-- ====================================================== --}}
    <div id="tab-divisi" class="lm-tab-panel org-tab-content {{ !$isJabatanTab ? 'active' : '' }}" role="tabpanel">

        {{-- Stats Summary: Divisi --}}
        <div class="lm-stats" data-org-stats="tab-divisi">
            <div class="lm-stat">
                <div class="lm-stat-value">{{ $totalDivisions }}</div>
                <div class="lm-stat-label">Total Divisi</div>
            </div>
            <div class="lm-stat-divider"></div>
            <div class="lm-stat">
                <div class="lm-stat-value lm-stat-value--success">{{ $divisionsWithSupervisor }}</div>
                <div class="lm-stat-label">Memiliki Supervisor</div>
            </div>
            <div class="lm-stat-divider"></div>
            <div class="lm-stat">
                <div class="lm-stat-value lm-stat-value--neutral">{{ $divisionsWithoutSupervisor }}</div>
                <div class="lm-stat-label">Tanpa Supervisor</div>
            </div>
        </div>

        {{-- Filter & Search Card: Divisi --}}
        <div class="lm-filter-card">
            <div class="lm-search-row">
                <div class="lm-search-input-wrap">
                    <svg class="lm-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="search"
                        class="lm-search-input"
                        placeholder="Cari nama divisi atau nama supervisor..."
                        autocomplete="off"
                        aria-label="Cari divisi"
                        data-org-search="tab-divisi">
                </div>

                <button
                    type="button"
                    class="lm-btn-reset"
                    data-org-reset="tab-divisi"
                    style="display: none;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset
                </button>

                <button
                    type="button"
                    class="lm-btn-toggle-filter"
                    data-org-filter-toggle="tab-divisi">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    <span class="lm-filter-badge" data-org-filter-badge="tab-divisi" style="display: none;">1</span>
                </button>
            </div>

            <div class="lm-filter-panel" data-org-filter-panel="tab-divisi" style="display: none;">
                <div class="lm-filter-grid">
                    <div class="lm-filter-group">
                        <label class="lm-filter-label" for="org_supervisor_status">Status Supervisor</label>
                        <select id="org_supervisor_status" class="lm-filter-input" data-org-filter-select="tab-divisi">
                            <option value="">Semua Divisi</option>
                            <option value="with_supervisor">Memiliki Supervisor</option>
                            <option value="without_supervisor">Tanpa Supervisor</option>
                        </select>
                    </div>

                    <div class="lm-filter-actions">
                        <button type="button" class="lm-btn-apply" data-org-filter-apply="tab-divisi">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card: Divisi --}}
        <div class="lm-table-card" aria-live="polite">
            @if($divisions->isEmpty())
                <div class="lm-empty">
                    <div class="lm-empty-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                    </div>
                    <h3 class="lm-empty-title">Belum Ada Divisi</h3>
                    <p class="lm-empty-desc">Belum ada data divisi. Tambahkan divisi pertama melalui tombol "Tambah Divisi" di atas.</p>
                </div>
            @else
                <div class="lm-table-wrap">
                    <table class="lm-data-table">
                        <thead>
                            <tr>
                                <th style="width: 56px;">#</th>
                                <th>Nama Divisi</th>
                                <th>Supervisor</th>
                                <th class="lm-actions-cell" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($divisions as $i => $division)
                                <tr data-filter-value="{{ $division->supervisor ? 'with_supervisor' : 'without_supervisor' }}">
                                    <td class="lm-cell-center lm-cell-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="lm-employee">
                                            <div class="lm-employee-avatar">{{ strtoupper(substr($division->name, 0, 1)) }}</div>
                                            <div class="lm-employee-info">
                                                <span class="lm-employee-name">{{ $division->name }}</span>
                                                <div class="lm-employee-detail">Divisi Organisasi</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($division->supervisor)
                                            <span class="lm-badge lm-badge--info">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                {{ $division->supervisor->name }}
                                            </span>
                                        @else
                                            <span class="lm-badge lm-badge--neutral">Belum Ditentukan</span>
                                        @endif
                                    </td>
                                    <td class="lm-actions-cell">
                                        <div class="lm-action-buttons">
                                            <a href="{{ route('hr.divisions.edit', $division->id) }}" class="lm-btn-action lm-btn-action--edit" title="Edit">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </a>
                                            <button
                                                type="button"
                                                class="lm-btn-action lm-btn-action--delete"
                                                title="Hapus"
                                                data-modal-target="modal-delete-division-{{ $division->id }}"
                                                data-modal-open="modal-delete-division-{{ $division->id }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="lm-empty" data-org-no-result="tab-divisi" style="display: none;">
                        <div class="lm-empty-icon">
                            <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="lm-empty-title">Divisi Tidak Ditemukan</h3>
                        <p class="lm-empty-desc">Data divisi tidak ditemukan untuk kata kunci atau filter tersebut.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ====================================================== --}}
    {{-- LAYOUT 2: TAB JABATAN                                  --}}
    {{-- ====================================================== --}}
    <div id="tab-jabatan" class="lm-tab-panel org-tab-content {{ $isJabatanTab ? 'active' : '' }}" role="tabpanel">

        {{-- Stats Summary: Jabatan --}}
        <div class="lm-stats" data-org-stats="tab-jabatan">
            <div class="lm-stat">
                <div class="lm-stat-value">{{ $totalPositions }}</div>
                <div class="lm-stat-label">Total Jabatan</div>
            </div>
            <div class="lm-stat-divider"></div>
            <div class="lm-stat">
                <div class="lm-stat-value lm-stat-value--success">{{ $positionsWithDivision }}</div>
                <div class="lm-stat-label">Terhubung Divisi</div>
            </div>
            <div class="lm-stat-divider"></div>
            <div class="lm-stat">
                <div class="lm-stat-value lm-stat-value--neutral">{{ $positionsWithoutDivision }}</div>
                <div class="lm-stat-label">Tanpa Divisi</div>
            </div>
        </div>

        {{-- Filter & Search Card: Jabatan --}}
        <div class="lm-filter-card">
            <div class="lm-search-row">
                <div class="lm-search-input-wrap">
                    <svg class="lm-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="search"
                        class="lm-search-input"
                        placeholder="Cari nama jabatan atau divisi..."
                        autocomplete="off"
                        aria-label="Cari jabatan"
                        data-org-search="tab-jabatan">
                </div>

                <button
                    type="button"
                    class="lm-btn-reset"
                    data-org-reset="tab-jabatan"
                    style="display: none;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset
                </button>

                <button
                    type="button"
                    class="lm-btn-toggle-filter"
                    data-org-filter-toggle="tab-jabatan">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    <span class="lm-filter-badge" data-org-filter-badge="tab-jabatan" style="display: none;">1</span>
                </button>
            </div>

            <div class="lm-filter-panel" data-org-filter-panel="tab-jabatan" style="display: none;">
                <div class="lm-filter-grid">
                    <div class="lm-filter-group">
                        <label class="lm-filter-label" for="org_position_division">Divisi</label>
                        <select id="org_position_division" class="lm-filter-input" data-org-filter-select="tab-jabatan">
                            <option value="">Semua Divisi</option>
                            <option value="none">Tanpa Divisi</option>
                            @foreach($divisions as $divisionOption)
                                <option value="div-{{ $divisionOption->id }}">{{ $divisionOption->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lm-filter-actions">
                        <button type="button" class="lm-btn-apply" data-org-filter-apply="tab-jabatan">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card: Jabatan --}}
        <div class="lm-table-card" aria-live="polite">
            @if($positions->isEmpty())
                <div class="lm-empty">
                    <div class="lm-empty-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                        </svg>
                    </div>
                    <h3 class="lm-empty-title">Belum Ada Jabatan</h3>
                    <p class="lm-empty-desc">Belum ada data jabatan. Tambahkan jabatan pertama melalui tombol "Tambah Jabatan" di atas.</p>
                </div>
            @else
                <div class="lm-table-wrap">
                    <table class="lm-data-table">
                        <thead>
                            <tr>
                                <th style="width: 56px;">#</th>
                                <th>Nama Jabatan</th>
                                <th>Divisi</th>
                                <th class="lm-actions-cell" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($positions as $i => $position)
                                <tr data-filter-value="{{ $position->division_id ? 'div-' . $position->division_id : 'none' }}">
                                    <td class="lm-cell-center lm-cell-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="lm-employee">
                                            <div class="lm-employee-avatar lm-employee-avatar--purple">{{ strtoupper(substr($position->name, 0, 1)) }}</div>
                                            <div class="lm-employee-info">
                                                <span class="lm-employee-name">{{ $position->name }}</span>
                                                <div class="lm-employee-detail">
                                                    {{ $position->division ? $position->division->name : 'Lintas Divisi / Umum' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($position->division)
                                            <span class="lm-badge lm-badge--success">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                                </svg>
                                                {{ $position->division->name }}
                                            </span>
                                        @else
                                            <span class="lm-badge lm-badge--neutral">Tanpa Divisi</span>
                                        @endif
                                    </td>
                                    <td class="lm-actions-cell">
                                        <div class="lm-action-buttons">
                                            <a href="{{ route('hr.positions.edit', $position->id) }}" class="lm-btn-action lm-btn-action--edit" title="Edit">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </a>
                                            <button
                                                type="button"
                                                class="lm-btn-action lm-btn-action--delete"
                                                title="Hapus"
                                                data-modal-target="modal-delete-position-{{ $position->id }}"
                                                data-modal-open="modal-delete-position-{{ $position->id }}">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="lm-empty" data-org-no-result="tab-jabatan" style="display: none;">
                        <div class="lm-empty-icon">
                            <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="lm-empty-title">Jabatan Tidak Ditemukan</h3>
                        <p class="lm-empty-desc">Data jabatan tidak ditemukan untuk kata kunci atau filter tersebut.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Modals for Divisions --}}
    @foreach($divisions as $division)
        <x-modal
            :id="'modal-delete-division-' . $division->id"
            title="Hapus Divisi?"
            variant="danger"
            type="confirm"
            confirmLabel="Hapus"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.divisions.destroy', $division->id)"
            confirmFormMethod="DELETE"
        >
            <p style="margin:0 0 4px 0;">Apakah Anda yakin ingin menghapus divisi berikut?</p>
            <p style="margin:0; font-weight:700; color:var(--text-primary);">{{ $division->name }}</p>
            <p style="color:var(--text-muted); font-size:0.85rem; margin:8px 0 0 0;">Tindakan ini tidak dapat dibatalkan.</p>
        </x-modal>
    @endforeach

    {{-- Delete Modals for Positions --}}
    @foreach($positions as $position)
        <x-modal
            :id="'modal-delete-position-' . $position->id"
            title="Hapus Jabatan?"
            variant="danger"
            type="confirm"
            confirmLabel="Hapus"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.positions.destroy', $position->id)"
            confirmFormMethod="DELETE"
        >
            <p style="margin:0 0 4px 0;">Apakah Anda yakin ingin menghapus jabatan berikut?</p>
            <p style="margin:0; font-weight:700; color:var(--text-primary);">{{ $position->name }}</p>
            <p style="color:var(--text-muted); font-size:0.85rem; margin:8px 0 0 0;">Tindakan ini tidak dapat dibatalkan.</p>
        </x-modal>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.org-tab-btn');
            const contents = document.querySelectorAll('.org-tab-content');
            const ctaButtons = document.querySelectorAll('[data-org-cta]');

            function activateTab(tabName, updateHistory = true) {
                const normalized = (tabName === 'positions' || tabName === 'jabatan') ? 'jabatan' : 'divisi';

                tabs.forEach(t => {
                    const isActive = t.dataset.tab === normalized;
                    t.classList.toggle('active', isActive);
                    t.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                contents.forEach(c => {
                    c.classList.toggle('active', c.id === 'tab-' + normalized);
                });

                ctaButtons.forEach(btn => {
                    btn.style.display = btn.getAttribute('data-org-cta') === normalized ? '' : 'none';
                });

                if (updateHistory && window.history && typeof window.history.replaceState === 'function') {
                    const url = new URL(window.location.href);
                    if (normalized === 'jabatan') {
                        url.searchParams.set('tab', 'positions');
                    } else {
                        url.searchParams.delete('tab');
                    }
                    window.history.replaceState({}, '', url.toString());
                }
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    activateTab(tab.dataset.tab, true);
                });
            });

            function applyTabFilters(tabId) {
                const tab = document.getElementById(tabId);
                if (!tab) return;

                const searchInput = document.querySelector('[data-org-search="' + tabId + '"]');
                const filterSelect = document.querySelector('[data-org-filter-select="' + tabId + '"]');
                const resetBtn = document.querySelector('[data-org-reset="' + tabId + '"]');
                const toggleBtn = document.querySelector('[data-org-filter-toggle="' + tabId + '"]');
                const badge = document.querySelector('[data-org-filter-badge="' + tabId + '"]');

                const keyword = searchInput ? searchInput.value.trim().toLowerCase() : '';
                const filterVal = filterSelect ? filterSelect.value : '';

                const rows = tab.querySelectorAll('tbody tr');
                let visible = 0;

                rows.forEach(row => {
                    const textMatch = keyword === '' || row.textContent.toLowerCase().indexOf(keyword) !== -1;
                    const rowFilterVal = row.getAttribute('data-filter-value') || '';
                    const selectMatch = filterVal === '' || rowFilterVal === filterVal;

                    const isMatch = textMatch && selectMatch;
                    row.style.display = isMatch ? '' : 'none';
                    if (isMatch) visible++;
                });

                const noResult = tab.querySelector('[data-org-no-result="' + tabId + '"]');
                if (noResult) {
                    noResult.style.display = (visible === 0 && (keyword !== '' || filterVal !== '')) ? '' : 'none';
                }

                const hasFilter = filterVal !== '';
                if (resetBtn) {
                    resetBtn.style.display = (keyword !== '' || hasFilter) ? '' : 'none';
                }
                if (toggleBtn) {
                    toggleBtn.classList.toggle('active', hasFilter);
                }
                if (badge) {
                    badge.style.display = hasFilter ? '' : 'none';
                }
            }

            document.querySelectorAll('[data-org-search]').forEach(input => {
                input.addEventListener('input', () => {
                    applyTabFilters(input.getAttribute('data-org-search'));
                });
            });

            document.querySelectorAll('[data-org-filter-select]').forEach(select => {
                select.addEventListener('change', () => {
                    applyTabFilters(select.getAttribute('data-org-filter-select'));
                });
            });

            document.querySelectorAll('[data-org-filter-apply]').forEach(btn => {
                btn.addEventListener('click', () => {
                    applyTabFilters(btn.getAttribute('data-org-filter-apply'));
                });
            });

            document.querySelectorAll('[data-org-filter-toggle]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const tabId = btn.getAttribute('data-org-filter-toggle');
                    const panel = document.querySelector('[data-org-filter-panel="' + tabId + '"]');
                    if (!panel) return;
                    const isHidden = panel.style.display === 'none';
                    panel.style.display = isHidden ? '' : 'none';
                    btn.classList.toggle('active', isHidden);
                });
            });

            document.querySelectorAll('[data-org-reset]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const tabId = btn.getAttribute('data-org-reset');
                    const searchInput = document.querySelector('[data-org-search="' + tabId + '"]');
                    const filterSelect = document.querySelector('[data-org-filter-select="' + tabId + '"]');
                    if (searchInput) searchInput.value = '';
                    if (filterSelect) filterSelect.value = '';
                    applyTabFilters(tabId);
                });
            });

            // Delegated modal handlers (supports data-modal-target and data-modal-open)
            document.addEventListener('click', function(e) {
                const openTrigger = e.target.closest('[data-modal-target], [data-modal-open]');
                if (openTrigger) {
                    const id = openTrigger.getAttribute('data-modal-target') || openTrigger.getAttribute('data-modal-open');
                    const modal = id ? document.getElementById(id) : null;
                    if (modal) modal.style.display = 'flex';
                    return;
                }

                const closeTrigger = e.target.closest('[data-modal-close="true"]');
                if (closeTrigger) {
                    const modal = closeTrigger.closest('.modal-backdrop');
                    if (modal) modal.style.display = 'none';
                    return;
                }

                if (e.target.classList && e.target.classList.contains('modal-backdrop')) {
                    e.target.style.display = 'none';
                }
            });
        });
    </script>

    <style>
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
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

        /* ========================================== */
        /* FLASH MESSAGES (selaras dengan /hr/leave/master) */
        /* ========================================== */
        .lm-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .lm-alert svg { flex-shrink: 0; }
        .lm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #15803d;
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #b91c1c;
        }

        /* ========================================== */
        /* CTA BAR + DUAL LAYOUT SWITCHER             */
        /* ========================================== */
        .lm-cta-bar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .lm-cta-bar--split {
            justify-content: space-between;
            align-items: center;
        }
        .lm-cta-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .lm-tabs {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .lm-tab-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 16px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .lm-tab-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
        .lm-tab-btn:hover:not(.active) {
            background: var(--gray-50);
            color: var(--primary);
        }
        .lm-tab-btn.active {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .lm-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 20px;
            padding: 0 7px;
            border-radius: 9999px;
            background: var(--gray-100);
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
        }
        .lm-tab-btn.active .lm-tab-count {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

        .lm-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.25);
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }
        .lm-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.35);
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* TAB PANELS & TRANSITION                    */
        /* ========================================== */
        .lm-tab-panel {
            display: none;
        }
        .lm-tab-panel.active {
            display: block;
            animation: lmTabFadeIn 0.2s ease-out;
        }
        @keyframes lmTabFadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ========================================== */
        /* STATS SUMMARY                              */
        /* ========================================== */
        .lm-stats {
            display: flex;
            align-items: center;
            background: var(--white);
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .lm-stat {
            flex: 1;
            text-align: center;
        }
        .lm-stat-value {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary-dark);
            line-height: 1.2;
        }
        .lm-stat-value--success { color: #16a34a; }
        .lm-stat-value--neutral { color: var(--text-muted); }
        .lm-stat-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }
        .lm-stat-divider {
            width: 1px;
            height: 32px;
            background: var(--border-light);
            flex-shrink: 0;
        }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .lm-filter-card {
            background: var(--white);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .lm-search-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .lm-search-input-wrap {
            position: relative;
            flex: 1;
        }
        .lm-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }
        .lm-search-input {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1.5px solid var(--border-light);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--gray-50);
            transition: all 0.2s ease;
            font-family: inherit;
            outline: none;
        }
        .lm-search-input::placeholder { color: var(--text-light); }
        .lm-search-input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }

        .lm-btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 11px 14px;
            border-radius: 12px;
            border: 1.5px solid rgba(239, 68, 68, 0.25);
            background: rgba(239, 68, 68, 0.06);
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
            font-family: inherit;
        }
        .lm-btn-reset:hover {
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.4);
        }

        .lm-btn-toggle-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 11px 16px;
            border-radius: 12px;
            border: 1.5px solid var(--border-light);
            background: var(--gray-50);
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            font-family: inherit;
            position: relative;
        }
        .lm-btn-toggle-filter:hover,
        .lm-btn-toggle-filter.active {
            border-color: var(--primary);
            color: var(--primary);
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
            background: var(--primary);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
        }

        .lm-filter-panel {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border-light);
        }
        .lm-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .lm-filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .lm-filter-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .lm-filter-input {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--border-light);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-primary);
            background: var(--gray-50);
            transition: all 0.2s ease;
            font-family: inherit;
            outline: none;
        }
        .lm-filter-input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .lm-filter-actions {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .lm-btn-apply {
            flex: 1;
            padding: 9px 16px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .lm-btn-apply:hover { background: var(--primary-dark); }

        /* ========================================== */
        /* TABLE CARD                                 */
        /* ========================================== */
        .lm-table-card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .lm-table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .lm-data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 640px;
        }
        .lm-data-table thead th {
            background: var(--gray-50);
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
            white-space: nowrap;
        }
        .lm-data-table tbody tr {
            border-bottom: 1px solid var(--border-light);
            transition: background 0.15s ease;
        }
        .lm-data-table tbody tr:last-child { border-bottom: none; }
        .lm-data-table tbody tr:hover { background: rgba(20, 93, 160, 0.02); }
        .lm-data-table td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 13px;
            color: var(--text-primary);
        }
        .lm-cell-center { text-align: center; }
        .lm-cell-muted { color: var(--text-muted); font-size: 12px; }

        /* ========================================== */
        /* ENTITY CELL (selaras dengan .lm-employee)  */
        /* ========================================== */
        .lm-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .lm-employee-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        .lm-employee-avatar--purple {
            background: linear-gradient(135deg, #5b21b6, #7c3aed);
        }
        .lm-employee-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .lm-employee-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 13px;
            line-height: 1.3;
        }
        .lm-employee-detail {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.3;
        }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .lm-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .lm-badge--info {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            border: 1px solid rgba(20, 93, 160, 0.16);
        }
        .lm-badge--success {
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
        .lm-badge--neutral {
            background: var(--gray-100);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        /* ========================================== */
        /* ACTION BUTTONS                             */
        /* ========================================== */
        .lm-actions-cell {
            text-align: right;
            white-space: nowrap;
        }
        thead .lm-actions-cell {
            text-align: right;
        }
        .lm-action-buttons {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            justify-content: flex-end;
        }
        .lm-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            cursor: pointer;
            font-family: inherit;
        }
        .lm-btn-action--edit {
            background: rgba(20, 93, 160, 0.06);
            color: var(--primary);
            border: 1px solid rgba(20, 93, 160, 0.15);
        }
        .lm-btn-action--edit:hover {
            background: rgba(20, 93, 160, 0.12);
            border-color: var(--primary);
        }
        .lm-btn-action--delete {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .lm-btn-action--delete:hover {
            background: rgba(239, 68, 68, 0.14);
            border-color: rgba(239, 68, 68, 0.35);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .lm-empty {
            text-align: center;
            padding: 48px 20px;
        }
        .lm-empty-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(20, 93, 160, 0.06);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }
        .lm-empty-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 4px;
        }
        .lm-empty-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 640px) {
            .lm-filter-grid {
                grid-template-columns: 1fr auto;
            }
        }

        @media (max-width: 639px) {
            .lm-cta-bar--split {
                flex-direction: column;
                align-items: stretch;
            }
            .lm-tabs {
                width: 100%;
            }
            .lm-tab-btn {
                flex: 1;
                padding: 10px 12px;
            }
            .lm-cta-actions,
            .lm-btn-primary {
                width: 100%;
                justify-content: center;
            }
            .lm-stats {
                padding: 12px;
            }
            .lm-stat-value {
                font-size: 18px;
            }
            .lm-search-row {
                flex-wrap: wrap;
            }
            .lm-search-input-wrap {
                flex-basis: 100%;
            }
            .lm-btn-reset,
            .lm-btn-toggle-filter {
                flex: 1;
                justify-content: center;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .lm-tab-panel.active {
                animation: none;
            }
        }
    </style>
</x-app>
