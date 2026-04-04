<x-app title="Data Supervisor & Manager">

    <div class="spv-container">

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

        @if(session('error'))
        <div class="flash flash-error">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        {{-- Page Header --}}
        <div class="page-header">
            <div class="page-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="page-header-text">
                <h1 class="page-title">Supervisor & Manager</h1>
                <p class="page-subtitle">Orang-orang ini memiliki akses menu Approval.</p>
            </div>
            <a href="{{ route('hr.supervisors.create') }}" class="btn btn-primary ml-auto">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Supervisor
            </a>
        </div>

        {{-- Table Card --}}
        <div class="table-card">
            @if($supervisors->isEmpty())
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <p>Belum ada data supervisor.</p>
            </div>
            @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama & Email</th>
                            <th>Jabatan & Divisi</th>
                            <th>Level</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supervisors as $spv)
                        <tr>
                            <td>
                                <div class="spv-info">
                                    <div class="spv-avatar">{{ substr($spv->name, 0, 1) }}</div>
                                    <div>
                                        <div class="spv-name">{{ $spv->name }}</div>
                                        <div class="spv-email">{{ $spv->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="spv-role">{{ $spv->position->name ?? '-' }}</div>
                                <div class="spv-division">{{ $spv->division->name ?? '-' }}</div>
                            </td>
                            <td>
                                @if($spv->role === \App\Enums\UserRole::MANAGER)
                                <span class="badge badge-manager">MANAGER</span>
                                @else
                                <span class="badge badge-supervisor">SUPERVISOR</span>
                                @endif
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.supervisors.edit', $spv->id) }}" class="action-btn" title="Edit Level">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <button type="button" class="action-btn action-btn-warning" title="Demote" data-modal-open="modal-demote-{{ $spv->id }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22v-5"/><path d="M9 8V2"/><path d="M15 8V2"/><path d="M18 8v5a6 6 0 0 1-6 6v0a6 6 0 0 1-6-6V8z"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if($supervisors->hasPages())
        <div class="pagination-wrap">
            {{ $supervisors->links() }}
        </div>
        @endif

    </div>

    {{-- Demote Modals --}}
    @foreach($supervisors as $spv)
    <x-modal
        :id="'modal-demote-' . $spv->id"
        title="Demote Jabatan?"
        variant="warning"
        type="confirm"
        confirmLabel="Demote"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.supervisors.destroy', $spv->id)"
        confirmFormMethod="DELETE"
    >
        <p>Apakah Anda yakin ingin menurunkan jabatan <strong>{{ $spv->name }}</strong> menjadi Staff biasa?</p>
        <p style="color: #6b7280; font-size: 0.85rem; margin-top: 8px;">Orang ini akan kehilangan akses menu Approval.</p>
    </x-modal>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            --warning-bg: #fffbeb;
            --warning-text: #c2410c;
            --warning-border: #fed7aa;
            --blue-light: #eff6ff;
            --blue-text: #1d4ed8;
            --amber-light: #fef3c7;
            --amber-text: #92400e;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        /* === RESET & BASE === */
        .spv-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px 16px 40px;
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
        .flash-error { background: var(--danger-bg); color: var(--danger-text); border: 1px solid var(--danger-border); }
        .flash-icon { width: 18px; height: 18px; flex-shrink: 0; }

        /* === PAGE HEADER === */
        .page-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .page-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            color: #fff;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .page-icon svg { width: 24px; height: 24px; }
        .page-header-text { flex: 1; min-width: 200px; }
        .page-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .page-subtitle {
            margin: 4px 0 0;
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        .ml-auto { margin-left: auto; }

        /* === BUTTONS === */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }

        /* === TABLE CARD === */
        .table-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        /* === TABLE === */
        .table-wrap { overflow-x: auto; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        .data-table th {
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
        .data-table td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #fafafa; }

        /* === SPV INFO CELL === */
        .spv-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .spv-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            background: var(--blue-light);
            color: var(--blue-text);
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .spv-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
        }
        .spv-email {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .spv-role {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-main);
        }
        .spv-division {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* === BADGES === */
        .badge {
            display: inline-flex;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.03em;
        }
        .badge-manager {
            background: var(--blue-light);
            color: var(--blue-text);
        }
        .badge-supervisor {
            background: var(--amber-light);
            color: var(--amber-text);
        }

        /* === ACTIONS === */
        .actions-cell { text-align: right; white-space: nowrap; }
        .action-btn {
            width: 34px;
            height: 34px;
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
        .action-btn:hover { background: var(--bg-body); color: var(--primary); }
        .action-btn-warning:hover { background: var(--warning-bg); color: var(--warning-text); }
        .action-btn svg { width: 16px; height: 16px; }

        /* === EMPTY STATE === */
        .empty-state {
            padding: 60px 24px;
            text-align: center;
            color: var(--text-muted);
        }
        .empty-state svg { width: 56px; height: 56px; margin-bottom: 16px; opacity: 0.3; }
        .empty-state p { font-size: 0.95rem; margin: 0; }

        /* === PAGINATION === */
        .pagination-wrap {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }

        /* === MOBILE RESPONSIVE === */
        @media (max-width: 640px) {
            .page-header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            .page-icon { margin: 0 auto; }
            .ml-auto { margin: 0 auto; }
            .btn-primary { width: 100%; }
            .data-table th, .data-table td { padding: 12px 14px; }
        }
    </style>
</x-app>
