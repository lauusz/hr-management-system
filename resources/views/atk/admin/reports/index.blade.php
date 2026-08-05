<x-atk-app title="Rekap ATK">
    <header class="atk-report-header">
        <div>
            <h1 class="atk-title">Rekap ATK</h1>
            <p class="atk-report-context">
                <strong>{{ $periodLabel }}</strong>
                <span aria-hidden="true">•</span>
                <span>{{ $selectedPtName }}</span>
                <span aria-hidden="true">•</span>
                <span>Diperbarui {{ $generatedAt->format('d/m/Y H:i') }}</span>
            </p>
        </div>
        <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.admin.reports.export', request()->query()) }}">Unduh Excel</a>
    </header>

    <form method="GET" class="atk-card atk-report-filter">
        <div>
            <label class="atk-label" for="month">Bulan</label>
            <input class="atk-input" id="month" type="month" name="month" value="{{ $month }}">
        </div>
        <div>
            <label class="atk-label" for="pt_id">PT</label>
            <select class="atk-select" id="pt_id" name="pt_id">
                <option value="">Semua PT</option>
                @foreach($pts as $pt)
                    <option value="{{ $pt->id }}" @selected((string) request('pt_id') === (string) $pt->id)>{{ $pt->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="atk-actions atk-report-filter-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Tampilkan</button>
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.reports.index') }}">Reset</a>
        </div>
    </form>

    <section class="atk-report-summary-grid" aria-label="Ringkasan laporan">
        <div class="atk-card atk-report-stat">
            <span>Pengajuan</span>
            <strong>{{ $summary['request_count'] }}</strong>
            <small>Sudah disetujui</small>
        </div>
        <div class="atk-card atk-report-stat">
            <span>Pengambil</span>
            <strong>{{ $summary['user_count'] }}</strong>
            <small>Karyawan aktif</small>
        </div>
        <div class="atk-card atk-report-stat">
            <span>PT</span>
            <strong>{{ $summary['pt_count'] }}</strong>
            <small>Memiliki pengajuan</small>
        </div>
        <div class="atk-card atk-report-stat">
            <span>Jenis Barang</span>
            <strong>{{ $summary['item_count'] }}</strong>
            <small>Barang yang diambil</small>
        </div>
    </section>

    <div class="atk-report-visual-grid">
        <section class="atk-card atk-report-panel atk-report-pt-chart" aria-labelledby="atk-report-pt-chart-title">
            <div class="atk-report-panel-header">
                <h2 id="atk-report-pt-chart-title">Pengajuan per PT</h2>
                <p>Jumlah pengajuan yang sudah disetujui.</p>
            </div>
            @if($ptRows->isNotEmpty())
                @php($maxPtRequests = max(1, (int) $ptRows->max('request_count')))
                <div class="atk-report-pt-bars">
                    @foreach($ptRows as $row)
                        <div class="atk-report-bar-row" data-pt-name="{{ $row->pt_name_snapshot ?? '-' }}">
                            <div class="atk-report-bar-label">
                                <strong>{{ $row->pt_name_snapshot ?? '-' }}</strong>
                                <span>{{ $row->request_count }} pengajuan · {{ number_format($row->percentage, 1, ',', '.') }}%</span>
                            </div>
                            <div class="atk-report-bar-track" aria-hidden="true">
                                <span style="--bar-width: {{ round(((int) $row->request_count / $maxPtRequests) * 100, 1) }}%"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="atk-report-visual-empty">Belum ada pengajuan disetujui pada periode ini.</div>
            @endif
        </section>

        <section class="atk-card atk-report-panel atk-report-item-chart" aria-labelledby="atk-report-item-chart-title">
            <div class="atk-report-panel-header">
                <h2 id="atk-report-item-chart-title">Barang Terbanyak</h2>
                <p>Sepuluh barang yang paling banyak diambil.</p>
            </div>
            @if($itemRows->isNotEmpty())
                @php($maxItemQty = max(1, (int) $itemRows->max('total_qty')))
                <div class="atk-report-bars">
                    @foreach($itemRows as $row)
                        <div class="atk-report-bar-row">
                            <div class="atk-report-bar-label">
                                <strong>{{ $row->item_name_snapshot }}</strong>
                                <span>{{ $row->total_qty }} {{ $row->unit_name_snapshot }}</span>
                            </div>
                            <div class="atk-report-bar-track" aria-hidden="true">
                                <span style="--bar-width: {{ round(((int) $row->total_qty / $maxItemQty) * 100, 1) }}%"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="atk-report-visual-empty">Belum ada pengajuan disetujui pada periode ini.</div>
            @endif
        </section>

        <section class="atk-card atk-report-panel atk-report-requester-chart" aria-labelledby="atk-report-requester-chart-title">
            <div class="atk-report-panel-header">
                <h2 id="atk-report-requester-chart-title">Sering Mengambil</h2>
                <p>Nama dengan pengajuan terbanyak.</p>
            </div>
            @if($requesterRows->isNotEmpty())
                <div class="atk-report-ranking">
                    @foreach($requesterRows as $row)
                        <div class="atk-report-ranking-row">
                            <span>{{ $loop->iteration }}</span>
                            <strong>{{ $row->user_name_snapshot }}</strong>
                            <small>{{ $row->request_count }} pengajuan</small>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="atk-report-visual-empty">Belum ada pengajuan disetujui pada periode ini.</div>
            @endif
        </section>
    </div>

    <details class="atk-card atk-report-history">
        <summary>
            <strong>Lihat Riwayat Pengambilan</strong>
            <small>{{ $detailRows->count() }} baris pada periode ini</small>
        </summary>
        <div class="atk-report-history-content">
            <div class="atk-table-wrap atk-report-table-wrap">
                <table class="atk-table atk-report-table">
                    <thead><tr><th>Tanggal</th><th>No. Request</th><th>Nama Pengambil</th><th>PT</th><th>Barang</th><th>Qty</th></tr></thead>
                    <tbody>
                        @forelse($detailRows as $row)
                            <tr>
                                <td data-label="Tanggal">{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d/m/Y H:i') : '-' }}</td>
                                <td data-label="No. Request">{{ $row->request_number }}</td>
                                <td data-label="Nama Pengambil">{{ $row->user_name_snapshot }}</td>
                                <td data-label="PT">{{ $row->pt_name_snapshot ?? '-' }}</td>
                                <td data-label="Barang"><strong>{{ $row->item_name_snapshot }}</strong></td>
                                <td data-label="Qty">{{ $row->qty }} {{ $row->unit_name_snapshot }}</td>
                            </tr>
                        @empty
                            <tr class="atk-report-empty"><td colspan="6">Belum ada riwayat pengambilan pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    <style>
        .atk-report-header {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 14px;
        }
        .atk-report-context {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 7px;
            margin: 5px 0 0;
            color: var(--atk-muted);
            font-size: 11px;
        }
        .atk-report-context strong { color: var(--atk-primary-dark); }
        .atk-report-filter {
            display: grid;
            gap: 12px;
            margin-bottom: 14px;
        }
        .atk-report-filter-actions,
        .atk-report-filter-actions .atk-btn,
        .atk-report-header > .atk-btn { width: 100%; }
        .atk-report-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }
        .atk-report-stat {
            min-width: 0;
            padding: 13px;
        }
        .atk-report-stat span,
        .atk-report-stat strong,
        .atk-report-stat small { display: block; }
        .atk-report-stat span {
            color: var(--atk-muted);
            font-size: 10px;
            font-weight: 700;
        }
        .atk-report-stat strong {
            margin: 5px 0 2px;
            color: var(--atk-primary-dark);
            font-size: 25px;
            line-height: 1.1;
        }
        .atk-report-stat small {
            color: var(--atk-muted);
            font-size: 9px;
            line-height: 1.4;
        }
        .atk-report-visual-grid {
            display: grid;
            gap: 14px;
            margin-bottom: 14px;
        }
        .atk-report-panel { min-width: 0; }
        .atk-report-panel-header { margin-bottom: 14px; }
        .atk-report-panel-header h2 {
            margin: 0;
            font-size: 15px;
        }
        .atk-report-panel-header p {
            margin: 3px 0 0;
            color: var(--atk-muted);
            font-size: 10px;
        }
        .atk-report-pt-bars,
        .atk-report-bars,
        .atk-report-ranking {
            display: grid;
            gap: 10px;
        }
        .atk-report-bar-row { display: grid; gap: 7px; }
        .atk-report-bar-label {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 10px;
        }
        .atk-report-bar-label strong {
            min-width: 0;
            font-size: 11px;
            overflow-wrap: anywhere;
        }
        .atk-report-bar-label span {
            flex: 0 0 auto;
            color: var(--atk-primary-dark);
            font-size: 10px;
            font-weight: 800;
        }
        .atk-report-bar-track {
            height: 9px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--atk-primary-soft);
        }
        .atk-report-bar-track span {
            display: block;
            width: var(--bar-width);
            min-width: 3px;
            height: 100%;
            border-radius: inherit;
            background: var(--atk-primary);
        }
        .atk-report-ranking-row {
            display: grid;
            grid-template-columns: 30px minmax(0, 1fr);
            gap: 2px 10px;
            align-items: center;
            padding: 9px 10px;
            border-radius: 12px;
            background: var(--atk-primary-softer);
        }
        .atk-report-ranking-row > span {
            display: inline-flex;
            grid-row: 1 / 3;
            width: 30px;
            height: 30px;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: var(--atk-primary-soft);
            color: var(--atk-primary-dark);
            font-size: 11px;
            font-weight: 800;
        }
        .atk-report-ranking-row:first-child > span {
            background: var(--atk-primary);
            color: #fff;
        }
        .atk-report-ranking-row strong {
            min-width: 0;
            font-size: 11px;
            overflow-wrap: anywhere;
        }
        .atk-report-ranking-row small {
            color: var(--atk-muted);
            font-size: 9px;
        }
        .atk-report-visual-empty {
            padding: 24px 8px;
            color: var(--atk-muted);
            font-size: 11px;
            text-align: center;
        }
        .atk-report-history { padding: 0; }
        .atk-report-history summary {
            min-height: 58px;
            padding: 14px 16px;
            cursor: pointer;
        }
        .atk-report-history summary strong,
        .atk-report-history summary small { display: block; }
        .atk-report-history summary strong { font-size: 13px; }
        .atk-report-history summary small {
            margin-top: 2px;
            color: var(--atk-muted);
            font-size: 10px;
            font-weight: 500;
        }
        .atk-report-history[open] summary { border-bottom: 1px solid var(--atk-border-soft); }
        .atk-report-history-content { padding: 14px; }
        .atk-report-table-wrap {
            overflow: visible;
            border: 0;
            border-radius: 0;
        }
        .atk-report-table,
        .atk-report-table tbody {
            display: grid;
            gap: 10px;
            min-width: 0;
        }
        .atk-report-table thead { display: none; }
        .atk-report-table tr {
            display: block;
            padding: 12px;
            border: 1px solid var(--atk-border);
            border-radius: 12px;
            background: var(--atk-surface);
        }
        .atk-report-table td {
            display: grid;
            grid-template-columns: minmax(104px, .75fr) minmax(0, 1fr);
            gap: 10px;
            padding: 6px 0;
            border: 0;
            font-size: 11px;
            overflow-wrap: anywhere;
        }
        .atk-report-table td::before {
            content: attr(data-label);
            color: var(--atk-muted);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .atk-report-table .atk-report-empty td {
            display: block;
            text-align: center;
        }
        .atk-report-table .atk-report-empty td::before { display: none; }
        @media (min-width: 640px) {
            .atk-report-header {
                align-items: center;
                flex-direction: row;
            }
            .atk-report-header > .atk-btn { width: auto; }
            .atk-report-filter {
                grid-template-columns: minmax(180px, .8fr) minmax(180px, 1fr) auto;
                align-items: end;
            }
            .atk-report-filter-actions,
            .atk-report-filter-actions .atk-btn { width: auto; }
        }
        @media (min-width: 768px) {
            .atk-report-summary-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .atk-report-visual-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                grid-template-areas:
                    "pt requester"
                    "items items";
                align-items: start;
            }
            .atk-report-pt-chart { grid-area: pt; }
            .atk-report-item-chart { grid-area: items; }
            .atk-report-requester-chart { grid-area: requester; }
            .atk-report-history-content { padding: 16px; }
            .atk-report-table-wrap {
                overflow-x: auto;
                border: 1px solid var(--atk-border);
                border-radius: 16px;
            }
            .atk-report-table {
                display: table;
                min-width: 720px;
            }
            .atk-report-table thead { display: table-header-group; }
            .atk-report-table tbody { display: table-row-group; }
            .atk-report-table tr {
                display: table-row;
                padding: 0;
                border: 0;
                border-radius: 0;
            }
            .atk-report-table td {
                display: table-cell;
                padding: 12px 14px;
                border-bottom: 1px solid var(--atk-border);
                font-size: 13px;
            }
            .atk-report-table td::before { display: none; }
        }
    </style>
</x-atk-app>
