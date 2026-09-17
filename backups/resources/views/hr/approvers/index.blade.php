<x-app title="Daftar Approver">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Daftar Approver</h1>
                <p class="section-subtitle">Pilih nama untuk melihat detail user yang di-approve.</p>
            </div>
        </div>
    </x-slot>

    <div class="approval-map-summary">
        <div class="approval-map-stat">
            <span>Managers</span>
            <strong>{{ $managers->count() }}</strong>
        </div>
        <div class="approval-map-stat">
            <span>Supervisors</span>
            <strong>{{ $supervisors->count() }}</strong>
        </div>
        <div class="approval-map-stat">
            <span>Approvers</span>
            <strong>{{ $approvers->count() }}</strong>
        </div>
    </div>

    <section class="approval-map-section" data-manager-layout>
        <div class="approval-map-section__header">
            <div>
                <h2>Managers</h2>
                <p>Manager yang memiliki mapping user.</p>
            </div>
            <span>{{ $managers->count() }} manager</span>
        </div>
        <div class="approval-map-list-panel">
            @forelse($managers as $manager)
                @php($employeeCount = $managerEmployees->get($manager->id, collect())->count())
                @include('hr.approvers.partials.approver-row', [
                    'approver' => $manager,
                    'employeeCount' => $employeeCount,
                    'roleLabel' => 'MANAGER',
                    'roleClass' => 'approval-map-badge--manager',
                ])
            @empty
                <div class="approval-map-empty-list">Belum ada Manager yang memiliki mapping user.</div>
            @endforelse
        </div>
    </section>

    <section class="approval-map-section" data-supervisor-layout>
        <div class="approval-map-section__header">
            <div>
                <h2>Supervisors</h2>
                <p>Supervisor yang memiliki mapping user.</p>
            </div>
            <span>{{ $supervisors->count() }} supervisor</span>
        </div>
        <div class="approval-map-list-panel">
            @forelse($supervisors as $supervisor)
                @php($employeeCount = $supervisorEmployees->get($supervisor->id, collect())->count())
                @include('hr.approvers.partials.approver-row', [
                    'approver' => $supervisor,
                    'employeeCount' => $employeeCount,
                    'roleLabel' => 'SUPERVISOR',
                    'roleClass' => 'approval-map-badge--supervisor',
                ])
            @empty
                <div class="approval-map-empty-list">Belum ada Supervisor yang memiliki mapping user.</div>
            @endforelse
        </div>
    </section>

    <section class="approval-map-section" data-approver-layout>
        <div class="approval-map-section__header">
            <div>
                <h2>Approvers</h2>
                <p>Approver khusus cuti/izin yang memiliki mapping user.</p>
            </div>
            <div class="approval-map-section__actions">
                <span>{{ $approvers->count() }} approver</span>
                <button type="button" class="approval-map-add-button" data-modal-target="approval-create-approver-modal">Tambah</button>
            </div>
        </div>
        <div class="approval-map-list-panel">
            @forelse($approvers as $approver)
                @php($employeeCount = $approverEmployees->get($approver->id, collect())->count())
                @include('hr.approvers.partials.approver-row', [
                    'approver' => $approver,
                    'employeeCount' => $employeeCount,
                    'roleLabel' => 'APPROVER',
                    'roleClass' => 'approval-map-badge--approver',
                ])
            @empty
                <div class="approval-map-empty-list">Belum ada Approver yang memiliki mapping user.</div>
            @endforelse
        </div>
    </section>

    <div id="approval-create-approver-modal" class="modal-backdrop approval-index-modal-backdrop" style="display:none;" data-approval-create-modal>
        <div class="approval-index-modal-card" role="dialog" aria-modal="true" aria-labelledby="approval-create-approver-title">
            <div class="approval-index-modal-header">
                <div>
                    <h2 id="approval-create-approver-title">Tambah Approver</h2>
                    <p>Cari user aktif yang akan diberikan hak approver cuti/izin.</p>
                </div>
                <button type="button" class="approval-index-modal-close" data-modal-close aria-label="Tutup modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('hr.approvers.store') }}" class="approval-index-modal-form" data-approver-create-form>
                @csrf
                <label for="approver-create-search">User</label>
                <div class="approval-index-modal-autocomplete">
                    <input type="hidden" name="user_id" value="{{ old('user_id') }}" data-approver-create-selected-id>
                    <input
                        id="approver-create-search"
                        type="search"
                        class="approval-index-modal-search"
                        placeholder="Ketik nama user..."
                        autocomplete="off"
                        data-approver-create-search
                        data-search-url="{{ route('hr.approvers.search') }}"
                    >
                    <ul id="approver-create-suggestions" class="approver-create-suggestions" role="listbox" aria-label="Saran calon approver"></ul>
                </div>
                @error('user_id')
                    <p class="approval-index-modal-error">{{ $message }}</p>
                @enderror
                <div class="approval-index-modal-actions">
                    <button type="button" class="approval-index-modal-button approval-index-modal-button--secondary" data-modal-close>Batal</button>
                    <button type="submit" class="approval-index-modal-button approval-index-modal-button--primary">Simpan</button>
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
        .approval-map-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .approval-map-stat {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            padding: 14px 16px;
        }
        .approval-map-stat span {
            display: block;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .approval-map-stat strong {
            display: block;
            margin-top: 6px;
            color: var(--text-primary);
            font-size: 1.35rem;
            line-height: 1;
        }
        .approval-map-section {
            margin-bottom: 18px;
        }
        .approval-map-section__header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 10px;
        }
        .approval-map-section__header h2 {
            margin: 0;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0;
        }
        .approval-map-section__header p {
            margin: 3px 0 0;
            color: var(--text-muted);
            font-size: 0.8125rem;
            line-height: 1.4;
        }
        .approval-map-section__header span {
            flex-shrink: 0;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 700;
        }
        .approval-map-section__actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .approval-map-add-button {
            border: 0;
            border-radius: 8px;
            background: var(--primary);
            color: var(--white);
            cursor: pointer;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 800;
            line-height: 1;
            padding: 9px 11px;
            text-decoration: none;
        }
        .approval-map-add-button:hover {
            background: var(--primary-dark);
            color: var(--white);
        }
        .approval-map-list-panel {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            overflow: hidden;
        }
        .approval-map-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-top: 1px solid var(--border-light);
            color: inherit;
            text-decoration: none;
        }
        .approval-map-row:first-child {
            border-top: 0;
        }
        .approval-map-row:hover {
            background: var(--gray-50);
        }
        .approval-map-person {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .approval-map-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-weight: 800;
            font-size: 0.8125rem;
            text-transform: uppercase;
        }
        .approval-map-name {
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .approval-map-meta,
        .approval-map-count {
            color: var(--text-muted);
            font-size: 0.75rem;
            line-height: 1.35;
        }
        .approval-map-count {
            font-weight: 700;
            white-space: nowrap;
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
        .approval-map-empty-list {
            color: var(--text-muted);
            font-size: 0.8125rem;
            line-height: 1.5;
            padding: 14px 16px;
        }
        .approval-index-modal-backdrop {
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 1100;
        }
        .approval-index-modal-card {
            width: min(680px, 100%);
            max-height: calc(100dvh - 36px);
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
            padding: 18px;
        }
        .approval-index-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }
        .approval-index-modal-header h2 {
            margin: 0;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 800;
        }
        .approval-index-modal-header p {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 0.8125rem;
            line-height: 1.45;
        }
        .approval-index-modal-close {
            border: 0;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            font-family: inherit;
            font-size: 1.35rem;
            line-height: 1;
        }
        .approval-index-modal-form {
            display: grid;
            gap: 8px;
            min-height: 0;
        }
        .approval-index-modal-form label {
            color: var(--text-primary);
            font-size: 0.8125rem;
            font-weight: 800;
        }
        .approval-index-modal-autocomplete {
            position: relative;
        }
        .approval-index-modal-search {
            width: 100%;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            background: var(--white);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.875rem;
            padding: 10px 12px;
        }
        .approval-index-modal-search:focus-visible,
        .approval-index-modal-close:focus-visible,
        .approval-index-modal-button:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
        .approver-create-suggestions {
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
        .approver-create-suggestions.is-open {
            display: block;
        }
        .approver-create-suggestion {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: 10px;
            border-radius: 8px;
            cursor: pointer;
            padding: 10px 12px;
        }
        .approver-create-suggestion:hover,
        .approver-create-suggestion.is-selected {
            background: var(--gray-50);
        }
        .approver-create-suggestion__avatar {
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
        .approver-create-suggestion__body {
            display: grid;
            gap: 2px;
            min-width: 0;
        }
        .approver-create-suggestion__name {
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .approver-create-suggestion__meta,
        .approver-create-suggestion-empty {
            color: var(--text-muted);
            font-size: 0.75rem;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .approver-create-suggestion-empty {
            padding: 12px;
            text-align: center;
        }
        .approval-index-modal-error {
            margin: 0;
            color: #b91c1c;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .approval-index-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 16px;
        }
        .approval-index-modal-button {
            border: 1px solid var(--border-light);
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 10px 12px;
        }
        .approval-index-modal-button--secondary {
            background: var(--white);
            color: var(--text-muted);
        }
        .approval-index-modal-button--primary {
            border-color: var(--primary);
            background: var(--primary);
            color: var(--white);
        }
        @media (max-width: 900px) {
            .approval-map-summary {
                grid-template-columns: 1fr;
            }
            .approval-map-section__header {
                align-items: flex-start;
                flex-direction: column;
            }
        }
        @media (max-width: 620px) {
            .approval-map-row {
                grid-template-columns: 1fr;
            }
            .approval-map-badge,
            .approval-map-count {
                width: max-content;
            }
            .approval-index-modal-actions {
                flex-direction: column;
            }
            .approval-index-modal-button {
                width: 100%;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(function() {
            const $form = $('[data-approver-create-form]');
            const $search = $('[data-approver-create-search]');
            const $selectedId = $('[data-approver-create-selected-id]');
            const $list = $('#approver-create-suggestions');
            let activeIndex = -1;
            let debounceTimer = null;
            let request = null;

            if ($search.length === 0 || $list.length === 0) return;

            function closeList() {
                $list.removeClass('is-open').empty();
                activeIndex = -1;
            }

            function visibleItems() {
                return $list.find('.approver-create-suggestion');
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
                        .addClass('approver-create-suggestion-empty')
                        .text('Tidak ada user yang cocok.')
                        .appendTo($list);
                    $list.addClass('is-open');
                    return;
                }

                users.forEach(function(user) {
                    const $item = $('<li>')
                        .addClass('approver-create-suggestion')
                        .attr('role', 'option')
                        .data('id', user.id)
                        .data('name', user.name || '');

                    $('<span>')
                        .addClass('approver-create-suggestion__avatar')
                        .text((user.name || '?').charAt(0))
                        .appendTo($item);

                    const $body = $('<span>').addClass('approver-create-suggestion__body').appendTo($item);
                    $('<span>').addClass('approver-create-suggestion__name').text(user.name || '').appendTo($body);
                    $('<span>').addClass('approver-create-suggestion__meta').text(user.meta || '').appendTo($body);

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

                $list.html('<li class="approver-create-suggestion-empty">Mencari...</li>').addClass('is-open');
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
</x-app>
