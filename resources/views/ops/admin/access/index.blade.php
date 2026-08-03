<x-ops-app title="Akses OPS">
    <div class="ops-header">
        <h1 class="ops-title">Akses OPS</h1>
        <p class="ops-subtitle">Pilih pengguna dan admin Kebutuhan Operasional.</p>
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
            @php($isUser = $user->hasAccessRole('OPS'))
            @php($isAdmin = $user->hasAccessRole('ADMIN OPS'))
            <article class="ops-card">
                <h2>{{ $user->name }}</h2>
                <p class="ops-muted">{{ $user->pt?->name ?? '-' }} · {{ $user->email ?? '-' }}</p>
                <div class="ops-actions" style="margin:10px 0">
                    @if($isUser)<span class="ops-badge">Pengguna OPS</span>@endif
                    @if($isAdmin)<span class="ops-badge">Admin OPS</span>@endif
                    @if($user->hasAccessRole('ADMIN ATK'))<span class="ops-badge">Admin ATK</span>@endif
                </div>
                <div class="ops-actions">
                    <form method="POST" action="{{ route($isUser ? 'v2.ops.admin.access.revoke-user' : 'v2.ops.admin.access.grant-user', $user) }}">
                        @csrf
                        @if($isUser) @method('DELETE') @endif
                        <button class="ops-btn {{ $isUser ? 'ops-btn-soft' : 'ops-btn-primary' }}" type="submit">{{ $isUser ? 'Cabut Pengguna' : 'Jadikan Pengguna' }}</button>
                    </form>
                    <form method="POST" action="{{ route($isAdmin ? 'v2.ops.admin.access.revoke-admin' : 'v2.ops.admin.access.grant-admin', $user) }}">
                        @csrf
                        @if($isAdmin) @method('DELETE') @endif
                        <button class="ops-btn {{ $isAdmin ? 'ops-btn-danger' : 'ops-btn-primary' }}" type="submit" @disabled($isAdmin && auth()->id() === $user->id)>{{ $isAdmin ? 'Cabut Admin' : 'Jadikan Admin' }}</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="ops-card ops-empty">Pengguna tidak ditemukan.</div>
        @endforelse
    </div>
    <x-pagination :items="$users" preserve-query />
</x-ops-app>
