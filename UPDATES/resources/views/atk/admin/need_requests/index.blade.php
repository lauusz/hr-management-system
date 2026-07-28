<x-atk-app title="Pengajuan Barang ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Pengajuan Barang</h1>
            <p class="atk-subtitle">Review pengajuan dan perbarui statusnya. Penambahan stok dilakukan melalui Master Barang.</p>
        </div>
    </div>

    <div class="atk-table-wrap atk-admin-need-mobile-table">
        <table class="atk-table atk-admin-need-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>PT</th>
                    <th>Barang</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($needRequests as $needRequest)
                    <tr class="atk-admin-need-card">
                        <td class="atk-admin-need-user" data-label="Pemohon">{{ $needRequest->user_name_snapshot }}</td>
                        <td class="atk-admin-need-pt" data-label="PT">{{ $needRequest->pt_name_snapshot ?? '-' }}</td>
                        <td class="atk-admin-need-item" data-label="Barang">
                            <strong>{{ $needRequest->requested_item_name }}</strong>
                            <div class="atk-product-meta">{{ $needRequest->reason }}</div>
                        </td>
                        <td class="atk-admin-need-qty" data-label="Jumlah">{{ $needRequest->qty }} {{ $needRequest->unit_name }}</td>
                        <td class="atk-admin-need-status" data-label="Status">
                            <span class="atk-badge atk-badge-{{ $needRequest->status === 'DONE' ? 'success' : ($needRequest->status === 'REJECTED' ? 'error' : 'warning') }}">
                                {{ $needRequest->status }}
                            </span>
                        </td>
                        <td class="atk-admin-need-actions" data-label="{{ $needRequest->status === 'PENDING' ? 'Aksi' : 'Catatan Admin' }}">
                            @if($needRequest->status === 'PENDING')
                                <div class="atk-admin-need-action-list">
                                    <form method="POST" action="{{ route('v2.atk.admin.need-requests.process', $needRequest) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="DONE">
                                        <button class="atk-btn atk-btn-primary" type="submit">Tandai Selesai</button>
                                    </form>

                                    <details class="atk-admin-need-reject">
                                        <summary class="atk-btn atk-btn-secondary">Tolak</summary>
                                        <form method="POST" action="{{ route('v2.atk.admin.need-requests.process', $needRequest) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="REJECTED">
                                            <label class="atk-label" for="reject-note-{{ $needRequest->id }}">Catatan penolakan</label>
                                            <textarea class="atk-input" id="reject-note-{{ $needRequest->id }}" name="admin_note" rows="2" placeholder="Alasan ditolak"></textarea>
                                            <button class="atk-btn atk-btn-danger" type="submit">Konfirmasi Tolak</button>
                                        </form>
                                    </details>
                                </div>
                            @else
                                {{ $needRequest->admin_note ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="atk-admin-need-empty"><td colspan="6">Belum ada pengajuan barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$needRequests" preserve-query />

    <style>
        .atk-admin-need-action-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: flex-start;
        }
        .atk-admin-need-reject > summary {
            cursor: pointer;
            list-style: none;
        }
        .atk-admin-need-reject > form {
            display: grid;
            gap: 8px;
            min-width: 240px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--atk-border-soft);
        }
        @media (max-width: 639px) {
            .atk-admin-need-mobile-table {
                overflow: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }
            .atk-admin-need-table {
                display: block;
                min-width: 0;
            }
            .atk-admin-need-table thead {
                display: none;
            }
            .atk-admin-need-table tbody {
                display: grid;
                gap: 12px;
            }
            .atk-admin-need-card {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "item status"
                    "user user"
                    "pt pt"
                    "qty qty"
                    "actions actions";
                gap: 0 10px;
                padding: 14px;
                border: 1px solid var(--atk-border);
                border-radius: 14px;
                background: var(--atk-surface);
                box-shadow: var(--atk-shadow);
            }
            .atk-admin-need-card td {
                display: grid;
                grid-template-columns: minmax(82px, .65fr) minmax(0, 1fr);
                gap: 10px;
                padding: 8px 0;
                border: 0;
                font-size: 12px;
            }
            .atk-admin-need-card td::before {
                content: attr(data-label);
                color: var(--atk-muted);
                font-size: 10px;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            .atk-admin-need-item {
                grid-area: item;
                display: block !important;
                padding-top: 0 !important;
            }
            .atk-admin-need-item::before {
                display: block;
                margin-bottom: 5px;
            }
            .atk-admin-need-status {
                grid-area: status;
                display: block !important;
                padding-top: 0 !important;
            }
            .atk-admin-need-status::before {
                display: none;
            }
            .atk-admin-need-user { grid-area: user; }
            .atk-admin-need-pt { grid-area: pt; }
            .atk-admin-need-qty { grid-area: qty; }
            .atk-admin-need-actions {
                grid-area: actions;
                display: block !important;
                margin-top: 4px;
                padding-bottom: 0 !important;
                border-top: 1px solid var(--atk-border-soft) !important;
            }
            .atk-admin-need-actions::before {
                display: block;
                margin: 10px 0 8px;
            }
            .atk-admin-need-action-list,
            .atk-admin-need-action-list form,
            .atk-admin-need-action-list .atk-btn,
            .atk-admin-need-reject,
            .atk-admin-need-reject textarea {
                width: 100%;
            }
            .atk-admin-need-reject > form {
                min-width: 0;
            }
            .atk-admin-need-empty {
                display: block;
                padding: 18px;
                border: 1px solid var(--atk-border);
                border-radius: 14px;
                background: var(--atk-surface);
                text-align: center;
            }
            .atk-admin-need-empty td {
                display: block;
                padding: 0;
                border: 0;
            }
        }
    </style>
</x-atk-app>
