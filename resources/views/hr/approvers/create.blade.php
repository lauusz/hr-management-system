<x-app title="Tambah Approver">
    <x-slot name="header">
        <div class="approval-create-header">
            <a href="{{ route('hr.approvers.index') }}" class="approval-create-back">Kembali</a>
            <div class="section-header-inline">
                <div class="section-icon icon-navy">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="section-title">Tambah Approver</h1>
                    <p class="section-subtitle">Pilih user aktif yang akan diberi akses sebagai Approver cuti/izin.</p>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $selectedCandidate = $candidates->firstWhere('id', (int) old('user_id'));
    @endphp

    <form method="POST" action="{{ route('hr.approvers.store') }}" class="approval-create-panel" data-approver-form>
        @csrf

        <div class="approval-create-field">
            <label for="approver-search">Cari nama user</label>
            <div class="approval-create-autocomplete">
                <input type="hidden" name="user_id" value="{{ old('user_id') }}" data-approver-selected-id>
                <input
                    id="approver-search"
                    type="search"
                    class="approval-create-search"
                    placeholder="Ketik nama user..."
                    value="{{ $selectedCandidate?->name }}"
                    autocomplete="off"
                    data-approver-search
                >
                <ul id="approver-suggestions" class="approver-suggestions" role="listbox" aria-label="Saran Approver">
                    @foreach($candidates as $candidate)
                        @php
                            $candidateMeta = collect([
                                $candidate->position->name ?? 'Tanpa jabatan',
                                $candidate->division->name ?? 'Tanpa divisi',
                                $candidate->profile?->pt?->name,
                            ])->filter()->implode(' · ');
                        @endphp
                        <li
                            class="approver-suggestion-item"
                            role="option"
                            data-approver-option
                            data-id="{{ $candidate->id }}"
                            data-name="{{ $candidate->name }}"
                            data-search="{{ \Illuminate\Support\Str::lower($candidate->name.' '.$candidateMeta) }}"
                        >
                            <span class="approver-suggestion-avatar">{{ substr($candidate->name, 0, 1) }}</span>
                            <span class="approver-suggestion-body">
                                <span class="approver-suggestion-name">{{ $candidate->name }}</span>
                                <span class="approver-suggestion-meta">{{ $candidateMeta }}</span>
                            </span>
                        </li>
                    @endforeach
                    <li class="approver-suggestion-empty" data-approver-no-result hidden>Tidak ada user yang cocok.</li>
                </ul>
            </div>
            @error('user_id')
                <p>{{ $message }}</p>
            @enderror
        </div>

        @if($candidates->isEmpty())
            <div class="approval-create-empty">Tidak ada user aktif yang tersedia untuk ditambahkan sebagai Approver.</div>
        @endif

        <div class="approval-create-actions">
            <a href="{{ route('hr.approvers.index') }}">Batal</a>
            <button type="submit" @disabled($candidates->isEmpty())>Simpan</button>
        </div>
    </form>

    <style>
        .approval-create-header {
            display: grid;
            gap: 8px;
        }
        .approval-create-back {
            width: max-content;
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 800;
            text-decoration: none;
        }
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
        .approval-create-panel {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            padding: 16px;
        }
        .approval-create-field {
            display: grid;
            gap: 7px;
        }
        .approval-create-field label {
            color: var(--text-primary);
            font-size: 0.8125rem;
            font-weight: 800;
        }
        .approval-create-autocomplete {
            position: relative;
        }
        .approval-create-search {
            width: 100%;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.875rem;
            padding: 10px 12px;
        }
        .approval-create-search:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.10);
            outline: none;
        }
        .approval-create-field p,
        .approval-create-empty {
            color: #b91c1c;
            font-size: 0.75rem;
            font-weight: 700;
            margin: 0;
        }
        .approval-create-empty {
            margin-top: 12px;
        }
        .approver-suggestions {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 20;
            display: none;
            max-height: 320px;
            overflow: auto;
            margin: 0;
            padding: 6px;
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
            list-style: none;
        }
        .approver-suggestions.is-open {
            display: block;
        }
        .approver-suggestion-item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: 10px;
            border-radius: 8px;
            cursor: pointer;
            padding: 10px 12px;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .approver-suggestion-item:hover,
        .approver-suggestion-item.is-selected {
            background: var(--gray-50);
        }
        .approver-suggestion-item[hidden],
        .approver-suggestion-empty[hidden] {
            display: none;
        }
        .approver-suggestion-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.8125rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .approver-suggestion-body {
            display: grid;
            gap: 2px;
            min-width: 0;
        }
        .approver-suggestion-name {
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .approver-suggestion-meta {
            color: var(--text-muted);
            font-size: 0.75rem;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .approver-suggestion-empty {
            color: var(--text-muted);
            font-size: 0.8125rem;
            line-height: 1.5;
            padding: 12px;
            text-align: center;
        }
        .approval-create-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 16px;
        }
        .approval-create-actions a,
        .approval-create-actions button {
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 800;
            line-height: 1;
            padding: 10px 12px;
            text-decoration: none;
        }
        .approval-create-actions a {
            color: var(--text-muted);
        }
        .approval-create-actions button {
            border: 0;
            background: var(--primary);
            color: var(--white);
            cursor: pointer;
        }
        .approval-create-actions button:disabled {
            background: var(--gray-200);
            color: var(--text-muted);
            cursor: not-allowed;
        }
        @media (max-width: 520px) {
            .approval-create-actions {
                align-items: stretch;
                flex-direction: column;
            }
            .approval-create-actions a,
            .approval-create-actions button {
                text-align: center;
                width: 100%;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(function() {
            const $form = $('[data-approver-form]');
            const $search = $('[data-approver-search]');
            const $selectedId = $('[data-approver-selected-id]');
            const $list = $('#approver-suggestions');
            const $options = $('[data-approver-option]');
            const $emptyState = $('[data-approver-no-result]');
            let activeIndex = -1;

            if ($search.length === 0 || $list.length === 0) return;

            function visibleOptions() {
                return $options.filter(function() {
                    return !$(this).prop('hidden');
                });
            }

            function openList() {
                $list.addClass('is-open');
            }

            function closeList() {
                $list.removeClass('is-open');
                $options.removeClass('is-selected');
                activeIndex = -1;
            }

            function updateActiveOption() {
                const $visible = visibleOptions();
                $options.removeClass('is-selected');

                if (activeIndex < 0 || activeIndex >= $visible.length) return;

                const $active = $visible.eq(activeIndex);
                $active.addClass('is-selected');
                $active[0].scrollIntoView({ block: 'nearest' });
            }

            function chooseOption($option) {
                $selectedId.val($option.data('id'));
                $search.val($option.data('name'));
                closeList();
            }

            function filterOptions() {
                const query = $.trim($search.val()).toLowerCase();
                let visibleCount = 0;

                $selectedId.val('');
                activeIndex = -1;

                if (query.length < 1) {
                    $options.prop('hidden', true).hide();
                    $emptyState.prop('hidden', true).hide();
                    closeList();
                    return;
                }

                $options.each(function() {
                    const $option = $(this);
                    const isVisible = ($option.data('search') || '').includes(query);

                    $option.prop('hidden', !isVisible).toggle(isVisible);
                    if (isVisible) visibleCount++;
                });

                $emptyState.prop('hidden', visibleCount > 0).toggle(visibleCount === 0);
                openList();
            }

            $search.on('input', filterOptions);

            $search.on('keydown', function(event) {
                if (!$list.hasClass('is-open')) {
                    if (event.key === 'Enter' && !$selectedId.val()) {
                        event.preventDefault();
                    }

                    return;
                }

                const $visible = visibleOptions();
                const total = $visible.length;

                if (event.key === 'ArrowDown' && total > 0) {
                    event.preventDefault();
                    activeIndex = (activeIndex + 1) % total;
                    updateActiveOption();
                } else if (event.key === 'ArrowUp' && total > 0) {
                    event.preventDefault();
                    activeIndex = (activeIndex - 1 + total) % total;
                    updateActiveOption();
                } else if (event.key === 'Enter') {
                    event.preventDefault();

                    if (activeIndex >= 0 && $visible.eq(activeIndex).length) {
                        chooseOption($visible.eq(activeIndex));
                    } else if (total === 1) {
                        chooseOption($visible.eq(0));
                    }
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    closeList();
                }
            });

            $options.on('click', function() {
                chooseOption($(this));
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

            $options.prop('hidden', true).hide();
            $emptyState.hide();
        });
    </script>
</x-app>
