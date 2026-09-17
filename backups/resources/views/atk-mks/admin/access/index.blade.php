<x-atk-mks-app title="Akses ATK MKS">
    <div class="atk-mks-header">
        <h1 class="atk-mks-title">Akses ATK MKS</h1>
        <p class="atk-mks-subtitle">Pilih PT pengguna dan tentukan Admin ATK MKS.</p>
    </div>

    <form method="POST" action="{{ route('v2.atk-mks.admin.access.pts.sync') }}" class="atk-mks-card" style="margin-bottom:18px">
        @csrf
        <h2>Akses Pengguna</h2>
        <p class="atk-mks-muted">Centang PT yang boleh membuka Stok ATK MKS.</p>
        <div class="atk-mks-grid" style="margin:14px 0">
            @forelse($pts as $pt)
                <label class="atk-mks-card" style="display:flex;align-items:center;gap:12px;padding:13px;cursor:pointer">
                    <input type="checkbox" name="pt_ids[]" value="{{ $pt->id }}" @checked(in_array($pt->id, $selectedPtIds, true)) style="width:20px;height:20px;accent-color:var(--atk-mks-primary)">
                    <span>
                        <strong style="display:block;font-size:13px">{{ $pt->name }}</strong>
                        <span class="atk-mks-muted">{{ $pt->active_users_count }} pengguna aktif</span>
                    </span>
                </label>
            @empty
                <div class="atk-mks-empty">PT belum tersedia.</div>
            @endforelse
        </div>
        <button class="atk-mks-btn atk-mks-btn-primary" type="submit">Simpan Akses Pengguna</button>
    </form>

    <div class="atk-mks-header">
        <h2 class="atk-mks-title" style="font-size:18px">Admin ATK MKS</h2>
        <p class="atk-mks-subtitle">Admin dapat mengelola barang dan menyetujui pengajuan.</p>
    </div>

    <form method="GET" class="atk-mks-card atk-mks-form-grid" style="margin-bottom:12px">
        <input class="atk-mks-input" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau PT" autocomplete="off">
        <div class="atk-mks-actions">
            <button class="atk-mks-btn atk-mks-btn-primary" type="submit">Cari</button>
            <a class="atk-mks-btn atk-mks-btn-soft" href="{{ route('v2.atk-mks.admin.access.index') }}">Reset</a>
        </div>
    </form>

    <div class="atk-mks-grid" data-atk-mks-mobile-cards>
        @forelse($users as $user)
            @php($isAdmin = $user->hasAccessRole('ADMIN ATK MKS'))
            <article class="atk-mks-card">
                <h2>{{ $user->name }}</h2>
                <p class="atk-mks-muted">{{ $user->pt?->name ?? '-' }} · {{ $user->email ?? '-' }}</p>
                <div class="atk-mks-actions" style="margin:10px 0">
                    @if($isAdmin)<span class="atk-mks-badge">Admin ATK MKS</span>@endif
                    @if($user->hasAccessRole('ADMIN ATK'))<span class="atk-mks-badge">Admin ATK</span>@endif
                </div>
                <form method="POST" action="{{ route($isAdmin ? 'v2.atk-mks.admin.access.revoke-admin' : 'v2.atk-mks.admin.access.grant-admin', $user) }}">
                    @csrf
                    @if($isAdmin) @method('DELETE') @endif
                    <button class="atk-mks-btn {{ $isAdmin ? 'atk-mks-btn-danger' : 'atk-mks-btn-primary' }}" type="submit" @disabled($isAdmin && auth()->id() === $user->id)>{{ $isAdmin ? 'Cabut Admin' : 'Jadikan Admin' }}</button>
                </form>
            </article>
        @empty
            <div class="atk-mks-card atk-mks-empty">Pengguna tidak ditemukan.</div>
        @endforelse
    </div>
    <x-pagination :items="$users" preserve-query />
</x-atk-mks-app>


