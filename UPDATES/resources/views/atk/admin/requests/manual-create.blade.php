<x-atk-app title="Input Pengambilan Manual ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Input Pengambilan Manual</h1>
            <p class="atk-subtitle">Catat pengambilan atas nama user, lalu lanjutkan ke halaman review admin.</p>
        </div>
        <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.requests.index') }}">Kembali</a>
    </div>

    <form class="atk-card atk-manual-request-form" method="POST" action="{{ route('v2.atk.admin.requests.manual.store') }}">
        @csrf
        <div>
            <label class="atk-label" for="user_id">User</label>
            <select class="atk-select" id="user_id" name="user_id" required>
                <option value="">Pilih user</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                        {{ $user->name }}{{ $user->profile?->pt ? ' — '.$user->profile->pt->name : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="atk-label" for="notes">Catatan</label>
            <textarea class="atk-textarea" id="notes" name="notes" maxlength="1000" placeholder="Opsional">{{ old('notes') }}</textarea>
        </div>

        <div>
            <h2 class="atk-section-title">Barang yang Diambil</h2>
            <div class="atk-manual-item-list">
                @forelse($items as $item)
                    <label class="atk-manual-item">
                        <span>
                            <strong>{{ $item->name }}</strong>
                            <small>Stok {{ $item->stock_qty }} {{ $item->unit_name }}</small>
                        </span>
                        <input class="atk-input" type="number" min="1" inputmode="numeric" name="quantities[{{ $item->id }}]" value="{{ old('quantities.'.$item->id) }}" placeholder="0" aria-label="Jumlah {{ $item->name }}">
                    </label>
                @empty
                    <div class="atk-empty">Belum ada barang aktif.</div>
                @endforelse
            </div>
        </div>

        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Buat dan Review</button>
        </div>
    </form>

    <style>
        .atk-manual-request-form,
        .atk-manual-item-list {
            display: grid;
            gap: 14px;
        }
        .atk-manual-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 88px;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--atk-border-soft);
            border-radius: 12px;
        }
        .atk-manual-item strong,
        .atk-manual-item small {
            display: block;
        }
        .atk-manual-item small {
            margin-top: 4px;
            color: var(--atk-muted);
        }
        @media (max-width: 639px) {
            .atk-header,
            .atk-header .atk-btn,
            .atk-manual-request-form > .atk-actions,
            .atk-manual-request-form > .atk-actions .atk-btn {
                width: 100%;
            }
        }
    </style>
</x-atk-app>
