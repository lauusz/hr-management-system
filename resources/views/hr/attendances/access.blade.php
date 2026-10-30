<x-app title="Atur Akses Absensi">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Atur Akses Absensi</h1>
                <p class="section-subtitle">Kelola daftar pengguna yang diberi hak melihat rekap absensi karyawan</p>
            </div>
        </div>
    </x-slot>

    @php
        $recipients = $viewers->where('attendance_visible_employees_count', '>', 0)->values();
        $totalCoverage = (int) $recipients->sum('attendance_visible_employees_count');
        $candidateCount = $viewers->count();
        $recipientRoles = $recipients
            ->map(fn ($user) => ['value' => $user->role->value, 'label' => $user->role->label()])
            ->unique('value')
            ->sortBy('label')
            ->values();
        $candidateRoles = $viewers
            ->map(fn ($user) => ['value' => $user->role->value, 'label' => $user->role->label()])
            ->unique('value')
            ->sortBy('label')
            ->values();
    @endphp

    {{-- Flash / Error Messages --}}
    @if (session('success'))
        <div class="lm-alert lm-alert--success" role="status">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="lm-alert lm-alert--error" role="alert">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- Top Navigation & Primary CTA --}}
    <div class="lm-cta-bar lm-cta-bar--split">
        <a href="{{ route('hr.attendances.index') }}" class="back-btn" aria-label="Kembali ke Master Absensi">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali ke Master Absensi</span>
        </a>

        <button type="button" class="lm-btn-primary" id="open-grant-access-modal" data-open-grant-modal>
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Penerima Akses
        </button>
    </div>

    {{-- Stats Summary --}}
    <div class="lm-stats" data-access-stats>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--success">{{ $recipients->count() }}</div>
            <div class="lm-stat-label">Penerima Akses</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--info">{{ $totalCoverage }}</div>
            <div class="lm-stat-label">Total Cakupan Karyawan</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--neutral">{{ $candidateCount }}</div>
            <div class="lm-stat-label">Kandidat Pengguna</div>
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
                    id="access-live-search"
                    placeholder="Cari nama penerima akses, jabatan, divisi, atau PT..."
                    autocomplete="off"
                    class="lm-search-input"
                    aria-label="Cari penerima akses absensi"
                    data-access-live-search>
            </div>

            <button
                type="button"
                id="access-filter-reset"
                class="lm-btn-reset"
                style="display: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reset
            </button>

            <button
                type="button"
                id="access-filter-toggle"
                class="lm-btn-toggle-filter"
                aria-expanded="false"
                aria-controls="access-filter-panel">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
                <span class="lm-filter-badge" id="access-filter-badge" style="display: none;">1</span>
            </button>
        </div>

        <div class="lm-filter-panel" id="access-filter-panel" style="display: none;">
            <div class="lm-filter-grid">
                <div class="lm-filter-group">
                    <label class="lm-filter-label" for="access-role-filter">Role Pengguna</label>
                    <select id="access-role-filter" class="lm-filter-input">
                        <option value="">Semua Role</option>
                        @foreach ($recipientRoles as $roleOption)
                            <option value="{{ $roleOption['value'] }}">{{ $roleOption['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lm-filter-actions">
                    <button type="button" id="access-filter-apply" class="lm-btn-apply">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Recipients Table Card --}}
    <div class="lm-table-card" data-access-results aria-live="polite">
        @if ($recipients->isEmpty())
            <div class="lm-empty">
                <div class="lm-empty-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="lm-empty-title">Belum Ada Penerima Akses</h3>
                <p class="lm-empty-desc">Belum ada pengguna yang diberi akses lihat absensi oleh HR. Tambahkan penerima akses melalui tombol di atas.</p>
                <div class="lm-empty-actions">
                    <button type="button" class="lm-btn-primary" data-open-grant-modal>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Pilih Pengguna Penerima Akses
                    </button>
                </div>
            </div>
        @else
            <div class="lm-table-wrap" id="access-table-wrap">
                <table class="lm-data-table">
                    <thead>
                        <tr>
                            <th style="width: 56px;">#</th>
                            <th>Pengguna Penerima Akses</th>
                            <th>Role</th>
                            <th>Cakupan Akses</th>
                            <th class="lm-actions-cell" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recipients as $index => $recipient)
                            @php
                                $metaParts = array_values(array_filter([
                                    $recipient->position?->name,
                                    $recipient->division?->name,
                                    $recipient->profile?->pt?->name,
                                ]));
                                $metaLine = count($metaParts) > 0 ? implode(' · ', $metaParts) : 'Pengguna Aktif';
                                $searchBlob = mb_strtolower(implode(' ', [
                                    $recipient->name,
                                    $recipient->role->label(),
                                    $metaLine,
                                ]));
                            @endphp
                            <tr
                                data-recipient-row
                                data-search="{{ $searchBlob }}"
                                data-role="{{ $recipient->role->value }}">
                                <td class="lm-cell-center lm-cell-muted" data-recipient-number>{{ $index + 1 }}</td>
                                <td>
                                    <div class="lm-employee">
                                        <div class="lm-employee-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($recipient->name, 0, 1)) }}</div>
                                        <div class="lm-employee-info">
                                            <span class="lm-employee-name">{{ $recipient->name }}</span>
                                            <div class="lm-employee-detail">{{ $metaLine }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="lm-role-chip">{{ $recipient->role->label() }}</span>
                                </td>
                                <td>
                                    <span class="lm-badge lm-badge--info">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $recipient->attendance_visible_employees_count }} Karyawan
                                    </span>
                                </td>
                                <td class="lm-actions-cell">
                                    <div class="lm-action-buttons">
                                        <a
                                            href="{{ route('hr.attendance-access.index', ['viewer' => $recipient->id]) }}"
                                            class="lm-btn-action lm-btn-action--edit"
                                            title="Lihat dan atur detail akses {{ $recipient->name }}">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="lm-empty" id="access-empty-filter" hidden>
                <div class="lm-empty-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h3 class="lm-empty-title">Penerima Akses Tidak Ditemukan</h3>
                <p class="lm-empty-desc">Tidak ada pengguna penerima akses yang cocok dengan kata kunci atau filter role Anda.</p>
            </div>
        @endif
    </div>

    {{-- Modal Pilih Pengguna Penerima Akses --}}
    <div
        class="ava-modal-backdrop"
        id="ava-grant-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="ava-grant-modal-title"
        hidden>
        <div class="ava-modal-dialog">
            <div class="ava-modal-header">
                <div class="ava-modal-header-main">
                    <div class="ava-modal-icon" aria-hidden="true">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="ava-modal-title" id="ava-grant-modal-title">Pilih Pengguna Penerima Akses</h2>
                        <p class="ava-modal-subtitle">Pilih pengguna untuk membuka halaman detail dan mengatur cakupan karyawan</p>
                    </div>
                </div>
                <button type="button" class="ava-modal-close" data-close-grant-modal aria-label="Tutup dialog">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="ava-modal-body">
                <div class="ava-modal-filters">
                    <div class="lm-search-input-wrap">
                        <svg class="lm-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="search"
                            id="ava-modal-search"
                            class="lm-search-input ava-modal-search-input"
                            placeholder="Cari nama pengguna, divisi, atau PT..."
                            autocomplete="off"
                            aria-label="Cari kandidat penerima akses">
                    </div>

                    <select id="ava-modal-role" class="lm-filter-input ava-modal-role-select" aria-label="Filter role kandidat">
                        <option value="">Semua Role</option>
                        @foreach ($candidateRoles as $roleOption)
                            <option value="{{ $roleOption['value'] }}">{{ $roleOption['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ava-candidate-list" id="ava-modal-list">
                    @forelse ($viewers as $candidate)
                        @php
                            $candidateMetaParts = array_values(array_filter([
                                $candidate->position?->name,
                                $candidate->division?->name,
                                $candidate->profile?->pt?->name,
                            ]));
                            $candidateMeta = count($candidateMetaParts) > 0 ? implode(' · ', $candidateMetaParts) : 'Pengguna Aktif';
                            $candidateSearch = mb_strtolower(implode(' ', [
                                $candidate->name,
                                $candidate->role->label(),
                                $candidateMeta,
                            ]));
                        @endphp
                        <div
                            class="ava-candidate-row"
                            data-candidate-row
                            data-search="{{ $candidateSearch }}"
                            data-role="{{ $candidate->role->value }}">
                            <div class="ava-candidate-info">
                                <div class="lm-employee-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($candidate->name, 0, 1)) }}</div>
                                <div class="ava-candidate-text">
                                    <div class="ava-candidate-title-row">
                                        <strong>{{ $candidate->name }}</strong>
                                        <span class="lm-role-chip">{{ $candidate->role->label() }}</span>
                                        @if ($candidate->attendance_visible_employees_count > 0)
                                            <span class="lm-badge lm-badge--info">{{ $candidate->attendance_visible_employees_count }} Karyawan</span>
                                        @endif
                                    </div>
                                    <small>{{ $candidateMeta }}</small>
                                </div>
                            </div>

                            <a
                                href="{{ route('hr.attendance-access.index', ['viewer' => $candidate->id]) }}"
                                class="lm-btn-action lm-btn-action--edit">
                                @if ($candidate->attendance_visible_employees_count > 0)
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Detail
                                @else
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Atur Akses
                                @endif
                            </a>
                        </div>
                    @empty
                        <div class="ava-candidate-empty">
                            Tidak ada pengguna non-HR aktif yang tersedia.
                        </div>
                    @endforelse
                </div>

                <div class="ava-candidate-empty" id="ava-modal-empty" hidden>
                    Tidak ada pengguna yang cocok dengan pencarian Anda.
                </div>
            </div>

            <div class="ava-modal-footer">
                <button type="button" class="ava-modal-btn-secondary" data-close-grant-modal>Tutup</button>
            </div>
        </div>
    </div>

    <script data-attendance-master>
        (() => {
            const searchInput = document.getElementById('access-live-search');
            const roleSelect = document.getElementById('access-role-filter');
            const filterPanel = document.getElementById('access-filter-panel');
            const filterToggle = document.getElementById('access-filter-toggle');
            const filterApply = document.getElementById('access-filter-apply');
            const filterReset = document.getElementById('access-filter-reset');
            const filterBadge = document.getElementById('access-filter-badge');
            const tableWrap = document.getElementById('access-table-wrap');
            const emptyFilter = document.getElementById('access-empty-filter');
            const recipientRows = [...document.querySelectorAll('[data-recipient-row]')];

            function applyRecipientFilter() {
                const keyword = (searchInput?.value || '').trim().toLocaleLowerCase();
                const selectedRole = roleSelect?.value || '';
                let visibleIndex = 0;

                recipientRows.forEach((row) => {
                    const matchesSearch = !keyword || (row.dataset.search || '').includes(keyword);
                    const matchesRole = !selectedRole || row.dataset.role === selectedRole;
                    const isVisible = matchesSearch && matchesRole;
                    row.hidden = !isVisible;
                    if (isVisible) {
                        visibleIndex += 1;
                        const numCell = row.querySelector('[data-recipient-number]');
                        if (numCell) numCell.textContent = String(visibleIndex);
                    }
                });

                const hasFilter = Boolean(keyword || selectedRole);
                if (filterReset) filterReset.style.display = hasFilter ? 'inline-flex' : 'none';
                if (filterBadge) filterBadge.style.display = selectedRole ? 'inline-flex' : 'none';

                if (recipientRows.length > 0) {
                    if (tableWrap) tableWrap.hidden = visibleIndex === 0;
                    if (emptyFilter) emptyFilter.hidden = visibleIndex > 0;
                }
            }

            filterToggle?.addEventListener('click', () => {
                if (!filterPanel) return;
                const isHidden = filterPanel.style.display === 'none';
                filterPanel.style.display = isHidden ? '' : 'none';
                filterToggle.classList.toggle('active', isHidden);
                filterToggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            });

            searchInput?.addEventListener('input', applyRecipientFilter);
            roleSelect?.addEventListener('change', applyRecipientFilter);
            filterApply?.addEventListener('click', applyRecipientFilter);
            filterReset?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (roleSelect) roleSelect.value = '';
                applyRecipientFilter();
            });

            // Grant Modal Logic
            const modal = document.getElementById('ava-grant-modal');
            const modalSearch = document.getElementById('ava-modal-search');
            const modalRole = document.getElementById('ava-modal-role');
            const modalEmpty = document.getElementById('ava-modal-empty');
            const candidateRows = [...document.querySelectorAll('[data-candidate-row]')];

            function filterCandidates() {
                const keyword = (modalSearch?.value || '').trim().toLocaleLowerCase();
                const role = modalRole?.value || '';
                let visibleCount = 0;

                candidateRows.forEach((row) => {
                    const matches = (!keyword || (row.dataset.search || '').includes(keyword))
                        && (!role || row.dataset.role === role);
                    row.hidden = !matches;
                    if (matches) visibleCount += 1;
                });

                if (modalEmpty && candidateRows.length > 0) {
                    modalEmpty.hidden = visibleCount > 0;
                }
            }

            function openGrantModal() {
                if (!modal) return;
                modal.hidden = false;
                if (document.body) document.body.style.overflow = 'hidden';
                if (modalSearch) {
                    modalSearch.value = '';
                    setTimeout(() => modalSearch.focus(), 30);
                }
                if (modalRole) modalRole.value = '';
                filterCandidates();
            }

            function closeGrantModal() {
                if (!modal) return;
                modal.hidden = true;
                if (document.body) document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-open-grant-modal]').forEach((btn) => {
                btn.addEventListener('click', openGrantModal);
            });
            document.querySelectorAll('[data-close-grant-modal]').forEach((btn) => {
                btn.addEventListener('click', closeGrantModal);
            });
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) closeGrantModal();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal && !modal.hidden) {
                    closeGrantModal();
                }
            });

            modalSearch?.addEventListener('input', filterCandidates);
            modalRole?.addEventListener('change', filterCandidates);
        })();
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

        /* ========================================== */
        /* FLASH MESSAGES (selaras /hr/leave/master)  */
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
            box-sizing: border-box;
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
        /* CTA BAR & BACK BUTTON                      */
        /* ========================================== */
        .lm-cta-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .lm-cta-bar--split {
            justify-content: space-between;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 38px;
            padding: 0 14px 0 11px;
            background: var(--white, #FFFFFF);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            color: var(--text-muted, #6B7280);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
        }
        .back-btn:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .back-btn svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .back-btn:hover svg {
            transform: translateX(-2px);
        }
        .back-btn-text {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }
        .lm-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
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
            font-family: inherit;
            box-sizing: border-box;
        }
        .lm-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.35);
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* STATS SUMMARY                              */
        /* ========================================== */
        .lm-stats {
            display: flex;
            align-items: center;
            background: var(--white, #FFFFFF);
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
        }
        .lm-stat {
            flex: 1;
            text-align: center;
        }
        .lm-stat-value {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary-dark, #0A3D62);
            line-height: 1.2;
        }
        .lm-stat-value--success { color: #16a34a; }
        .lm-stat-value--info { color: var(--primary, #145DA0); }
        .lm-stat-value--neutral { color: var(--text-muted, #6B7280); }
        .lm-stat-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }
        .lm-stat-divider {
            width: 1px;
            height: 32px;
            background: var(--border-light, #E5E7EB);
            flex-shrink: 0;
        }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .lm-filter-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
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
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }
        .lm-search-input {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
            transition: all 0.2s ease;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
        }
        .lm-search-input::placeholder { color: var(--text-light, #9CA3AF); }
        .lm-search-input:focus {
            border-color: var(--primary, #145DA0);
            background: var(--white, #FFFFFF);
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
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
            font-family: inherit;
            box-sizing: border-box;
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
            border: 1.5px solid var(--border-light, #E5E7EB);
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #374151);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            font-family: inherit;
            position: relative;
            box-sizing: border-box;
        }
        .lm-btn-toggle-filter:hover,
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
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            box-sizing: border-box;
        }
        .lm-filter-panel {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border-light, #E5E7EB);
        }
        .lm-filter-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
        }
        .lm-filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .lm-filter-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .lm-filter-input {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
            transition: all 0.2s ease;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
        }
        .lm-filter-input:focus {
            border-color: var(--primary, #145DA0);
            background: var(--white, #FFFFFF);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1);
        }
        .lm-filter-actions {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .lm-btn-apply {
            padding: 10px 18px;
            background: var(--primary, #145DA0);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .lm-btn-apply:hover { background: var(--primary-dark, #0A3D62); }

        /* ========================================== */
        /* TABLE CARD & DATA TABLE                    */
        /* ========================================== */
        .lm-table-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
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
            background: var(--gray-50, #F5F7FA);
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            white-space: nowrap;
        }
        .lm-data-table tbody tr {
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            transition: background 0.15s ease;
        }
        .lm-data-table tbody tr:last-child { border-bottom: none; }
        .lm-data-table tbody tr:hover { background: rgba(20, 93, 160, 0.02); }
        .lm-data-table td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 13px;
            color: var(--text-primary, #111827);
        }
        .lm-cell-center { text-align: center; }
        .lm-cell-muted { color: var(--text-muted, #6B7280); font-size: 12px; }

        /* ========================================== */
        /* EMPLOYEE / ENTITY CELL                     */
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
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        .lm-employee-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .lm-employee-name {
            font-weight: 600;
            color: var(--text-primary, #111827);
            font-size: 13px;
            line-height: 1.3;
        }
        .lm-employee-detail {
            font-size: 11px;
            color: var(--text-muted, #6B7280);
            line-height: 1.35;
        }

        /* ========================================== */
        /* BADGES & CHIPS                             */
        /* ========================================== */
        .lm-role-chip {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 6px;
            background: var(--gray-100, #F3F4F6);
            color: var(--text-secondary, #374151);
            border: 1px solid var(--border-light, #E5E7EB);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }
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
            color: var(--primary, #145DA0);
            border: 1px solid rgba(20, 93, 160, 0.16);
        }

        /* ========================================== */
        /* ACTION BUTTONS                             */
        /* ========================================== */
        .lm-actions-cell {
            text-align: right;
            white-space: nowrap;
        }
        thead .lm-actions-cell { text-align: right; }
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
            box-sizing: border-box;
        }
        .lm-btn-action--edit {
            background: rgba(20, 93, 160, 0.06);
            color: var(--primary, #145DA0);
            border: 1px solid rgba(20, 93, 160, 0.15);
        }
        .lm-btn-action--edit:hover {
            background: rgba(20, 93, 160, 0.12);
            border-color: var(--primary, #145DA0);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .lm-empty {
            padding: 48px 24px;
            text-align: center;
            box-sizing: border-box;
        }
        .lm-empty[hidden] { display: none; }
        .lm-empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 14px;
            border-radius: 16px;
            background: rgba(20, 93, 160, 0.06);
            color: var(--primary, #145DA0);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lm-empty-title {
            margin: 0 0 6px;
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary, #111827);
        }
        .lm-empty-desc {
            margin: 0 auto;
            max-width: 420px;
            font-size: 13px;
            color: var(--text-muted, #6B7280);
            line-height: 1.5;
        }
        .lm-empty-actions {
            margin-top: 18px;
        }

        /* ========================================== */
        /* MODAL PILIH PENGGUNA PENERIMA AKSES        */
        /* ========================================== */
        @keyframes avaModalPop {
            0% { opacity: 0; transform: scale(0.97) translateY(8px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        .ava-modal-backdrop {
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
            box-sizing: border-box;
        }
        .ava-modal-backdrop[hidden] {
            display: none;
        }
        .ava-modal-dialog {
            width: min(640px, 100%);
            max-height: min(86vh, 680px);
            background: var(--white, #FFFFFF);
            border-radius: 18px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22);
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            animation: avaModalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .ava-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            box-sizing: border-box;
        }
        .ava-modal-header-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .ava-modal-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(10, 61, 98, 0.2);
        }
        .ava-modal-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #111827);
            line-height: 1.25;
        }
        .ava-modal-subtitle {
            margin: 3px 0 0;
            font-size: 12px;
            color: var(--text-muted, #6B7280);
            line-height: 1.4;
        }
        .ava-modal-close {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            border: 1px solid var(--border-light, #E5E7EB);
            background: var(--gray-50, #F5F7FA);
            color: var(--text-muted, #6B7280);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .ava-modal-close:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--white, #FFFFFF);
        }
        .ava-modal-body {
            padding: 16px 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 0;
            flex: 1;
            box-sizing: border-box;
        }
        .ava-modal-filters {
            display: grid;
            grid-template-columns: 1fr 180px;
            gap: 10px;
        }
        .ava-modal-search-input {
            padding: 9px 12px 9px 38px;
            font-size: 13px;
            border-radius: 10px;
        }
        .ava-modal-role-select {
            padding: 9px 12px;
        }
        .ava-candidate-list {
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
            overflow-y: auto;
            max-height: 360px;
            background: var(--white, #FFFFFF);
        }
        .ava-candidate-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            transition: background 0.15s ease;
            box-sizing: border-box;
        }
        .ava-candidate-row:last-child { border-bottom: none; }
        .ava-candidate-row:hover { background: rgba(20, 93, 160, 0.03); }
        .ava-candidate-row[hidden] { display: none; }
        .ava-candidate-info {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .ava-candidate-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .ava-candidate-title-row {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .ava-candidate-title-row strong {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }
        .ava-candidate-text small {
            font-size: 11.5px;
            color: var(--text-muted, #6B7280);
        }
        .ava-candidate-empty {
            padding: 28px 16px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted, #6B7280);
        }
        .ava-candidate-empty[hidden] { display: none; }
        .ava-modal-footer {
            padding: 12px 20px;
            border-top: 1px solid var(--border-light, #E5E7EB);
            display: flex;
            justify-content: flex-end;
            box-sizing: border-box;
        }
        .ava-modal-btn-secondary {
            padding: 9px 16px;
            border-radius: 10px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #374151);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .ava-modal-btn-secondary:hover {
            background: var(--gray-100, #E5E7EB);
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (max-width: 640px) {
            .lm-cta-bar--split {
                flex-direction: column;
                align-items: stretch;
            }
            .lm-cta-bar--split .back-btn,
            .lm-cta-bar--split .lm-btn-primary {
                justify-content: center;
                width: 100%;
            }
            .lm-stats {
                padding: 12px 10px;
            }
            .lm-stat-value {
                font-size: 18px;
            }
            .lm-stat-label {
                font-size: 10px;
            }
            .lm-search-row {
                flex-wrap: wrap;
            }
            .lm-search-input-wrap {
                flex: 1 1 100%;
            }
            .lm-filter-grid,
            .ava-modal-filters {
                grid-template-columns: 1fr;
            }
            .ava-candidate-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .ava-candidate-row .lm-btn-action {
                align-self: flex-end;
            }
        }
    </style>
</x-app>
