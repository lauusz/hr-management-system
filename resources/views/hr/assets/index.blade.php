<x-app title="Inventaris Karyawan">
    <style>
        .asset-page { display: grid; gap: 16px; }
        .asset-header { display: flex; flex-direction: column; gap: 12px; }
        .asset-title { margin: 0; font-size: 22px; font-weight: 800; color: var(--text-primary); }
        .asset-subtitle { margin: 4px 0 0; color: var(--text-muted); font-size: 13px; }
        .asset-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .asset-btn { min-height: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; border-radius: 12px; border: 0; font: inherit; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .asset-btn-primary { color: #fff; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
        .asset-btn-secondary { color: var(--text-secondary); background: var(--white); border: 1.5px solid var(--border); }
        .asset-filter { display: grid; gap: 10px; grid-template-columns: 1fr; }
        .asset-input, .asset-select { width: 100%; min-height: 44px; border: 1.5px solid var(--border); border-radius: 12px; padding: 0 14px; font: inherit; font-size: 13px; background: var(--white); }
        .asset-input:focus, .asset-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(20, 93, 160, .1); }
        .asset-table-wrap { overflow-x: auto; background: var(--white); border: 1px solid var(--border); border-radius: 16px; }
        .asset-table { width: 100%; min-width: 860px; border-collapse: collapse; }
        .asset-thumb { width: 42px; height: 42px; object-fit: cover; border-radius: 10px; border: 1px solid var(--border); background: var(--gray-50); }
        .asset-table th, .asset-table td { padding: 13px 14px; border-bottom: 1px solid var(--border); text-align: left; font-size: 13px; }
        .asset-table th { color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: .04em; background: var(--gray-50); }
        .asset-table tr:last-child td { border-bottom: 0; }
        .asset-badge { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .asset-badge-success { color: #15803D; background: rgba(34,197,94,.12); }
        .asset-badge-warning { color: #B45309; background: rgba(245,158,11,.13); }
        .asset-badge-neutral { color: #4B5563; background: var(--gray-100); }
        @media (min-width: 768px) {
            .asset-header { flex-direction: row; justify-content: space-between; align-items: center; }
            .asset-filter { grid-template-columns: minmax(0, 1fr) 190px auto; align-items: center; }
        }
    </style>

    <div class="asset-page">
        <div class="asset-header">
            <div>
                <h1 class="asset-title">Inventaris Karyawan</h1>
                <p class="asset-subtitle">Catat laptop, perangkat, dan fasilitas yang sedang dipegang karyawan.</p>
            </div>
            <a class="asset-btn asset-btn-primary" href="{{ route('hr.assets.create') }}">Tambah Asset</a>
        </div>

        <form class="card asset-filter" method="GET">
            <input class="asset-input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari kode, nama, serial, hostname, email laptop" autocomplete="off">
            <select class="asset-select" name="status">
                <option value="">Semua status</option>
                @foreach(['AVAILABLE' => 'Available', 'ASSIGNED' => 'Assigned', 'SERVICE' => 'Service', 'LOST' => 'Lost', 'DISPOSAL' => 'Disposal'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="asset-actions">
                <button class="asset-btn asset-btn-primary" type="submit">Filter</button>
                <a class="asset-btn asset-btn-secondary" href="{{ route('hr.assets.index') }}">Reset</a>
            </div>
        </form>

        <div class="asset-table-wrap">
            <table class="asset-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Asset</th>
                        <th>Kategori</th>
                        <th>Pemegang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td>{{ $asset->asset_code }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    @if($asset->photo_path)
                                        <img class="asset-thumb" src="{{ asset('storage/'.$asset->photo_path) }}" alt="Foto {{ $asset->name }}">
                                    @endif
                                    <div>
                                        <strong>{{ $asset->name }}</strong><br>
                                        <span style="color:var(--text-muted)">{{ $asset->serial_number ?: '-' }} · {{ $asset->hostname ?: '-' }} · {{ $asset->email_laptop ?: '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $asset->category->name ?? '-' }}</td>
                            <td>{{ $asset->currentUser->name ?? '-' }}</td>
                            <td>
                                <span class="asset-badge {{ $asset->asset_status === 'ASSIGNED' ? 'asset-badge-success' : ($asset->asset_status === 'AVAILABLE' ? 'asset-badge-neutral' : 'asset-badge-warning') }}">{{ $asset->asset_status }}</span>
                            </td>
                            <td><a class="asset-btn asset-btn-secondary" href="{{ route('hr.assets.show', $asset) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada asset.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :items="$assets" preserve-query />
    </div>
</x-app>
