<x-app title="Pilih Layanan">

    <div class="access-list">
        <a class="access-item access-item--hrd" href="{{ route('dashboard') }}">
            <span class="access-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
            <span class="access-body">
                <strong>HRD System</strong>
                <small>Absensi, cuti, lembur &amp; karyawan</small>
            </span>
            <span class="access-go" aria-hidden="true">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </a>

        <a class="access-item access-item--atk" href="{{ route('v2.atk.catalog') }}">
            <span class="access-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </span>
            <span class="access-body">
                <strong>Kebutuhan Kantor</strong>
                <small>Katalog &amp; pengajuan ATK</small>
            </span>
            <span class="access-go" aria-hidden="true">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </a>

        @if(auth()->user()->canAccessOps())
            <a class="access-item access-item--ops" href="{{ route('v2.ops.catalog') }}">
                <span class="access-icon" aria-hidden="true">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                <span class="access-body">
                    <strong>Kebutuhan Operasional</strong>
                    <small>Katalog &amp; pengajuan OPS</small>
                </span>
                <span class="access-go" aria-hidden="true">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>
        @endif

        @if(auth()->user()->canAccessAtkMks())
            <a class="access-item access-item--atk-mks" href="{{ route('v2.atk-mks.catalog') }}">
                <span class="access-icon" aria-hidden="true">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <span class="access-body">
                    <strong>Stok ATK MKS</strong>
                    <small>Katalog &amp; pengajuan ATK Makassar</small>
                </span>
                <span class="access-go" aria-hidden="true">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>
        @endif
    </div>

    <style>
        /* Mobile-first: 1 kolom penuh */
        .access-list {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            box-sizing: border-box;
        }

        .access-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            box-sizing: border-box;
            background: var(--white, #FFFFFF);
            border: 1px solid var(--border, #E5E7EB);
            border-left: 4px solid var(--item-accent, #145DA0);
            border-radius: 14px;
            text-decoration: none;
            color: var(--text-primary, #111827);
            box-shadow: 0 1px 2px rgba(17, 24, 39, 0.04);
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }

        .access-item:hover {
            border-color: var(--item-accent, #145DA0);
            box-shadow: 0 6px 16px rgba(17, 24, 39, 0.07);
            transform: translateY(-1px);
        }

        .access-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: var(--item-tint, rgba(20, 93, 160, 0.1));
            color: var(--item-accent, #145DA0);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .access-body {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .access-body strong {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.3;
        }

        .access-body small {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            line-height: 1.4;
        }

        .access-go {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--gray-50, #F5F7FA);
            color: var(--text-muted, #6B7280);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background-color .15s ease, color .15s ease, transform .15s ease;
        }

        .access-item:hover .access-go {
            background: var(--item-tint, rgba(20, 93, 160, 0.1));
            color: var(--item-accent, #145DA0);
            transform: translateX(2px);
        }

        .access-item--hrd {
            --item-accent: #145DA0;
            --item-tint: rgba(20, 93, 160, 0.1);
        }

        .access-item--atk {
            --item-accent: #5B35B7;
            --item-tint: #F3EEFF;
        }

        .access-item--ops {
            --item-accent: #0F766E;
            --item-tint: #E8F8F3;
        }

        .access-item--atk-mks {
            --item-accent: #A16207;
            --item-tint: #FEF3C7;
        }

        /* Tablet & Desktop: 2 kolom penuh menyesuaikan lebar halaman */
        @media (min-width: 640px) {
            .access-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
            }

            /* Bila jumlah layanan ganjil (mis. 3 layanan), kartu terakhir melebar penuh agar rata */
            .access-item:last-child:nth-child(odd) {
                grid-column: 1 / -1;
            }
        }

        @media (min-width: 1024px) {
            .access-list {
                gap: 16px;
            }

            .access-item {
                padding: 20px 22px;
                gap: 16px;
            }

            .access-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
            }

            .access-body strong {
                font-size: 1.05rem;
            }

            .access-body small {
                font-size: 0.85rem;
            }
        }
    </style>
</x-app>
