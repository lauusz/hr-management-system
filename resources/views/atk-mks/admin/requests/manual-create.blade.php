<x-atk-mks-app title="Input Pengambilan Manual ATK MKS">
    <div class="atk-mks-header atk-mks-manual-header">
        <div>
            <h1 class="atk-mks-title">Input Pengambilan Manual</h1>
            <p class="atk-mks-subtitle">Catat pengambilan atas nama pengguna, lalu lanjutkan ke pemeriksaan.</p>
        </div>
        <a class="atk-mks-btn atk-mks-btn-soft" href="{{ route('v2.atk-mks.admin.requests.index') }}">Kembali</a>
    </div>

    <form class="atk-mks-card atk-mks-manual-request-form" method="POST" action="{{ route('v2.atk-mks.admin.requests.manual.store') }}">
        @csrf
        <div>
            <label class="atk-mks-label" for="user_id">Pengguna</label>
            <select class="atk-mks-select" id="user_id" name="user_id" required>
                <option value="">Pilih pengguna</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }}{{ $user->profile?->pt ? ' — '.$user->profile->pt->name : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="atk-mks-label" for="notes">Catatan</label>
            <textarea class="atk-mks-textarea" id="notes" name="notes" maxlength="1000" placeholder="Opsional">{{ old('notes') }}</textarea>
        </div>
        <div>
            <h2 class="atk-mks-manual-title">Barang yang Diambil</h2>
            <div class="atk-mks-manual-item-list">
                @forelse($items as $item)
                    <label class="atk-mks-manual-item">
                        <span><strong>{{ $item->name }}</strong><small>Stok {{ $item->stock_qty }} {{ $item->unit_name }}</small></span>
                        <input class="atk-mks-input" type="number" min="1" inputmode="numeric" name="quantities[{{ $item->id }}]" value="{{ old('quantities.'.$item->id) }}" placeholder="0" aria-label="Jumlah {{ $item->name }}">
                    </label>
                @empty
                    <div class="atk-mks-empty">Belum ada barang aktif.</div>
                @endforelse
            </div>
        </div>
        <div class="atk-mks-actions"><button class="atk-mks-btn atk-mks-btn-primary" type="submit">Buat dan Periksa</button></div>
    </form>

    <style>
        .atk-mks-manual-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .atk-mks-manual-request-form,.atk-mks-manual-item-list { display:grid; gap:14px; }
        .atk-mks-manual-title { margin:0 0 10px; font-size:15px; }
        .atk-mks-manual-item { display:grid; grid-template-columns:minmax(0,1fr) 88px; align-items:center; gap:12px; padding:12px; border:1px solid var(--atk-mks-border); border-radius:12px; }
        .atk-mks-manual-item strong,.atk-mks-manual-item small { display:block; }
        .atk-mks-manual-item small { margin-top:4px; color:var(--atk-mks-muted); }
        @media (max-width:639px) {
            .atk-mks-manual-header { flex-direction:column; }
            .atk-mks-manual-header .atk-mks-btn,.atk-mks-manual-request-form>.atk-mks-actions,.atk-mks-manual-request-form>.atk-mks-actions .atk-mks-btn { width:100%; }
        }
    </style>
</x-atk-mks-app>


