<x-app title="Data Supervisor & Manager">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Supervisor & Manager</h1>
                <p class="section-subtitle">Kelola level akses dan jatah OFF supervisor</p>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="spv-alert spv-alert--success">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="spv-alert spv-alert--error">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Action Button --}}
    <div class="spv-cta-bar">
        <a href="{{ route('hr.supervisors.create') }}" class="spv-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Supervisor
        </a>
    </div>

    {{-- Search & Role Filter --}}
    <div class="spv-filter-card">
        <form method="GET" action="{{ route('hr.supervisors.index') }}">
            <div class="spv-search-row">
                <div class="spv-search-input-wrap">
                    <svg class="spv-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Cari nama supervisor..."
                        autocomplete="off"
                        class="spv-search-input"
                    >
                </div>

                <fieldset class="spv-role-filter">
                    <legend>Level</legend>
                    <label>
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="{{ \App\Enums\UserRole::SUPERVISOR->value }}"
                            @checked(in_array(\App\Enums\UserRole::SUPERVISOR->value, $selectedRoles, true))
                        >
                        Supervisor
                    </label>
                    <label>
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="{{ \App\Enums\UserRole::MANAGER->value }}"
                            @checked(in_array(\App\Enums\UserRole::MANAGER->value, $selectedRoles, true))
                        >
                        Manager
                    </label>
                </fieldset>

                <button type="submit" class="spv-btn-search">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>

                @if($q !== '' || $isRoleFilterActive)
                    <a href="{{ route('hr.supervisors.index') }}" class="spv-btn-reset">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="table-card spv-desktop-view">
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
                            <th>OFF Aktif</th>
                            <th class="actions-heading">Aksi</th>
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
                            <td>
                                @if($spv->isSupervisor() && isset($spv->off_spv_stats))
                                    <div class="spv-role">{{ $spv->off_spv_stats['approved'] }} digunakan · {{ $spv->off_spv_stats['pending'] }} pending</div>
                                    <div class="spv-division">Sisa {{ $spv->off_spv_stats['remaining'] }} hari</div>
                                @else
                                    <span class="spv-division">-</span>
                                @endif
                            </td>
                            <td class="actions-cell">
                                @if($spv->isSupervisor())
                                <a href="{{ route('hr.supervisors.show', $spv) }}" class="action-btn" title="Detail OFF SPV">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/></svg>
                                </a>
                                @endif
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

    {{-- Mobile Cards --}}
    <div class="spv-mobile-list">
            @forelse($supervisors as $spv)
                <article class="spv-mobile-card">
                    <div class="spv-mobile-card__head">
                        <div class="spv-avatar">{{ substr($spv->name, 0, 1) }}</div>
                        <div class="spv-mobile-card__identity">
                            <div class="spv-name">{{ $spv->name }}</div>
                            <div class="spv-email">{{ $spv->email ?? '-' }}</div>
                        </div>
                        @if($spv->role === \App\Enums\UserRole::MANAGER)
                            <span class="badge badge-manager">MANAGER</span>
                        @else
                            <span class="badge badge-supervisor">SUPERVISOR</span>
                        @endif
                    </div>

                    <div class="spv-mobile-card__details">
                        <div>
                            <span>Jabatan & Divisi</span>
                            <strong>{{ $spv->position->name ?? '-' }}</strong>
                            <small>{{ $spv->division->name ?? '-' }}</small>
                        </div>
                        <div>
                            <span>OFF Aktif</span>
                            @if($spv->isSupervisor() && isset($spv->off_spv_stats))
                                <strong>{{ $spv->off_spv_stats['approved'] }} digunakan · {{ $spv->off_spv_stats['pending'] }} pending</strong>
                                <small>Sisa {{ $spv->off_spv_stats['remaining'] }} hari</small>
                            @else
                                <strong>-</strong>
                                <small>Tidak tersedia</small>
                            @endif
                        </div>
                    </div>

                    <div class="spv-mobile-card__actions">
                        @if($spv->isSupervisor())
                            <a href="{{ route('hr.supervisors.show', $spv) }}" class="spv-mobile-action">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/></svg>
                                Detail OFF SPV
                            </a>
                        @endif
                        <a href="{{ route('hr.supervisors.edit', $spv->id) }}" class="spv-mobile-action">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit Level
                        </a>
                        <button type="button" class="spv-mobile-action spv-mobile-action--warning" data-modal-open="modal-demote-{{ $spv->id }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22v-5"/><path d="M9 8V2"/><path d="M15 8V2"/><path d="M18 8v5a6 6 0 0 1-6 6v0a6 6 0 0 1-6-6V8z"/></svg>
                            Demote
                        </button>
                    </div>
                </article>
            @empty
                <div class="table-card empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <p>Belum ada data supervisor.</p>
                </div>
            @endforelse
    </div>

    {{-- Pagination --}}
    @if($supervisors->hasPages())
        <div class="pagination-wrap">
            {{ $supervisors->links() }}
        </div>
    @endif

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
        /* === SECTION HEADER === */
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
        .section-icon svg { width: 16px; height: 16px; }
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
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark);
        }

        /* === CTA === */
        .spv-cta-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .spv-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            flex: 1;
        }
        .spv-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .spv-btn-primary svg { width: 18px; height: 18px; }

        /* === SEARCH & ROLE FILTER === */
        .spv-filter-card {
            margin-bottom: 16px;
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .spv-search-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px 16px;
        }
        .spv-search-input-wrap {
            position: relative;
            flex: 1;
        }
        .spv-search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            color: var(--text-light);
            pointer-events: none;
            transform: translateY(-50%);
        }
        .spv-search-input {
            width: 100%;
            padding: 10px 14px 10px 42px;
            color: var(--text-primary);
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            outline: none;
            font: inherit;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .spv-search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .spv-search-input::placeholder { color: var(--text-light); }
        .spv-role-filter {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            margin: 0;
            padding: 0;
            border: 0;
        }
        .spv-role-filter legend {
            margin-bottom: 6px;
            color: var(--text-muted);
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .spv-role-filter label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-secondary);
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
        }
        .spv-role-filter input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: var(--primary);
        }
        .spv-btn-search,
        .spv-btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 42px;
            padding: 10px 16px;
            border-radius: 12px;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .spv-btn-search {
            color: #fff;
            background: var(--primary);
            border: 1px solid var(--primary);
        }
        .spv-btn-search:hover { background: var(--primary-dark); }
        .spv-btn-reset {
            color: var(--text-muted);
            background: var(--white);
            border: 1.5px solid var(--border);
        }
        .spv-btn-reset:hover {
            color: var(--primary);
            background: rgba(20, 93, 160, 0.04);
            border-color: var(--primary);
        }

        /* === ALERTS === */
        .spv-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .spv-alert--success { background: rgba(34, 197, 94, 0.08); color: #16a34a; border: 1px solid rgba(34, 197, 94, 0.25); }
        .spv-alert--error { background: rgba(239, 68, 68, 0.08); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); }
        .flash-icon { width: 18px; height: 18px; flex-shrink: 0; }

        /* === TABLE CARD === */
        .table-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .table-wrap { overflow-x: auto; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        .data-table th {
            text-align: left;
            padding: 14px 16px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--gray-50);
            border-bottom: 1px solid var(--border-light);
        }
        .data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: top;
            font-size: 0.8125rem;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover td { background: var(--gray-50); }

        /* === SUPERVISOR CELLS === */
        .spv-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .spv-avatar {
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
        }
        .spv-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .spv-email {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .spv-role {
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        .spv-division {
            display: block;
            margin-top: 2px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* === BADGES === */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }
        .badge-manager {
            background: rgba(212, 175, 55, 0.14);
            color: #8a6a00;
        }
        .badge-supervisor {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark);
        }

        /* === ACTIONS === */
        .data-table th.actions-heading { text-align: center; }
        .actions-cell { text-align: right; white-space: nowrap; }
        .action-btn {
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
            text-decoration: none;
        }
        .action-btn:hover { background: var(--gray-50); color: var(--primary); }
        .action-btn-warning:hover { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .action-btn svg { width: 16px; height: 16px; }

        /* === MOBILE CARDS === */
        .spv-mobile-list { display: none; }
        .spv-mobile-card {
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .spv-mobile-card__head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
        }
        .spv-mobile-card__identity { min-width: 0; flex: 1; }
        .spv-mobile-card__identity .spv-name,
        .spv-mobile-card__identity .spv-email {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .spv-mobile-card__head .badge { flex-shrink: 0; }
        .spv-mobile-card__details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 16px;
            padding: 16px;
        }
        .spv-mobile-card__details span,
        .spv-mobile-card__details strong,
        .spv-mobile-card__details small { display: block; }
        .spv-mobile-card__details span {
            margin-bottom: 5px;
            color: var(--text-muted);
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .spv-mobile-card__details strong {
            color: var(--text-primary);
            font-size: 0.8125rem;
            line-height: 1.4;
        }
        .spv-mobile-card__details small {
            margin-top: 2px;
            color: var(--text-muted);
            font-size: 0.75rem;
        }
        .spv-mobile-card__actions {
            display: flex;
            gap: 8px;
            padding: 12px 16px;
            border-top: 1px solid var(--border-light);
            background: var(--gray-50);
        }
        .spv-mobile-action {
            min-height: 42px;
            display: inline-flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            background: var(--white);
            color: var(--text-primary);
            font: inherit;
            font-size: 0.72rem;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .spv-mobile-action:hover { border-color: var(--primary); color: var(--primary); }
        .spv-mobile-action svg { width: 15px; height: 15px; flex-shrink: 0; }
        .spv-mobile-action--warning { color: #a16207; }
        .spv-mobile-action--warning:hover { border-color: var(--warning); color: #a16207; }

        /* === EMPTY STATE === */
        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: var(--text-muted);
            background: var(--white);
        }
        .empty-state svg {
            width: 56px;
            height: 56px;
            margin-bottom: 16px;
            padding: 14px;
            border-radius: 50%;
            background: var(--gray-50);
            color: var(--text-light);
        }
        .empty-state p {
            max-width: 280px;
            margin: 0 auto;
            font-size: 0.8125rem;
            line-height: 1.5;
        }

        /* === PAGINATION === */
        .pagination-wrap { margin-top: 16px; }

        @media (min-width: 480px) {
            .spv-cta-bar { justify-content: flex-start; }
            .spv-btn-primary { flex: none; }
            .spv-search-row {
                flex-direction: row;
                align-items: end;
                flex-wrap: wrap;
                padding: 16px 20px;
            }
            .spv-search-input-wrap { min-width: 240px; }
            .spv-role-filter { flex-shrink: 0; }
            .spv-btn-search,
            .spv-btn-reset {
                width: auto;
                flex-shrink: 0;
            }
        }

        /* === MOBILE RESPONSIVE === */
        @media (max-width: 767px) {
            .spv-desktop-view { display: none; }
            .spv-mobile-list { display: grid; gap: 12px; }
        }

        @media (min-width: 1024px) {
            .data-table th,
            .data-table td { padding: 16px 20px; }
        }

        @media (max-width: 420px) {
            .spv-mobile-card__details { grid-template-columns: 1fr; }
            .spv-mobile-card__actions { flex-wrap: wrap; }
            .spv-mobile-action { min-width: calc(50% - 4px); }
        }
    </style>
</x-app>
