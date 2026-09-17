<x-ops-app title="Akses OPS">
    <div class="ops-header">
        <h1 class="ops-title">Akses OPS</h1>
        <p class="ops-subtitle">Pilih divisi pengguna dan tentukan Admin OPS.</p>
    </div>

    <form method="POST" action="{{ route('v2.ops.admin.access.divisions.sync') }}" class="ops-card" style="margin-bottom:18px">
        @csrf
        <h2>Akses Pengguna</h2>
        <p class="ops-muted">Centang divisi yang boleh membuka Kebutuhan Operasional.</p>
        <div class="ops-grid" style="margin:14px 0">
            @forelse($divisions as $division)
                <label class="ops-card" style="display:flex;align-items:center;gap:12px;padding:13px;cursor:pointer">
                    <input type="checkbox" name="division_ids[]" value="{{ $division->id }}" @checked(in_array($division->id, $selectedDivisionIds, true)) style="width:20px;height:20px;accent-color:var(--ops-primary)">
                    <span>
                        <strong style="display:block;font-size:13px">{{ $division->name }}</strong>
                        <span class="ops-muted">{{ $division->active_users_count }} pengguna aktif</span>
                    </span>
                </label>
            @empty
                <div class="ops-empty">Divisi belum tersedia.</div>
            @endforelse
        </div>
        <button class="ops-btn ops-btn-primary" type="submit">Simpan Akses Pengguna</button>
    </form>

    <div class="ops-header">
        <h2 class="ops-title" style="font-size:18px">Admin OPS</h2>
        <p class="ops-subtitle">Admin dapat mengelola barang dan menyetujui pengajuan.</p>
    </div>

    <form method="GET" class="ops-card ops-form-grid" style="margin-bottom:12px">
        <input class="ops-input" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau PT" autocomplete="off">
        <div class="ops-actions">
            <button class="ops-btn ops-btn-primary" type="submit">Cari</button>
            <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.access.index') }}">Reset</a>
        </div>
    </form>

    <div class="ops-grid" data-ops-mobile-cards>
        @forelse($users as $user)
            @php($isAdmin = $user->hasAccessRole('ADMIN OPS'))
            <article class="ops-card">
                <h2>{{ $user->name }}</h2>
                <p class="ops-muted">{{ $user->pt?->name ?? '-' }} · {{ $user->email ?? '-' }}</p>
                <div class="ops-actions" style="margin:10px 0">
                    @if($isAdmin)<span class="ops-badge">Admin OPS</span>@endif
                    @if($user->hasAccessRole('ADMIN ATK'))<span class="ops-badge">Admin ATK</span>@endif
                </div>
                <form method="POST" action="{{ route($isAdmin ? 'v2.ops.admin.access.revoke-admin' : 'v2.ops.admin.access.grant-admin', $user) }}">
                    @csrf
                    @if($isAdmin) @method('DELETE') @endif
                    <button class="ops-btn {{ $isAdmin ? 'ops-btn-danger' : 'ops-btn-primary' }}" type="submit" @disabled($isAdmin && auth()->id() === $user->id)>{{ $isAdmin ? 'Cabut Admin' : 'Jadikan Admin' }}</button>
                </form>
            </article>
        @empty
            <div class="ops-card ops-empty">Pengguna tidak ditemukan.</div>
        @endforelse
    </div>
    <x-pagination :items="$users" preserve-query />
</x-ops-app>
