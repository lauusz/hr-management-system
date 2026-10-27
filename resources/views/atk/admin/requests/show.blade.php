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
        'PENDING' => 'Menunggu pemeriksaan',
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
<x-atk-app title="Periksa Pengajuan ATK">
    <style>
        .atk-admin-review-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .atk-admin-review-head-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .atk-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 32px;
            padding: 0 10px;
            border-radius: 7px;
            border: 1px solid var(--atk-border);
            background: #FFFFFF;
            color: var(--atk-muted);
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 600;
            transition: all .14s ease;
        }
        .atk-back-btn:hover {
            color: var(--atk-text);
            border-color: #CBD5E1;
            background: #F8FAFC;
        }

        /* Unified Review Card */
        .atk-admin-review-panel {
            background: #FFFFFF;
            border: 1px solid var(--atk-border);
            border-radius: 10px;
            padding: 0;
            overflow: hidden;
            box-shadow: var(--atk-shadow);
        }

        /* Compact Metadata Strip */
        .atk-review-meta-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px 18px;
            padding: 13px 18px;
            background: #F8FAFC;
            border-bottom: 1px solid var(--atk-border);
        }
        .atk-review-meta-item span {
            display: block;
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748B;
        }
        .atk-review-meta-item strong {
            display: block;
            margin-top: 2px;
            font-size: 13px;
            font-weight: 600;
            color: var(--atk-text);
        }

        /* Table */
        .atk-admin-review-table-wrap {
            border: 0;
            border-radius: 0;
        }
        .atk-admin-review-table th {
            background: #FFFFFF;
            border-bottom: 1px solid var(--atk-border);
            color: #64748B;
            font-size: 11px;
        }
        .atk-admin-review-table td {
            vertical-align: middle;
            padding: 11px 16px;
        }
        .atk-admin-review-table th {
            padding: 10px 16px;
        }
        .atk-item-note {
            margin-top: 3px;
            font-size: 11.5px;
            color: #DC2626;
            line-height: 1.35;
        }
        .atk-item-actions {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            justify-content: flex-end;
        }
        .atk-item-action-form {
            display: inline-flex;
            gap: 5px;
            align-items: center;
        }
        .atk-item-note-input {
            min-height: 32px;
            height: 32px;
            padding: 0 10px;
            font-size: 12px;
            width: 165px;
            border-radius: 7px;
            background: #F8FAFC;
        }
        .atk-item-note-input:focus {
            background: #FFFFFF;
        }
        .atk-btn-sm {
            min-height: 32px;
            height: 32px;
            padding: 0 11px;
            font-size: 12px;
            border-radius: 7px;
        }
        .atk-inline-warning {
            margin-top: 5px;
            font-size: 11px;
            font-weight: 600;
            color: #B45309;
        }
        .atk-review-note {
            display: grid;
            gap: 2px;
        }
        .atk-review-note.is-warning strong {
            color: #B45309;
        }
        .atk-review-note strong {
            font-size: 12px;
            font-weight: 600;
            color: var(--atk-text);
        }
        .atk-review-note span {
            font-size: 11px;
            color: var(--atk-muted);
        }

        /* Footer Bar */
        .atk-review-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding: 12px 18px;
            background: #F8FAFC;
            border-top: 1px solid var(--atk-border);
        }
        .atk-review-footer-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .atk-review-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }
        .atk-review-stat {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--atk-muted);
        }
        .atk-review-stat strong {
            color: var(--atk-text);
            font-weight: 700;
        }
        .atk-finalize-hint {
            margin: 0;
            font-size: 11.5px;
            color: #64748B;
        }
        .atk-admin-final-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .atk-admin-reject-all-form {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .atk-admin-reject-all-input {
            min-height: 34px;
            height: 34px;
            width: 195px;
            padding: 0 10px;
            font-size: 12px;
            border-radius: 7px;
            background: #FFFFFF;
        }
        .atk-btn-reject-outline {
            min-height: 34px;
            height: 34px;
            padding: 0 12px;
            font-size: 12px;
            border-radius: 7px;
            background: #FFFFFF;
            border: 1px solid #FECACA;
            color: #DC2626;
            font-weight: 600;
        }
        .atk-btn-reject-outline:hover {
            background: #FEF2F2;
            border-color: #FCA5A5;
        }
        .atk-admin-finalize-form .atk-btn {
            min-height: 34px;
            height: 34px;
            padding: 0 14px;
            font-size: 12.5px;
            border-radius: 7px;
        }

        @media (max-width: 639px) {
            .atk-review-meta-strip {
                grid-template-columns: 1fr 1fr;
                padding: 12px 14px;
            }
            .atk-admin-review-table-wrap {
                overflow: visible;
            }
            .atk-admin-review-table {
                display: block;
                min-width: 0;
            }
            .atk-admin-review-table thead {
                display: none;
            }
            .atk-admin-review-table tbody {
                display: grid;
                gap: 10px;
                padding: 12px;
            }
            .atk-admin-review-item {
                display: block;
                padding: 12px;
                border: 1px solid var(--atk-border);
                border-radius: 10px;
                background: var(--atk-surface);
            }
            .atk-admin-review-item td {
                display: grid;
                grid-template-columns: minmax(90px, .7fr) minmax(0, 1fr);
                gap: 10px;
                padding: 6px 0;
                border: 0;
                font-size: 12px;
            }
            .atk-admin-review-item td::before {
                content: attr(data-label);
                color: var(--atk-muted);
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            .atk-admin-review-item td:first-child {
                display: block;
                padding-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid var(--atk-border-soft);
            }
            .atk-admin-review-item td:first-child::before,
            .atk-admin-review-item td:last-child::before {
                display: block;
                margin-bottom: 5px;
            }
            .atk-admin-review-item td:last-child {
                display: block;
                padding-bottom: 0;
            }
            .atk-item-actions {
                display: flex;
                flex-direction: column;
                align-items: stretch;
            }
            .atk-item-action-form {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }
            .atk-item-note-input {
                width: 100%;
            }
            .atk-item-action-form .atk-btn,
            .atk-item-actions > .atk-btn {
                width: 100%;
            }
            .atk-review-footer {
                padding: 12px 14px;
            }
            .atk-admin-final-actions,
            .atk-admin-final-actions form,
            .atk-admin-final-actions .atk-input,
            .atk-admin-final-actions .atk-btn {
                width: 100%;
            }
            .atk-admin-reject-all-form {
                display: grid;
                gap: 6px;
            }
        }
    </style>

    <div class="atk-admin-review-header">
        <div class="atk-admin-review-head-left">
            <a class="atk-back-btn" href="{{ route('v2.atk.admin.requests.index') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
            <h1 class="atk-title">{{ $atkRequest->request_number }}</h1>
            <span class="atk-badge atk-badge-{{ $statusBadgeMap[$atkRequest->status] ?? 'neutral' }}">{{ $atkRequest->status }}</span>
        </div>
    </div>

    <div class="atk-admin-review-panel">
        <div class="atk-review-meta-strip">
            <div class="atk-review-meta-item">
                <span>Pemohon</span>
                <strong>{{ $atkRequest->user_name_snapshot }}</strong>
            </div>
            <div class="atk-review-meta-item">
                <span>Unit / PT</span>
                <strong>{{ $atkRequest->pt_name_snapshot ?? '-' }}</strong>
            </div>
            <div class="atk-review-meta-item">
                <span>Waktu Pengajuan</span>
                <strong>{{ $atkRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
            </div>
            @if($atkRequest->notes)
                <div class="atk-review-meta-item">
                    <span>Catatan Pengaju</span>
                    <strong>{{ $atkRequest->notes }}</strong>
                </div>
            @endif
            @if($atkRequest->admin_note && !$isPending)
                <div class="atk-review-meta-item">
                    <span>Catatan Admin</span>
                    <strong>{{ $atkRequest->admin_note }}</strong>
                </div>
            @endif
        </div>

        <div class="atk-table-wrap atk-admin-review-table-wrap">
            <table class="atk-table atk-admin-review-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Stok Saat Ini</th>
                        <th>Status Barang</th>
                        <th style="text-align:right">{{ $isPending ? 'Aksi Pemeriksaan' : 'Keterangan Pemeriksaan' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($atkRequest->items as $requestItem)
                        @php
                            $stockQty = $requestItem->item?->stock_qty ?? 0;
                            $isInsufficient = $stockQty < $requestItem->qty;
                            $itemStatus = $requestItem->status ?? 'PENDING';
                            $reviewSummary = match (true) {
                                $itemStatus === 'APPROVED' && $isPending && $isInsufficient => 'Perlu ditinjau ulang, stok saat ini tidak cukup.',
                                $itemStatus === 'APPROVED' => 'Stok dikurangi saat finalisasi.',
                                $itemStatus === 'REJECTED' => 'Tidak mengurangi stok.',
                                default => 'Menunggu pemeriksaan admin.',
                            };
                        @endphp
                        <tr class="atk-admin-review-item">
                            <td data-label="Barang">
                                <strong>{{ $requestItem->item_name_snapshot }}</strong>
                                @if($requestItem->admin_note)
                                    <div class="atk-item-note">Catatan: {{ $requestItem->admin_note }}</div>
                                @endif
                            </td>
                            <td data-label="Jumlah" style="font-weight:600;white-space:nowrap">{{ $requestItem->qty }} {{ $requestItem->unit_name_snapshot }}</td>
                            <td data-label="Stok Saat Ini" style="white-space:nowrap;color:var(--atk-muted)">{{ $stockQty }} {{ $requestItem->unit_name_snapshot }}</td>
                            <td data-label="Status Item">
                                <span class="atk-badge atk-badge-{{ $itemStatusBadgeMap[$itemStatus] ?? 'neutral' }}">{{ $itemStatusLabel[$itemStatus] ?? $itemStatus }}</span>
                            </td>
                            <td data-label="{{ $isPending ? 'Aksi Review' : 'Keterangan Review' }}" style="text-align:right">
                                @if($isPending && $itemStatus === 'PENDING')
                                    <div class="atk-item-actions">
                                        @if($isInsufficient)
                                            <button class="atk-btn atk-btn-muted atk-btn-sm" type="button" disabled>Stok tidak cukup</button>
                                        @else
                                            <form method="POST" action="{{ route('v2.atk.admin.requests.items.review', [$atkRequest, $requestItem]) }}" class="atk-item-action-form">
                                                @csrf
                                                <input type="hidden" name="status" value="APPROVED">
                                                <button class="atk-btn atk-btn-primary atk-btn-sm" type="submit">Setujui</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('v2.atk.admin.requests.items.review', [$atkRequest, $requestItem]) }}" class="atk-item-action-form">
                                            @csrf
                                            <input type="hidden" name="status" value="REJECTED">
                                            <input class="atk-input atk-item-note-input" type="text" name="admin_note" placeholder="Alasan tolak (wajib)" maxlength="1000" required>
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
                                            <span>Diperiksa {{ $requestItem->reviewed_at->format('d M Y H:i') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="atk-review-footer">
            <div class="atk-review-footer-left">
                <div class="atk-review-summary">
                    <span class="atk-review-stat">Menunggu: <strong>{{ $pendingCount }}</strong></span>
                    <span style="color:#CBD5E1">·</span>
                    <span class="atk-review-stat">Disetujui: <strong>{{ $approvedCount }}</strong></span>
                    <span style="color:#CBD5E1">·</span>
                    <span class="atk-review-stat">Tidak diproses: <strong>{{ $rejectedCount }}</strong></span>
                </div>
                @if($isPending && ($pendingCount > 0 || $insufficientApprovedCount > 0))
                    <p class="atk-finalize-hint">
                        @if($pendingCount > 0)
                            Tombol selesai aktif setelah semua barang diperiksa ({{ $pendingCount }} barang belum diperiksa).
                        @elseif($insufficientApprovedCount > 0)
                            Ada {{ $insufficientApprovedCount }} barang yang sudah disetujui tetapi stok saat ini tidak cukup. Ubah pemeriksaan barang atau tunggu penambahan stok.
                        @endif
                    </p>
                @endif
            </div>

            @if($isPending)
                <div class="atk-admin-final-actions">
                    <form method="POST" action="{{ route('v2.atk.admin.requests.reject', $atkRequest) }}" class="atk-admin-reject-all-form">
                        @csrf
                        <input class="atk-input atk-admin-reject-all-input" type="text" name="admin_note" placeholder="Alasan tolak semua (wajib)" required maxlength="1000">
                        <button class="atk-btn atk-btn-reject-outline" type="submit">Tolak Semua</button>
                    </form>
                    <form method="POST" action="{{ route('v2.atk.admin.requests.finalize', $atkRequest) }}" class="atk-admin-finalize-form">
                        @csrf
                        <button class="atk-btn atk-btn-primary" type="submit" @disabled($pendingCount > 0 || $insufficientApprovedCount > 0)>Selesaikan Pemeriksaan</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-atk-app>
