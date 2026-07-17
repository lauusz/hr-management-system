<x-atk-app title="Request Masuk ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Request Masuk</h1>
            <p class="atk-subtitle">Buka halaman review admin untuk cek item dan finalisasi stok.</p>
        </div>
    </div>
    <form method="GET" class="atk-card atk-form-grid" style="margin-bottom:14px">
        <input class="atk-input" name="q" value="{{ request('q') }}" placeholder="Cari no request, user, atau PT" autocomplete="off">
        <select class="atk-select" name="status">
            <option value="">Semua status</option>
            <option value="PENDING" @selected(request('status') === 'PENDING')>Pending</option>
            <option value="APPROVED" @selected(request('status') === 'APPROVED')>Approved</option>
            <option value="PARTIAL" @selected(request('status') === 'PARTIAL')>Partial</option>
            <option value="REJECTED" @selected(request('status') === 'REJECTED')>Rejected</option>
        </select>
        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Filter</button>
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.requests.index') }}">Reset</a>
        </div>
    </form>
    <div class="atk-admin-request-mobile-list">
        @forelse($requests as $request)
            <article class="atk-admin-request-card">
                <div class="atk-admin-request-top">
                    <time datetime="{{ $request->created_at?->toIso8601String() }}">{{ $request->created_at?->format('d/m/Y H:i') }}</time>
                    <span class="atk-badge atk-badge-{{ $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'error' : 'warning') }}">{{ $request->status }}</span>
                </div>
                <div class="atk-admin-request-person">
                    <strong>{{ $request->user_name_snapshot }}</strong>
                    <span>{{ $request->pt_name_snapshot ?? '-' }}</span>
                </div>
                <div class="atk-admin-request-count">{{ $request->items->count() }} Item</div>
                <a class="atk-btn atk-btn-secondary atk-admin-request-action" href="{{ route('v2.atk.admin.requests.show', $request) }}">
                    {{ $request->status === 'PENDING' ? 'Review Admin' : 'Lihat Review' }}
                </a>
            </article>
        @empty
            <div class="atk-card atk-empty">Belum ada request.</div>
        @endforelse
    </div>

    <div class="atk-table-wrap atk-admin-request-desktop-table">
        <table class="atk-table">
            <thead><tr><th>Tgl Pengajuan</th><th>User</th><th>PT</th><th>Item</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ $request->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $request->user_name_snapshot }}</td>
                        <td>{{ $request->pt_name_snapshot ?? '-' }}</td>
                        <td>{{ $request->items->count() }}</td>
                        <td><span class="atk-badge atk-badge-{{ $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'error' : 'warning') }}">{{ $request->status }}</span></td>
                        <td>
                            <a class="atk-btn atk-btn-secondary atk-btn-sm" href="{{ route('v2.atk.admin.requests.show', $request) }}">
                                {{ $request->status === 'PENDING' ? 'Review Admin' : 'Lihat Review' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada request.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$requests" preserve-query />

    <style>
        .atk-admin-request-mobile-list {
            display: grid;
            gap: 12px;
        }
        .atk-admin-request-card {
            padding: 16px;
            border: 1px solid var(--atk-border);
            border-radius: 16px;
            background: var(--atk-surface);
            box-shadow: 0 1px 3px rgba(17, 24, 39, .04);
        }
        .atk-admin-request-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--atk-border-soft);
        }
        .atk-admin-request-top time {
            color: var(--atk-muted);
            font-size: 11px;
            font-weight: 700;
        }
        .atk-admin-request-person {
            margin-top: 14px;
        }
        .atk-admin-request-person strong,
        .atk-admin-request-person span {
            display: block;
            overflow-wrap: anywhere;
        }
        .atk-admin-request-person strong {
            font-size: 14px;
            line-height: 1.45;
        }
        .atk-admin-request-person span {
            margin-top: 4px;
            color: var(--atk-muted);
            font-size: 12px;
            font-weight: 600;
        }
        .atk-admin-request-count {
            margin: 14px 0;
            padding: 8px 10px;
            border-radius: 10px;
            background: var(--atk-primary-softer);
            color: var(--atk-primary-dark);
            font-size: 12px;
            font-weight: 800;
        }
        .atk-admin-request-action {
            width: 100%;
            min-height: 44px;
        }
        .atk-admin-request-desktop-table {
            display: none;
        }

        @media (min-width: 640px) {
            .atk-admin-request-mobile-list {
                display: none;
            }
            .atk-admin-request-desktop-table {
                display: block;
            }
        }
    </style>
</x-atk-app>
