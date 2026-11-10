@php
    $labels = [
        'PENDING' => 'Menunggu Pemeriksaan',
        'APPROVED' => 'Disetujui',
        'REJECTED' => 'Ditolak',
        'PARTIAL' => 'Disetujui Sebagian',
    ];
    $itemLabels = [
        'PENDING' => 'Belum Diperiksa',
        'APPROVED' => 'Disetujui',
        'REJECTED' => 'Ditolak',
    ];
    $badgeTone = static fn (string $st): string => match ($st) {
        'APPROVED' => 'approved',
        'REJECTED' => 'rejected',
        'PARTIAL' => 'partial',
        default => 'pending',
    };
    $pending = $pesonaRequest->status === 'PENDING';
    $counts = $pesonaRequest->items->countBy('status');
    $canFinalize = $pesonaRequest->items->isNotEmpty() && $pesonaRequest->items->every(fn ($detail) => in_array($detail->status, ['APPROVED', 'REJECTED'], true));
    $approvedTotals = $pesonaRequest->items->where('status', 'APPROVED')->groupBy('inv_pesona_item_id')->map(fn ($group) => $group->sum('qty'));
@endphp
<x-inv-pesona-app title="Detail Pengajuan — Inventory Pesona">
    <style>
        .pesona-rev-page {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .pesona-rev-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pesona-rev-head-left {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pesona-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 34px;
            padding: 0 11px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 9px;
            background: #FFFFFF;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color .14s ease, color .14s ease, border-color .14s ease;
        }
        .pesona-back-btn:hover {
            background: #F8FAFC;
            color: #0F172A;
            border-color: #CBD5E1;
        }

        /* Status Badges */
        .pesona-rev-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 24px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pesona-rev-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            flex-shrink: 0;
        }
        .pesona-rev-badge-pending { background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; }
        .pesona-rev-badge-pending .pesona-rev-dot { background: #F59E0B; }
        .pesona-rev-badge-approved { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
        .pesona-rev-badge-approved .pesona-rev-dot { background: #10B981; }
        .pesona-rev-badge-partial { background: #EEF2FF; color: #3730A3; border: 1px solid #C7D2FE; }
        .pesona-rev-badge-partial .pesona-rev-dot { background: #6366F1; }
        .pesona-rev-badge-rejected { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
        .pesona-rev-badge-rejected .pesona-rev-dot { background: #EF4444; }

        /* Main Review Card */
        .pesona-rev-panel {
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            overflow: hidden;
        }
        .pesona-rev-meta-strip {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 16px;
            padding: 14px 16px;
            background: #F8FAFC;
            border-bottom: 1px solid var(--pesona-border, #E2E8F0);
        }
        .pesona-rev-meta-item {
            min-width: 0;
        }
        .pesona-rev-meta-item.is-wide {
            grid-column: 1 / -1;
        }
        .pesona-rev-meta-label {
            display: block;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748B;
        }
        .pesona-rev-meta-val {
            display: block;
            margin-top: 2px;
            font-size: 13px;
            font-weight: 700;
            color: #0F172A;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .pesona-rev-notice {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #FFFBEB;
            border-bottom: 1px solid #FDE68A;
            color: #92400E;
            font-size: 12.5px;
            font-weight: 600;
        }

        /* Compact Item Review List */
        .pesona-rev-items-list {
            display: flex;
            flex-direction: column;
        }
        .pesona-rev-item {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid #F1F5F9;
            transition: background-color .15s ease;
        }
        .pesona-rev-item:last-child {
            border-bottom: 0;
        }
        .pesona-rev-item.is-row-approved {
            background: #F7FEFA;
            box-shadow: inset 4px 0 0 #16A34A;
        }
        .pesona-rev-item.is-row-rejected {
            background: #FFFBFB;
            box-shadow: inset 4px 0 0 #DC2626;
        }

        .pesona-rev-item-row {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Left: Thumbnail + Item Info */
        .pesona-rev-item-left {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }
        .pesona-rev-thumb {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94A3B8;
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.04em;
            overflow: hidden;
        }
        .pesona-rev-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .pesona-rev-item-info {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .pesona-rev-item-title-line {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pesona-rev-item-name {
            font-size: 14px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }
        .pesona-rev-item-chips {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }
        .pesona-rev-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 6px;
            background: #F1F5F9;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
        }
        .pesona-rev-chip strong {
            color: #0F172A;
            font-weight: 800;
        }
        .pesona-rev-chip.is-req-qty {
            background: rgba(40, 53, 90, 0.08);
            color: var(--pesona-primary, #28355A);
        }
        .pesona-rev-chip.is-req-qty strong {
            color: var(--pesona-primary, #28355A);
        }

        /* Right: Quick Setuju / Tolak Buttons */
        .pesona-rev-item-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            flex-shrink: 0;
        }
        .pesona-rev-quick-form {
            margin: 0;
            display: inline-flex;
        }
        .pesona-rev-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 36px;
            padding: 0 14px;
            border-radius: 9px;
            font: inherit;
            font-size: 12.5px;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
            transition: all .14s ease;
        }
        .pesona-rev-btn-approve {
            background: #FFFFFF;
            border: 1.5px solid #16A34A;
            color: #15803D;
        }
        .pesona-rev-btn-approve:hover {
            background: #ECFDF5;
        }
        .pesona-rev-btn-approve.is-active {
            background: #16A34A;
            border-color: #16A34A;
            color: #FFFFFF;
            box-shadow: 0 1px 2px rgba(22, 163, 74, 0.25);
        }

        .pesona-rev-btn-reject {
            background: #FFFFFF;
            border: 1.5px solid #DC2626;
            color: #B91C1C;
        }
        .pesona-rev-btn-reject:hover {
            background: #FEF2F2;
        }
        .pesona-rev-btn-reject.is-active {
            background: #DC2626;
            border-color: #DC2626;
            color: #FFFFFF;
            box-shadow: 0 1px 2px rgba(220, 38, 38, 0.25);
        }

        /* Inline Rejection Reason Box (Only shown when Tolak is clicked or item is REJECTED) */
        .pesona-rev-reject-box {
            display: none;
            margin-top: 2px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
        }
        .pesona-rev-reject-box.is-open {
            display: block;
        }
        .pesona-rev-reject-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin: 0;
        }
        .pesona-rev-reject-input {
            width: 100%;
            min-height: 36px;
            padding: 6px 11px;
            border: 1px solid #FCA5A5;
            border-radius: 8px;
            background: #FFFFFF;
            color: #0F172A;
            font: inherit;
            font-size: 13px;
            box-sizing: border-box;
        }
        .pesona-rev-reject-input:focus {
            outline: none;
            border-color: #DC2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        }
        .pesona-rev-reject-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .pesona-rev-btn-confirm-reject {
            height: 36px;
            padding: 0 14px;
            border: 0;
            border-radius: 8px;
            background: #DC2626;
            color: #FFFFFF;
            font: inherit;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }
        .pesona-rev-btn-confirm-reject:hover {
            background: #B91C1C;
        }
        .pesona-rev-btn-cancel-reject {
            height: 36px;
            padding: 0 11px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            background: #FFFFFF;
            color: #475569;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }

        .pesona-rev-item-note {
            margin: 2px 0 0;
            padding: 6px 10px;
            border-radius: 7px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
            font-size: 12px;
            white-space: pre-wrap;
        }
        .pesona-rev-item-audit {
            margin: 0;
            font-size: 11px;
            color: #64748B;
        }
        .pesona-rev-item-warn {
            margin: 2px 0 0;
            padding: 7px 10px;
            border-radius: 8px;
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            color: #92400E;
            font-size: 12px;
            font-weight: 600;
        }
        .pesona-rev-error {
            display: block;
            margin-top: 4px;
            font-size: 11.5px;
            font-weight: 700;
            color: #B91C1C;
        }

        /* Footer Bar */
        .pesona-rev-footer {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 14px 16px;
            background: #F8FAFC;
            border-top: 1px solid var(--pesona-border, #E2E8F0);
        }
        .pesona-rev-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            font-size: 12.5px;
            color: #475569;
        }
        .pesona-rev-summary strong {
            color: #0F172A;
            font-weight: 800;
        }
        .pesona-rev-finalize-hint {
            margin: 2px 0 0;
            font-size: 12px;
            color: #64748B;
        }
        .pesona-rev-finalize-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 40px;
            padding: 0 18px;
            border: 0;
            border-radius: 10px;
            background: var(--pesona-accent, #BA3925);
            color: #FFFFFF;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color .15s ease;
        }
        .pesona-rev-finalize-btn:hover {
            background: #9E2F1E;
        }

        /* Modal Summary & Buttons */
        .pesona-fin-summary-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 12px 0;
        }
        .pesona-fin-summary-card {
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 12.5px;
        }
        .pesona-fin-summary-card.is-ok {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }
        .pesona-fin-summary-card.is-no {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }
        .pesona-fin-summary-num {
            display: block;
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 2px;
        }
        .pesona-fin-summary-sub {
            display: block;
            font-size: 11.5px;
            opacity: 0.9;
        }
        .pesona-fin-note {
            margin: 0;
            font-size: 13px;
            color: #475569;
            line-height: 1.45;
        }
        .pesona-fin-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #F1F5F9;
        }
        .pesona-fin-btn-cancel {
            height: 38px;
            padding: 0 14px;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            background: #FFFFFF;
            color: #475569;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .pesona-fin-btn-cancel:hover {
            background: #F8FAFC;
            color: #0F172A;
        }
        .pesona-fin-btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 38px;
            padding: 0 16px;
            border: 0;
            border-radius: 9px;
            background: var(--pesona-primary, #28355A);
            color: #FFFFFF;
            font: inherit;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }
        .pesona-fin-btn-submit:hover {
            background: #1E2846;
        }

        @media (min-width: 640px) {
            .pesona-rev-item-row {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .pesona-rev-reject-form {
                flex-direction: row;
                align-items: center;
            }
        }

        @media (min-width: 768px) {
            .pesona-rev-meta-strip {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                padding: 14px 18px;
            }
            .pesona-rev-item {
                padding: 14px 18px;
            }
            .pesona-rev-footer {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 14px 18px;
            }
        }
    </style>

    <div class="pesona-rev-page">
        {{-- Header --}}
        <div class="pesona-rev-head">
            <div class="pesona-rev-head-left">
                <a class="pesona-back-btn" href="{{ route('inventory.pesona.admin.requests.index') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    <span>Kembali ke Daftar</span>
                </a>
                <h1 class="pesona-title">{{ $pesonaRequest->request_number }}</h1>
                <span class="pesona-rev-badge pesona-rev-badge-{{ $badgeTone($pesonaRequest->status) }}">
                    <span class="pesona-rev-dot"></span>
                    {{ $labels[$pesonaRequest->status] ?? 'Status Tidak Dikenal' }}
                </span>
            </div>
        </div>

        <div class="pesona-rev-panel">
            {{-- Metadata Strip --}}
            <div class="pesona-rev-meta-strip">
                <div class="pesona-rev-meta-item">
                    <span class="pesona-rev-meta-label">Pemohon</span>
                    <strong class="pesona-rev-meta-val">{{ $pesonaRequest->user_name_snapshot }}</strong>
                </div>
                <div class="pesona-rev-meta-item">
                    <span class="pesona-rev-meta-label">PT</span>
                    <strong class="pesona-rev-meta-val">{{ $pesonaRequest->pt_name_snapshot ?? '—' }}</strong>
                </div>
                <div class="pesona-rev-meta-item">
                    <span class="pesona-rev-meta-label">Waktu Pengajuan</span>
                    <strong class="pesona-rev-meta-val">{{ $pesonaRequest->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</strong>
                </div>
                <div class="pesona-rev-meta-item">
                    <span class="pesona-rev-meta-label">Nopol Armada</span>
                    <strong class="pesona-rev-meta-val">{{ $pesonaRequest->nopol_snapshot ?? 'Belum dicatat' }}</strong>
                </div>
                @if($pesonaRequest->approved_at || $pesonaRequest->approved_by)
                    <div class="pesona-rev-meta-item">
                        <span class="pesona-rev-meta-label">Finalisasi</span>
                        <strong class="pesona-rev-meta-val">{{ $pesonaRequest->approved_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }} · {{ $pesonaRequest->approvedBy?->name ?? '—' }}</strong>
                    </div>
                @elseif($pesonaRequest->rejected_at || $pesonaRequest->rejected_by)
                    <div class="pesona-rev-meta-item">
                        <span class="pesona-rev-meta-label">Finalisasi Penolakan</span>
                        <strong class="pesona-rev-meta-val">{{ $pesonaRequest->rejected_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }} · {{ $pesonaRequest->rejectedBy?->name ?? '—' }}</strong>
                    </div>
                @endif
                @if($pesonaRequest->notes !== null && $pesonaRequest->notes !== '')
                    <div class="pesona-rev-meta-item is-wide">
                        <span class="pesona-rev-meta-label">Tujuan Pengambilan</span>
                        <strong class="pesona-rev-meta-val">{{ $pesonaRequest->notes }}</strong>
                    </div>
                @endif
                @if($pesonaRequest->admin_note !== null && $pesonaRequest->admin_note !== '')
                    <div class="pesona-rev-meta-item is-wide">
                        <span class="pesona-rev-meta-label">Catatan Admin</span>
                        <strong class="pesona-rev-meta-val">{{ $pesonaRequest->admin_note }}</strong>
                    </div>
                @endif
            </div>

            @if($pending)
                <div class="pesona-rev-notice">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>Klik <strong>Setuju</strong> atau <strong>Tolak</strong> pada setiap barang, lalu klik <strong>Selesaikan Pemeriksaan</strong> di bagian bawah.</span>
                </div>
            @endif

            {{-- Items Review List --}}
            <div class="pesona-rev-items-list">
                @forelse($pesonaRequest->items as $detail)
                    @php
                        $master = $detail->item;
                        $invalidMaster = ! $master || $master->deleted_at !== null || ! $master->is_active || $master->unit_name !== $detail->unit_name_snapshot || $master->unit_size !== $detail->unit_size_snapshot || $master->content_unit_name !== $detail->content_unit_name_snapshot;
                        $isOldReview = is_scalar(old('review_form_id')) && (string) old('review_form_id') === (string) $detail->id;
                        $rowToneClass = match ($detail->status) {
                            'APPROVED' => 'is-row-approved',
                            'REJECTED' => 'is-row-rejected',
                            default => '',
                        };
                        $showRejectBox = $isOldReview && ($errors->has('admin_note') || old('status') === 'REJECTED');
                    @endphp
                    <article class="pesona-rev-item {{ $rowToneClass }}">
                        <div class="pesona-rev-item-row">
                            {{-- Left: Thumbnail + Item Info --}}
                            <div class="pesona-rev-item-left">
                                <div class="pesona-rev-thumb">
                                    @if($master?->image_path)
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($master->image_path) }}"
                                            alt="Foto {{ $detail->item_name_snapshot }}"
                                            loading="lazy"
                                        >
                                    @else
                                        <span>PESONA</span>
                                    @endif
                                </div>

                                <div class="pesona-rev-item-info">
                                    <div class="pesona-rev-item-title-line">
                                        <strong class="pesona-rev-item-name">{{ $detail->item_name_snapshot }}</strong>
                                        <span class="pesona-rev-badge pesona-rev-badge-{{ $badgeTone($detail->status) }}">
                                            <span class="pesona-rev-dot"></span>
                                            {{ $itemLabels[$detail->status] ?? 'Status Tidak Dikenal' }}
                                        </span>
                                    </div>

                                    <div class="pesona-rev-item-chips">
                                        <span class="pesona-rev-chip is-req-qty">
                                            Diajukan: <strong>{{ $detail->qty }} {{ $detail->unit_name_snapshot }}</strong>
                                            @if($detail->unit_size_snapshot > 1)
                                                ({{ number_format((int) $detail->qty * (int) $detail->unit_size_snapshot, 0, ',', '.') }} {{ $detail->content_unit_name_snapshot }})
                                            @endif
                                        </span>
                                        @if($master)
                                            <span class="pesona-rev-chip">
                                                Stok tersedia: <strong>{{ $master->stock_qty }} {{ $master->unit_name }}</strong>
                                                @if($master->deleted_at !== null)
                                                    · Terhapus
                                                @elseif(! $master->is_active)
                                                    · Nonaktif
                                                @endif
                                            </span>
                                        @else
                                            <span class="pesona-rev-chip" style="color:#991B1B;background:#FEF2F2;">Master barang tidak tersedia</span>
                                        @endif
                                    </div>

                                    @if($detail->admin_note !== null && $detail->admin_note !== '' && ! $showRejectBox)
                                        <p class="pesona-rev-item-note">Alasan ditolak: {{ $detail->admin_note }}</p>
                                    @endif

                                    @if($detail->reviewed_at || $detail->reviewed_by)
                                        <p class="pesona-rev-item-audit">
                                            Diperiksa {{ $detail->reviewed_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }} oleh {{ $detail->reviewedBy?->name ?? '—' }}
                                        </p>
                                    @endif

                                    @if($pending && $detail->status === 'APPROVED' && ($invalidMaster || $master->stock_qty < $approvedTotals[$detail->inv_pesona_item_id]))
                                        <p class="pesona-rev-item-warn">Barang tidak tersedia, satuan berubah, atau stok total tidak mencukupi. Periksa sebelum finalisasi.</p>
                                    @endif

                                    @if($isOldReview && $errors->has('status'))
                                        <span class="pesona-rev-error">{{ $errors->first('status') }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Right: Quick Action Buttons (Setuju / Tolak) --}}
                            @if($pending)
                                <div class="pesona-rev-item-right">
                                    <form class="pesona-rev-quick-form" method="POST" action="{{ route('inventory.pesona.admin.requests.items.review', [$pesonaRequest, $detail]) }}">
                                        @csrf
                                        <input type="hidden" name="review_form_id" value="{{ $detail->id }}">
                                        <input type="hidden" name="status" value="APPROVED">
                                        <button
                                            type="submit"
                                            class="pesona-rev-btn pesona-rev-btn-approve {{ $detail->status === 'APPROVED' ? 'is-active' : '' }}"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <span>Setuju</span>
                                        </button>
                                    </form>

                                    <button
                                        type="button"
                                        class="pesona-rev-btn pesona-rev-btn-reject {{ $detail->status === 'REJECTED' || $showRejectBox ? 'is-active' : '' }}"
                                        data-toggle-reject="reject-box-{{ $detail->id }}"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Inline Rejection Reason Input (Shown when Tolak button is clicked) --}}
                        @if($pending)
                            <div id="reject-box-{{ $detail->id }}" class="pesona-rev-reject-box {{ $showRejectBox ? 'is-open' : '' }}">
                                <form class="pesona-rev-reject-form" method="POST" action="{{ route('inventory.pesona.admin.requests.items.review', [$pesonaRequest, $detail]) }}">
                                    @csrf
                                    <input type="hidden" name="review_form_id" value="{{ $detail->id }}">
                                    <input type="hidden" name="status" value="REJECTED">
                                    <input
                                        type="text"
                                        name="admin_note"
                                        class="pesona-rev-reject-input"
                                        maxlength="1000"
                                        required
                                        placeholder="Tulis alasan penolakan untuk barang ini (wajib diisi)..."
                                        value="{{ $isOldReview ? (is_string(old('admin_note')) ? old('admin_note') : '') : $detail->admin_note }}"
                                        @if($isOldReview && $errors->has('admin_note')) aria-invalid="true" @endif
                                    >
                                    <div class="pesona-rev-reject-actions">
                                        <button type="submit" class="pesona-rev-btn-confirm-reject">Simpan Tolak</button>
                                        <button type="button" class="pesona-rev-btn-cancel-reject" data-cancel-reject="reject-box-{{ $detail->id }}">Batal</button>
                                    </div>
                                </form>
                                @if($isOldReview)
                                    @error('admin_note')<span class="pesona-rev-error">{{ $message }}</span>@enderror
                                @endif
                            </div>
                        @endif
                    </article>
                @empty
                    <div style="padding:24px;text-align:center;color:#64748B">Detail barang tidak tersedia.</div>
                @endforelse
            </div>

            {{-- Footer Summary & Finalization --}}
            <div class="pesona-rev-footer">
                <div>
                    <div class="pesona-rev-summary">
                        <span>Belum diperiksa: <strong>{{ $counts['PENDING'] ?? 0 }}</strong></span>
                        <span style="color:#CBD5E1">·</span>
                        <span>Disetujui: <strong>{{ $counts['APPROVED'] ?? 0 }}</strong></span>
                        <span style="color:#CBD5E1">·</span>
                        <span>Ditolak: <strong>{{ $counts['REJECTED'] ?? 0 }}</strong> baris</span>
                    </div>
                    @if($pending && ! $canFinalize)
                        <p class="pesona-rev-finalize-hint">Tombol Selesaikan Pemeriksaan akan muncul setelah semua barang dipilih Setuju atau Tolak.</p>
                    @endif
                </div>

                @if($pending && $canFinalize)
                    <div>
                        <button class="pesona-rev-finalize-btn" id="pesona-finalize-open" type="button" aria-haspopup="dialog" aria-controls="confirm-pesona-finalize">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span>Selesaikan Pemeriksaan</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (() => {
            document.addEventListener('click', (event) => {
                const toggleBtn = event.target.closest('[data-toggle-reject]');
                if (toggleBtn) {
                    const boxId = toggleBtn.getAttribute('data-toggle-reject');
                    const box = boxId ? document.getElementById(boxId) : null;
                    if (box) {
                        const isOpen = box.classList.toggle('is-open');
                        if (isOpen) {
                            box.querySelector('input[name="admin_note"]')?.focus();
                        }
                    }
                    return;
                }

                const cancelBtn = event.target.closest('[data-cancel-reject]');
                if (cancelBtn) {
                    const boxId = cancelBtn.getAttribute('data-cancel-reject');
                    const box = boxId ? document.getElementById(boxId) : null;
                    if (box) {
                        box.classList.remove('is-open');
                    }
                }
            });
        })();
    </script>

    @if($pending && $canFinalize)
        <x-modal id="confirm-pesona-finalize" title="Selesaikan Pemeriksaan Ini?" type="form" variant="warning">
            <div class="pesona-fin-summary-box">
                <div class="pesona-fin-summary-card is-ok">
                    <span class="pesona-fin-summary-num">{{ $counts['APPROVED'] ?? 0 }} Barang Disetujui</span>
                    <span class="pesona-fin-summary-sub">Stok gudang otomatis dikurangi</span>
                </div>
                <div class="pesona-fin-summary-card is-no">
                    <span class="pesona-fin-summary-num">{{ $counts['REJECTED'] ?? 0 }} Barang Ditolak</span>
                    <span class="pesona-fin-summary-sub">Stok tidak berkurang</span>
                </div>
            </div>
            <p class="pesona-fin-note">
                Pastikan keputusan Anda sudah benar. Setelah disimpan, pemeriksaan ini selesai dan tidak bisa diubah lagi.
            </p>
            <form method="POST" action="{{ route('inventory.pesona.admin.requests.finalize', $pesonaRequest) }}" class="pesona-fin-actions">
                @csrf
                <button class="pesona-fin-btn-cancel" type="button" data-modal-close>Cek Lagi</button>
                <button class="pesona-fin-btn-submit" type="submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>Ya, Simpan & Selesaikan</span>
                </button>
            </form>
        </x-modal>
        <script>
            (() => {
                const trigger = document.getElementById('pesona-finalize-open'), modal = document.getElementById('confirm-pesona-finalize');
                let open = false;
                function close() { if (!open) return; open = false; modal.style.display = 'none'; document.body.style.overflow = ''; trigger.focus(); }
                trigger.addEventListener('click', () => { open = true; modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; modal.querySelector('[data-modal-close]').focus(); });
                modal.addEventListener('click', (event) => { if (event.target === modal || event.target.closest('[data-modal-close]')) close(); });
                document.addEventListener('keydown', (event) => {
                    if (!open) return;
                    if (event.key === 'Escape') { close(); return; }
                    if (event.key !== 'Tab') return;
                    const buttons = Array.from(modal.querySelectorAll('button:not([disabled])')).filter((button) => button.getClientRects().length);
                    const first = buttons[0], last = buttons[buttons.length - 1];
                    if (event.shiftKey && (document.activeElement === first || !modal.contains(document.activeElement))) { event.preventDefault(); last.focus(); }
                    else if (!event.shiftKey && (document.activeElement === last || !modal.contains(document.activeElement))) { event.preventDefault(); first.focus(); }
                });
            })();
        </script>
    @endif
</x-inv-pesona-app>
