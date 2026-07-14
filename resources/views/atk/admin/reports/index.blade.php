<x-atk-app title="Report Bulanan Approved">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Report Bulanan Approved</h1>
            <p class="atk-subtitle">Pemakaian ATK yang sudah approved pada periode {{ $periodLabel }}.</p>
        </div>
    </div>

    <form method="GET" class="atk-card atk-form-grid" style="margin-bottom:14px">
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
        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Filter</button>
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.reports.index') }}">Reset</a>
            <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.admin.reports.export', request()->query()) }}">Export Excel</a>
        </div>
    </form>

    <div class="atk-grid" style="margin-bottom:14px">
        <div class="atk-card atk-stat-card">
            <strong>{{ $summary['request_count'] }}</strong>
            <span>Request Approved</span>
        </div>
        <div class="atk-card atk-stat-card">
            <strong>{{ $summary['total_qty'] }}</strong>
            <span>Total Qty Keluar</span>
        </div>
        <div class="atk-card atk-stat-card">
            <strong>{{ $summary['pt_count'] }}</strong>
            <span>PT Mengambil</span>
        </div>
        <div class="atk-card atk-stat-card">
            <strong>{{ $summary['detail_count'] }}</strong>
            <span>Detail Baris</span>
        </div>
    </div>

    <div class="atk-card" style="margin-bottom:14px">
        <h2 class="atk-section-title">Rekap per PT</h2>
        <div class="atk-table-wrap">
            <table class="atk-table">
                <thead>
                    <tr>
                        <th>PT</th>
                        <th>Request Approved</th>
                        <th>Total Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ptRows as $row)
                        <tr>
                            <td>{{ $row->pt_name_snapshot ?? '-' }}</td>
                            <td>{{ $row->request_count }}</td>
                            <td>{{ $row->total_qty }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">Belum ada pemakaian approved pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="atk-card" style="margin-bottom:14px">
        <h2 class="atk-section-title">Rekap per Barang</h2>
        <div class="atk-table-wrap">
            <table class="atk-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Total Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemRows as $row)
                        <tr>
                            <td>{{ $row->item_name_snapshot }}</td>
                            <td>{{ $row->total_qty }} {{ $row->unit_name_snapshot }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2">Belum ada barang keluar pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="atk-card">
        <h2 class="atk-section-title">Detail Pengambilan</h2>
        <div class="atk-table-wrap">
            <table class="atk-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Request</th>
                        <th>User</th>
                        <th>PT</th>
                        <th>Barang</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailRows as $row)
                        <tr>
                            <td>{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $row->request_number }}</td>
                            <td>{{ $row->user_name_snapshot }}</td>
                            <td>{{ $row->pt_name_snapshot ?? '-' }}</td>
                            <td>{{ $row->item_name_snapshot }}</td>
                            <td>{{ $row->qty }} {{ $row->unit_name_snapshot }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada detail pengambilan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-atk-app>
