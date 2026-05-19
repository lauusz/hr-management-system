<x-app title="Master Lokasi Presensi">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Daftar Lokasi</h1>
                <p class="section-subtitle">Master lokasi presensi karyawan (kantor, gudang, dll).</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="loc-alert loc-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- CTA --}}
    <div class="loc-cta-bar">
        <a href="{{ route('hr.locations.create') }}" class="loc-btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Lokasi
        </a>
    </div>

    {{-- Card Table --}}
    <div class="loc-card">
        <div class="table-wrapper">
            <table class="loc-table">
                <thead>
                    <tr>
                        <th>Nama Lokasi</th>
                        <th style="max-width: 250px;">Alamat</th>
                        <th>Koordinat</th>
                        <th>Radius</th>
                        <th>Status</th>
                        <th class="text-right" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $loc)
                    <tr>
                        <td class="fw-bold">{{ $loc->name }}</td>

                        <td style="max-width: 250px;">
                            <div class="text-truncate" title="{{ $loc->address }}">
                                {{ $loc->address ?: '-' }}
                            </div>
                        </td>

                        <td>
                            <div class="coord-box">
                                <span class="text-muted">{{ $loc->latitude }}, {{ $loc->longitude }}</span>
                            </div>
                        </td>

                        <td>
                            <span class="fw-bold">{{ $loc->radius_meters }}</span>
                            <span class="text-muted" style="font-size:11px;">meter</span>
                        </td>

                        <td>
                            @if($loc->is_active)
                                <span class="loc-badge loc-badge--success">Aktif</span>
                            @else
                                <span class="loc-badge loc-badge--neutral">Nonaktif</span>
                            @endif
                        </td>

                        <td class="text-right">
                            <div class="action-buttons">
                                <a href="{{ route('hr.locations.edit', $loc->id) }}" class="loc-btn-secondary">
                                    Edit
                                </a>

                                <button type="button"
                                    data-modal-target="delete-location-{{ $loc->id }}"
                                    class="loc-btn-danger">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <div class="loc-empty">
                                <div class="loc-empty-icon">
                                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <h3 class="loc-empty-title">Belum Ada Lokasi</h3>
                                <p class="loc-empty-desc">Tambahkan lokasi presensi baru dengan menekan tombol "Tambah Lokasi" di atas.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="loc-pagination">
        <x-pagination :items="$items" />
    </div>

    @foreach($items as $loc)
    <x-modal
        id="delete-location-{{ $loc->id }}"
        title="Hapus Lokasi?"
        type="confirm"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        :confirmFormAction="route('hr.locations.destroy', $loc->id)"
        confirmFormMethod="DELETE">
        <p style="margin:0 0 4px 0;">
            Yakin ingin menghapus lokasi berikut?
        </p>
        <p style="margin:0; font-weight:700; color:var(--text-primary);">
            {{ $loc->name }}
        </p>
        <p style="margin:8px 0 0 0; font-size:0.85rem; color:var(--text-muted);">
            Pastikan lokasi ini tidak sedang digunakan dalam jadwal aktif karyawan.
        </p>
    </x-modal>
    @endforeach

    <style>
        /* --- SECTION HEADER (x-slot) --- */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark); }

        /* --- ALERT --- */
        .loc-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .loc-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }

        /* --- CTA BAR --- */
        .loc-cta-bar {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 16px;
        }
        .loc-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            flex-shrink: 0;
        }
        .loc-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .loc-btn-primary svg {
            flex-shrink: 0;
        }

        /* --- CARD --- */
        .loc-card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        /* --- TABLE --- */
        .table-wrapper { width: 100%; overflow-x: auto; }
        .loc-table { width: 100%; border-collapse: collapse; min-width: 800px; }

        .loc-table th {
            background: var(--gray-100);
            padding: 12px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
        }

        .loc-table td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-light);
            font-size: 14px;
            color: var(--text-primary);
            vertical-align: middle;
        }

        .loc-table tr:last-child td { border-bottom: none; }
        .loc-table tbody tr:hover td { background: var(--gray-50); }

        .fw-bold { font-weight: 600; color: var(--text-primary); }
        .text-muted { color: var(--text-muted); font-size: 13px; }
        .text-right { text-align: right; }

        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-secondary);
            font-size: 13.5px;
        }

        .coord-box {
            font-family: monospace;
            background: var(--gray-100);
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
            border: 1px solid var(--border-light);
        }

        /* --- BADGES --- */
        .loc-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .loc-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .loc-badge--neutral { background: var(--gray-100); color: var(--text-muted); }

        /* --- ACTION BUTTONS --- */
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .loc-btn-secondary {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid var(--border);
            background: var(--white);
            color: var(--text-secondary);
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            line-height: 1.4;
        }
        .loc-btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--border);
            color: var(--primary);
        }

        .loc-btn-danger {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #fecaca;
            background: var(--danger-light);
            color: var(--error);
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            line-height: 1.4;
            font-family: inherit;
        }
        .loc-btn-danger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        /* --- EMPTY STATE --- */
        .loc-empty {
            text-align: center;
            padding: 48px 24px;
        }
        .loc-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
        }
        .loc-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin: 0 0 6px;
        }
        .loc-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* --- PAGINATION --- */
        .loc-pagination {
            margin-top: 24px;
        }

        /* --- RESPONSIVE --- */
        @media (min-width: 480px) {
            .loc-cta-bar {
                justify-content: flex-end;
            }
        }
    </style>
</x-app>
