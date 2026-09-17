<x-app title="Divisi & Jabatan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm14 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Divisi & Jabatan</h1>
                <p class="section-subtitle">Kelola struktur organisasi: data divisi dan jabatan karyawan</p>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="org-alert org-alert--success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="org-alert org-alert--error">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="org-tabs" role="tablist">
        <button class="org-tab-btn active" data-tab="divisi" role="tab" aria-selected="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Divisi
            <span class="org-tab-count">{{ $divisions->count() }}</span>
        </button>
        <button class="org-tab-btn" data-tab="jabatan" role="tab" aria-selected="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Jabatan
            <span class="org-tab-count">{{ $positions->count() }}</span>
        </button>
    </div>

    {{-- Tab: Divisi --}}
    <div id="tab-divisi" class="org-tab-content active">
        <div class="org-card">
            <div class="org-card-header">
                <div class="org-card-title">
                    <span>Daftar Divisi</span>
                    <span class="org-card-count">{{ $divisions->count() }}</span>
                </div>
                <div class="org-card-actions">
                    <div class="org-search-wrap">
                        <svg class="org-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search" class="org-search-input" placeholder="Cari divisi..." autocomplete="off" aria-label="Cari divisi" data-org-search="tab-divisi">
                    </div>
                    <a href="{{ route('hr.divisions.create') }}" class="org-btn org-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Divisi
                    </a>
                </div>
            </div>

            @if($divisions->isEmpty())
            <div class="org-empty">
                <div class="org-empty-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3 class="org-empty-title">Belum Ada Divisi</h3>
                <p class="org-empty-desc">Belum ada data divisi. Tambahkan divisi pertama melalui tombol "Tambah Divisi".</p>
            </div>
            @else
            <div class="org-table-wrap">
                <table class="org-table">
                    <thead>
                        <tr>
                            <th class="org-col-no">#</th>
                            <th>Nama Divisi</th>
                            <th>Supervisor</th>
                            <th class="org-col-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($divisions as $i => $division)
                        <tr>
                            <td class="org-cell-muted org-cell-center">{{ $i + 1 }}</td>
                            <td>
                                <div class="org-name-cell">
                                    <div class="org-avatar-sm">{{ substr($division->name, 0, 1) }}</div>
                                    <span class="org-name">{{ $division->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($division->supervisor)
                                <span class="org-badge org-badge-blue">{{ $division->supervisor->name }}</span>
                                @else
                                <span class="org-text-muted">-</span>
                                @endif
                            </td>
                            <td class="org-actions-cell">
                                <a href="{{ route('hr.divisions.edit', $division->id) }}" class="org-icon-btn" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <button type="button" class="org-icon-btn org-icon-btn-danger" title="Hapus" data-modal-open="modal-delete-division-{{ $division->id }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="org-no-result" data-org-no-result="tab-divisi" style="display:none;">
                    <p>Data divisi tidak ditemukan untuk kata kunci tersebut.</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Tab: Jabatan --}}
    <div id="tab-jabatan" class="org-tab-content">
        <div class="org-card">
            <div class="org-card-header">
                <div class="org-card-title">
                    <span>Daftar Jabatan</span>
                    <span class="org-card-count">{{ $positions->count() }}</span>
                </div>
                <div class="org-card-actions">
                    <div class="org-search-wrap">
                        <svg class="org-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search" class="org-search-input" placeholder="Cari jabatan..." autocomplete="off" aria-label="Cari jabatan" data-org-search="tab-jabatan">
                    </div>
                    <a href="{{ route('hr.positions.create') }}" class="org-btn org-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Jabatan
                    </a>
                </div>
            </div>

            @if($positions->isEmpty())
            <div class="org-empty">
                <div class="org-empty-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <h3 class="org-empty-title">Belum Ada Jabatan</h3>
                <p class="org-empty-desc">Belum ada data jabatan. Tambahkan jabatan pertama melalui tombol "Tambah Jabatan".</p>
            </div>
            @else
            <div class="org-table-wrap">
                <table class="org-table">
                    <thead>
                        <tr>
                            <th class="org-col-no">#</th>
                            <th>Nama Jabatan</th>
                            <th>Divisi</th>
                            <th class="org-col-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($positions as $i => $position)
                        <tr>
                            <td class="org-cell-muted org-cell-center">{{ $i + 1 }}</td>
                            <td>
                                <div class="org-name-cell">
                                    <div class="org-avatar-sm org-avatar-purple">{{ substr($position->name, 0, 1) }}</div>
                                    <span class="org-name">{{ $position->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($position->division)
                                <span class="org-badge org-badge-green">{{ $position->division->name }}</span>
                                @else
                                <span class="org-text-muted">Tanpa Divisi</span>
                                @endif
                            </td>
                            <td class="org-actions-cell">
                                <a href="{{ route('hr.positions.edit', $position->id) }}" class="org-icon-btn" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <button type="button" class="org-icon-btn org-icon-btn-danger" title="Hapus" data-modal-open="modal-delete-position-{{ $position->id }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="org-no-result" data-org-no-result="tab-jabatan" style="display:none;">
                    <p>Data jabatan tidak ditemukan untuk kata kunci tersebut.</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Delete Modals for Divisions --}}
    @foreach($divisions as $division)
    <x-modal
        :id="'modal-delete-division-' . $division->id"
        title="Hapus Divisi"
        variant="danger"
        type="confirm"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.divisions.destroy', $division->id)"
        confirmFormMethod="DELETE"
    >
        <p>Apakah Anda yakin ingin menghapus divisi <strong>{{ $division->name }}</strong>?</p>
        <p style="color:#6b7280; font-size:0.85rem; margin-top:8px;">Tindakan ini tidak dapat dibatalkan.</p>
    </x-modal>
    @endforeach

    {{-- Delete Modals for Positions --}}
    @foreach($positions as $position)
    <x-modal
        :id="'modal-delete-position-' . $position->id"
        title="Hapus Jabatan"
        variant="danger"
        type="confirm"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.positions.destroy', $position->id)"
        confirmFormMethod="DELETE"
    >
        <p>Apakah Anda yakin ingin menghapus jabatan <strong>{{ $position->name }}</strong>?</p>
        <p style="color:#6b7280; font-size:0.85rem; margin-top:8px;">Tindakan ini tidak dapat dibatalkan.</p>
    </x-modal>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.org-tab-btn');
            const contents = document.querySelectorAll('.org-tab-content');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => {
                        t.classList.remove('active');
                        t.setAttribute('aria-selected', 'false');
                    });
                    contents.forEach(c => c.classList.remove('active'));
                    tab.classList.add('active');
                    tab.setAttribute('aria-selected', 'true');
                    document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
                });
            });

            // Live search per tab (client-side, tidak mengubah data)
            const searchInputs = document.querySelectorAll('[data-org-search]');
            searchInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    var tabId = input.getAttribute('data-org-search');
                    var tab = document.getElementById(tabId);
                    if (!tab) return;
                    var keyword = input.value.trim().toLowerCase();
                    var rows = tab.querySelectorAll('tbody tr');
                    var visible = 0;
                    rows.forEach(function(row) {
                        var match = row.textContent.toLowerCase().indexOf(keyword) !== -1;
                        row.style.display = match ? '' : 'none';
                        if (match) visible++;
                    });
                    var noResult = tab.querySelector('[data-org-no-result="' + tabId + '"]');
                    if (noResult) noResult.style.display = (visible === 0 && keyword !== '') ? '' : 'none';
                });
            });

            // Modal open handlers
            const openButtons = document.querySelectorAll('[data-modal-open]');
            openButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = btn.getAttribute('data-modal-open');
                    if (!id) return;
                    var modal = document.getElementById(id);
                    if (modal) modal.style.display = 'flex';
                });
            });

            // Modal close handlers
            const closeButtons = document.querySelectorAll('[data-modal-close="true"]');
            closeButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var modal = btn.closest('.modal-backdrop');
                    if (modal) modal.style.display = 'none';
                });
            });

            // Close modal on backdrop click
            const modals = document.querySelectorAll('.modal-backdrop');
            modals.forEach(function(modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) modal.style.display = 'none';
                });
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
        /* FLASH MESSAGES                             */
        /* ========================================== */
        .org-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .org-alert svg { flex-shrink: 0; }
        .org-alert--success {
            background: rgba(34, 197, 94, 0.08);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .org-alert--error {
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }

        /* ========================================== */
        /* TABS                                       */
        /* ========================================== */
        .org-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 16px;
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .org-tab-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 0.8125rem;
            font-weight: 600;
            font-family: inherit;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .org-tab-btn svg { width: 17px; height: 17px; }
        .org-tab-btn:hover:not(.active) {
            background: var(--gray-50);
            color: var(--primary);
        }
        .org-tab-btn.active {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .org-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 20px;
            padding: 0 7px;
            border-radius: 9999px;
            background: var(--gray-100);
            color: var(--text-muted);
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1;
        }
        .org-tab-btn.active .org-tab-count {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }
        .org-tab-content { display: none; }
        .org-tab-content.active { display: block; }

        /* ========================================== */
        /* CARD                                       */
        /* ========================================== */
        .org-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .org-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-light);
            background: var(--gray-50);
        }
        .org-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .org-card-count {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 9999px;
            line-height: 1.4;
        }
        .org-card-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ========================================== */
        /* SEARCH                                     */
        /* ========================================== */
        .org-search-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .org-search-icon {
            position: absolute;
            left: 12px;
            color: var(--text-light);
            pointer-events: none;
        }
        .org-search-input {
            width: 220px;
            max-width: 100%;
            padding: 9px 12px 9px 36px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.8125rem;
            font-family: inherit;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
        }
        .org-search-input::placeholder { color: var(--text-light); }
        .org-search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.12);
        }

        /* ========================================== */
        /* BUTTONS                                    */
        /* ========================================== */
        .org-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .org-btn svg { width: 15px; height: 15px; }
        .org-btn-primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .org-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* TABLE                                      */
        /* ========================================== */
        .org-table-wrap { overflow-x: auto; }
        .org-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 560px;
        }
        .org-table th {
            text-align: left;
            padding: 13px 16px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--white);
            border-bottom: 1px solid var(--border-light);
        }
        .org-table td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
            font-size: 0.8125rem;
        }
        .org-table tr:last-child td { border-bottom: none; }
        .org-table tbody tr:hover td { background: var(--gray-50); }
        .org-col-no { width: 48px; text-align: center; }
        .org-col-action { width: 96px; }

        /* ========================================== */
        /* NAME CELL                                  */
        /* ========================================== */
        .org-name-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .org-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .org-avatar-purple {
            background: rgba(147, 51, 234, 0.1);
            color: #7c3aed;
        }
        .org-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .org-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 9999px;
            white-space: nowrap;
        }
        .org-badge-blue { background: rgba(20, 93, 160, 0.08); color: var(--primary); }
        .org-badge-green { background: rgba(34, 197, 94, 0.1); color: #15803d; }

        /* ========================================== */
        /* CELL HELPERS                               */
        /* ========================================== */
        .org-text-muted { color: var(--text-muted); font-size: 0.8125rem; }
        .org-cell-muted { color: var(--text-muted); }
        .org-cell-center { text-align: center; }

        /* ========================================== */
        /* ACTIONS                                    */
        /* ========================================== */
        .org-actions-cell { text-align: right; white-space: nowrap; }
        .org-icon-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            vertical-align: middle;
        }
        .org-icon-btn:hover { background: var(--gray-50); color: var(--primary); }
        .org-icon-btn-danger:hover { background: rgba(239, 68, 68, 0.08); color: var(--error); }
        .org-icon-btn svg { width: 16px; height: 16px; }

        /* ========================================== */
        /* EMPTY STATE & NO RESULT                    */
        /* ========================================== */
        .org-empty {
            text-align: center;
            padding: 48px 24px;
        }
        .org-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
        }
        .org-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin: 0 0 6px;
        }
        .org-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 320px;
            line-height: 1.5;
        }
        .org-no-result {
            padding: 28px 24px;
            text-align: center;
            border-top: 1px solid var(--border-light);
        }
        .org-no-result p {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
        }

        /* ========================================== */
        /* MOBILE RESPONSIVE                          */
        /* ========================================== */
        @media (max-width: 640px) {
            .org-card-header {
                flex-direction: column;
                align-items: stretch;
            }
            .org-card-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .org-search-wrap { width: 100%; }
            .org-search-input { width: 100%; }
            .org-btn-primary { width: 100%; }
            .org-tab-btn { padding: 10px 12px; font-size: 0.78rem; gap: 6px; }
            .org-tab-btn svg { width: 15px; height: 15px; }
            .org-table th, .org-table td { padding: 12px 12px; }
        }
    </style>
</x-app>
