<x-app title="Pilih Layanan">
    <div class="access-v2">
        <div class="access-hero">
            <h1>Pilih Layanan</h1>
            <p>Masuk ke HRD System atau modul kebutuhan kantor tanpa logout.</p>
        </div>

        <div class="access-grid">
            <a class="access-card access-card-hrd" href="{{ route('dashboard') }}">
                <span class="access-kicker">Existing</span>
                <strong>HRD System</strong>
                <small>Absensi, izin/cuti, lembur, payroll, dan master data HRD.</small>
            </a>

            <a class="access-card access-card-atk" href="{{ route('v2.atk.catalog') }}">
                <span class="access-kicker">V2 Testing</span>
                <strong>Kebutuhan Kantor</strong>
                <small>Katalog ATK, keranjang, request barang, stok, dan approval Admin ATK.</small>
            </a>
        </div>
    </div>

    <style>
        .access-v2 { display: flex; flex-direction: column; gap: 16px; }
        .access-hero {
            background: #fff;
            border: 1px solid var(--border, #E5E7EB);
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .access-hero h1 { margin: 0; font-size: 1.35rem; font-weight: 800; color: var(--text-primary, #111827); }
        .access-hero p { margin: 6px 0 0; color: var(--text-muted, #6B7280); font-size: .875rem; }
        .access-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .access-card {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-height: 180px;
            padding: 22px;
            border-radius: 22px;
            border: 1px solid var(--border, #E5E7EB);
            text-decoration: none;
            background: #fff;
            color: var(--text-primary, #111827);
            box-shadow: 0 8px 24px rgba(17,24,39,.06);
            transition: .18s ease;
        }
        .access-card:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(17,24,39,.09); }
        .access-card strong { font-size: 1.15rem; font-weight: 800; }
        .access-card small { color: var(--text-muted, #6B7280); line-height: 1.6; }
        .access-kicker { width: fit-content; border-radius: 999px; padding: 5px 10px; font-size: .68rem; font-weight: 800; }
        .access-card-hrd .access-kicker { background: rgba(20, 93, 160, .1); color: #145DA0; }
        .access-card-atk { border-color: #E4D8FF; }
        .access-card-atk .access-kicker { background: #F3EEFF; color: #5B35B7; }
        @media (max-width: 720px) { .access-grid { grid-template-columns: 1fr; } }
    </style>
</x-app>
