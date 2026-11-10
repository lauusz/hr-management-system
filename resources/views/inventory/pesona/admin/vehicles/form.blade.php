<x-inv-pesona-app :title="($vehicle->exists ? 'Edit Nopol' : 'Tambah Nopol').' — Inventory Pesona'">
    <style>
        .pesona-vform-shell {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .pesona-vform-head {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .pesona-vform-head-top {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pesona-vform-back {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            align-self: flex-start;
            height: 34px;
            padding: 0 11px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 9px;
            background: #FFFFFF;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.14s ease, color 0.14s ease, border-color 0.14s ease;
        }
        .pesona-vform-back:hover {
            background: #F8FAFC;
            color: #0F172A;
            border-color: #CBD5E1;
        }
        .pesona-vform-card {
            width: 100%;
            padding: 20px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            display: flex;
            flex-direction: column;
            gap: 18px;
            box-sizing: border-box;
        }
        .pesona-vform-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            align-items: start;
        }
        .pesona-vform-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }
        .pesona-vform-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #1E293B;
        }
        .pesona-vform-req {
            color: #DC2626;
        }
        .pesona-vform-input,
        .pesona-vform-select {
            width: 100%;
            height: 42px;
            padding: 0 13px;
            border: 1.5px solid #CBD5E1;
            border-radius: 10px;
            background: #FFFFFF;
            color: #0F172A;
            font: inherit;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .pesona-vform-input.is-plate {
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .pesona-vform-input:focus,
        .pesona-vform-select:focus {
            outline: none;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.12);
        }
        .pesona-vform-hint {
            font-size: 12px;
            color: #64748B;
        }
        .pesona-vform-error {
            font-size: 12px;
            font-weight: 700;
            color: #B91C1C;
        }
        .pesona-vform-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 14px;
            border-top: 1px solid #F1F5F9;
        }
        .pesona-vform-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 40px;
            padding: 0 18px;
            border: 0;
            border-radius: 10px;
            background: var(--pesona-primary, #28355A);
            color: #FFFFFF;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.14s ease;
        }
        .pesona-vform-submit:hover {
            background: #1E2846;
        }
        .pesona-vform-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 16px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            background: #FFFFFF;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: background-color 0.14s ease, color 0.14s ease;
        }
        .pesona-vform-cancel:hover {
            background: #F8FAFC;
            color: #0F172A;
        }

        @media (min-width: 768px) {
            .pesona-vform-grid {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
        }
    </style>

    <div class="pesona-vform-shell">
        <div class="pesona-vform-head">
            <div class="pesona-vform-head-top">
                <a class="pesona-vform-back" href="{{ route('inventory.pesona.admin.vehicles.index') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    <span>Kembali ke Daftar Nopol</span>
                </a>
                <h1 class="pesona-title" style="margin:0;">{{ $vehicle->exists ? 'Edit Nopol' : 'Tambah Nopol' }}</h1>
            </div>
            <p class="pesona-subtitle">
                {{ $vehicle->exists ? 'Perbarui nomor polisi atau status aktif armada kendaraan.' : 'Tambahkan nomor polisi armada baru agar dapat dipilih saat pengajuan pengambilan.' }}
            </p>
        </div>

        <form
            method="POST"
            action="{{ $vehicle->exists ? route('inventory.pesona.admin.vehicles.update', $vehicle) : route('inventory.pesona.admin.vehicles.store') }}"
            class="pesona-vform-card"
        >
            @csrf
            @if($vehicle->exists) @method('PUT') @endif

            <div class="pesona-vform-grid">
                <div class="pesona-vform-field">
                    <label class="pesona-vform-label" for="vehicle-nopol">
                        Nomor Polisi (Nopol) <span class="pesona-vform-req" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="vehicle-nopol"
                        name="nopol"
                        class="pesona-vform-input is-plate"
                        value="{{ old('nopol', $vehicle->nopol) }}"
                        maxlength="30"
                        placeholder="Contoh: B 1234 XYZ"
                        required
                        @error('nopol') aria-invalid="true" aria-describedby="vehicle-nopol-error" @enderror
                    >
                    <span class="pesona-vform-hint">Otomatis diubah menjadi huruf kapital.</span>
                    @error('nopol')
                        <span id="vehicle-nopol-error" class="pesona-vform-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="pesona-vform-field">
                    <label class="pesona-vform-label" for="vehicle-active">Status Kendaraan</label>
                    <select id="vehicle-active" name="is_active" class="pesona-vform-select" required>
                        <option value="1" @selected((string) old('is_active', (int) $vehicle->is_active) === '1')>Aktif (Tampil di pilihan pengambilan)</option>
                        <option value="0" @selected((string) old('is_active', (int) $vehicle->is_active) === '0')>Nonaktif (Disembunyikan dari pengajuan baru)</option>
                    </select>
                    <span class="pesona-vform-hint">Hanya nopol berstatus Aktif yang muncul pada pilihan pengajuan barang.</span>
                    @error('is_active')
                        <span class="pesona-vform-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="pesona-vform-actions">
                <button class="pesona-vform-submit" type="submit">Simpan Nopol</button>
                <a class="pesona-vform-cancel" href="{{ route('inventory.pesona.admin.vehicles.index') }}">Batal</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('vehicle-nopol')?.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    </script>
</x-inv-pesona-app>
