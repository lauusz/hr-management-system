<x-app title="Master PT">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4a2 2 0 012-2h2a2 2 0 012 2v4"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Master PT</h1>
                <p class="section-subtitle">Master data perusahaan untuk karyawan</p>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="ptm-alert ptm-alert--success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="ptm-alert ptm-alert--error">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Card: Daftar PT --}}
    <div class="ptm-card">
        <div class="ptm-card-header">
            <div class="ptm-card-title">
                <span>Daftar PT</span>
                <span class="ptm-card-count">{{ $items->total() }}</span>
            </div>
            <div class="ptm-card-actions">
                @if($items->isNotEmpty())
                <div class="ptm-search-wrap">
                    <svg class="ptm-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search" class="ptm-search-input" placeholder="Cari PT..." autocomplete="off" aria-label="Cari PT" data-ptm-search>
                </div>
                @endif
                <a href="{{ route('hr.pts.create') }}" class="ptm-btn ptm-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah PT
                </a>
            </div>
        </div>

        @if($items->isEmpty())
        <div class="ptm-empty">
            <div class="ptm-empty-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <h3 class="ptm-empty-title">Belum Ada PT</h3>
            <p class="ptm-empty-desc">Belum ada data PT yang terdaftar. Tambahkan PT pertama melalui tombol "Tambah PT".</p>
        </div>
        @else
        <div class="ptm-table-wrap">
            <table class="ptm-table">
                <thead>
                    <tr>
                        <th class="ptm-col-no">#</th>
                        <th>Nama Perusahaan (PT)</th>
                        <th class="ptm-col-action"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $pt)
                    <tr>
                        <td class="ptm-cell-muted ptm-cell-center">{{ ($items->firstItem() ?? 1) + $i }}</td>
                        <td>
                            <div class="ptm-name-cell">
                                <div class="ptm-avatar-sm">{{ substr($pt->name, 0, 1) }}</div>
                                <span class="ptm-name">{{ $pt->name }}</span>
                            </div>
                        </td>
                        <td class="ptm-actions-cell">
                            <a href="{{ route('hr.pts.edit', $pt->id) }}" class="ptm-icon-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <button type="button" class="ptm-icon-btn ptm-icon-btn-danger" title="Hapus" data-modal-open="delete-pt-{{ $pt->id }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="ptm-no-result" data-ptm-no-result style="display:none;">
                <p>Data PT tidak ditemukan untuk kata kunci tersebut.</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="ptm-pagination-wrap">
        <x-pagination :items="$items" />
    </div>
    @endif

    {{-- Delete Modals --}}
    @foreach($items as $pt)
    <x-modal
        :id="'delete-pt-' . $pt->id"
        title="Hapus PT"
        variant="danger"
        type="confirm"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.pts.destroy', $pt->id)"
        confirmFormMethod="DELETE"
    >
        <p>Apakah Anda yakin ingin menghapus PT <strong>{{ $pt->name }}</strong>?</p>
        <p style="color:#6b7280; font-size:0.85rem; margin-top:8px;">Pastikan PT ini tidak sedang digunakan oleh karyawan aktif.</p>
    </x-modal>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Live search (client-side, tidak mengubah data)
            var searchInput = document.querySelector('[data-ptm-search]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    var keyword = searchInput.value.trim().toLowerCase();
                    var rows = document.querySelectorAll('.ptm-table tbody tr');
                    var visible = 0;
                    rows.forEach(function(row) {
                        var match = row.textContent.toLowerCase().indexOf(keyword) !== -1;
                        row.style.display = match ? '' : 'none';
                        if (match) visible++;
                    });
                    var noResult = document.querySelector('[data-ptm-no-result]');
                    if (noResult) noResult.style.display = (visible === 0 && keyword !== '') ? '' : 'none';
                });
            }

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
        .ptm-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .ptm-alert svg { flex-shrink: 0; }
        .ptm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .ptm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }

        /* ========================================== */
        /* CARD                                       */
        /* ========================================== */
        .ptm-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            max-width: 100%;
        }
        .ptm-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-light);
            background: var(--gray-50);
        }
        .ptm-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .ptm-card-count {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 9999px;
            line-height: 1.4;
        }
        .ptm-card-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ========================================== */
        /* SEARCH                                     */
        /* ========================================== */
        .ptm-search-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .ptm-search-icon {
            position: absolute;
            left: 12px;
            color: var(--text-light);
            pointer-events: none;
        }
        .ptm-search-input {
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
        .ptm-search-input::placeholder { color: var(--text-light); }
        .ptm-search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.12);
        }

        /* ========================================== */
        /* BUTTONS                                    */
        /* ========================================== */
        .ptm-btn {
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
        .ptm-btn svg { width: 15px; height: 15px; }
        .ptm-btn-primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .ptm-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* TABLE                                      */
        /* ========================================== */
        .ptm-table-wrap { overflow-x: auto; }
        .ptm-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 480px;
        }
        .ptm-table th {
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
        .ptm-table td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
            font-size: 0.8125rem;
        }
        .ptm-table tr:last-child td { border-bottom: none; }
        .ptm-table tbody tr:hover td { background: var(--gray-50); }
        .ptm-col-no { width: 48px; text-align: center; }
        .ptm-col-action { width: 96px; }

        /* ========================================== */
        /* NAME CELL                                  */
        /* ========================================== */
        .ptm-name-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .ptm-avatar-sm {
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
        .ptm-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* ========================================== */
        /* CELL HELPERS                               */
        /* ========================================== */
        .ptm-cell-muted { color: var(--text-muted); }
        .ptm-cell-center { text-align: center; }

        /* ========================================== */
        /* ACTIONS                                    */
        /* ========================================== */
        .ptm-actions-cell { text-align: right; white-space: nowrap; }
        .ptm-icon-btn {
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
        .ptm-icon-btn:hover { background: var(--gray-50); color: var(--primary); }
        .ptm-icon-btn-danger:hover { background: rgba(239, 68, 68, 0.08); color: var(--error); }
        .ptm-icon-btn svg { width: 16px; height: 16px; }

        /* ========================================== */
        /* EMPTY STATE & NO RESULT                    */
        /* ========================================== */
        .ptm-empty {
            text-align: center;
            padding: 48px 24px;
        }
        .ptm-empty-icon {
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
        .ptm-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin: 0 0 6px;
        }
        .ptm-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 320px;
            line-height: 1.5;
        }
        .ptm-no-result {
            padding: 28px 24px;
            text-align: center;
            border-top: 1px solid var(--border-light);
        }
        .ptm-no-result p {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .ptm-pagination-wrap {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }

        /* ========================================== */
        /* MOBILE RESPONSIVE                          */
        /* ========================================== */
        @media (max-width: 640px) {
            .ptm-card-header {
                flex-direction: column;
                align-items: stretch;
            }
            .ptm-card-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .ptm-search-wrap { width: 100%; }
            .ptm-search-input { width: 100%; }
            .ptm-btn-primary { width: 100%; }
            .ptm-table th, .ptm-table td { padding: 12px 12px; }
        }
    </style>
</x-app>
