<x-inv-pesona-app title="Daftar Ambil — Inventory Pesona">
    @php
        $totalItems = count($cart);
        $totalQty = 0;
        $hasProblems = $cartInvalid;
        foreach ($cart as $cartId => $cartQty) {
            $validId = filter_var($cartId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
            $validQty = (is_int($cartQty) || is_string($cartQty)) && filter_var($cartQty, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => min(PHP_INT_MAX, 4294967295)]]) !== false;
            $cartItem = $validId ? $items->get($cartId) : null;
            if ($validQty) {
                $totalQty += (int) $cartQty;
            }
            if (! $validId || ! $validQty || ! $cartItem || ! $cartItem->is_active || $cartItem->deleted_at !== null || (int) $cartQty < $cartItem->min_request_qty || (int) $cartQty > $cartItem->stock_qty) {
                $hasProblems = true;
            }
        }
    @endphp

    <div class="pesona-cart-toolbar">
        <div class="pesona-cart-heading">
            <div>
                <div class="pesona-cart-title-row">
                    <h1 class="pesona-title">Daftar Ambil</h1>
                    @if($totalItems > 0)
                        <span class="pesona-cart-count-pill" id="pesonaCartKindCount">{{ $totalItems }} barang</span>
                    @endif
                </div>
                <p class="pesona-subtitle">Pengajuan pengambilan tidak mengurangi atau mereservasi stok sebelum disetujui.</p>
            </div>
        </div>
        <a class="pesona-btn pesona-btn-secondary pesona-cart-back-btn" href="{{ route('inventory.pesona.catalog') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            <span>Tambah Barang</span>
        </a>
    </div>

    @if($legacyNotice)
        <div class="pesona-alert pesona-alert-warning" role="status">
            Daftar ambil sebelum pembaruan tidak dibawa. Silakan pilih ulang barang lama; barang yang ditambahkan setelah pembaruan tetap tersedia.
        </div>
    @endif

    @if($cartInvalid)
        <div class="pesona-alert pesona-alert-error" role="alert">
            Data daftar ambil tidak valid. Hapus barang yang bermasalah atau hubungi administrator.
        </div>
    @endif

    @if($cart === [] && ! $cartInvalid)
        <div class="pesona-card pesona-cart-empty">
            <div class="pesona-cart-empty-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#pesona-icon-cart"/>
                </svg>
            </div>
            <p class="pesona-cart-empty-title">Daftar ambil masih kosong</p>
            <p class="pesona-cart-empty-sub">Pilih barang yang diperlukan dari daftar persediaan garasi.</p>
            <a class="pesona-btn pesona-btn-primary" href="{{ route('inventory.pesona.catalog') }}">Lihat Daftar Barang</a>
        </div>
    @elseif($cart !== [])
        <div class="pesona-cart-layout">
            {{-- Panel Daftar Barang --}}
            <div class="pesona-card pesona-cart-panel">
                <div class="pesona-cart-table-wrap">
                    <table class="pesona-cart-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Jumlah Ambil</th>
                                <th>Setara</th>
                                <th style="width:1%;white-space:nowrap;text-align:right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cart as $id => $qty)
                                @php
                                    $validId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
                                    $validQty = (is_int($qty) || is_string($qty)) && filter_var($qty, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => min(PHP_INT_MAX, 4294967295)]]) !== false;
                                    $item = $validId ? $items->get($id) : null;
                                    $problem = ! $validQty || ! $item || ! $item->is_active || $item->deleted_at !== null || (int) $qty < $item->min_request_qty || (int) $qty > $item->stock_qty;
                                    $hasConversion = $item && ($item->unit_size > 1 || $item->unit_name !== $item->content_unit_name);
                                @endphp
                                <tr
                                    class="pesona-cart-row {{ $problem ? 'is-problem' : '' }}"
                                    data-unit-size="{{ $item?->unit_size ?? 1 }}"
                                    data-content-unit="{{ $item?->content_unit_name ?? ($item?->unit_name ?? '') }}"
                                >
                                    <td class="pesona-cart-item-cell">
                                        <div class="pesona-cart-item-media-wrap">
                                            @if($item?->image_path)
                                                <img class="pesona-cart-thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="Foto {{ $item->name }}" loading="lazy">
                                            @else
                                                <span class="pesona-cart-thumb pesona-cart-thumb-placeholder">PESONA</span>
                                            @endif
                                            <div class="pesona-cart-item-info">
                                                <strong class="pesona-cart-item-name">{{ $item?->name ?? 'Barang tidak tersedia' }}</strong>
                                                @if($item)
                                                    <span class="pesona-cart-item-meta">
                                                        Stok tersedia: <strong>{{ $item->stock_qty }} {{ $item->unit_name }}</strong>
                                                        · Min. ambil: {{ $item->min_request_qty }} {{ $item->unit_name }}
                                                        @if($hasConversion)
                                                            · 1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}
                                                        @endif
                                                    </span>
                                                @endif
                                                @if($problem)
                                                    <span class="pesona-cart-item-error" role="alert">
                                                        Barang atau jumlah tidak sesuai kondisi stok terbaru. Sesuaikan jumlah atau hapus sebelum mengajukan.
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="pesona-cart-qty-cell">
                                        <span class="pesona-cart-mobile-label">Jumlah Ambil</span>
                                        @if($item && $item->is_active && $item->deleted_at === null)
                                            <div class="pesona-cart-qty-wrap">
                                                <div class="pesona-stepper" data-min="{{ $item->min_request_qty }}" data-max="{{ $item->stock_qty }}">
                                                    <button class="pesona-stepper-btn pesona-stepper-minus" type="button" aria-label="Kurangi jumlah {{ $item->name }}">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                                    </button>
                                                    <form class="pesona-cart-qty-form" method="POST" action="{{ route('inventory.pesona.cart.update', $item) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input
                                                            class="pesona-stepper-input"
                                                            type="number"
                                                            name="qty"
                                                            min="{{ $item->min_request_qty }}"
                                                            max="{{ $item->stock_qty }}"
                                                            step="1"
                                                            value="{{ $validQty ? $qty : $item->min_request_qty }}"
                                                            aria-label="Jumlah {{ $item->name }} ({{ $item->unit_name }})"
                                                            required
                                                        >
                                                        <noscript><button type="submit">Ubah</button></noscript>
                                                    </form>
                                                    <button class="pesona-stepper-btn pesona-stepper-plus" type="button" aria-label="Tambah jumlah {{ $item->name }}">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                                    </button>
                                                </div>
                                                <span class="pesona-stepper-unit">{{ $item->unit_name }}</span>
                                            </div>
                                        @else
                                            <span class="pesona-cart-invalid-qty">{{ $validQty ? $qty : 'Tidak valid' }} {{ $item?->unit_name ?? '' }}</span>
                                        @endif
                                    </td>

                                    <td class="pesona-cart-equivalent-cell">
                                        <span class="pesona-cart-mobile-label">Setara</span>
                                        @if($item && $validQty)
                                            <strong class="pesona-cart-equivalent-value">
                                                {{ number_format((int) $qty * $item->unit_size, 0, ',', '.') }} {{ $item->content_unit_name }}
                                            </strong>
                                        @else
                                            <strong class="pesona-cart-equivalent-value">—</strong>
                                        @endif
                                    </td>

                                    <td class="pesona-cart-action-cell">
                                        @if($validId)
                                            <form method="POST" action="{{ route('inventory.pesona.cart.remove', ['item' => $id]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="pesona-btn pesona-cart-remove-btn" type="submit" title="Hapus {{ $item?->name ?? 'barang' }}">Hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ringkasan & Submit --}}
            <aside class="pesona-card pesona-cart-summary">
                <h2 class="pesona-cart-summary-title">Ringkasan Pengambilan</h2>
                <div class="pesona-cart-summary-stats">
                    <div class="pesona-cart-stat-row">
                        <span>Jenis Barang</span>
                        <strong>{{ $totalItems }} item</strong>
                    </div>
                    <div class="pesona-cart-stat-row">
                        <span>Total Kuantitas</span>
                        <strong id="pesonaCartTotalQty">{{ number_format($totalQty, 0, ',', '.') }}</strong>
                    </div>
                </div>

                @if($hasProblems)
                    <div class="pesona-cart-summary-warning" role="alert">
                        Terdapat barang yang jumlah atau stoknya perlu disesuaikan sebelum pengajuan dapat dikirim.
                    </div>
                @endif

                <form method="POST" action="{{ route('inventory.pesona.cart.submit') }}" class="pesona-cart-submit-form">
                    @csrf
                    <label class="pesona-label" for="pesona-cart-vehicle">Nopol Armada <span class="pesona-required" aria-hidden="true">*</span></label>
                    <select class="pesona-select" id="pesona-cart-vehicle" name="inv_pesona_vehicle_id" required @error('inv_pesona_vehicle_id') aria-invalid="true" aria-describedby="cart-vehicle-error" @enderror>
                        <option value="">Pilih Nopol</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected((string) old('inv_pesona_vehicle_id') === (string) $vehicle->id)>{{ $vehicle->nopol }}</option>
                        @endforeach
                    </select>
                    @error('inv_pesona_vehicle_id')<span class="pesona-field-error" id="cart-vehicle-error">{{ $message }}</span>@enderror
                    @if($vehicles->isEmpty())<p role="status">Belum ada nopol aktif. Hubungi admin Inventory Pesona.</p>@endif
                    <label class="pesona-label" for="pesona-cart-notes">
                        Tujuan Pengambilan <span class="pesona-required" aria-hidden="true">*</span>
                    </label>
                    <textarea
                        class="pesona-textarea pesona-cart-notes"
                        id="pesona-cart-notes"
                        name="notes"
                        rows="3"
                        maxlength="1000"
                        required
                        placeholder="Tulis tujuan pengambilan atau unit kendaraan (wajib diisi)..."
                        @error('notes') aria-invalid="true" aria-describedby="cart-notes-error" @enderror
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="pesona-field-error" id="cart-notes-error">{{ $message }}</span>
                    @enderror

                    <button class="pesona-btn pesona-btn-primary pesona-cart-submit" type="submit" @disabled($hasProblems || $vehicles->isEmpty())>
                        Ajukan Pengambilan
                    </button>
                </form>
            </aside>
        </div>
    @endif

    <style>
        /* ========== TOOLBAR (Mobile-First) ========== */
        .pesona-cart-toolbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }
        .pesona-cart-heading {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .pesona-cart-title-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pesona-cart-count-pill {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--pesona-primary);
            background: var(--pesona-primary-soft);
            border: 1px solid rgba(40, 53, 90, 0.12);
            padding: 2px 9px;
            border-radius: 999px;
        }
        .pesona-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 38px;
            padding: 0 14px;
            border: 0;
            border-radius: 10px;
            text-decoration: none;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease, box-shadow .15s ease;
        }
        .pesona-btn-primary {
            color: #FFFFFF;
            background: var(--pesona-primary);
        }
        .pesona-btn-primary:hover:not(:disabled) {
            background: var(--pesona-primary-dark);
        }
        .pesona-btn-secondary {
            color: var(--pesona-primary);
            background: var(--pesona-primary-soft);
            border: 1px solid rgba(40, 53, 90, 0.12);
        }
        .pesona-btn-secondary:hover {
            background: #E2E7F3;
        }
        .pesona-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }
        .pesona-cart-back-btn {
            height: 36px;
            min-height: 36px;
            padding: 0 12px;
            font-size: 12.5px;
            flex-shrink: 0;
        }

        .pesona-card {
            background: var(--pesona-surface);
            border: 1px solid var(--pesona-border);
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 1px 3px rgba(17, 24, 39, 0.05);
        }

        /* ========== EMPTY STATE ========== */
        .pesona-cart-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 8px;
            padding: 40px 20px;
        }
        .pesona-cart-empty-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--pesona-primary-soft);
            color: var(--pesona-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2px;
        }
        .pesona-cart-empty-title {
            margin: 0;
            font-size: 14.5px;
            font-weight: 700;
            color: var(--pesona-text);
        }
        .pesona-cart-empty-sub {
            margin: 0 0 6px;
            font-size: 12.5px;
            color: var(--pesona-muted);
        }

        /* ========== LAYOUT (Mobile-First -> Desktop) ========== */
        .pesona-cart-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            align-items: start;
        }
        .pesona-cart-panel {
            padding: 0;
            overflow: hidden;
        }
        .pesona-cart-table-wrap {
            overflow-x: auto;
        }
        .pesona-cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        .pesona-cart-table th,
        .pesona-cart-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--pesona-border-soft);
            text-align: left;
            font-size: 13px;
            vertical-align: middle;
        }
        .pesona-cart-table th {
            padding: 10px 16px;
            color: var(--pesona-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            background: var(--pesona-primary-softer);
            border-bottom: 1px solid var(--pesona-border);
        }
        .pesona-cart-table tr:last-child td {
            border-bottom: 0;
        }
        .pesona-cart-row.is-problem {
            background: #FFFBEB;
        }

        .pesona-cart-item-media-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .pesona-cart-thumb {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            border-radius: 8px;
            object-fit: contain;
            background: var(--pesona-primary-softer);
            border: 1px solid var(--pesona-border-soft);
            padding: 3px;
        }
        .pesona-cart-thumb-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .05em;
            color: #94A3B8;
        }
        .pesona-cart-item-info {
            min-width: 0;
            flex: 1;
        }
        .pesona-cart-item-name {
            display: block;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--pesona-text);
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .pesona-cart-item-meta {
            display: block;
            margin-top: 2px;
            font-size: 11.5px;
            color: var(--pesona-muted);
            line-height: 1.4;
        }
        .pesona-cart-item-error {
            display: block;
            margin-top: 5px;
            font-size: 11.5px;
            font-weight: 600;
            color: #B91C1C;
        }

        .pesona-cart-mobile-label {
            display: block;
            margin-bottom: 4px;
            color: var(--pesona-muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        /* ========== UNIFIED STEPPER ========== */
        .pesona-cart-qty-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .pesona-stepper {
            display: inline-grid;
            grid-template-columns: 32px 50px 32px;
            height: 34px;
            border: 1px solid var(--pesona-border);
            border-radius: 8px;
            background: var(--pesona-surface);
            overflow: hidden;
        }
        .pesona-stepper-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 100%;
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--pesona-primary);
            cursor: pointer;
            transition: background .12s ease, opacity .12s ease;
        }
        .pesona-stepper-minus { border-right: 1px solid var(--pesona-border-soft); }
        .pesona-stepper-plus { border-left: 1px solid var(--pesona-border-soft); }
        .pesona-stepper-btn:hover:not(:disabled) { background: var(--pesona-primary-softer); }
        .pesona-stepper-btn:active:not(:disabled) { background: var(--pesona-primary-soft); }
        .pesona-stepper-btn:disabled { opacity: .38; cursor: not-allowed; }
        .pesona-cart-qty-form {
            display: flex;
            margin: 0;
            height: 100%;
        }
        .pesona-stepper-input {
            width: 100%;
            height: 100%;
            text-align: center;
            border: 0;
            padding: 0;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            color: var(--pesona-text);
            background: transparent;
            appearance: textfield;
            -moz-appearance: textfield;
        }
        .pesona-stepper-input::-webkit-outer-spin-button,
        .pesona-stepper-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .pesona-stepper-input:focus { outline: none; }
        .pesona-stepper-unit {
            font-size: 12px;
            font-weight: 600;
            color: var(--pesona-muted);
        }
        .pesona-cart-invalid-qty {
            font-size: 12.5px;
            font-weight: 700;
            color: #B91C1C;
        }

        .pesona-cart-equivalent-value {
            font-size: 13px;
            font-weight: 700;
            color: var(--pesona-text-secondary);
            white-space: nowrap;
        }
        .pesona-cart-action-cell {
            text-align: right;
        }
        .pesona-cart-remove-btn {
            height: 30px;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            background: var(--pesona-surface);
            color: #B91C1C;
            border: 1px solid #FECACA;
        }
        .pesona-cart-remove-btn:hover {
            background: #FEF2F2;
        }

        /* ========== SUMMARY CARD ========== */
        .pesona-cart-summary {
            padding: 16px;
        }
        .pesona-cart-summary-title {
            margin: 0 0 12px;
            font-size: 14px;
            font-weight: 700;
            color: var(--pesona-text);
        }
        .pesona-cart-summary-stats {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid var(--pesona-border-soft);
        }
        .pesona-cart-stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: var(--pesona-muted);
        }
        .pesona-cart-stat-row strong {
            color: var(--pesona-text);
            font-weight: 700;
        }
        .pesona-cart-summary-warning {
            margin-bottom: 12px;
            padding: 9px 11px;
            border-radius: 8px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #B91C1C;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
        }
        .pesona-label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            color: var(--pesona-text-secondary);
            font-weight: 600;
        }
        .pesona-optional {
            font-weight: 400;
            color: var(--pesona-muted);
        }
        .pesona-required {
            color: #DC2626;
            font-weight: 700;
        }
        .pesona-select {
            width: 100%;
            height: 38px;
            min-height: 38px;
            padding: 0 12px;
            margin-bottom: 12px;
            border: 1.5px solid var(--pesona-border);
            border-radius: 10px;
            background: #FFFFFF;
            color: var(--pesona-text);
            font: inherit;
            font-size: 13px;
            box-sizing: border-box;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .pesona-select:focus {
            outline: none;
            border-color: var(--pesona-primary);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.12);
        }
        .pesona-textarea {
            width: 100%;
            min-height: 80px;
            padding: 10px 12px;
            border: 1.5px solid var(--pesona-border);
            border-radius: 10px;
            background: #FFFFFF;
            color: var(--pesona-text);
            font: inherit;
            font-size: 13px;
            resize: vertical;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .pesona-textarea:focus {
            outline: none;
            border-color: var(--pesona-primary);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.12);
        }
        .pesona-cart-notes {
            margin-bottom: 12px;
        }
        .pesona-field-error {
            display: block;
            margin: -6px 0 10px;
            font-size: 12px;
            font-weight: 600;
            color: #B91C1C;
        }
        .pesona-cart-submit {
            width: 100%;
        }

        /* Toast feedback */
        .pesona-cart-toast {
            position: fixed;
            left: 50%;
            bottom: 24px;
            transform: translate(-50%, 16px);
            max-width: calc(100vw - 32px);
            padding: 11px 16px;
            border-radius: 10px;
            background: var(--pesona-primary);
            border: 1px solid var(--pesona-primary-dark);
            color: #FFFFFF;
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(40, 53, 90, 0.3);
            opacity: 0;
            transition: opacity .2s ease, transform .2s ease;
            z-index: 1500;
            pointer-events: none;
        }
        .pesona-cart-toast.is-warning {
            background: #D97706;
            border-color: #B45309;
            box-shadow: 0 10px 25px rgba(217, 119, 6, 0.35);
        }
        .pesona-cart-toast.show {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        /* Mobile Card View (< 640px) */
        @media (max-width: 639px) {
            .pesona-cart-panel {
                padding: 0;
                border: 0;
                background: transparent;
                box-shadow: none;
            }
            .pesona-cart-table-wrap {
                overflow: visible;
                background: transparent;
            }
            .pesona-cart-table {
                display: block;
            }
            .pesona-cart-table thead {
                display: none;
            }
            .pesona-cart-table tbody {
                display: grid;
                gap: 10px;
            }
            .pesona-cart-table .pesona-cart-row {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 10px;
                padding: 12px;
                border: 1px solid var(--pesona-border);
                border-radius: 12px;
                background: var(--pesona-surface);
            }
            .pesona-cart-table .pesona-cart-row.is-problem {
                background: #FFFBEB;
                border-color: #FDE68A;
            }
            .pesona-cart-table .pesona-cart-row td {
                padding: 0;
                border: 0;
            }
            .pesona-cart-item-cell {
                grid-column: 1;
            }
            .pesona-cart-action-cell {
                grid-column: 2;
                align-self: start;
            }
            .pesona-cart-qty-cell {
                grid-column: 1;
                align-self: end;
            }
            .pesona-cart-equivalent-cell {
                grid-column: 2;
                text-align: right;
                align-self: end;
            }
        }

        @media (min-width: 640px) {
            .pesona-cart-mobile-label {
                display: none;
            }
        }

        @media (min-width: 960px) {
            .pesona-cart-layout {
                grid-template-columns: minmax(0, 1fr) 320px;
                gap: 16px;
            }
            .pesona-cart-summary {
                position: sticky;
                top: 22px;
            }
        }
    </style>

    <script>
        (function () {
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            var vehicleSelect = document.getElementById('pesona-cart-vehicle');
            var vehicleKey = 'inv_pesona_cart_vehicle_{{ auth()->id() }}';
            try {
                var savedVehicle = sessionStorage.getItem(vehicleKey);
                if (vehicleSelect && !vehicleSelect.value && savedVehicle !== null) vehicleSelect.value = savedVehicle;
                sessionStorage.removeItem(vehicleKey);
            } catch (_) {}
            function preserveVehicle() {
                try { if (vehicleSelect) sessionStorage.setItem(vehicleKey, vehicleSelect.value); } catch (_) {}
            }
            document.querySelectorAll('.pesona-cart-remove-btn').forEach(function (button) {
                button.form.addEventListener('submit', preserveVehicle);
            });

            function formatNumber(value) {
                return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function updateTotalQty(count) {
                var totalQtyEl = document.getElementById('pesonaCartTotalQty');
                if (totalQtyEl && typeof count === 'number') {
                    totalQtyEl.textContent = formatNumber(count);
                }
            }

            function showToast(message, isWarning) {
                var existing = document.getElementById('pesonaCartToast');
                if (existing) existing.remove();
                var toast = document.createElement('div');
                toast.id = 'pesonaCartToast';
                toast.className = 'pesona-cart-toast' + (isWarning ? ' is-warning' : '');
                toast.textContent = message;
                document.body.appendChild(toast);
                requestAnimationFrame(function () { toast.classList.add('show'); });
                setTimeout(function () {
                    toast.classList.remove('show');
                    setTimeout(function () { if (toast.parentNode) toast.remove(); }, 200);
                }, 2500);
            }

            document.querySelectorAll('.pesona-stepper').forEach(function (stepper) {
                var min = parseInt(stepper.getAttribute('data-min'), 10) || 1;
                var max = parseInt(stepper.getAttribute('data-max'), 10) || 9999;
                var row = stepper.closest('.pesona-cart-row');
                var minusBtn = stepper.querySelector('.pesona-stepper-minus');
                var plusBtn = stepper.querySelector('.pesona-stepper-plus');
                var input = stepper.querySelector('.pesona-stepper-input');
                var form = stepper.querySelector('.pesona-cart-qty-form');
                var equivalent = row ? row.querySelector('.pesona-cart-equivalent-value') : null;
                var unitSize = row ? parseInt(row.getAttribute('data-unit-size'), 10) || 1 : 1;
                var contentUnit = row ? row.getAttribute('data-content-unit') || '' : '';
                if (!minusBtn || !plusBtn || !input || !form) return;

                var busy = false;
                var lastCommitted = parseInt(input.value, 10) || min;

                function syncButtons() {
                    var value = parseInt(input.value, 10) || min;
                    minusBtn.disabled = value <= min || busy;
                    plusBtn.disabled = value >= max || busy;
                }

                function updateEquivalent(qty) {
                    if (!equivalent) return;
                    equivalent.textContent = formatNumber(qty * unitSize) + ' ' + contentUnit;
                }

                function submitQty(next) {
                    if (busy) return;
                    next = Math.min(max, Math.max(min, next));
                    if (next === lastCommitted) {
                        input.value = lastCommitted;
                        syncButtons();
                        return;
                    }

                    var previous = lastCommitted;
                    busy = true;
                    input.value = next;
                    syncButtons();
                    updateEquivalent(next);

                    var formData = new FormData(form);
                    formData.set('qty', next);

                    fetch(form.getAttribute('action'), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                        credentials: 'same-origin',
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { ok: response.ok, data: data };
                            });
                        })
                        .then(function (result) {
                            busy = false;
                            var data = result.data || {};
                            if (result.ok && data.success && typeof data.qty === 'number') {
                                lastCommitted = data.qty;
                                input.value = data.qty;
                                updateEquivalent(data.qty);
                                updateTotalQty(data.cartCount);
                                if (row && row.classList.contains('is-problem')) {
                                    preserveVehicle();
                                    window.location.reload();
                                    return;
                                }
                            } else {
                                input.value = previous;
                                updateEquivalent(previous);
                                var errMsg = data.message || (data.errors && data.errors.qty && data.errors.qty[0]) || 'Gagal memperbarui jumlah.';
                                showToast(errMsg, true);
                            }
                            syncButtons();
                        })
                        .catch(function () {
                            busy = false;
                            input.value = previous;
                            updateEquivalent(previous);
                            showToast('Gagal memperbarui jumlah. Silakan coba lagi.', true);
                            syncButtons();
                        });
                }

                minusBtn.addEventListener('click', function () {
                    var current = parseInt(input.value, 10) || min;
                    submitQty(current - 1);
                });

                plusBtn.addEventListener('click', function () {
                    var current = parseInt(input.value, 10) || min;
                    submitQty(current + 1);
                });

                input.addEventListener('change', function () {
                    var val = parseInt(input.value, 10);
                    if (!Number.isFinite(val)) val = min;
                    submitQty(val);
                });

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    var val = parseInt(input.value, 10);
                    if (!Number.isFinite(val)) val = min;
                    submitQty(val);
                });

                syncButtons();
            });
        })();
    </script>
</x-inv-pesona-app>
