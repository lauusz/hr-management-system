<x-app title="Divisi & Jabatan">

    <div class="org-container">

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

        {{-- Tab Navigation --}}
        <div class="org-tabs">
            <button class="org-tab-btn active" data-tab="divisi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Divisi
            </button>
            <button class="org-tab-btn" data-tab="jabatan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                Jabatan
            </button>
        </div>

        {{-- Tab: Divisi --}}
        <div id="tab-divisi" class="org-tab-content active">
            <div class="org-card">
                <div class="org-card-header">
                    <div class="org-card-title">
                        <span class="org-card-count">{{ $divisions->count() }}</span>
                        <span>Divisi</span>
                    </div>
                    <a href="{{ route('hr.divisions.create') }}" class="org-btn org-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Divisi
                    </a>
                </div>

                @if($divisions->isEmpty())
                <div class="org-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <p>Belum ada data divisi.</p>
                </div>
                @else
                <div class="org-table-wrap">
                    <table class="org-table">
                        <thead>
                            <tr>
                                <th>Nama Divisi</th>
                                <th>Supervisor</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($divisions as $division)
                            <tr>
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
                </div>
                @endif
            </div>
        </div>

        {{-- Tab: Jabatan --}}
        <div id="tab-jabatan" class="org-tab-content">
            <div class="org-card">
                <div class="org-card-header">
                    <div class="org-card-title">
                        <span class="org-card-count">{{ $positions->count() }}</span>
                        <span>Jabatan</span>
                    </div>
                    <a href="{{ route('hr.positions.create') }}" class="org-btn org-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Jabatan
                    </a>
                </div>

                @if($positions->isEmpty())
                <div class="org-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <p>Belum ada data jabatan.</p>
                </div>
                @else
                <div class="org-table-wrap">
                    <table class="org-table">
                        <thead>
                            <tr>
                                <th>Nama Jabatan</th>
                                <th>Divisi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($positions as $position)
                            <tr>
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
                </div>
                @endif
            </div>
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
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    tab.classList.add('active');
                    document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
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
            --blue-light: #eff6ff;
            --blue-text: #1d4ed8;
            --purple-light: #faf5ff;
            --purple-text: #7e22ce;
            --green-light: #f0fdf4;
            --green-text: #15803d;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        /* === RESET & BASE === */
        .org-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 16px;
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
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .flash-success { background: var(--success-bg); color: var(--success-text); border: 1px solid var(--success-border); }
        .flash-icon { width: 18px; height: 18px; flex-shrink: 0; }

        /* === TABS === */
        .org-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 16px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 4px;
        }
        .org-tab-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s;
        }
        .org-tab-btn svg { width: 18px; height: 18px; }
        .org-tab-btn.active {
            background: var(--primary);
            color: #fff;
        }
        .org-tab-content { display: none; }
        .org-tab-content.active { display: block; }

        /* === CARD === */
        .org-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        .org-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: #fafafa;
        }
        .org-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .org-card-count {
            background: var(--primary);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
        }

        /* === BUTTONS === */
        .org-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }
        .org-btn svg { width: 16px; height: 16px; }
        .org-btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .org-btn-primary:hover { background: var(--primary-dark); }

        /* === TABLE === */
        .org-table-wrap { overflow-x: auto; }
        .org-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }
        .org-table th {
            text-align: left;
            padding: 12px 20px;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--bg-body);
            border-bottom: 1px solid var(--border);
        }
        .org-table td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .org-table tr:last-child td { border-bottom: none; }
        .org-table tr:hover td { background: #fafafa; }

        /* === NAME CELL === */
        .org-name-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .org-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--blue-light);
            color: var(--blue-text);
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .org-avatar-purple {
            background: var(--purple-light);
            color: var(--purple-text);
        }
        .org-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
        }

        /* === BADGES === */
        .org-badge {
            display: inline-flex;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .org-badge-blue { background: var(--blue-light); color: var(--blue-text); }
        .org-badge-green { background: var(--green-light); color: var(--green-text); }

        /* === COUNT === */
        .org-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            background: var(--bg-body);
            color: var(--text-main);
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: var(--radius-sm);
        }

        /* === TEXT UTILITIES === */
        .org-text-muted { color: var(--text-muted); font-size: 0.9rem; }

        /* === ACTIONS === */
        .org-actions-cell { text-align: right; white-space: nowrap; }
        .org-icon-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: 0.2s;
            vertical-align: middle;
        }
        .org-icon-btn:hover { background: var(--bg-body); color: var(--primary); }
        .org-icon-btn-danger:hover { background: var(--danger-bg); color: var(--danger-text); }
        .org-icon-btn svg { width: 16px; height: 16px; }

        /* === EMPTY STATE === */
        .org-empty {
            padding: 60px 24px;
            text-align: center;
            color: var(--text-muted);
        }
        .org-empty svg { width: 56px; height: 56px; margin-bottom: 16px; opacity: 0.3; }
        .org-empty p { font-size: 0.95rem; margin: 0; }

        /* === MOBILE RESPONSIVE === */
        @media (max-width: 640px) {
            .org-card-header {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
            .org-btn-primary { width: 100%; justify-content: center; }
            .org-tab-btn { padding: 10px 12px; font-size: 0.85rem; }
            .org-tab-btn svg { width: 16px; height: 16px; }
            .org-table th, .org-table td { padding: 12px 14px; }
        }
    </style>
</x-app>
