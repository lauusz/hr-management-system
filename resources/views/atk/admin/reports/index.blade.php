<x-atk-app title="Laporan Pemakaian ATK">
    <div class="atk-card atk-report-hero">
        <div>
            <span class="atk-report-eyebrow">Laporan Manajemen</span>
            <h1 class="atk-title">Laporan Pemakaian ATK</h1>
            <p class="atk-subtitle">Ringkasan pemakaian barang yang telah disetujui untuk kebutuhan evaluasi manajemen.</p>
        </div>
        <div class="atk-report-meta">
            <div><span>Periode</span><strong>{{ $periodLabel }}</strong></div>
            <div><span>Cakupan</span><strong>{{ $selectedPtName }}</strong></div>
            <div><span>Diperbarui</span><strong>{{ $generatedAt->format('d/m/Y H:i') }}</strong></div>
        </div>
    </div>

    <form method="GET" class="atk-card atk-report-filter">
        <div class="atk-report-filter-title">
            <strong>Parameter Laporan</strong>
            <span>Pilih periode dan perusahaan yang ingin ditinjau.</span>
        </div>
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
            <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.admin.reports.export', request()->query()) }}">Export Excel</a>
        </div>
    </form>

    <section class="atk-report-section">
        <div class="atk-report-section-header">
            <span>01</span>
            <div>
                <h2>Ringkasan Eksekutif</h2>
                <p>Gambaran singkat aktivitas pemakaian pada periode terpilih.</p>
            </div>
        </div>
        <div class="atk-report-summary-grid">
            <div class="atk-card atk-report-stat">
                <span>Request Disetujui</span>
                <strong>{{ $summary['request_count'] }}</strong>
                <small>Pengajuan dengan item yang disetujui</small>
            </div>
            <div class="atk-card atk-report-stat">
                <span>Pengambil Aktif</span>
                <strong>{{ $summary['user_count'] }}</strong>
                <small>Karyawan unik yang mengambil ATK</small>
            </div>
            <div class="atk-card atk-report-stat">
                <span>PT Aktif</span>
                <strong>{{ $summary['pt_count'] }}</strong>
                <small>Perusahaan dengan aktivitas pemakaian</small>
            </div>
            <div class="atk-card atk-report-stat">
                <span>Jenis Barang</span>
                <strong>{{ $summary['item_count'] }}</strong>
                <small>Barang unik yang digunakan</small>
            </div>
        </div>
    </section>

    <div class="atk-report-visual-grid">
        <section class="atk-card atk-report-section atk-report-pt-chart" aria-labelledby="atk-report-pt-chart-title">
            <div class="atk-report-section-header">
                <span>02</span>
                <div>
                    <h2 id="atk-report-pt-chart-title">Pengajuan per PT</h2>
                    <p>Perbandingan pengajuan yang telah disetujui.</p>
                </div>
            </div>
            @if($ptRows->isNotEmpty())
                <div class="atk-report-donut-layout">
                    <div class="atk-report-donut" style="--atk-report-donut: conic-gradient({{ $ptChartGradient }})" role="img" aria-label="Pembagian {{ $summary['request_count'] }} pengajuan disetujui berdasarkan PT">
                        <div class="atk-report-donut-center">
                            <strong>{{ $summary['request_count'] }}</strong>
                            <span>pengajuan</span>
                        </div>
                    </div>
                    <div class="atk-report-legend" aria-label="Rincian pengajuan per PT">
                        @foreach($ptRows as $row)
                            <div class="atk-report-legend-row" data-pt-name="{{ $row->pt_name_snapshot ?? '-' }}">
                                <span class="atk-report-legend-dot" style="--legend-color: {{ $row->color }}" aria-hidden="true"></span>
                                <strong>{{ $row->pt_name_snapshot ?? '-' }}</strong>
                                <span>{{ $row->request_count }} pengajuan</span>
                                <small>{{ number_format($row->percentage, 1, ',', '.') }}%</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="atk-report-visual-empty">Belum ada pengajuan disetujui pada periode ini.</div>
            @endif
        </section>

        <section class="atk-card atk-report-section atk-report-item-chart" aria-labelledby="atk-report-item-chart-title">
            <div class="atk-report-section-header">
                <span>03</span>
                <div>
                    <h2 id="atk-report-item-chart-title">Barang Paling Banyak Diambil</h2>
                    <p>Sepuluh barang dengan jumlah pengambilan terbesar.</p>
                </div>
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

        <section class="atk-card atk-report-section atk-report-requester-chart" aria-labelledby="atk-report-requester-chart-title">
            <div class="atk-report-section-header">
                <span>04</span>
                <div>
                    <h2 id="atk-report-requester-chart-title">Sering Mengambil</h2>
                    <p>Nama dengan pengajuan disetujui terbanyak.</p>
                </div>
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

    <section class="atk-card atk-report-section">
        <div class="atk-report-section-header">
            <span>05</span>
            <div>
                <h2>Detail Transaksi</h2>
                <p>Rincian pengambilan untuk kebutuhan penelusuran.</p>
            </div>
        </div>
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
                        <tr class="atk-report-empty"><td colspan="6">Belum ada detail transaksi pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <style>
        .atk-report-hero {
            display: grid;
            gap: 18px;
            margin-bottom: 14px;
            border-left: 4px solid var(--atk-primary);
        }
        .atk-report-eyebrow {
            display: block;
            margin-bottom: 7px;
            color: var(--atk-primary-dark);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .atk-report-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }
        .atk-report-meta div {
            padding: 10px;
            border-radius: 12px;
            background: var(--atk-primary-softer);
        }
        .atk-report-meta span,
        .atk-report-meta strong {
            display: block;
        }
        .atk-report-meta span {
            margin-bottom: 4px;
            color: var(--atk-muted);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .atk-report-meta strong {
            font-size: 11px;
        }
        .atk-report-filter {
            display: grid;
            gap: 12px;
            margin-bottom: 20px;
        }
        .atk-report-filter-title strong,
        .atk-report-filter-title span {
            display: block;
        }
        .atk-report-filter-title strong { font-size: 13px; }
        .atk-report-filter-title span { margin-top: 3px; color: var(--atk-muted); font-size: 11px; }
        .atk-report-section { margin-bottom: 18px; }
        .atk-report-section-header {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 12px;
        }
        .atk-report-section-header > span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            border-radius: 9px;
            background: var(--atk-primary-soft);
            color: var(--atk-primary-dark);
            font-size: 10px;
            font-weight: 800;
        }
        .atk-report-section-header h2 { margin: 0; font-size: 15px; }
        .atk-report-section-header p { margin: 3px 0 0; color: var(--atk-muted); font-size: 11px; }
        .atk-report-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .atk-report-stat { padding: 14px; }
        .atk-report-stat span,
        .atk-report-stat strong,
        .atk-report-stat small { display: block; }
        .atk-report-stat span { color: var(--atk-muted); font-size: 10px; font-weight: 700; }
        .atk-report-stat strong { margin: 8px 0 5px; color: var(--atk-primary-dark); font-size: 26px; }
        .atk-report-stat small { color: var(--atk-muted); font-size: 9px; line-height: 1.45; }
        .atk-report-visual-grid {
            display: grid;
            gap: 14px;
        }
        .atk-report-visual-grid .atk-report-section { margin-bottom: 0; }
        .atk-report-donut-layout {
            display: grid;
            gap: 18px;
            align-items: center;
        }
        .atk-report-donut {
            width: min(220px, 72vw);
            aspect-ratio: 1;
            margin: 4px auto;
            padding: 24px;
            border-radius: 50%;
            background: var(--atk-report-donut);
        }
        .atk-report-donut-center {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 50%;
            background: var(--atk-surface);
            text-align: center;
        }
        .atk-report-donut-center strong { color: var(--atk-primary-dark); font-size: 28px; line-height: 1; }
        .atk-report-donut-center span { margin-top: 7px; color: var(--atk-muted); font-size: 10px; font-weight: 700; }
        .atk-report-legend,
        .atk-report-ranking,
        .atk-report-bars { display: grid; gap: 9px; }
        .atk-report-legend-row {
            display: grid;
            grid-template-columns: 10px minmax(0, 1fr) auto;
            gap: 3px 8px;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid var(--atk-border-soft);
        }
        .atk-report-legend-row:last-child { border-bottom: 0; }
        .atk-report-legend-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--legend-color); }
        .atk-report-legend-row strong { min-width: 0; font-size: 11px; overflow-wrap: anywhere; }
        .atk-report-legend-row span:not(.atk-report-legend-dot) { grid-column: 2; color: var(--atk-muted); font-size: 9px; }
        .atk-report-legend-row small { grid-column: 3; grid-row: 1 / 3; color: var(--atk-muted); font-size: 10px; font-weight: 700; }
        .atk-report-bar-row { display: grid; gap: 7px; }
        .atk-report-bar-label { display: flex; align-items: flex-end; justify-content: space-between; gap: 10px; }
        .atk-report-bar-label strong { min-width: 0; font-size: 11px; overflow-wrap: anywhere; }
        .atk-report-bar-label span { flex: 0 0 auto; color: var(--atk-primary-dark); font-size: 10px; font-weight: 800; }
        .atk-report-bar-track { height: 10px; overflow: hidden; border-radius: 999px; background: var(--atk-primary-soft); }
        .atk-report-bar-track span { display: block; width: var(--bar-width); min-width: 3px; height: 100%; border-radius: inherit; background: var(--atk-primary); }
        .atk-report-ranking-row {
            display: grid;
            grid-template-columns: 30px minmax(0, 1fr);
            gap: 2px 10px;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--atk-border-soft);
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
        .atk-report-ranking-row strong { min-width: 0; font-size: 11px; overflow-wrap: anywhere; }
        .atk-report-ranking-row small { color: var(--atk-muted); font-size: 9px; }
        .atk-report-visual-empty { padding: 26px 12px; color: var(--atk-muted); font-size: 11px; text-align: center; }
        @media (max-width: 639px) {
            .atk-report-meta { grid-template-columns: 1fr; }
            .atk-report-filter-actions,
            .atk-report-filter-actions .atk-btn { width: 100%; }
            .atk-report-table-wrap {
                overflow: visible;
                border: 0;
                border-radius: 0;
            }
            .atk-report-table,
            .atk-report-table tbody { display: grid; gap: 10px; min-width: 0; }
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
            .atk-report-table .atk-report-empty td { display: block; text-align: center; }
            .atk-report-table .atk-report-empty td::before { display: none; }
        }
        @media (min-width: 768px) {
            .atk-report-hero { grid-template-columns: minmax(0, 1fr) minmax(420px, .8fr); align-items: center; }
            .atk-report-filter { grid-template-columns: minmax(220px, .8fr) minmax(180px, .7fr) minmax(280px, auto); align-items: end; }
            .atk-report-filter-title { grid-column: 1 / -1; }
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
        }
        @media (min-width: 1100px) {
            .atk-report-donut-layout { grid-template-columns: minmax(160px, .8fr) minmax(160px, 1fr); }
        }
    </style>
</x-atk-app>
