<x-inv-pesona-app title="Daftar Ambil — Inventory Pesona">
    <style>
        .pesona-unavail-card {
            max-width: 520px;
            margin: 18px auto 0;
            padding: 28px 22px;
            border: 1px solid var(--pesona-border);
            border-radius: 14px;
            background: var(--pesona-surface);
            box-shadow: 0 1px 3px rgba(17, 24, 39, 0.05);
            text-align: center;
        }
        .pesona-unavail-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            border-radius: 12px;
            background: var(--pesona-accent-soft);
            color: var(--pesona-accent);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pesona-unavail-title {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 700;
            color: var(--pesona-text);
        }
        .pesona-unavail-desc {
            margin: 0 0 18px;
            font-size: 13px;
            color: var(--pesona-muted);
            line-height: 1.5;
        }
        .pesona-unavail-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }
        .pesona-unavail-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 16px;
            border-radius: 10px;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color .15s ease;
        }
        .pesona-unavail-btn-primary {
            background: var(--pesona-primary);
            color: #FFFFFF;
        }
        .pesona-unavail-btn-primary:hover {
            background: var(--pesona-primary-dark);
        }
        .pesona-unavail-btn-secondary {
            background: var(--pesona-primary-soft);
            color: var(--pesona-primary);
            border: 1px solid rgba(40, 53, 90, 0.12);
        }
        .pesona-unavail-btn-secondary:hover {
            background: #E2E7F3;
        }
    </style>

    <div class="pesona-unavail-card" role="alert">
        <div class="pesona-unavail-icon" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4M12 16h.01"/>
            </svg>
        </div>
        <h1 class="pesona-unavail-title">Daftar Ambil Inventory Pesona</h1>
        @if($inflight)
            <p class="pesona-unavail-desc">Hasil pengiriman sebelumnya belum dapat dipastikan. Periksa Riwayat Pengambilan sebelum melakukan pengiriman lagi.</p>
            <div class="pesona-unavail-actions">
                <a class="pesona-unavail-btn pesona-unavail-btn-primary" href="{{ route('inventory.pesona.requests.index') }}">Riwayat Pengambilan</a>
                <a class="pesona-unavail-btn pesona-unavail-btn-secondary" href="{{ route('inventory.pesona.catalog') }}">Kembali ke Daftar Barang</a>
            </div>
        @else
            <p class="pesona-unavail-desc">Daftar ambil sementara tidak dapat dimuat. Silakan coba lagi.</p>
            <div class="pesona-unavail-actions">
                <a class="pesona-unavail-btn pesona-unavail-btn-primary" href="{{ route('inventory.pesona.cart.show') }}">Coba Lagi</a>
                <a class="pesona-unavail-btn pesona-unavail-btn-secondary" href="{{ route('inventory.pesona.catalog') }}">Kembali ke Daftar Barang</a>
            </div>
        @endif
    </div>
</x-inv-pesona-app>
