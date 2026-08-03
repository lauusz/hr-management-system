<x-ops-app title="Input Pengambilan Manual OPS">
    <div class="ops-header ops-manual-header">
        <div>
            <h1 class="ops-title">Input Pengambilan Manual</h1>
            <p class="ops-subtitle">Catat pengambilan atas nama pengguna, lalu lanjutkan ke review.</p>
        </div>
        <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.requests.index') }}">Kembali</a>
    </div>

    <form class="ops-card ops-manual-request-form" method="POST" action="{{ route('v2.ops.admin.requests.manual.store') }}">
        @csrf
        <div>
            <label class="ops-label" for="user_id">Pengguna</label>
            <select class="ops-select" id="user_id" name="user_id" required>
                <option value="">Pilih pengguna</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }}{{ $user->profile?->pt ? ' — '.$user->profile->pt->name : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="ops-label" for="notes">Catatan</label>
            <textarea class="ops-textarea" id="notes" name="notes" maxlength="1000" placeholder="Opsional">{{ old('notes') }}</textarea>
        </div>
        <div>
            <h2 class="ops-manual-title">Barang yang Diambil</h2>
            <div class="ops-manual-item-list">
                @forelse($items as $item)
                    <label class="ops-manual-item">
                        <span><strong>{{ $item->name }}</strong><small>Stok {{ $item->stock_qty }} {{ $item->unit_name }}</small></span>
                        <input class="ops-input" type="number" min="1" inputmode="numeric" name="quantities[{{ $item->id }}]" value="{{ old('quantities.'.$item->id) }}" placeholder="0" aria-label="Jumlah {{ $item->name }}">
                    </label>
                @empty
                    <div class="ops-empty">Belum ada barang aktif.</div>
                @endforelse
            </div>
        </div>
        <div class="ops-actions"><button class="ops-btn ops-btn-primary" type="submit">Buat dan Review</button></div>
    </form>

    <style>
        .ops-manual-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .ops-manual-request-form,.ops-manual-item-list { display:grid; gap:14px; }
        .ops-manual-title { margin:0 0 10px; font-size:15px; }
        .ops-manual-item { display:grid; grid-template-columns:minmax(0,1fr) 88px; align-items:center; gap:12px; padding:12px; border:1px solid var(--ops-border); border-radius:12px; }
        .ops-manual-item strong,.ops-manual-item small { display:block; }
        .ops-manual-item small { margin-top:4px; color:var(--ops-muted); }
        @media (max-width:639px) {
            .ops-manual-header { flex-direction:column; }
            .ops-manual-header .ops-btn,.ops-manual-request-form>.ops-actions,.ops-manual-request-form>.ops-actions .ops-btn { width:100%; }
        }
    </style>
</x-ops-app>
