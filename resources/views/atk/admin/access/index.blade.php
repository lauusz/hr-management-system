<x-atk-app title="Akses Admin ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Akses Admin ATK</h1>
            <p class="atk-subtitle">Tunjuk user yang boleh membuka panel admin ATK.</p>
        </div>
    </div>
    <form method="GET" id="atkAccessSearchForm" action="{{ route('v2.atk.admin.access.index') }}" class="atk-card atk-form-grid" style="margin-bottom:14px">
        <input class="atk-input" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, username, atau PT" autocomplete="off" data-async-search>
        <div class="atk-actions">
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.access.index') }}" data-async-reset>Reset</a>
        </div>
    </form>
    <div id="atkAccessResults">
        <div class="atk-table-wrap">
            <table class="atk-table">
                <thead><tr><th>Nama</th><th>PT</th><th>Email</th><th>Akses</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($users as $user)
                        @php($hasAtkAccess = $user->canManageAtk())
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->pt?->name ?? '-' }}</td>
                            <td>{{ $user->email ?? '-' }}</td>
                            <td>
                                @if($hasAtkAccess)
                                    <span class="atk-badge atk-badge-success">Admin ATK</span>
                                @else
                                    <span class="atk-badge atk-badge-neutral">User</span>
                                @endif
                            </td>
                            <td>
                                @if($hasAtkAccess)
                                    <form method="POST" action="{{ route('v2.atk.admin.access.revoke', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="atk-btn atk-btn-danger" type="submit" @disabled(auth()->id() === $user->id)>Cabut</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('v2.atk.admin.access.grant', $user) }}">
                                        @csrf
                                        <button class="atk-btn atk-btn-secondary" type="submit">Jadikan Admin</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">User tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination :items="$users" preserve-query />
    </div>
    <script>
        (function () {
            const form = document.getElementById('atkAccessSearchForm');
            const input = form ? form.querySelector('[data-async-search]') : null;
            const reset = form ? form.querySelector('[data-async-reset]') : null;
            const results = document.getElementById('atkAccessResults');
            if (!form || !input || !results) return;

            let timer;
            function load(url) {
                fetch(url.toString())
                    .then(function (response) { return response.text(); })
                    .then(function (html) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const next = doc.getElementById('atkAccessResults');
                        if (!next) {
                            window.location.href = url.toString();
                            return;
                        }
                        results.innerHTML = next.innerHTML;
                        window.history.replaceState(null, '', url.toString());
                    })
                    .catch(function () {
                        window.location.href = url.toString();
                    });
            }

            function search() {
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                if (!params.get('q')) params.delete('q');
                url.search = params.toString();
                load(url);
            }

            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(search, 350);
            });

            if (reset) {
                reset.addEventListener('click', function (event) {
                    event.preventDefault();
                    input.value = '';
                    load(new URL(reset.href));
                });
            }

            results.addEventListener('click', function (event) {
                const link = event.target.closest('a');
                if (!link || !link.closest('.hrd-pagination')) return;

                event.preventDefault();
                load(new URL(link.href));
            });
        })();
    </script>
</x-atk-app>
