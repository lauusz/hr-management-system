<x-atk-app title="Dashboard Admin ATK">
    <style>
        .atk-dashboard-split { display: grid; grid-template-columns: 1fr; gap: 14px; }
        .atk-dashboard-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .atk-action-grid { display: grid; grid-template-columns: 1fr; gap: 10px; margin-bottom: 14px; }
        .atk-action-card { display: flex; align-items: center; justify-content: space-between; gap: 12px; text-decoration: none; color: inherit; }
        .atk-action-card strong { display: block; font-size: 20px; line-height: 1; margin-bottom: 5px; }
        .atk-action-card span { color: var(--atk-muted); font-size: 12px; font-weight: 700; }
        .atk-chart-row { display: grid; gap: 7px; margin-bottom: 12px; }
        .atk-chart-head { display: flex; justify-content: space-between; gap: 12px; font-size: 13px; font-weight: 800; }
        .atk-chart-label { min-width: 0; overflow-wrap: anywhere; }
        .atk-chart-track { height: 10px; overflow: hidden; border-radius: 999px; background: var(--atk-primary-soft); }
        .atk-chart-fill { height: 100%; border-radius: inherit; background: var(--atk-primary); }
        .atk-stock-list { display: grid; gap: 10px; }
        .atk-stock-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--atk-border-soft); }
        .atk-stock-item:last-child { padding-bottom: 0; border-bottom: 0; }
        .atk-stock-name { font-size: 13px; font-weight: 800; }
        .atk-stock-meta { margin-top: 2px; color: var(--atk-muted); font-size: 12px; }
        .atk-mini-table { display: grid; gap: 10px; }
        .atk-mini-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--atk-border-soft); }
        .atk-mini-row:last-child { padding-bottom: 0; border-bottom: 0; }
        .atk-mini-title { font-size: 13px; font-weight: 800; }
        .atk-mini-meta { margin-top: 2px; color: var(--atk-muted); font-size: 12px; }
        @media (min-width: 900px) {
            .atk-dashboard-split { grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr); }
            .atk-action-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
    </style>

    <div class="atk-header">
        <div>
            <h1 class="atk-title">Dashboard Admin ATK</h1>
            <p class="atk-subtitle">Pantau request, stok, dan pemakaian bulan ini.</p>
        </div>
        <a class="atk-btn atk-btn-primary" href="{{ route('v2.atk.admin.items.create') }}">Tambah Barang</a>
    </div>

    <div class="atk-dashboard-actions">
        <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.admin.requests.index', ['status' => 'PENDING']) }}">Request masuk</a>
        <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.admin.reports.index') }}">Lihat report</a>
        <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.stock-movements.index') }}">Riwayat stok</a>
    </div>

    <div class="atk-grid" style="margin-bottom:14px">
        <div class="atk-card atk-stat-card"><strong>{{ $pendingRequests }}</strong><p class="atk-subtitle">Request pending</p></div>
        <div class="atk-card atk-stat-card"><strong>{{ $approvedThisMonth }}</strong><p class="atk-subtitle">Approved bulan ini</p></div>
        <div class="atk-card atk-stat-card"><strong>{{ $qtyOutThisMonth }}</strong><p class="atk-subtitle">Qty keluar bulan ini</p></div>
        <div class="atk-card atk-stat-card"><strong>{{ $outOfStockItems }}</strong><p class="atk-subtitle">Stok habis</p></div>
        <div class="atk-card atk-stat-card"><strong>{{ $lowStockItems }}</strong><p class="atk-subtitle">Stok menipis</p></div>
        <div class="atk-card atk-stat-card"><strong>{{ $needRequests }}</strong><p class="atk-subtitle">Pengajuan barang</p></div>
    </div>

    <h2 class="atk-section-title">Tindakan perlu diproses</h2>
    <div class="atk-action-grid">
        <a class="atk-card atk-action-card" href="{{ route('v2.atk.admin.requests.index', ['status' => 'PENDING']) }}">
            <div><strong>{{ $pendingRequests }}</strong><span>Request pending</span></div>
            <span class="atk-badge atk-badge-warning">Cek</span>
        </a>
        <a class="atk-card atk-action-card" href="{{ route('v2.atk.admin.need-requests.index') }}">
            <div><strong>{{ $needRequests }}</strong><span>Pengajuan barang</span></div>
            <span class="atk-badge atk-badge-warning">Cek</span>
        </a>
        <a class="atk-card atk-action-card" href="{{ route('v2.atk.admin.items.index', ['stock' => 'out']) }}">
            <div><strong>{{ $outOfStockItems }}</strong><span>Stok habis</span></div>
            <span class="atk-badge atk-badge-error">Urgent</span>
        </a>
        <a class="atk-card atk-action-card" href="{{ route('v2.atk.admin.items.index', ['stock' => 'low']) }}">
            <div><strong>{{ $lowStockItems }}</strong><span>Stok menipis</span></div>
            <span class="atk-badge atk-badge-warning">Monitor</span>
        </a>
    </div>

    <div class="atk-dashboard-split" style="margin-bottom:14px">
        <div class="atk-card">
            <h2 class="atk-section-title">Barang paling banyak keluar</h2>
            @php $maxTopQty = max(1, (int) ($topItemsThisMonth->max('total_qty') ?? 0)); @endphp
            @forelse($topItemsThisMonth as $item)
                <div class="atk-chart-row">
                    <div class="atk-chart-head">
                        <span class="atk-chart-label">{{ $item->item_name_snapshot }}</span>
                        <span>{{ $item->total_qty }} {{ $item->unit_name_snapshot }}</span>
                    </div>
                    <div class="atk-chart-track">
                        <div class="atk-chart-fill" style="width: {{ ((int) $item->total_qty / $maxTopQty) * 100 }}%"></div>
                    </div>
                </div>
            @empty
                <div class="atk-empty">Belum ada barang keluar bulan ini.</div>
            @endforelse
        </div>

        <div class="atk-card">
            <h2 class="atk-section-title">Stok perlu perhatian</h2>
            <div class="atk-stock-list">
                @forelse($lowStockPreview as $item)
                    <div class="atk-stock-item">
                        <div>
                            <div class="atk-stock-name">{{ $item->name }}</div>
                            <div class="atk-stock-meta">Minimum {{ $item->minimum_stock }} {{ $item->unit_name }}</div>
                        </div>
                        <span class="atk-badge {{ $item->stock_qty <= 0 ? 'atk-badge-error' : 'atk-badge-warning' }}">{{ $item->stock_qty }} {{ $item->unit_name }}</span>
                    </div>
                @empty
                    <div class="atk-empty">Tidak ada stok menipis.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="atk-dashboard-split" style="margin-bottom:14px">
        <div class="atk-card">
            <h2 class="atk-section-title">Trend 6 bulan</h2>
            @php $maxTrendQty = max(1, collect($trendRows)->max('total_qty') ?? 0); @endphp
            @foreach($trendRows as $row)
                <div class="atk-chart-row">
                    <div class="atk-chart-head">
                        <span class="atk-chart-label">{{ $row['label'] }}</span>
                        <span>{{ $row['total_qty'] }} qty</span>
                    </div>
                    <div class="atk-chart-track">
                        <div class="atk-chart-fill" style="width: {{ ((int) $row['total_qty'] / $maxTrendQty) * 100 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="atk-card">
            <h2 class="atk-section-title">Top PT bulan ini</h2>
            <div class="atk-mini-table">
                @forelse($topPtsThisMonth as $row)
                    <div class="atk-mini-row">
                        <div>
                            <div class="atk-mini-title">{{ $row->pt_name_snapshot ?? '-' }}</div>
                            <div class="atk-mini-meta">{{ $row->request_count }} request approved</div>
                        </div>
                        <span class="atk-badge atk-badge-neutral">{{ $row->total_qty }} qty</span>
                    </div>
                @empty
                    <div class="atk-empty">Belum ada pemakaian PT bulan ini.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="atk-dashboard-split" style="margin-bottom:14px">
        <div class="atk-card">
            <h2 class="atk-section-title">Aktivitas stok terbaru</h2>
            <div class="atk-mini-table">
                @forelse($recentStockMovements as $movement)
                    <div class="atk-mini-row">
                        <div>
                            <div class="atk-mini-title">{{ $movement->item->name ?? '-' }}</div>
                            <div class="atk-mini-meta">{{ $movement->notes ?: $movement->source_label }} · {{ $movement->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <span class="atk-badge {{ $movement->movement_type === 'OUT' ? 'atk-badge-error' : ($movement->movement_type === 'ADJUSTMENT' ? 'atk-badge-warning' : 'atk-badge-success') }}">{{ $movement->movement_type }} {{ $movement->qty }}</span>
                    </div>
                @empty
                    <div class="atk-empty">Belum ada aktivitas stok.</div>
                @endforelse
            </div>
        </div>

        <div class="atk-card">
            <h2 class="atk-section-title">Data master perlu dilengkapi</h2>
            <div class="atk-mini-table">
                @foreach($masterWarnings as $warning)
                    <div class="atk-mini-row">
                        <div class="atk-mini-title">{{ $warning['label'] }}</div>
                        <span class="atk-badge {{ $warning['count'] > 0 ? 'atk-badge-warning' : 'atk-badge-success' }}">{{ $warning['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="atk-card">
        <h2 class="atk-section-title">Request Terbaru</h2>
        <div class="atk-table-wrap">
            <table class="atk-table">
                <thead><tr><th>No</th><th>User</th><th>PT</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($latestRequests as $request)
                        <tr>
                            <td>{{ $request->request_number }}</td>
                            <td>{{ $request->user_name_snapshot }}</td>
                            <td>{{ $request->pt_name_snapshot ?? '-' }}</td>
                            <td>
                                <span class="atk-badge {{ $request->status === 'APPROVED' ? 'atk-badge-success' : ($request->status === 'REJECTED' ? 'atk-badge-error' : 'atk-badge-warning') }}">
                                    {{ $request->status }}
                                </span>
                            </td>
                            <td><a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.admin.requests.show', $request) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Belum ada request.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-atk-app>
