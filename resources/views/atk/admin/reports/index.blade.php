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

    <section class="atk-card atk-report-section">
        <div class="atk-report-section-header">
            <span>02</span>
            <div>
                <h2>Rekap Penggunaan per PT</h2>
                <p>Perbandingan aktivitas berdasarkan perusahaan.</p>
            </div>
        </div>
        <div class="atk-table-wrap atk-report-table-wrap">
            <table class="atk-table atk-report-table">
                <thead><tr><th>PT</th><th>Jumlah Request</th><th>Jumlah Pengambil</th><th>Jenis Barang</th></tr></thead>
                <tbody>
                    @forelse($ptRows as $row)
                        <tr>
                            <td data-label="PT"><strong>{{ $row->pt_name_snapshot ?? '-' }}</strong></td>
                            <td data-label="Jumlah Request">{{ $row->request_count }}</td>
                            <td data-label="Jumlah Pengambil">{{ $row->user_count }}</td>
                            <td data-label="Jenis Barang">{{ $row->item_count }}</td>
                        </tr>
                    @empty
                        <tr class="atk-report-empty"><td colspan="4">Belum ada pemakaian yang disetujui pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="atk-card atk-report-section">
        <div class="atk-report-section-header">
            <span>03</span>
            <div>
                <h2>Rekap Konsumsi per Barang</h2>
                <p>Barang yang paling banyak digunakan beserta jangkauan pemakaiannya.</p>
            </div>
        </div>
        <div class="atk-table-wrap atk-report-table-wrap">
            <table class="atk-table atk-report-table">
                <thead><tr><th>Barang</th><th>Total Keluar</th><th>Jumlah Request</th><th>PT Pengguna</th></tr></thead>
                <tbody>
                    @forelse($itemRows as $row)
                        <tr>
                            <td data-label="Barang"><strong>{{ $row->item_name_snapshot }}</strong></td>
                            <td data-label="Total Keluar">{{ $row->total_qty }} {{ $row->unit_name_snapshot }}</td>
                            <td data-label="Jumlah Request">{{ $row->request_count }}</td>
                            <td data-label="PT Pengguna">{{ $row->pt_count }}</td>
                        </tr>
                    @empty
                        <tr class="atk-report-empty"><td colspan="4">Belum ada barang keluar pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="atk-card atk-report-section">
        <div class="atk-report-section-header">
            <span>04</span>
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
        }
    </style>
</x-atk-app>
