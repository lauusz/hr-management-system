<x-atk-app title="Pengajuan Barang Saya">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Pengajuan Barang Saya</h1>
            <p class="atk-subtitle">Pantau status pengajuan restock atau barang baru.</p>
        </div>
        <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.need-requests.create') }}">Ajukan Barang</a>
    </div>
    <div class="atk-need-request-mobile-list">
        @forelse($needRequests as $needRequest)
            <article class="atk-need-request-card">
                <div class="atk-need-request-top">
                    <div>
                        <strong>{{ $needRequest->requested_item_name }}</strong>
                        @if($needRequest->item)
                            <div class="atk-product-meta">{{ $needRequest->item->name }}</div>
                        @endif
                    </div>
                    <span class="atk-badge atk-badge-{{ $needRequest->status === 'DONE' ? 'success' : ($needRequest->status === 'REJECTED' ? 'error' : 'warning') }}">
                        {{ $needRequest->status }}
                    </span>
                </div>
                <dl class="atk-need-request-meta">
                    <div>
                        <dt>Jumlah</dt>
                        <dd>{{ $needRequest->qty }} {{ $needRequest->unit_name }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal</dt>
                        <dd>{{ $needRequest->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
                <div class="atk-need-request-note">
                    <span>Alasan</span>
                    <p>{{ $needRequest->reason }}</p>
                </div>
                @if($needRequest->admin_note)
                    <div class="atk-need-request-note atk-need-request-admin-note">
                        <span>Catatan Admin</span>
                        <p>{{ $needRequest->admin_note }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="atk-card atk-empty">Belum ada pengajuan barang.</div>
        @endforelse
    </div>

    <div class="atk-table-wrap atk-need-request-desktop-table">
        <table class="atk-table">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Jumlah</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Catatan Admin</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($needRequests as $needRequest)
                    <tr>
                        <td>
                            <strong>{{ $needRequest->requested_item_name }}</strong>
                            @if($needRequest->item)
                                <div class="atk-product-meta">{{ $needRequest->item->name }}</div>
                            @endif
                        </td>
                        <td>{{ $needRequest->qty }} {{ $needRequest->unit_name }}</td>
                        <td>{{ $needRequest->reason }}</td>
                        <td>
                            <span class="atk-badge atk-badge-{{ $needRequest->status === 'DONE' ? 'success' : ($needRequest->status === 'REJECTED' ? 'error' : 'warning') }}">
                                {{ $needRequest->status }}
                            </span>
                        </td>
                        <td>{{ $needRequest->admin_note ?? '-' }}</td>
                        <td>{{ $needRequest->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada pengajuan barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$needRequests" preserve-query />

    <style>
        .atk-need-request-mobile-list {
            display: grid;
            gap: 12px;
        }
        .atk-need-request-card {
            padding: 16px;
            border: 1px solid var(--atk-border);
            border-radius: 16px;
            background: var(--atk-surface);
            box-shadow: 0 1px 3px rgba(17, 24, 39, .04);
        }
        .atk-need-request-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--atk-border-soft);
        }
        .atk-need-request-top > div {
            min-width: 0;
        }
        .atk-need-request-top strong {
            display: block;
            font-size: 14px;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }
        .atk-need-request-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 14px 0;
        }
        .atk-need-request-meta dt,
        .atk-need-request-note span {
            color: var(--atk-muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .atk-need-request-meta dd {
            margin: 4px 0 0;
            font-size: 12px;
            font-weight: 800;
        }
        .atk-need-request-note {
            padding-top: 12px;
            border-top: 1px solid var(--atk-border-soft);
        }
        .atk-need-request-note p {
            margin: 5px 0 0;
            color: var(--atk-text);
            font-size: 12px;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }
        .atk-need-request-admin-note {
            margin-top: 12px;
        }
        .atk-need-request-admin-note p {
            color: var(--error);
        }
        .atk-need-request-desktop-table {
            display: none;
        }

        @media (min-width: 640px) {
            .atk-need-request-mobile-list {
                display: none;
            }
            .atk-need-request-desktop-table {
                display: block;
            }
        }
    </style>
</x-atk-app>
