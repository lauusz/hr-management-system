<x-app title="Detail Asset">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 7.5A2.5 2.5 0 0 1 7.5 5h9A2.5 2.5 0 0 1 19 7.5v9A2.5 2.5 0 0 1 16.5 19h-9A2.5 2.5 0 0 1 5 16.5v-9Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 9h6M9 13h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Inventaris Karyawan</h1>
                <p class="section-subtitle">Data asset, pemegang, dan riwayat movement.</p>
            </div>
        </div>
    </x-slot>

    @php
        $statusLabels = [
            'AVAILABLE' => 'Tersedia',
            'ASSIGNED' => 'Dipakai',
            'SERVICE' => 'Service',
            'LOST' => 'Hilang',
            'DISPOSAL' => 'Disposal',
        ];

        $conditionLabels = [
            'GOOD' => 'Baik',
            'NEED_REPAIR' => 'Perlu Service',
            'DAMAGED' => 'Rusak',
        ];

        $movementLabels = [
            'TRANSFER' => 'Transfer',
            'ASSIGN' => 'Assign',
            'RETURN' => 'Return',
            'SERVICE' => 'Service',
            'LOST' => 'Lost',
            'DISPOSAL' => 'Disposal',
        ];

        $statusClass = match ($asset->asset_status) {
            'ASSIGNED' => 'badge-success',
            'SERVICE' => 'badge-warning',
            'LOST', 'DISPOSAL' => 'badge-danger',
            default => 'badge-neutral',
        };

        $brandModel = trim(($asset->brand ?: '') . ' ' . ($asset->model ?: ''));
    @endphp

    <style>
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-navy {
            color: var(--white);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 10px 24px rgba(10, 61, 98, .18);
        }

        .section-title {
            margin: 0;
            color: var(--text-primary);
            font-size: 20px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: 0;
        }

        .section-subtitle {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
        }

        .asset-detail-page {
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 16px 32px;
        }

        .page-header {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 16px;
        }

        .btn-back,
        .btn-primary-action {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 14px;
            border-radius: 12px;
            border: 1.5px solid transparent;
            font: inherit;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .btn-back {
            color: var(--text-secondary);
            background: var(--white);
            border-color: var(--border);
            box-shadow: 0 4px 14px rgba(17, 24, 39, .05);
        }

        .btn-primary-action {
            color: var(--white);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 10px 24px rgba(20, 93, 160, .22);
        }

        .btn-back:hover,
        .btn-primary-action:hover {
            transform: translateY(-1px);
        }

        .asset-stack {
            display: grid;
            gap: 16px;
        }

        .asset-hero-card,
        .section-card {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--white);
            box-shadow: 0 10px 28px rgba(17, 24, 39, .06);
        }

        .asset-hero-accent {
            height: 6px;
            background: linear-gradient(90deg, var(--primary-dark), var(--primary), var(--accent-gold));
        }

        .asset-hero-body {
            display: grid;
            gap: 16px;
            padding: 18px;
        }

        .asset-photo-wrap {
            width: 100%;
            aspect-ratio: 16 / 10;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--gray-50);
        }

        .asset-photo-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
            line-height: 1.3;
        }

        .asset-photo {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .asset-photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
            text-align: center;
        }

        .asset-title-row {
            display: grid;
            gap: 10px;
        }

        .asset-name {
            margin: 0;
            color: var(--text-primary);
            font-size: 24px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: 0;
        }

        .asset-meta {
            margin: 8px 0 0;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.5;
        }

        .asset-summary-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 16px;
        }

        .summary-item {
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--background);
        }

        .summary-label,
        .field-label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
            line-height: 1.3;
        }

        .summary-value,
        .info-value {
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 800;
            line-height: 1.45;
        }

        .status-badge,
        .movement-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            min-height: 30px;
            padding: 0 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
        }

        .badge-success {
            color: #15803D;
            background: rgba(34, 197, 94, .12);
        }

        .badge-warning {
            color: #B45309;
            background: rgba(245, 158, 11, .14);
        }

        .badge-danger {
            color: #B91C1C;
            background: rgba(239, 68, 68, .12);
        }

        .badge-neutral {
            color: var(--primary-dark);
            background: rgba(20, 93, 160, .10);
        }

        .asset-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .section-card {
            padding: 18px;
        }

        .section-card-header {
            margin-bottom: 16px;
        }

        .movement-form .section-card-header {
            grid-column: 1 / -1;
        }

        .section-card-title {
            margin: 0;
            color: var(--text-primary);
            font-size: 16px;
            font-weight: 800;
            line-height: 1.3;
        }

        .section-card-subtitle {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
            line-height: 1.5;
        }

        .info-grid,
        .movement-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .info-row:first-child {
            padding-top: 0;
        }

        .info-row:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .form-field {
            min-width: 0;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            min-height: 44px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 0 14px;
            color: var(--text-primary);
            background: var(--white);
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .form-textarea {
            min-height: 92px;
            padding: 12px 14px;
            resize: vertical;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, .10);
        }

        .submit-row {
            display: flex;
            justify-content: flex-end;
        }

        .submit-row .btn-primary-action {
            width: 100%;
            border: 0;
        }

        .history-list {
            display: grid;
            gap: 12px;
        }

        .history-item {
            display: grid;
            gap: 8px;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--background);
        }

        .history-route {
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 800;
            line-height: 1.45;
        }

        .history-meta,
        .empty-text {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
            line-height: 1.5;
        }

        .doc-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: fit-content;
            color: var(--primary);
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .doc-link:hover {
            text-decoration: underline;
        }

        .empty-state {
            padding: 18px;
            border: 1px dashed var(--border);
            border-radius: 14px;
            background: var(--background);
            text-align: center;
        }

        @media (min-width: 640px) {
            .asset-summary-list,
            .movement-form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .submit-row {
                grid-column: 1 / -1;
            }

            .submit-row .btn-primary-action {
                width: auto;
                min-width: 180px;
            }
        }

        @media (min-width: 768px) {
            .asset-detail-page {
                padding: 0 24px 40px;
            }

            .asset-hero-body {
                grid-template-columns: 260px minmax(0, 1fr);
                align-items: center;
                padding: 22px;
            }

            .asset-title-row {
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: flex-start;
            }
        }

        @media (min-width: 1024px) {
            .asset-detail-page {
                padding-left: 0;
                padding-right: 0;
            }

            .asset-layout {
                grid-template-columns: minmax(0, .9fr) minmax(360px, .7fr);
                align-items: start;
            }
        }
    </style>

    <div class="asset-detail-page">
        <div class="page-header">
            <a class="btn-back" href="{{ route('hr.assets.index') }}" aria-label="Kembali ke daftar asset">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Kembali
            </a>
            <a class="btn-primary-action" href="{{ route('hr.assets.edit', $asset) }}">
                Edit Asset
            </a>
        </div>

        <div class="asset-stack">
            <section class="asset-hero-card">
                <div class="asset-hero-accent"></div>
                <div class="asset-hero-body">
                    <div>
                        <span class="asset-photo-label">Foto Asset</span>
                        <div class="asset-photo-wrap">
                            @if($asset->photo_path)
                                <button type="button" data-image-viewer-src="{{ asset('storage/'.$asset->photo_path) }}" data-image-viewer-alt="Foto asset {{ $asset->name }}" style="width:100%; height:100%; border:0; padding:0; background:transparent; cursor:pointer;">
                                    <img class="asset-photo" src="{{ asset('storage/'.$asset->photo_path) }}" alt="Foto {{ $asset->name }}">
                                </button>
                            @else
                                <div class="asset-photo-placeholder">
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9Z" stroke="currentColor" stroke-width="2"/>
                                        <path d="M8 15l2.2-2.2a1 1 0 0 1 1.4 0L13 14.2l1.2-1.2a1 1 0 0 1 1.4 0L18 15.4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M8.5 9.5h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    Belum ada foto asset
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="asset-title-row">
                            <div>
                                <h2 class="asset-name">{{ $asset->name }}</h2>
                                <p class="asset-meta">
                                    {{ $asset->asset_code }} &middot; {{ $asset->category->name ?? 'Tanpa kategori' }}
                                </p>
                                <p class="asset-meta">
                                    {{ $brandModel !== '' ? $brandModel : 'Brand / model belum diisi' }}
                                </p>
                            </div>
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusLabels[$asset->asset_status] ?? $asset->asset_status }}
                            </span>
                        </div>

                        <div class="asset-summary-list">
                            <div class="summary-item">
                                <span class="summary-label">Pemegang</span>
                                <div class="summary-value">{{ $asset->currentUser->name ?? '-' }}</div>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">PT</span>
                                <div class="summary-value">{{ $asset->currentPt->name ?? '-' }}</div>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Kondisi</span>
                                <div class="summary-value">{{ $conditionLabels[$asset->condition_status] ?? $asset->condition_status }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="asset-layout">
                <section class="section-card">
                    <div class="section-card-header">
                        <h3 class="section-card-title">Informasi Asset</h3>
                        <p class="section-card-subtitle">Identitas teknis dan posisi asset saat ini.</p>
                    </div>

                    <div class="info-grid">
                        <div class="info-row">
                            <span class="field-label">Status</span>
                            <div class="info-value">{{ $statusLabels[$asset->asset_status] ?? $asset->asset_status }}</div>
                        </div>
                        <div class="info-row">
                            <span class="field-label">Kondisi</span>
                            <div class="info-value">{{ $conditionLabels[$asset->condition_status] ?? $asset->condition_status }}</div>
                        </div>
                        <div class="info-row">
                            <span class="field-label">Brand / Model</span>
                            <div class="info-value">{{ $brandModel !== '' ? $brandModel : '-' }}</div>
                        </div>
                        <div class="info-row">
                            <span class="field-label">Serial Number</span>
                            <div class="info-value">{{ $asset->serial_number ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <span class="field-label">Hostname</span>
                            <div class="info-value">{{ $asset->hostname ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <span class="field-label">Email Laptop</span>
                            <div class="info-value">{{ $asset->email_laptop ?: '-' }}</div>
                        </div>
                    </div>
                </section>

                <form class="section-card movement-form" method="POST" action="{{ route('hr.assets.movements.store', $asset) }}">
                    @csrf
                    <div class="section-card-header">
                        <h3 class="section-card-title">Catat Movement</h3>
                        <p class="section-card-subtitle">Gunakan saat asset pindah tangan, service, return, atau disposal.</p>
                    </div>

                    <div class="form-field">
                        <label class="field-label" for="movement_type">Jenis Movement</label>
                        <select class="form-select" id="movement_type" name="movement_type" required>
                            @foreach($movementLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label class="field-label" for="movement_date">Tanggal</label>
                        <input class="form-input" id="movement_date" type="date" name="movement_date" value="{{ now()->toDateString() }}" required>
                    </div>

                    <div class="form-field">
                        <label class="field-label" for="to_user_id">Ke Karyawan</label>
                        <select class="form-select" id="to_user_id" name="to_user_id">
                            <option value="">-</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label class="field-label" for="to_pt_id">Ke PT</label>
                        <select class="form-select" id="to_pt_id" name="to_pt_id">
                            <option value="">-</option>
                            @foreach($pts as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label class="field-label" for="condition_after">Kondisi Setelah</label>
                        <select class="form-select" id="condition_after" name="condition_after">
                            @foreach($conditionLabels as $value => $label)
                                <option value="{{ $value }}" @selected($asset->condition_status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label class="field-label" for="notes">Catatan</label>
                        <textarea class="form-textarea" id="notes" name="notes"></textarea>
                    </div>

                    <div class="submit-row">
                        <button class="btn-primary-action" type="submit">Catat Movement</button>
                    </div>
                </form>
            </div>

            <section class="section-card">
                <div class="section-card-header">
                    <h3 class="section-card-title">Riwayat Movement</h3>
                    <p class="section-card-subtitle">Log perpindahan dan perubahan kondisi asset.</p>
                </div>

                <div class="history-list">
                    @forelse($asset->movements->sortByDesc('movement_date') as $movement)
                        <div class="history-item">
                            <span class="movement-badge badge-neutral">
                                {{ $movementLabels[$movement->movement_type] ?? $movement->movement_type }}
                            </span>
                            <div class="history-route">
                                {{ $movement->fromUser->name ?? '-' }} ke {{ $movement->toUser->name ?? '-' }}
                            </div>
                            <div class="history-meta">
                                {{ $movement->movement_date->format('d/m/Y') }} &middot; {{ $movement->notes ?: '-' }}
                            </div>
                            @if($movement->handover_document_path)
                                <a class="doc-link" href="{{ asset('storage/'.$movement->handover_document_path) }}" target="_blank" rel="noopener">
                                    Dokumen Serah Terima
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-text">Belum ada riwayat movement.</div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app>
