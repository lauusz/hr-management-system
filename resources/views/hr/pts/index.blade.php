<x-app title="Master PT">

    <div class="pt-container">

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

        {{-- Page Header --}}
        <div class="page-header">
            <div class="page-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div>
                <h1 class="page-title">Master PT</h1>
                <p class="page-subtitle">Master data perusahaan untuk karyawan.</p>
            </div>
            <a href="{{ route('hr.pts.create') }}" class="btn btn-primary ml-auto">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah PT
            </a>
        </div>

        {{-- Table Card --}}
        <div class="table-card">
            @if($items->isEmpty())
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <p>Belum ada data PT yang terdaftar.</p>
            </div>
            @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Perusahaan (PT)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $pt)
                        <tr>
                            <td>
                                <div class="pt-name-cell">
                                    <div class="pt-avatar">{{ substr($pt->name, 0, 1) }}</div>
                                    <span class="pt-name">{{ $pt->name }}</span>
                                </div>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('hr.pts.edit', $pt->id) }}" class="action-btn" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <button type="button" class="action-btn action-btn-danger" title="Hapus" data-modal-open="delete-pt-{{ $pt->id }}">
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

        {{-- Pagination --}}
        @if($items->hasPages())
        <div class="pagination-wrap">
            <x-pagination :items="$items" />
        </div>
        @endif

    </div>

    {{-- Delete Modals --}}
    @foreach($items as $pt)
    <x-modal
        :id="'delete-pt-' . $pt->id"
        title="Hapus PT?"
        variant="danger"
        type="confirm"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.pts.destroy', $pt->id)"
        confirmFormMethod="DELETE"
    >
        <p>Apakah Anda yakin ingin menghapus PT berikut?</p>
        <p style="font-weight: 700; color: #111827; margin-top: 8px;">{{ $pt->name }}</p>
        <p style="color: #6b7280; font-size: 0.85rem; margin-top: 8px;">Pastikan PT ini tidak sedang digunakan oleh karyawan aktif.</p>
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
            --blue-light: #eff6ff;
            --blue-text: #1d4ed8;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        /* === RESET & BASE === */
        .pt-container {
            max-width: 900px;
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
            min-width: 400px;
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

        /* === PT NAME CELL === */
        .pt-name-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .pt-avatar {
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
        .pt-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
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
        .action-btn-danger:hover { background: var(--danger-bg); color: var(--danger-text); }
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
            .page-icon {
                margin: 0 auto;
            }
            .ml-auto {
                margin: 0 auto;
            }
            .btn-primary {
                width: 100%;
            }
            .data-table th, .data-table td {
                padding: 12px 14px;
            }
        }
    </style>
</x-app>
