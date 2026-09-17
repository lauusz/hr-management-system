<x-app title="Detail Approver">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Approver</h1>
                <p class="section-subtitle">User yang saat ini di-approve oleh {{ $approver->name }}.</p>
            </div>
        </div>
    </x-slot>

    {{-- Back Button --}}
    <button type="button" class="back-btn" onclick="window.location.href='{{ route('hr.approvers.index') }}';">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="back-btn-text">Kembali</span>
    </button>

    <section class="approval-detail-profile">
        <div class="approval-detail-person">
            <div class="approval-detail-avatar">{{ substr($approver->name, 0, 1) }}</div>
            <div>
                <div class="approval-detail-name">{{ $approver->name }}</div>
                <div class="approval-detail-meta">
                    {{ $approver->position->name ?? 'Tanpa jabatan' }} · {{ $approver->division->name ?? 'Tanpa divisi' }}
                    @if($approver->profile?->pt)
                        · {{ $approver->profile->pt->name }}
                    @endif
                </div>
            </div>
        </div>
        <div class="approval-detail-actions">
            <span class="approval-map-badge {{ $roleClass }}">{{ $roleLabel }}</span>
            <button type="button" class="approval-detail-button" data-modal-target="approval-add-user-modal">Tambah User</button>
            @if($roleLabel === 'APPROVER')
                <form method="POST" action="{{ route('hr.approvers.revoke', $approver) }}" class="approval-detail-action-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="approval-detail-button approval-detail-button--danger" onclick="return confirm('Cabut hak Approver untuk user ini?')">Cabut Hak Approver</button>
                </form>
            @endif
        </div>
    </section>

    <section class="approval-detail-section">
        <div class="approval-detail-section__header">
            <div>
                <h2>Daftar User</h2>
                <p>User yang masuk dalam mapping approval ini.</p>
            </div>
            <span>{{ $employees->count() }} {{ \Illuminate\Support\Str::plural('user', $employees->count()) }}</span>
        </div>

        <div class="approval-detail-list">
            @forelse($employees as $employee)
                <div class="approval-detail-row">
                    <div>
                        <div class="approval-detail-row__name">{{ $employee->name }}</div>
                        <div class="approval-detail-row__meta">
                            {{ $employee->position->name ?? 'Tanpa jabatan' }} · {{ $employee->division->name ?? 'Tanpa divisi' }}
                            @if($employee->profile?->pt)
                                · {{ $employee->profile->pt->name }}
                            @endif
                        </div>
                    </div>
                    <div class="approval-detail-row__actions">
                        @if($employee->pending_initial_count)
                            <span class="approval-detail-row__status">{{ $employee->pending_initial_count }} pending</span>
                        @endif
                        @if($employee->pending_hr_count)
                            <span class="approval-detail-row__status">{{ $employee->pending_hr_count }} pending HR</span>
                        @endif
                        <button
                            type="button"
                            class="approval-detail-row__delete"
                            data-modal-target="approval-delete-user-modal"
                            data-approval-delete-open
                            data-delete-action="{{ route('hr.approvers.users.destroy', [$approver, $employee]) }}"
                            data-delete-name="{{ $employee->name }}"
                        >Hapus</button>
                    </div>
                </div>
            @empty
                <div class="approval-detail-empty">Belum ada user pada mapping ini.</div>
            @endforelse
        </div>
    </section>

    <div id="approval-add-user-modal" class="modal-backdrop approval-modal-backdrop" style="display:none;" data-approval-add-modal>
        <div class="approval-modal-card" role="dialog" aria-modal="true" aria-labelledby="approval-add-user-title">
            <div class="approval-modal-header">
                <div>
                    <h2 id="approval-add-user-title">Tambah User</h2>
                    <p>Pilih user aktif untuk masuk ke mapping {{ $roleLabel }} {{ $approver->name }}.</p>
                </div>
                <button type="button" class="approval-modal-close" data-modal-close aria-label="Tutup modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('hr.approvers.users.store', $approver) }}" class="approval-modal-form" data-approver-user-form>
                @csrf
                <label for="approver-modal-user-search">User</label>
                <div class="approval-modal-autocomplete">
                    <input type="hidden" name="user_id" value="{{ old('user_id') }}" data-approver-user-selected-id>
                    <input
                        id="approver-modal-user-search"
                        type="search"
                        class="approval-modal-search"
                        placeholder="Ketik nama user..."
                        autocomplete="off"
                        data-approver-user-search
                        data-search-url="{{ route('hr.approvers.users.search', $approver) }}"
                    >
                    <ul id="approver-modal-user-suggestions" class="approver-modal-suggestions" role="listbox" aria-label="Saran user approval"></ul>
                </div>
                @error('user_id')
                    <p class="approval-modal-error">{{ $message }}</p>
                @enderror
                <div class="approval-modal-actions">
                    <button type="button" class="approval-modal-button approval-modal-button--secondary" data-modal-close>Batal</button>
                    <button type="submit" class="approval-modal-button approval-modal-button--primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="approval-delete-user-modal" class="modal-backdrop approval-modal-backdrop" style="display:none;" data-approval-delete-modal>
        <div class="approval-modal-card approval-modal-card--sm" role="dialog" aria-modal="true" aria-labelledby="approval-delete-user-title">
            <div class="approval-modal-header">
                <div>
                    <h2 id="approval-delete-user-title">Hapus User</h2>
                    <p>Hapus <strong data-approval-delete-name>user ini</strong> dari mapping approval?</p>
                </div>
                <button type="button" class="approval-modal-close" data-modal-close aria-label="Tutup modal">&times;</button>
            </div>
            <form method="POST" action="#" data-approval-delete-form>
                @csrf
                @method('DELETE')
                <div class="approval-modal-actions">
                    <button type="button" class="approval-modal-button approval-modal-button--secondary" data-modal-close>Batal</button>
                    <button type="submit" class="approval-modal-button approval-modal-button--danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <style>
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
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: 0;
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
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            margin: 0 0 14px;
            padding: 0 12px 0 10px;
            background: var(--white, #fff);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            color: var(--text-secondary, #374151);
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .back-btn:hover,
        .back-btn:focus-visible {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
            outline: none;
        }
        .back-btn:active {
            transform: translateY(1px);
        }
        .back-btn:disabled,
        .back-btn[aria-disabled="true"] {
            cursor: not-allowed;
            opacity: 0.55;
        }
        .back-btn:hover svg,
        .back-btn:focus-visible svg {
            transform: translateX(-2px);
        }
        .back-btn svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        @media (prefers-reduced-motion: reduce) {
            .back-btn,
            .back-btn svg {
                transition-duration: 0.01ms;
            }
            .back-btn:hover svg,
            .back-btn:focus-visible svg,
            .back-btn:active {
                transform: none;
            }
        }
        .approval-detail-profile,
        .approval-detail-list {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            overflow: hidden;
        }
        .approval-detail-profile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px;
            margin-bottom: 18px;
        }
        .approval-detail-person {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .approval-detail-avatar {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-weight: 800;
            font-size: 1rem;
            text-transform: uppercase;
        }
        .approval-detail-name {
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .approval-detail-meta {
            margin-top: 2px;
            color: var(--text-muted);
            font-size: 0.78rem;
            line-height: 1.35;
        }
        .approval-detail-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }
        .approval-detail-action-form {
            margin: 0;
        }
        .approval-detail-button {
            border: 1px solid var(--border-light);
            border-radius: 8px;
            background: var(--gray-50);
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 800;
            padding: 8px 10px;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
        }
        a.approval-detail-button {
            color: var(--text-secondary);
            cursor: pointer;
        }
        button.approval-detail-button:hover,
        a.approval-detail-button:hover {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary-dark);
        }
        .approval-detail-button--danger {
            color: #b91c1c;
        }
        .approval-detail-button--danger:hover {
            background: rgba(239, 68, 68, 0.10);
            border-color: rgba(239, 68, 68, 0.18);
        }
        button.approval-detail-button:disabled {
            cursor: not-allowed;
        }
        .approval-map-badge {
            flex-shrink: 0;
            border-radius: 9999px;
            padding: 5px 9px;
            font-size: 0.6875rem;
            font-weight: 800;
            line-height: 1;
        }
        .approval-map-badge--manager {
            background: rgba(212, 175, 55, 0.16);
            color: #7a5a00;
        }
        .approval-map-badge--supervisor {
            background: rgba(20, 93, 160, 0.09);
            color: var(--primary-dark);
        }
        .approval-map-badge--approver {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }
        .approval-detail-section__header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 10px;
        }
        .approval-detail-section__header h2 {
            margin: 0;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0;
        }
        .approval-detail-section__header p {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 0.8125rem;
            line-height: 1.4;
        }
        .approval-detail-section__header span {
            flex-shrink: 0;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 700;
        }
        .approval-detail-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-top: 1px solid var(--border-light);
        }
        .approval-detail-row:first-child {
            border-top: 0;
        }
        .approval-detail-row__name {
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .approval-detail-row__meta {
            margin-top: 2px;
            color: var(--text-muted);
            font-size: 0.75rem;
            line-height: 1.35;
        }
        .approval-detail-row__actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }
        .approval-detail-row__actions form {
            margin: 0;
        }
        .approval-detail-row__status,
        .approval-detail-row__delete {
            display: inline-flex;
            border-radius: 9999px;
            padding: 4px 8px;
            font-size: 0.6875rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .approval-detail-row__status {
            background: rgba(245, 158, 11, 0.13);
            color: #92400e;
        }
        .approval-detail-row__delete {
            border: 0;
            background: rgba(239, 68, 68, 0.10);
            color: #b91c1c;
            cursor: pointer;
            font-family: inherit;
        }
        .approval-detail-row__delete:hover {
            background: rgba(239, 68, 68, 0.16);
        }
        .approval-detail-empty {
            color: var(--text-muted);
            font-size: 0.8125rem;
            line-height: 1.5;
            padding: 14px 16px;
        }
        .approval-modal-backdrop {
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 1100;
        }
        .approval-modal-card {
            width: min(680px, 100%);
            max-height: calc(100dvh - 36px);
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
            padding: 18px;
        }
        .approval-modal-card--sm {
            width: min(420px, 100%);
        }
        .approval-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }
        .approval-modal-header h2 {
            margin: 0;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 800;
        }
        .approval-modal-header p {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 0.8125rem;
            line-height: 1.45;
        }
        .approval-modal-close {
            border: 0;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            font-family: inherit;
            font-size: 1.35rem;
            line-height: 1;
        }
        .approval-modal-form {
            display: grid;
            gap: 8px;
            min-height: 0;
        }
        .approval-modal-form label {
            color: var(--text-primary);
            font-size: 0.8125rem;
            font-weight: 800;
        }
        .approval-modal-autocomplete {
            position: relative;
        }
        .approval-modal-search {
            width: 100%;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            background: var(--white);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.875rem;
            padding: 10px 12px;
        }
        .approval-modal-search:focus-visible,
        .approval-modal-close:focus-visible,
        .approval-modal-button:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
        .approver-modal-suggestions {
            position: static;
            display: none;
            max-height: min(46dvh, 420px);
            overflow: auto;
            margin: 6px 0 0;
            padding: 6px;
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
            list-style: none;
        }
        .approver-modal-suggestions.is-open {
            display: block;
        }
        .approver-modal-suggestion {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: 10px;
            border-radius: 8px;
            cursor: pointer;
            padding: 10px 12px;
        }
        .approver-modal-suggestion:hover,
        .approver-modal-suggestion.is-selected {
            background: var(--gray-50);
        }
        .approver-modal-suggestion__avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.8125rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .approver-modal-suggestion__body {
            display: grid;
            gap: 2px;
            min-width: 0;
        }
        .approver-modal-suggestion__name {
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .approver-modal-suggestion__meta,
        .approver-modal-suggestion-empty {
            color: var(--text-muted);
            font-size: 0.75rem;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .approver-modal-suggestion-empty {
            padding: 12px;
            text-align: center;
        }
        .approval-modal-error {
            margin: 0;
            color: #b91c1c;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .approval-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 16px;
        }
        .approval-modal-button {
            border: 1px solid var(--border-light);
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 10px 12px;
        }
        .approval-modal-button--secondary {
            background: var(--white);
            color: var(--text-muted);
        }
        .approval-modal-button--primary {
            border-color: var(--primary);
            background: var(--primary);
            color: var(--white);
        }
        .approval-modal-button--danger {
            border-color: rgba(239, 68, 68, 0.2);
            background: rgba(239, 68, 68, 0.10);
            color: #b91c1c;
        }
        .approval-modal-button:disabled {
            cursor: not-allowed;
            opacity: 0.55;
        }
        @media (max-width: 720px) {
            .approval-detail-profile,
            .approval-detail-section__header {
                align-items: flex-start;
                flex-direction: column;
            }
            .approval-detail-actions {
                justify-content: flex-start;
            }
            .approval-detail-row {
                grid-template-columns: 1fr;
            }
            .approval-modal-actions {
                flex-direction: column;
            }
            .approval-modal-button {
                width: 100%;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(function() {
            const $form = $('[data-approver-user-form]');
            const $search = $('[data-approver-user-search]');
            const $selectedId = $('[data-approver-user-selected-id]');
            const $list = $('#approver-modal-user-suggestions');
            let activeIndex = -1;
            let debounceTimer = null;
            let request = null;

            if ($search.length === 0 || $list.length === 0) return;

            function closeList() {
                $list.removeClass('is-open').empty();
                activeIndex = -1;
            }

            function visibleItems() {
                return $list.find('.approver-modal-suggestion');
            }

            function updateActiveItem() {
                const $items = visibleItems();
                $items.removeClass('is-selected');

                if (activeIndex < 0 || activeIndex >= $items.length) return;

                const $active = $items.eq(activeIndex);
                $active.addClass('is-selected');
                $active[0].scrollIntoView({ block: 'nearest' });
            }

            function chooseItem($item) {
                $selectedId.val($item.data('id'));
                $search.val($item.data('name'));
                closeList();
            }

            function renderUsers(users) {
                $list.empty();
                activeIndex = -1;

                if (!users || users.length === 0) {
                    $('<li>')
                        .addClass('approver-modal-suggestion-empty')
                        .text('Tidak ada user yang cocok.')
                        .appendTo($list);
                    $list.addClass('is-open');
                    return;
                }

                users.forEach(function(user) {
                    const $item = $('<li>')
                        .addClass('approver-modal-suggestion')
                        .attr('role', 'option')
                        .data('id', user.id)
                        .data('name', user.name || '');

                    $('<span>')
                        .addClass('approver-modal-suggestion__avatar')
                        .text((user.name || '?').charAt(0))
                        .appendTo($item);

                    const $body = $('<span>').addClass('approver-modal-suggestion__body').appendTo($item);
                    $('<span>').addClass('approver-modal-suggestion__name').text(user.name || '').appendTo($body);
                    $('<span>').addClass('approver-modal-suggestion__meta').text(user.meta || '').appendTo($body);

                    $item.on('click', function() {
                        chooseItem($(this));
                    });

                    $item.appendTo($list);
                });

                $list.addClass('is-open');
            }

            function searchUsers(query) {
                const searchUrl = $search.data('search-url');
                if (!searchUrl) return;

                if (request) request.abort();

                $list.html('<li class="approver-modal-suggestion-empty">Mencari...</li>').addClass('is-open');
                request = $.getJSON(searchUrl, { q: query })
                    .done(renderUsers)
                    .fail(function(xhr, status) {
                        if (status !== 'abort') closeList();
                    });
            }

            $search.on('input', function() {
                const query = $.trim($search.val());
                $selectedId.val('');
                clearTimeout(debounceTimer);

                if (query.length < 1) {
                    closeList();
                    return;
                }

                debounceTimer = setTimeout(function() {
                    searchUsers(query);
                }, 300);
            });

            $search.on('keydown', function(event) {
                const $items = visibleItems();
                const total = $items.length;

                if (!$list.hasClass('is-open')) {
                    if (event.key === 'Enter' && !$selectedId.val()) event.preventDefault();
                    return;
                }

                if (event.key === 'ArrowDown' && total > 0) {
                    event.preventDefault();
                    activeIndex = (activeIndex + 1) % total;
                    updateActiveItem();
                } else if (event.key === 'ArrowUp' && total > 0) {
                    event.preventDefault();
                    activeIndex = (activeIndex - 1 + total) % total;
                    updateActiveItem();
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    if (activeIndex >= 0 && $items.eq(activeIndex).length) chooseItem($items.eq(activeIndex));
                    else if (total === 1) chooseItem($items.eq(0));
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    closeList();
                }
            });

            $form.on('submit', function(event) {
                if ($selectedId.val()) return;

                event.preventDefault();
                if (window.showToast) {
                    window.showToast('Pilih user dari hasil pencarian terlebih dahulu.', 'warning');
                }
                $search.focus();
            });

            $(document).on('click', function(event) {
                if (!$search.is(event.target) && !$list.is(event.target) && $list.has(event.target).length === 0) {
                    closeList();
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForm = document.querySelector('[data-approval-delete-form]');
            const deleteName = document.querySelector('[data-approval-delete-name]');

            document.querySelectorAll('[data-approval-delete-open]').forEach(function(button) {
                button.addEventListener('click', function() {
                    if (deleteForm) deleteForm.action = button.dataset.deleteAction || '#';
                    if (deleteName) deleteName.textContent = button.dataset.deleteName || 'user ini';
                });
            });
        });
    </script>
</x-app>
