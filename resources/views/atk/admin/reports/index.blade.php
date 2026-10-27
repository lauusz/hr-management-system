<x-atk-app title="Rekap ATK">
    <style>
        .atk-report-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .atk-report-title-wrap {
            display: flex;
            align-items: baseline;
            gap: 10px;
            flex-wrap: wrap;
        }
        .atk-report-context {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            margin: 0;
            color: var(--atk-muted);
            font-size: 12px;
        }
        .atk-report-context strong {
            color: var(--atk-text);
            font-weight: 700;
        }
        .atk-report-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Compact Filter Bar */
        .atk-report-filter {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 10px 14px;
            margin-bottom: 14px;
            background: #FFFFFF;
            border: 1px solid var(--atk-border);
            border-radius: 10px;
        }
        .atk-report-filter-fields {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            flex: 1 1 auto;
        }
        .atk-report-filter-item {
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .atk-report-filter-item label {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            color: var(--atk-muted);
            white-space: nowrap;
        }
        .atk-report-filter-item .atk-input,
        .atk-report-filter-item .atk-select {
            height: 32px;
            min-height: 32px;
            padding: 0 10px;
            font-size: 12.5px;
            border-radius: 7px;
            background: #F8FAFC;
            width: auto;
        }
        .atk-report-filter-item .atk-select {
            padding-right: 28px;
            min-width: 170px;
        }
        .atk-report-filter-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .atk-report-filter-actions .atk-btn {
            min-height: 32px;
            height: 32px;
            padding: 0 12px;
            font-size: 12px;
            border-radius: 7px;
        }

        /* Unified KPI Ledger Strip */
        .atk-report-summary-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            background: #FFFFFF;
            border: 1px solid var(--atk-border);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 14px;
        }
        .atk-report-stat {
            padding: 12px 16px;
            border-right: 1px solid var(--atk-border-soft);
            background: transparent;
        }
        .atk-report-stat:last-child {
            border-right: 0;
        }
        .atk-report-stat span {
            display: block;
            color: var(--atk-muted);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .atk-report-stat strong {
            display: block;
            margin: 4px 0 2px;
            color: var(--atk-text);
            font-size: 22px;
            font-weight: 700;
            line-height: 1.15;
            font-variant-numeric: tabular-nums;
        }
        .atk-report-stat small {
            display: block;
            color: #94A3B8;
            font-size: 11px;
        }

        /* Visual & Report Tables Grid */
        .atk-report-visual-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .atk-report-panel {
            background: #FFFFFF;
            border: 1px solid var(--atk-border);
            border-radius: 10px;
            padding: 0;
            overflow: hidden;
            min-width: 0;
        }
        .atk-report-panel-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 12px 18px;
            background: #F8FAFC;
            border-bottom: 1px solid var(--atk-border);
        }
        .atk-report-panel-header h2 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--atk-text);
        }
        .atk-report-panel-header p {
            margin: 0;
            color: var(--atk-muted);
            font-size: 12px;
        }

        /* Top Donut + Legend Band (Stacked cleanly above full-width table) */
        .atk-report-donut-layout {
            display: grid;
            grid-template-columns: 104px minmax(0, 1fr);
            align-items: center;
            gap: 18px;
            padding: 14px 18px;
            background: #FAFBFD;
            border-bottom: 1px solid var(--atk-border);
        }
        .atk-report-donut {
            display: grid;
            width: 104px;
            height: 104px;
            place-items: center;
            border-radius: 50%;
            background: conic-gradient(var(--donut-gradient));
            flex-shrink: 0;
        }
        .atk-report-donut-center {
            display: grid;
            width: 68%;
            height: 68%;
            place-content: center;
            border-radius: 50%;
            background: #FFFFFF;
            text-align: center;
            box-shadow: inset 0 0 0 1px var(--atk-border-soft);
        }
        .atk-report-donut-center strong {
            color: var(--atk-text);
            font-size: 16px;
            font-weight: 700;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }
        .atk-report-donut-center span {
            color: var(--atk-muted);
            font-size: 9px;
            font-weight: 600;
        }
        .atk-report-donut-legend {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 8px 12px;
            width: 100%;
            min-width: 0;
        }
        .atk-report-donut-legend-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 7px;
            background: #FFFFFF;
            border: 1px solid var(--atk-border-soft);
            min-width: 0;
            overflow: hidden;
        }
        .atk-report-donut-legend-left {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
            flex: 1 1 auto;
            overflow: hidden;
        }
        .atk-report-donut-color {
            width: 9px;
            height: 9px;
            border-radius: 3px;
            background: var(--segment-color);
            flex-shrink: 0;
        }
        .atk-report-donut-legend-row strong {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--atk-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }
        .atk-report-donut-legend-row small {
            color: var(--atk-muted);
            font-size: 11px;
            white-space: nowrap;
            flex-shrink: 0;
            font-variant-numeric: tabular-nums;
        }

        /* Formal Data Tables */
        .atk-report-data-table-wrap {
            width: 100%;
            overflow-x: auto;
            max-height: 440px;
            overflow-y: auto;
        }
        .atk-report-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        .atk-report-data-table th,
        .atk-report-data-table td {
            padding: 9px 16px;
            border-bottom: 1px solid var(--atk-border-soft);
            text-align: left;
            vertical-align: middle;
        }
        .atk-report-data-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #FFFFFF;
            color: #64748B;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid var(--atk-border);
            white-space: nowrap;
        }
        .atk-report-data-table tbody tr:hover {
            background: #F8FAFC;
        }
        .atk-report-data-table tfoot td {
            position: sticky;
            bottom: 0;
            background: #F8FAFC;
            font-weight: 700;
            color: var(--atk-text);
            border-top: 1px solid var(--atk-border);
            border-bottom: 0;
        }
        .atk-num {
            text-align: right !important;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .atk-Share-bar-wrap {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            min-width: 110px;
        }
        .atk-share-bar {
            width: 56px;
            height: 6px;
            border-radius: 999px;
            background: #F1F5F9;
            overflow: hidden;
            flex-shrink: 0;
        }
        .atk-share-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: var(--atk-primary);
        }

        /* Employee Breakdown Table (Sering Mengambil) */
        .atk-report-ranking {
            width: 100%;
            overflow-x: auto;
        }
        .atk-report-ranking-row {
            display: grid;
            grid-template-columns: 36px minmax(190px, 1.2fr) minmax(240px, 2fr) 100px 110px;
            gap: 12px;
            align-items: center;
            padding: 10px 18px;
            border-bottom: 1px solid var(--atk-border-soft);
            font-size: 12.5px;
        }
        .atk-report-ranking-row:last-child {
            border-bottom: 0;
        }
        .atk-report-ranking-row:hover {
            background: #F8FAFC;
        }
        .atk-report-ranking-head {
            display: grid;
            grid-template-columns: 36px minmax(190px, 1.2fr) minmax(240px, 2fr) 100px 110px;
            gap: 12px;
            padding: 9px 18px;
            background: #FFFFFF;
            border-bottom: 1px solid var(--atk-border);
            color: #64748B;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .atk-report-rank-num {
            display: inline-flex;
            width: 24px;
            height: 24px;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: #F1F5F9;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
        }
        .atk-report-ranking-row:first-of-type .atk-report-rank-num {
            background: var(--atk-primary-soft);
            color: var(--atk-primary-dark);
        }
        .atk-report-ranking-person {
            min-width: 0;
        }
        .atk-report-ranking-person strong {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--atk-text);
            overflow-wrap: anywhere;
        }
        .atk-report-ranking-items {
            min-width: 0;
            font-size: 12px;
            color: var(--atk-muted);
            line-height: 1.45;
            overflow-wrap: anywhere;
        }
        .atk-report-ranking-qty {
            text-align: right;
            font-weight: 600;
            color: var(--atk-text);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .atk-report-ranking-freq {
            text-align: right;
            color: var(--atk-muted);
            font-size: 12px;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .atk-report-visual-empty {
            padding: 24px 16px;
            color: var(--atk-muted);
            font-size: 12px;
            text-align: center;
        }

        /* Transaction History Details */
        .atk-report-history {
            padding: 0;
            border-radius: 10px;
            overflow: hidden;
        }
        .atk-report-history summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 16px;
            background: #F8FAFC;
            cursor: pointer;
            list-style: none;
        }
        .atk-report-history summary::-webkit-details-marker {
            display: none;
        }
        .atk-report-history summary strong {
            font-size: 13.5px;
            color: var(--atk-text);
        }
        .atk-report-history summary small {
            color: var(--atk-muted);
            font-size: 11.5px;
            font-weight: 600;
        }
        .atk-report-history[open] summary {
            border-bottom: 1px solid var(--atk-border);
        }
        .atk-report-history-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 16px;
            border-bottom: 1px solid var(--atk-border-soft);
            background: #FFFFFF;
        }
        .atk-report-log-search {
            max-width: 300px;
            height: 30px;
            min-height: 30px;
            font-size: 12px;
            padding: 0 10px;
            border-radius: 6px;
            background: #F8FAFC;
        }
        .atk-report-table-wrap {
            border: 0;
            border-radius: 0;
        }

        @media (max-width: 960px) {
            .atk-report-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .atk-report-stat {
                border-bottom: 1px solid var(--atk-border-soft);
            }
            .atk-report-ranking-head {
                display: none;
            }
            .atk-report-ranking-row {
                grid-template-columns: 28px minmax(0, 1fr) auto;
                gap: 4px 10px;
            }
            .atk-report-ranking-items {
                grid-column: 2 / -1;
            }
            .atk-report-ranking-qty {
                grid-column: 2 / 3;
                text-align: left;
                font-size: 11.5px;
            }
        }

        @media (max-width: 600px) {
            .atk-report-donut-layout {
                grid-template-columns: 1fr;
                justify-items: center;
                padding: 14px;
            }
            .atk-report-donut-legend {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .atk-sidebar,
            .atk-topbar,
            .atk-report-filter,
            .atk-report-actions,
            .atk-report-history-toolbar {
                display: none !important;
            }
            .atk-report-data-table-wrap {
                max-height: none !important;
                overflow: visible !important;
            }
            .atk-main,
            .atk-content {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>

    <header class="atk-report-top">
        <div class="atk-report-title-wrap">
            <h1 class="atk-title">Rekap ATK</h1>
            <p class="atk-report-context">
                <strong>{{ $periodLabel }}</strong>
                <span aria-hidden="true">•</span>
                <span>{{ $selectedPtName }}</span>
                <span aria-hidden="true">•</span>
                <span>Diperbarui {{ $generatedAt->format('d/m/Y H:i') }}</span>
            </p>
        </div>
        <div class="atk-report-actions">
            <button type="button" class="atk-btn atk-btn-muted atk-btn-sm" onclick="window.print()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                Cetak
            </button>
            <a class="atk-btn atk-btn-primary atk-btn-sm" href="{{ route('v2.atk.admin.reports.export', request()->query()) }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                Unduh Excel
            </a>
        </div>
    </header>

    <form method="GET" class="atk-report-filter" id="atkReportFilterForm">
        <div class="atk-report-filter-fields">
            <div class="atk-report-filter-item">
                <label for="month">Periode Bulan</label>
                <input class="atk-input" id="month" type="month" name="month" value="{{ $month }}">
            </div>
            <div class="atk-report-filter-item">
                <label for="pt_id">Unit / PT</label>
                <select class="atk-select" id="pt_id" name="pt_id">
                    <option value="">Semua PT</option>
                    @foreach($pts as $pt)
                        <option value="{{ $pt->id }}" @selected((string) request('pt_id') === (string) $pt->id)>{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="atk-report-filter-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Tampilkan</button>
            @if(request()->filled('pt_id') || request('month', now()->format('Y-m')) !== now()->format('Y-m'))
                <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.reports.index') }}">Reset</a>
            @endif
        </div>
    </form>

    <section class="atk-report-summary-grid" aria-label="Ringkasan laporan">
        <div class="atk-report-stat">
            <span>Pengajuan</span>
            <strong>{{ $summary['request_count'] }}</strong>
            <small>Sudah disetujui</small>
        </div>
        <div class="atk-report-stat">
            <span>Total Barang Keluar</span>
            <strong>{{ $itemQtyTotal }}</strong>
            <small>Akumulasi kuantitas</small>
        </div>
        <div class="atk-report-stat">
            <span>Pengambil</span>
            <strong>{{ $summary['user_count'] }}</strong>
            <small>Karyawan aktif</small>
        </div>
        <div class="atk-report-stat">
            <span>PT</span>
            <strong>{{ $summary['pt_count'] }}</strong>
            <small>Memiliki pengajuan</small>
        </div>
        <div class="atk-report-stat">
            <span>Jenis Barang</span>
            <strong>{{ $summary['item_count'] }}</strong>
            <small>Barang yang diambil</small>
        </div>
    </section>

    <div class="atk-report-visual-grid">
        {{-- 1. Rekapitulasi Pemakaian per PT --}}
        <section class="atk-report-panel atk-report-pt-chart" aria-labelledby="atk-report-pt-chart-title">
            <div class="atk-report-panel-header">
                <h2 id="atk-report-pt-chart-title">Pengajuan per PT</h2>
                <p>Distribusi frekuensi pengajuan, jumlah pemohon, dan total kuantitas barang keluar per PT.</p>
            </div>
            @if($ptRows->isNotEmpty())
                <div class="atk-report-split">
                    <div class="atk-report-donut-layout">
                        <div class="atk-report-donut" data-chart="pt" data-donut-total="{{ $ptRequestTotal }}" role="img" aria-label="{{ $ptRequestTotal }} pengajuan berdasarkan PT" style="--donut-gradient: {{ $ptChartGradient }}">
                            <div class="atk-report-donut-center">
                                <strong>{{ $ptRequestTotal }}</strong>
                                <span>pengajuan</span>
                            </div>
                        </div>
                        <div class="atk-report-donut-legend" aria-label="Rincian pengajuan per PT">
                            @foreach($ptRows as $row)
                                <div class="atk-report-donut-legend-row" data-pt-name="{{ $row->pt_name_snapshot ?? '-' }}">
                                    <div class="atk-report-donut-legend-left">
                                        <span class="atk-report-donut-color" style="--segment-color: {{ $row->color }}" aria-hidden="true"></span>
                                        <strong>{{ $row->pt_name_snapshot ?? '-' }}</strong>
                                    </div>
                                    <small>{{ number_format($row->percentage, 1, ',', '.') }}%</small>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="atk-report-data-table-wrap">
                        <table class="atk-report-data-table">
                            <thead>
                                <tr>
                                    <th>Nama PT</th>
                                    <th class="atk-num">Pengajuan</th>
                                    <th class="atk-num">Pemohon</th>
                                    <th class="atk-num">Jenis Item</th>
                                    <th class="atk-num">Total Qty Keluar</th>
                                    <th class="atk-num">Proporsi Pengajuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ptRows as $row)
                                    <tr>
                                        <td>
                                            <span style="display:inline-flex;align-items:center;gap:7px;font-weight:600;color:var(--atk-text)">
                                                <span class="atk-report-donut-color" style="--segment-color: {{ $row->color }}"></span>
                                                {{ $row->pt_name_snapshot ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="atk-num"><strong>{{ $row->request_count }}</strong>x</td>
                                        <td class="atk-num">{{ $row->user_count }} org</td>
                                        <td class="atk-num">{{ $row->item_count }} item</td>
                                        <td class="atk-num"><strong>{{ (int) ($row->total_qty ?? 0) }}</strong> unit</td>
                                        <td class="atk-num">
                                            <div class="atk-Share-bar-wrap">
                                                <div class="atk-share-bar">
                                                    <div class="atk-share-bar-fill" style="width: {{ min(100, $row->percentage) }}%; background: {{ $row->color }}"></div>
                                                </div>
                                                <span>{{ number_format($row->percentage, 1, ',', '.') }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>TOTAL</td>
                                    <td class="atk-num">{{ $ptRequestTotal }}x</td>
                                    <td class="atk-num">{{ $summary['user_count'] }} org</td>
                                    <td class="atk-num">{{ $summary['item_count'] }} item</td>
                                    <td class="atk-num">{{ $itemQtyTotal }} unit</td>
                                    <td class="atk-num">100,0%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                <div class="atk-report-visual-empty">Belum ada pengajuan disetujui pada periode ini.</div>
            @endif
        </section>

        {{-- 2. Rekapitulasi Pemakaian & Sisa Stok per Barang --}}
        <section class="atk-report-panel atk-report-item-chart" aria-labelledby="atk-report-item-chart-title">
            <div class="atk-report-panel-header">
                <h2 id="atk-report-item-chart-title">Barang Keluar Terbanyak</h2>
                <p>Rincian kuantitas pemakaian per barang beserta posisi sisa stok di gudang saat ini.</p>
            </div>
            @if($itemChartRows->isNotEmpty())
                <div class="atk-report-split">
                    <div class="atk-report-donut-layout">
                        <div class="atk-report-donut" data-chart="items" data-donut-total="{{ $itemQtyTotal }}" role="img" aria-label="{{ $itemQtyTotal }} barang keluar berdasarkan jenis barang" style="--donut-gradient: {{ $itemChartGradient }}">
                            <div class="atk-report-donut-center">
                                <strong>{{ $itemQtyTotal }}</strong>
                                <span>barang keluar</span>
                            </div>
                        </div>
                        <div class="atk-report-donut-legend" aria-label="Rincian barang keluar">
                            @foreach($itemChartRows as $row)
                                <div class="atk-report-donut-legend-row" data-item-segment="{{ $row->item_name_snapshot }}">
                                    <div class="atk-report-donut-legend-left">
                                        <span class="atk-report-donut-color" style="--segment-color: {{ $row->color }}" aria-hidden="true"></span>
                                        <strong>{{ $row->item_name_snapshot }}</strong>
                                    </div>
                                    <small>{{ $row->total_qty }} ({{ number_format($row->percentage, 1, ',', '.') }}%)</small>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="atk-report-data-table-wrap">
                        <table class="atk-report-data-table">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th class="atk-num">Frekuensi</th>
                                    <th class="atk-num">PT Pemakai</th>
                                    <th class="atk-num">Qty Keluar</th>
                                    <th class="atk-num">Sisa Stok Gudang</th>
                                    <th class="atk-num">Porsi Keluar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($itemRows ?? $itemChartRows) as $row)
                                    @php
                                        $curStock = $row->current_stock ?? null;
                                        $minStock = (int) ($row->minimum_stock ?? 0);
                                        $isLow = $curStock !== null && (int) $curStock <= $minStock;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $row->item_name_snapshot }}</strong></td>
                                        <td class="atk-num">{{ $row->request_count ?? '-' }}x</td>
                                        <td class="atk-num">{{ $row->pt_count ?? '-' }} PT</td>
                                        <td class="atk-num"><strong>{{ $row->total_qty }}</strong> {{ $row->unit_name_snapshot ?? '' }}</td>
                                        <td class="atk-num">
                                            @if($curStock !== null)
                                                <span class="atk-badge atk-badge-{{ (int) $curStock <= 0 ? 'error' : ($isLow ? 'warning' : 'neutral') }}">
                                                    {{ (int) $curStock }} {{ $row->unit_name_snapshot ?? '' }}
                                                </span>
                                            @else
                                                <span style="color:#94A3B8">-</span>
                                            @endif
                                        </td>
                                        <td class="atk-num">{{ number_format($row->percentage ?? 0, 1, ',', '.') }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="atk-report-visual-empty">Belum ada pengajuan disetujui pada periode ini.</div>
            @endif
        </section>

        {{-- 3. Rincian Pemakaian per Karyawan --}}
        <section class="atk-report-panel atk-report-requester-chart" aria-labelledby="atk-report-requester-chart-title">
            <div class="atk-report-panel-header">
                <h2 id="atk-report-requester-chart-title">Sering Mengambil</h2>
                <p>Daftar karyawan dengan frekuensi pengambilan tertinggi beserta rincian barang yang diambil.</p>
            </div>
            @if($requesterRows->isNotEmpty())
                <div class="atk-report-ranking">
                    <div class="atk-report-ranking-head">
                        <span>No</span>
                        <span>Nama Karyawan &amp; PT</span>
                        <span>Rincian Barang Diambil</span>
                        <span style="text-align:right">Total Qty</span>
                        <span style="text-align:right">Frekuensi</span>
                    </div>
                    @foreach($requesterRows as $row)
                        <div class="atk-report-ranking-row">
                            <span class="atk-report-rank-num">{{ $loop->iteration }}</span>
                            <div class="atk-report-ranking-person">
                                <strong>{{ $row->user_name_snapshot }} · {{ $row->pt_name_snapshot ? 'PT. '.preg_replace('/^PT\.?\s*/i', '', $row->pt_name_snapshot) : '-' }}</strong>
                            </div>
                            <div class="atk-report-ranking-items">{{ $row->items_summary ?? '-' }}</div>
                            <div class="atk-report-ranking-qty">{{ (int) ($row->total_qty ?? 0) }} unit</div>
                            <small class="atk-report-ranking-freq">{{ $row->request_count }} pengajuan</small>
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
            <div class="atk-report-history-toolbar">
                <span style="font-size:12px;color:var(--atk-muted)">Rincian transaksi pengambilan barang yang telah disetujui</span>
                <input type="search" class="atk-input atk-report-log-search" id="reportLogSearch" placeholder="Filter nama, PT, atau barang..." autocomplete="off">
            </div>
            <div class="atk-table-wrap atk-report-table-wrap">
                <table class="atk-table atk-report-table" id="reportLogTable">
                    <thead>
                        <tr>
                            <th>Tanggal Disetujui</th>
                            <th>Nama Pengambil</th>
                            <th>PT</th>
                            <th>Barang</th>
                            <th style="text-align:right">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailRows as $row)
                            <tr data-log-row>
                                <td data-label="Tanggal" style="white-space:nowrap;color:var(--atk-muted)">{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d/m/Y H:i') : '-' }}</td>
                                <td data-label="Nama Pengambil"><strong>{{ $row->user_name_snapshot }}</strong></td>
                                <td data-label="PT">{{ $row->pt_name_snapshot ?? '-' }}</td>
                                <td data-label="Barang">{{ $row->item_name_snapshot }}</td>
                                <td data-label="Qty" style="text-align:right;font-weight:700;white-space:nowrap">{{ $row->qty }} {{ $row->unit_name_snapshot }}</td>
                            </tr>
                        @empty
                            <tr class="atk-report-empty"><td colspan="5">Belum ada riwayat pengambilan pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    <script>
        (function () {
            var detailsEl = document.querySelector('.atk-report-history');
            if (detailsEl) {
                detailsEl.setAttribute('open', 'open');
            }

            var filterForm = document.getElementById('atkReportFilterForm');
            var monthInput = document.getElementById('month');
            var ptSelect = document.getElementById('pt_id');
            if (filterForm && monthInput && ptSelect) {
                monthInput.addEventListener('change', function () { filterForm.submit(); });
                ptSelect.addEventListener('change', function () { filterForm.submit(); });
            }

            var logSearch = document.getElementById('reportLogSearch');
            var logTable = document.getElementById('reportLogTable');
            if (logSearch && logTable) {
                logSearch.addEventListener('input', function () {
                    var q = (logSearch.value || '').trim().toLowerCase();
                    logTable.querySelectorAll('[data-log-row]').forEach(function (tr) {
                        var match = !q || tr.textContent.toLowerCase().includes(q);
                        tr.style.display = match ? '' : 'none';
                    });
                });
            }
        })();
    </script>
</x-atk-app>
