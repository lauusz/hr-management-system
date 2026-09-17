<x-atk-app title="Input Pengambilan Manual ATK">
    @php($selectedUser = $users->firstWhere('id', (int) old('user_id')))

    <div class="atk-header">
        <div>
            <h1 class="atk-title">Input Pengambilan Manual</h1>
            <p class="atk-subtitle">Pilih nama dan barang yang diambil, lalu lanjutkan ke review.</p>
        </div>
        <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.requests.index') }}">Kembali</a>
    </div>

    <form class="atk-card atk-manual-request-form" method="POST" action="{{ route('v2.atk.admin.requests.manual.store') }}">
        @csrf

        <section class="atk-manual-section">
            <label class="atk-label" for="manual_user_search">Cari nama</label>
            <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') }}" required data-manual-user-id>
            <div data-manual-user-picker @if($selectedUser) hidden @endif>
                <input class="atk-input" id="manual_user_search" type="search" placeholder="Ketik nama atau PT..." autocomplete="off" data-manual-user-search>
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
                    <small>Nama terpilih</small>
                    <strong data-manual-selected-name>{{ $selectedUser?->name }}</strong>
                    <span data-manual-selected-pt>{{ $selectedUser?->profile?->pt?->name }}</span>
                </span>
                <button class="atk-btn atk-btn-muted" type="button" data-manual-user-change>Ganti</button>
            </div>
            @error('user_id')
                <p class="atk-manual-error">{{ $message }}</p>
            @enderror
        </section>

        <div>
            <label class="atk-label" for="notes">Catatan</label>
            <textarea class="atk-textarea" id="notes" name="notes" maxlength="1000" placeholder="Opsional">{{ old('notes') }}</textarea>
        </div>

        <section class="atk-manual-section">
            <h2 class="atk-section-title">Barang yang Diambil</h2>
            @error('quantities')
                <p class="atk-manual-error">{{ $message }}</p>
            @enderror
            <input class="atk-input atk-manual-item-search" type="search" placeholder="Cari barang ATK..." autocomplete="off" aria-label="Cari barang ATK" data-manual-item-search>
            <div class="atk-manual-item-list" data-manual-item-list>
                @forelse($items as $item)
                    @php($quantity = (int) old('quantities.'.$item->id, 0))
                    <div class="atk-manual-item" data-manual-item>
                        <span class="atk-manual-item-copy">
                            <strong>{{ $item->name }}</strong>
                            <small>Stok {{ $item->stock_qty }} {{ $item->unit_name }}</small>
                        </span>
                        <div class="atk-stepper" data-manual-stepper>
                            <button class="atk-stepper-btn" type="button" data-manual-stepper-decrease aria-label="Kurangi jumlah {{ $item->name }}" aria-disabled="{{ $quantity <= 0 ? 'true' : 'false' }}" @disabled($quantity <= 0)>&minus;</button>
                            <input class="atk-stepper-input" type="number" min="0" max="{{ $item->stock_qty }}" name="quantities[{{ $item->id }}]" value="{{ $quantity }}" inputmode="numeric" aria-label="Jumlah {{ $item->name }}" data-manual-stepper-input>
                            <button class="atk-stepper-btn" type="button" data-manual-stepper-increase aria-label="Tambah jumlah {{ $item->name }}" aria-disabled="{{ $quantity >= $item->stock_qty ? 'true' : 'false' }}" @disabled($quantity >= $item->stock_qty)>+</button>
                        </div>
                    </div>
                @empty
                    <div class="atk-empty">Belum ada barang aktif.</div>
                @endforelse
            </div>
            <p class="atk-manual-not-found" data-manual-item-empty hidden>Barang tidak ditemukan.</p>
        </section>

        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Buat dan Review</button>
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

            itemSearch?.addEventListener('input', function () {
                const keyword = itemSearch.value.trim().toLocaleLowerCase('id');
                let visible = 0;
                itemList.querySelectorAll('[data-manual-item]').forEach(function (item) {
                    const match = item.textContent.toLocaleLowerCase('id').includes(keyword);
                    item.hidden = !match;
                    if (match) visible += 1;
                });
                itemEmpty.hidden = visible !== 0;
            });

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
            });

            itemList?.querySelectorAll('[data-manual-stepper]').forEach(syncStepper);
        })();
    </script>

    <style>
        .atk-manual-request-form,
        .atk-manual-item-list { display: grid; gap: 14px; }
        .atk-manual-section { min-width: 0; }
        .atk-manual-user-results {
            display: grid;
            max-height: 260px;
            gap: 7px;
            margin-top: 8px;
            overflow-y: auto;
        }
        .atk-manual-user-option {
            display: grid;
            min-height: 52px;
            gap: 2px;
            padding: 9px 12px;
            border: 1px solid var(--atk-border-soft);
            border-radius: 12px;
            background: var(--atk-surface);
            color: var(--atk-text);
            font: inherit;
            text-align: left;
            cursor: pointer;
        }
        .atk-manual-user-option:hover,
        .atk-manual-user-option:focus-visible {
            border-color: var(--atk-primary);
            background: var(--atk-primary-softer);
            outline: none;
        }
        .atk-manual-user-option strong { font-size: 12px; }
        .atk-manual-user-option small { color: var(--atk-muted); font-size: 10px; }
        .atk-manual-user-option[hidden],
        .atk-manual-item[hidden],
        .atk-manual-not-found[hidden] { display: none; }
        .atk-manual-user-selected {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 64px;
            padding: 10px 12px;
            border: 1px solid #DDD2FA;
            border-radius: 14px;
            background: var(--atk-primary-softer);
        }
        .atk-manual-user-selected[hidden] { display: none; }
        .atk-manual-user-selected span > * { display: block; }
        .atk-manual-user-selected small,
        .atk-manual-user-selected span span { color: var(--atk-muted); font-size: 10px; }
        .atk-manual-user-selected strong { margin: 2px 0; font-size: 13px; }
        .atk-manual-item-search { margin-bottom: 10px; }
        .atk-manual-item {
            display: grid;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--atk-border-soft);
            border-radius: 12px;
        }
        .atk-manual-item-copy strong,
        .atk-manual-item-copy small { display: block; }
        .atk-manual-item-copy small { margin-top: 4px; color: var(--atk-muted); font-size: 11px; }
        .atk-manual-not-found,
        .atk-manual-error { margin: 8px 0 0; font-size: 11px; }
        .atk-manual-not-found { color: var(--atk-muted); }
        .atk-manual-error { color: var(--error); font-weight: 600; }
        .atk-stepper {
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr) 44px;
            width: 100%;
            min-height: 44px;
            border: 1px solid var(--atk-border);
            border-radius: 11px;
            background: var(--atk-surface);
        }
        .atk-stepper-btn,
        .atk-stepper-input {
            min-width: 0;
            min-height: 44px;
            border: 0;
            background: transparent;
            color: var(--atk-text);
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }
        .atk-stepper-btn { padding: 0; color: var(--atk-primary-dark); cursor: pointer; }
        .atk-stepper-btn:first-child { border-right: 1px solid var(--atk-border-soft); }
        .atk-stepper-btn:last-child { border-left: 1px solid var(--atk-border-soft); }
        .atk-stepper-input { width: 100%; padding: 0 2px; appearance: textfield; -moz-appearance: textfield; }
        .atk-stepper-input::-webkit-inner-spin-button,
        .atk-stepper-input::-webkit-outer-spin-button { margin: 0; appearance: none; }
        .atk-stepper-btn:focus-visible,
        .atk-stepper-input:focus-visible { position: relative; z-index: 1; outline: 2px solid var(--atk-primary); outline-offset: 2px; }
        .atk-stepper-btn:disabled { color: var(--atk-muted); cursor: not-allowed; opacity: .5; }
        @media (max-width: 639px) {
            .atk-header,
            .atk-header .atk-btn,
            .atk-manual-request-form > .atk-actions,
            .atk-manual-request-form > .atk-actions .atk-btn { width: 100%; }
        }
        @media (min-width: 640px) {
            .atk-manual-item { grid-template-columns: minmax(0, 1fr) 180px; align-items: center; }
        }
    </style>
</x-atk-app>
