<x-atk-app title="Pengajuan Saya">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Pengajuan Saya</h1>
            <p class="atk-subtitle">Pantau status permintaan ATK Anda.</p>
        </div>
    </div>

    <form method="GET" class="atk-card atk-form-grid" style="margin-bottom:14px">
        <div>
            <label class="atk-label" for="status">Status</label>
            <select class="atk-select" id="status" name="status">
                <option value="">Semua</option>
                <option value="PENDING" @selected(request('status') === 'PENDING')>Pending</option>
                <option value="APPROVED" @selected(request('status') === 'APPROVED')>Approved</option>
                <option value="PARTIAL" @selected(request('status') === 'PARTIAL')>Partial</option>
                <option value="REJECTED" @selected(request('status') === 'REJECTED')>Rejected</option>
            </select>
        </div>
        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Filter</button>
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.requests.index') }}">Reset</a>
        </div>
    </form>

    <div class="atk-request-mobile-list">
        @forelse($requests as $request)
            <article class="atk-request-card">
                <div class="atk-request-card-top">
                    <strong class="atk-request-number">{{ $request->request_number }}</strong>
                    <span class="atk-badge atk-badge-{{ $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'error' : 'warning') }}">
                        {{ $request->status }}
                    </span>
                </div>
                <dl class="atk-request-meta">
                    <div>
                        <dt>PT</dt>
                        <dd>{{ $request->pt_name_snapshot ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal Pengajuan</dt>
                        <dd>{{ $request->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
                <a class="atk-btn atk-btn-secondary atk-request-detail" href="{{ route('v2.atk.requests.show', $request) }}">Lihat Detail</a>
            </article>
        @empty
            <div class="atk-card atk-empty">Belum ada pengajuan.</div>
        @endforelse
    </div>

    <div class="atk-table-wrap atk-request-desktop-table">
        <table class="atk-table">
            <thead><tr><th>No Request</th><th>PT</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td><strong>{{ $request->request_number }}</strong></td>
                        <td>{{ $request->pt_name_snapshot ?? '-' }}</td>
                        <td><span class="atk-badge atk-badge-{{ $request->status === 'APPROVED' ? 'success' : ($request->status === 'REJECTED' ? 'error' : 'warning') }}">{{ $request->status }}</span></td>
                        <td>{{ $request->created_at?->format('d/m/Y H:i') }}</td>
                        <td><a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.requests.show', $request) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$requests" preserve-query />

    <style>
        .atk-request-mobile-list {
            display: grid;
            gap: 12px;
        }
        .atk-request-card {
            padding: 16px;
            border: 1px solid var(--atk-border);
            border-radius: 16px;
            background: var(--atk-surface);
            box-shadow: 0 1px 3px rgba(17, 24, 39, .04);
        }
        .atk-request-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--atk-border-soft);
        }
        .atk-request-number {
            min-width: 0;
            font-size: 14px;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }
        .atk-request-meta {
            display: grid;
            gap: 10px;
            margin: 14px 0;
        }
        .atk-request-meta div {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
        }
        .atk-request-meta dt {
            color: var(--atk-muted);
            font-size: 11px;
            font-weight: 700;
        }
        .atk-request-meta dd {
            margin: 0;
            color: var(--atk-text);
            font-size: 12px;
            font-weight: 700;
            text-align: right;
        }
        .atk-request-detail {
            width: 100%;
            min-height: 44px;
        }
        .atk-request-desktop-table {
            display: none;
        }

        @media (min-width: 640px) {
            .atk-request-mobile-list {
                display: none;
            }
            .atk-request-desktop-table {
                display: block;
            }
        }
    </style>
</x-atk-app>
