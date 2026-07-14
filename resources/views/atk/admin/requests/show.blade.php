@php
    $statusBadgeMap = [
        'PENDING' => 'warning',
        'APPROVED' => 'success',
        'REJECTED' => 'error',
        'PARTIAL' => 'warning',
    ];
    $itemStatusBadgeMap = [
        'PENDING' => 'warning',
        'APPROVED' => 'success',
        'REJECTED' => 'error',
    ];
    $itemStatusLabel = [
        'PENDING' => 'Menunggu review',
        'APPROVED' => 'Disetujui',
        'REJECTED' => 'Tidak diproses',
    ];
    $pendingCount = $atkRequest->items->where('status', 'PENDING')->count();
    $approvedCount = $atkRequest->items->where('status', 'APPROVED')->count();
    $rejectedCount = $atkRequest->items->where('status', 'REJECTED')->count();
    $insufficientApprovedCount = $atkRequest->items
        ->filter(fn ($item) => ($item->status ?? 'PENDING') === 'APPROVED' && (($item->item?->stock_qty ?? 0) < $item->qty))
        ->count();
    $isPending = $atkRequest->status === 'PENDING';
@endphp
<x-atk-app title="Review Pengajuan ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">{{ $atkRequest->request_number }}</h1>
            <p class="atk-subtitle">{{ $atkRequest->user_name_snapshot }} - {{ $atkRequest->pt_name_snapshot ?? '-' }}</p>
        </div>
        <span class="atk-badge atk-badge-{{ $statusBadgeMap[$atkRequest->status] ?? 'neutral' }}">{{ $atkRequest->status }}</span>
    </div>

    <div class="atk-actions" style="margin-bottom:14px">
        <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.requests.index') }}">Kembali ke List Admin</a>
    </div>

    <div class="atk-alert atk-alert-warning">
        Halaman ini khusus admin untuk review item dan finalisasi stok, jadi semua informasi yang dibutuhkan admin dirangkum di sini.
    </div>

    @if($atkRequest->notes)
        <div class="atk-card" style="margin-bottom:14px">
            <div class="atk-product-meta">Catatan Pengaju</div>
            <div>{{ $atkRequest->notes }}</div>
        </div>
    @endif

    <div class="atk-card">
        <div class="atk-table-wrap">
            <table class="atk-table">
                <thead><tr><th>Barang</th><th>Qty</th><th>Stok Saat Ini</th><th>Status Item</th><th>{{ $isPending ? 'Aksi Review' : 'Keterangan Review' }}</th></tr></thead>
                <tbody>
                    @foreach($atkRequest->items as $requestItem)
                        @php
                            $stockQty = $requestItem->item?->stock_qty ?? 0;
                            $isInsufficient = $stockQty < $requestItem->qty;
                            $itemStatus = $requestItem->status ?? 'PENDING';
                            $reviewSummary = match (true) {
                                $itemStatus === 'APPROVED' && $isPending && $isInsufficient => 'Perlu ditinjau ulang, stok saat ini tidak cukup.',
                                $itemStatus === 'APPROVED' => 'Stok dikurangi saat finalisasi.',
                                'REJECTED' => 'Tidak mengurangi stok.',
                                default => 'Menunggu review admin.',
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $requestItem->item_name_snapshot }}</strong>
                                @if($requestItem->admin_note)
                                    <div class="atk-item-note">“{{ $requestItem->admin_note }}”</div>
                                @endif
                            </td>
                            <td>{{ $requestItem->qty }} {{ $requestItem->unit_name_snapshot }}</td>
                            <td>{{ $stockQty }} {{ $requestItem->unit_name_snapshot }}</td>
                            <td>
                                <span class="atk-badge atk-badge-{{ $itemStatusBadgeMap[$itemStatus] ?? 'neutral' }}">{{ $itemStatusLabel[$itemStatus] ?? $itemStatus }}</span>
                            </td>
                            <td>
                                @if($isPending && $itemStatus === 'PENDING')
                                    <div class="atk-item-actions">
                                        @if($isInsufficient)
                                            <button class="atk-btn atk-btn-muted atk-btn-sm" type="button" disabled>Stok tidak cukup</button>
                                        @else
                                            <form method="POST" action="{{ route('v2.atk.admin.requests.items.review', [$atkRequest, $requestItem]) }}" class="atk-item-action-form">
                                                @csrf
                                                <input type="hidden" name="status" value="APPROVED">
                                                <button class="atk-btn atk-btn-secondary atk-btn-sm" type="submit">Setujui</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('v2.atk.admin.requests.items.review', [$atkRequest, $requestItem]) }}" class="atk-item-action-form">
                                            @csrf
                                            <input type="hidden" name="status" value="REJECTED">
                                            <input class="atk-input atk-item-note-input" type="text" name="admin_note" placeholder="Alasan (wajib)" maxlength="1000" required>
                                            <button class="atk-btn atk-btn-muted atk-btn-sm" type="submit">Tidak Diproses</button>
                                        </form>
                                    </div>
                                    @if($isInsufficient)
                                        <div class="atk-inline-warning">Stok saat ini kurang. Tandai item ini sebagai Tidak diproses atau tunggu restock.</div>
                                    @endif
                                @else
                                    <div class="atk-review-note {{ $itemStatus === 'APPROVED' && $isPending && $isInsufficient ? 'is-warning' : '' }}">
                                        <strong>{{ $reviewSummary }}</strong>
                                        @if($requestItem->reviewed_at)
                                            <span>Direview {{ $requestItem->reviewed_at->format('d M Y H:i') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Ringkasan jumlah per status --}}
        <div class="atk-review-summary">
            <span class="atk-review-stat"><span class="atk-review-stat-label">Menunggu</span><span class="atk-badge atk-badge-warning">{{ $pendingCount }}</span></span>
            <span class="atk-review-stat"><span class="atk-review-stat-label">Disetujui</span><span class="atk-badge atk-badge-success">{{ $approvedCount }}</span></span>
            <span class="atk-review-stat"><span class="atk-review-stat-label">Tidak diproses</span><span class="atk-badge atk-badge-error">{{ $rejectedCount }}</span></span>
        </div>

        @if($isPending)
            <div class="atk-actions" style="justify-content:flex-end;margin-top:14px;flex-wrap:wrap;gap:8px">
                <form method="POST" action="{{ route('v2.atk.admin.requests.reject', $atkRequest) }}">
                    @csrf
                    <input class="atk-input" type="text" name="admin_note" placeholder="Alasan tolak semua (wajib)" required maxlength="1000" style="width:240px">
                    <button class="atk-btn atk-btn-danger" type="submit">Tolak Semua</button>
                </form>
                <form method="POST" action="{{ route('v2.atk.admin.requests.finalize', $atkRequest) }}">
                    @csrf
                    <button class="atk-btn atk-btn-primary" type="submit" @disabled($pendingCount > 0 || $insufficientApprovedCount > 0)>Selesaikan Review</button>
                </form>
            </div>
            @if($pendingCount > 0 || $insufficientApprovedCount > 0)
                <p class="atk-finalize-hint">
                    @if($pendingCount > 0)
                        Tombol final aktif setelah semua item direview ({{ $pendingCount }} item belum direview).
                    @elseif($insufficientApprovedCount > 0)
                        Ada {{ $insufficientApprovedCount }} item yang sudah disetujui tetapi stok saat ini tidak cukup. Ubah review item atau tunggu restock.
                    @endif
                </p>
            @endif
        @endif
    </div>

    @if($atkRequest->admin_note && !$isPending)
        <div class="atk-card" style="margin-top:14px">
            <div class="atk-product-meta">Catatan Admin</div>
            <div>{{ $atkRequest->admin_note }}</div>
        </div>
    @endif

    <style>
        .atk-item-note {
            margin-top: 4px;
            font-size: 11px;
            color: var(--error);
            font-style: italic;
            line-height: 1.4;
        }
        .atk-item-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }
        .atk-item-action-form {
            display: flex;
            gap: 4px;
            align-items: center;
        }
        .atk-item-note-input {
            min-height: 36px;
            padding: 4px 10px;
            font-size: 12px;
            width: 180px;
        }
        .atk-btn-sm {
            min-height: 36px;
            padding: 0 12px;
            font-size: 12px;
        }
        .atk-review-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            padding: 12px 0 0;
            margin-top: 12px;
            border-top: 1px solid var(--atk-border);
        }
        .atk-review-stat {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .atk-review-stat-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--atk-muted);
        }
        .atk-finalize-hint {
            margin: 8px 0 0;
            font-size: 11px;
            color: var(--atk-muted);
            text-align: right;
        }
        .atk-inline-warning {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #B45309;
        }
        .atk-review-note {
            display: grid;
            gap: 2px;
            min-width: 160px;
        }
        .atk-review-note.is-warning strong {
            color: #B45309;
        }
        .atk-review-note strong {
            font-size: 12px;
            color: var(--atk-text);
        }
        .atk-review-note span {
            font-size: 11px;
            color: var(--atk-muted);
        }
        @media (max-width: 639px) {
            .atk-item-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .atk-item-action-form {
                width: 100%;
            }
            .atk-item-note-input {
                width: 100%;
                flex: 1;
            }
        }
    </style>
</x-atk-app>
