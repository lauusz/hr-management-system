<x-atk-app title="Input Pengambilan Manual ATK">
    @php($selectedUser = $users->firstWhere('id', (int) old('user_id')))

    <style>
        .atk-manual-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }
        .atk-manual-head-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .atk-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 32px;
            padding: 0 10px;
            border-radius: 7px;
            border: 1px solid var(--atk-line);
            background: #FFFFFF;
            color: var(--atk-muted);
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 600;
            transition: all .14s ease;
        }
        .atk-back-btn:hover {
            color: var(--atk-text);
            border-color: #CBD5E1;
            background: #F8FAFC;
        }

        .atk-manual-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 16px;
            align-items: start;
        }
        .atk-manual-panel {
            background: #FFFFFF;
            border: 1px solid var(--atk-line);
            border-radius: 10px;
            overflow: hidden;
        }
        .atk-manual-side {
            position: sticky;
            top: 16px;
            display: grid;
            gap: 14px;
        }
        .atk-manual-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid #F1F5F9;
            background: #F8FAFC;
        }
        .atk-manual-panel-title {
            margin: 0;
            font-size: 12.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #475569;
        }
        .atk-manual-panel-body {
            padding: 14px 16px;
        }

        /* Left Column: Item Search & List */
        .atk-manual-item-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--atk-line);
            background: #FFFFFF;
            flex-wrap: wrap;
        }
        .atk-manual-search-wrap {
            position: relative;
            flex: 1 1 220px;
        }
        .atk-manual-search-wrap svg {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .atk-manual-item-search {
            padding-left: 30px;
            height: 34px;
            font-size: 12.5px;
            background: #F8FAFC;
        }
        .atk-manual-item-search:focus {
            background: #FFFFFF;
        }
        .atk-manual-filter-pills {
            display: inline-flex;
            padding: 2px;
            border-radius: 7px;
            background: #F1F5F9;
            border: 1px solid var(--atk-line);
        }
        .atk-manual-filter-btn {
            border: 0;
            background: transparent;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--atk-muted);
            cursor: pointer;
        }
        .atk-manual-filter-btn.is-active {
            background: #FFFFFF;
            color: var(--atk-text);
            box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
        }

        .atk-manual-item-list {
            display: grid;
            max-height: 540px;
            overflow-y: auto;
            divide-y: 1px solid #F1F5F9;
        }
        .atk-manual-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 16px;
            border-bottom: 1px solid #F1F5F9;
            transition: background .12s ease;
        }
        .atk-manual-item:last-child {
            border-bottom: 0;
        }
        .atk-manual-item:hover {
            background: #F8FAFC;
        }
        .atk-manual-item.is-selected {
            background: #FAF8FF;
        }
        .atk-manual-item-copy {
            min-width: 0;
            flex: 1;
        }
        .atk-manual-item-copy strong {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--atk-text);
            line-height: 1.35;
        }
        .atk-manual-item-copy small {
            display: inline-block;
            margin-top: 2px;
            color: var(--atk-muted);
            font-size: 11.5px;
        }

        /* Compact Stepper */
        .atk-stepper {
            display: inline-grid;
            grid-template-columns: 30px 44px 30px;
            height: 32px;
            border: 1px solid var(--atk-line);
            border-radius: 7px;
            background: #FFFFFF;
            overflow: hidden;
            flex-shrink: 0;
        }
        .atk-manual-item.is-selected .atk-stepper {
            border-color: var(--atk-primary);
        }
        .atk-stepper-btn,
        .atk-stepper-input {
            min-width: 0;
            height: 100%;
            border: 0;
            background: transparent;
            color: var(--atk-text);
            font: inherit;
            font-size: 12.5px;
            font-weight: 700;
            text-align: center;
            padding: 0;
        }
        .atk-stepper-btn {
            color: #475569;
            cursor: pointer;
            transition: background .12s ease;
        }
        .atk-stepper-btn:hover:not(:disabled) {
            background: #F1F5F9;
            color: var(--atk-text);
        }
        .atk-stepper-btn:first-child { border-right: 1px solid #E2E8F0; }
        .atk-stepper-btn:last-child { border-left: 1px solid #E2E8F0; }
        .atk-stepper-input {
            width: 100%;
            appearance: textfield;
            -moz-appearance: textfield;
        }
        .atk-stepper-input::-webkit-inner-spin-button,
        .atk-stepper-input::-webkit-outer-spin-button {
            margin: 0;
            appearance: none;
        }
        .atk-stepper-btn:disabled {
            color: #CBD5E1;
            cursor: not-allowed;
        }

        /* Right Column: User Picker */
        .atk-manual-user-results {
            display: grid;
            max-height: 185px;
            gap: 4px;
            margin-top: 8px;
            overflow-y: auto;
            padding-right: 2px;
        }
        .atk-manual-user-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 7px 10px;
            border: 1px solid #F1F5F9;
            border-radius: 7px;
            background: #F8FAFC;
            color: var(--atk-text);
            font: inherit;
            text-align: left;
            cursor: pointer;
            transition: all .12s ease;
        }
        .atk-manual-user-option:hover,
        .atk-manual-user-option:focus-visible {
            border-color: #CBD5E1;
            background: #F1F5F9;
            outline: none;
        }
        .atk-manual-user-option strong {
            font-size: 12.5px;
            font-weight: 600;
        }
        .atk-manual-user-option small {
            color: var(--atk-muted);
            font-size: 11px;
            text-align: right;
        }
        .atk-manual-user-option[hidden],
        .atk-manual-item[hidden],
        .atk-manual-not-found[hidden] {
            display: none !important;
        }
        .atk-manual-user-selected {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid var(--atk-line);
            border-radius: 8px;
            background: #F8FAFC;
        }
        .atk-manual-user-selected[hidden] {
            display: none !important;
        }
        .atk-manual-user-selected span > * {
            display: block;
        }
        .atk-manual-user-selected small {
            color: #64748B;
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .atk-manual-user-selected strong {
            margin: 2px 0 1px;
            font-size: 13px;
            color: var(--atk-text);
        }
        .atk-manual-user-selected span span {
            color: var(--atk-muted);
            font-size: 11.5px;
        }

        /* Summary Box in Right Panel */
        .atk-manual-picked-list {
            display: grid;
            gap: 6px;
            max-height: 170px;
            overflow-y: auto;
            margin-bottom: 12px;
        }
        .atk-manual-picked-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 6px 8px;
            border-radius: 6px;
            background: #F8FAFC;
            font-size: 12px;
        }
        .atk-manual-picked-row span {
            color: var(--atk-text);
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .atk-manual-picked-row strong {
            color: var(--atk-primary);
            font-weight: 700;
            white-space: nowrap;
        }
        .atk-manual-picked-empty {
            padding: 10px;
            border-radius: 7px;
            background: #F8FAFC;
            border: 1px dashed #E2E8F0;
            color: #94A3B8;
            font-size: 11.5px;
            text-align: center;
            margin-bottom: 12px;
        }
        .atk-manual-not-found {
            padding: 16px;
            text-align: center;
            color: var(--atk-muted);
            font-size: 12px;
            margin: 0;
        }
        .atk-manual-error {
            margin: 6px 0 0;
            font-size: 11.5px;
            color: #DC2626;
            font-weight: 600;
        }

        @media (max-width: 860px) {
            .atk-manual-layout {
                grid-template-columns: 1fr;
            }
            .atk-manual-side {
                position: static;
            }
        }
    </style>

    <div class="atk-manual-head">
        <div class="atk-manual-head-left">
            <a class="atk-back-btn" href="{{ route('v2.atk.admin.requests.index') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
            <h1 class="atk-title">Input Pengambilan Manual</h1>
        </div>
    </div>

    <form class="atk-manual-request-form" method="POST" action="{{ route('v2.atk.admin.requests.manual.store') }}">
        @csrf

        <div class="atk-manual-layout">
            {{-- Kolom Kiri: Daftar Barang --}}
            <section class="atk-manual-panel atk-manual-section">
                <div class="atk-manual-item-toolbar">
                    <div class="atk-manual-search-wrap">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input class="atk-input atk-manual-item-search" type="search" placeholder="Cari nama barang ATK..." autocomplete="off" aria-label="Cari barang ATK" data-manual-item-search>
                    </div>
                    <div class="atk-manual-filter-pills">
                        <button type="button" class="atk-manual-filter-btn is-active" data-manual-filter="all">Semua ({{ $items->count() }})</button>
                        <button type="button" class="atk-manual-filter-btn" data-manual-filter="selected">Dipilih (<span data-manual-selected-count>0</span>)</button>
                    </div>
                </div>

                @error('quantities')
                    <div style="padding: 8px 16px 0">
                        <p class="atk-manual-error">{{ $message }}</p>
                    </div>
                @enderror

                <div class="atk-manual-item-list" data-manual-item-list>
                    @forelse($items as $item)
                        @php($quantity = (int) old('quantities.'.$item->id, 0))
                        <div class="atk-manual-item {{ $quantity > 0 ? 'is-selected' : '' }}" data-manual-item data-item-name="{{ $item->name }}" data-item-unit="{{ $item->unit_name }}">
                            <span class="atk-manual-item-copy">
                                <strong>{{ $item->name }}</strong>
                                <small>Stok tersedia: {{ $item->stock_qty }} {{ $item->unit_name }}</small>
                            </span>
                            <div class="atk-stepper" data-manual-stepper>
                                <button class="atk-stepper-btn" type="button" data-manual-stepper-decrease aria-label="Kurangi jumlah {{ $item->name }}" aria-disabled="{{ $quantity <= 0 ? 'true' : 'false' }}" @disabled($quantity <= 0)>&minus;</button>
                                <input class="atk-stepper-input" type="number" min="0" max="{{ $item->stock_qty }}" name="quantities[{{ $item->id }}]" value="{{ $quantity }}" inputmode="numeric" aria-label="Jumlah {{ $item->name }}" data-manual-stepper-input>
                                <button class="atk-stepper-btn" type="button" data-manual-stepper-increase aria-label="Tambah jumlah {{ $item->name }}" aria-disabled="{{ $quantity >= $item->stock_qty ? 'true' : 'false' }}" @disabled($quantity >= $item->stock_qty)>+</button>
                            </div>
                        </div>
                    @empty
                        <div class="atk-empty" style="padding:24px">Belum ada barang aktif.</div>
                    @endforelse
                </div>
                <p class="atk-manual-not-found" data-manual-item-empty hidden>Barang tidak ditemukan.</p>
            </section>

            {{-- Kolom Kanan: Pemohon & Ringkasan Submit --}}
            <aside class="atk-manual-side">
                <section class="atk-manual-panel atk-manual-section">
                    <div class="atk-manual-panel-head">
                        <h2 class="atk-manual-panel-title">1. Pilih Pemohon</h2>
                    </div>
                    <div class="atk-manual-panel-body">
                        <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') }}" required data-manual-user-id>

                        <div data-manual-user-picker @if($selectedUser) hidden @endif>
                            <label class="atk-label" for="manual_user_search" style="display:none">Cari nama</label>
                            <input class="atk-input" id="manual_user_search" type="search" placeholder="Ketik nama karyawan atau PT..." autocomplete="off" data-manual-user-search>
                            <div class="atk-manual-user-results" data-manual-user-results>
                                @foreach($users as $user)
                                    <button class="atk-manual-user-option" type="button" data-manual-user-option data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-pt="{{ $user->profile?->pt?->name ?? '' }}">
                                        <strong>{{ $user->name }}</strong>
                                        <small>{{ $user->profile?->pt?->name ?? 'PT belum tersedia' }}</small>
                                    </button>
                                @endforeach
                            </div>
                            <p class="atk-manual-not-found" data-manual-user-empty hidden>Nama tidak ditemukan.</p>
                        </div>

                        <div class="atk-manual-user-selected" data-manual-user-selected @if(!$selectedUser) hidden @endif>
                            <span>
                                <small>Pemohon Terpilih</small>
                                <strong data-manual-selected-name>{{ $selectedUser?->name }}</strong>
                                <span data-manual-selected-pt>{{ $selectedUser?->profile?->pt?->name }}</span>
                            </span>
                            <button class="atk-btn atk-btn-muted atk-btn-sm" type="button" data-manual-user-change>Ganti</button>
                        </div>

                        @error('user_id')
                            <p class="atk-manual-error">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                <section class="atk-manual-panel">
                    <div class="atk-manual-panel-head">
                        <h2 class="atk-manual-panel-title">2. Ringkasan &amp; Simpan</h2>
                        <span style="font-size:11.5px;font-weight:700;color:var(--atk-primary)" id="manualSummaryBadge">0 item</span>
                    </div>
                    <div class="atk-manual-panel-body">
                        <div class="atk-manual-picked-empty" id="manualPickedEmpty">
                            Belum ada barang yang dipilih dari daftar di sebelah kiri.
                        </div>
                        <div class="atk-manual-picked-list" id="manualPickedList" hidden></div>

                        <div style="margin-bottom:12px">
                            <label class="atk-label" for="notes">Catatan <span style="font-weight:400;color:#94A3B8">(Opsional)</span></label>
                            <textarea class="atk-textarea" id="notes" name="notes" rows="2" maxlength="1000" placeholder="Keterangan pengambilan manual..." style="min-height:56px">{{ old('notes') }}</textarea>
                        </div>

                        <button class="atk-btn atk-btn-primary" type="submit" style="width:100%;height:36px">
                            Buat dan Review
                        </button>
                    </div>
                </section>
            </aside>
        </div>
    </form>

    <script>
        (function () {
            const userId = document.querySelector('[data-manual-user-id]');
            const userPicker = document.querySelector('[data-manual-user-picker]');
            const userSearch = document.querySelector('[data-manual-user-search]');
            const userResults = document.querySelector('[data-manual-user-results]');
            const userEmpty = document.querySelector('[data-manual-user-empty]');
            const selected = document.querySelector('[data-manual-user-selected]');
            const selectedName = document.querySelector('[data-manual-selected-name]');
            const selectedPt = document.querySelector('[data-manual-selected-pt]');
            const changeUser = document.querySelector('[data-manual-user-change]');
            const itemSearch = document.querySelector('[data-manual-item-search]');
            const itemList = document.querySelector('[data-manual-item-list]');
            const itemEmpty = document.querySelector('[data-manual-item-empty]');
            const filterBtns = document.querySelectorAll('[data-manual-filter]');
            const selectedCountEls = document.querySelectorAll('[data-manual-selected-count]');
            const pickedListEl = document.getElementById('manualPickedList');
            const pickedEmptyEl = document.getElementById('manualPickedEmpty');
            const summaryBadgeEl = document.getElementById('manualSummaryBadge');

            let currentFilterMode = 'all';

            function selectUser(option) {
                if (!option || !userId || !selected || !userPicker) return;
                userId.value = option.dataset.userId;
                selectedName.textContent = option.dataset.userName;
                selectedPt.textContent = option.dataset.userPt || 'PT belum tersedia';
                userPicker.hidden = true;
                selected.hidden = false;
            }

            userResults?.addEventListener('click', function (event) {
                selectUser(event.target.closest('[data-manual-user-option]'));
            });

            userSearch?.addEventListener('input', function () {
                const keyword = userSearch.value.trim().toLocaleLowerCase('id');
                let visible = 0;
                userResults.querySelectorAll('[data-manual-user-option]').forEach(function (option) {
                    const match = option.textContent.toLocaleLowerCase('id').includes(keyword);
                    option.hidden = !match;
                    if (match) visible += 1;
                });
                userEmpty.hidden = visible !== 0;
            });

            changeUser?.addEventListener('click', function () {
                userId.value = '';
                selected.hidden = true;
                userPicker.hidden = false;
                userSearch.value = '';
                userResults.querySelectorAll('[data-manual-user-option]').forEach(function (option) { option.hidden = false; });
                userEmpty.hidden = true;
                userSearch.focus();
            });

            function applyItemFilter() {
                if (!itemList) return;
                const keyword = (itemSearch?.value || '').trim().toLocaleLowerCase('id');
                let visible = 0;
                itemList.querySelectorAll('[data-manual-item]').forEach(function (item) {
                    const input = item.querySelector('[data-manual-stepper-input]');
                    const qty = Number(input?.value || 0);
                    const matchesText = item.textContent.toLocaleLowerCase('id').includes(keyword);
                    const matchesMode = currentFilterMode === 'all' || qty > 0;
                    const show = matchesText && matchesMode;
                    item.hidden = !show;
                    if (show) visible += 1;
                });
                if (itemEmpty) itemEmpty.hidden = visible !== 0;
            }

            itemSearch?.addEventListener('input', applyItemFilter);

            filterBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    currentFilterMode = btn.getAttribute('data-manual-filter') || 'all';
                    filterBtns.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
                    applyItemFilter();
                });
            });

            function updateSummary() {
                if (!itemList) return;
                let pickedKinds = 0;
                let totalQty = 0;
                const rowsHtml = [];

                itemList.querySelectorAll('[data-manual-item]').forEach(function (item) {
                    const input = item.querySelector('[data-manual-stepper-input]');
                    const qty = Number(input?.value || 0);
                    item.classList.toggle('is-selected', qty > 0);
                    if (qty > 0) {
                        pickedKinds += 1;
                        totalQty += qty;
                        const name = item.getAttribute('data-item-name') || '';
                        const unit = item.getAttribute('data-item-unit') || 'pcs';
                        const safeName = name.replace(/[<>&]/g, function (c) {
                            return { '<': '&lt;', '>': '&gt;', '&': '&amp;' }[c];
                        });
                        rowsHtml.push('<div class="atk-manual-picked-row"><span>' + safeName + '</span><strong>' + qty + ' ' + unit + '</strong></div>');
                    }
                });

                selectedCountEls.forEach(function (el) { el.textContent = String(pickedKinds); });
                if (summaryBadgeEl) {
                    summaryBadgeEl.textContent = pickedKinds + ' jenis (' + totalQty + ' item)';
                }
                if (pickedListEl && pickedEmptyEl) {
                    if (pickedKinds > 0) {
                        pickedListEl.innerHTML = rowsHtml.join('');
                        pickedListEl.hidden = false;
                        pickedEmptyEl.hidden = true;
                    } else {
                        pickedListEl.innerHTML = '';
                        pickedListEl.hidden = true;
                        pickedEmptyEl.hidden = false;
                    }
                }
            }

            function syncStepper(stepper) {
                const input = stepper?.querySelector('[data-manual-stepper-input]');
                const decrease = stepper?.querySelector('[data-manual-stepper-decrease]');
                const increase = stepper?.querySelector('[data-manual-stepper-increase]');
                if (!input || !decrease || !increase) return;
                const value = Number(input.value);
                const min = Number(input.min);
                const max = Number(input.max);
                const decreaseDisabled = !Number.isFinite(value) || value <= min;
                const increaseDisabled = !Number.isFinite(value) || value >= max;
                decrease.disabled = decreaseDisabled;
                increase.disabled = increaseDisabled;
                decrease.setAttribute('aria-disabled', decreaseDisabled ? 'true' : 'false');
                increase.setAttribute('aria-disabled', increaseDisabled ? 'true' : 'false');
            }

            itemList?.addEventListener('click', function (event) {
                const button = event.target.closest('[data-manual-stepper-decrease], [data-manual-stepper-increase]');
                if (!button) return;
                const stepper = button.closest('[data-manual-stepper]');
                const input = stepper.querySelector('[data-manual-stepper-input]');
                button.hasAttribute('data-manual-stepper-decrease') ? input.stepDown() : input.stepUp();
                syncStepper(stepper);
                updateSummary();
                if (currentFilterMode === 'selected') applyItemFilter();
            });

            itemList?.addEventListener('change', function (event) {
                if (!event.target.matches('[data-manual-stepper-input]')) return;
                const input = event.target;
                const value = Number(input.value);
                const min = Number(input.min);
                const max = Number(input.max);
                if (!Number.isFinite(value) || value < min) input.value = min;
                if (Number(input.value) > max) input.value = max;
                syncStepper(input.closest('[data-manual-stepper]'));
                updateSummary();
                if (currentFilterMode === 'selected') applyItemFilter();
            });

            itemList?.querySelectorAll('[data-manual-stepper]').forEach(syncStepper);
            updateSummary();
        })();
    </script>
</x-atk-app>
