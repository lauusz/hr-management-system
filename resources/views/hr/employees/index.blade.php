<x-app title="Daftar Karyawan">

    @if(session('success'))
    <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;">
        {{ session('success') }}
    </div>
    @endif

    <div class="card" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:10px;flex-wrap:wrap;">
        <div style="font-size:0.9rem;opacity:.7;">
            Daftar karyawan yang terdaftar di sistem. HR dapat mengelola data karyawan.
        </div>
        <div style="font-size:0.9rem;opacity:.7;">
            Total Karyawan: {{ $totalEmployees }}
        </div>

        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" action="{{ route('hr.employees.index') }}" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari nama..."
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:160px;">

                <select
                    name="pt_id"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:160px;background:#fff;">
                    <option value="">Semua PT</option>
                    @foreach($ptOptions as $ptOption)
                    <option value="{{ $ptOption->id }}" @selected(($ptId ?? '' )==$ptOption->id)>
                        {{ $ptOption->name }}
                    </option>
                    @endforeach
                </select>

                <select
                    name="kategori"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:150px;background:#fff;">
                    <option value="">Semua Kategori</option>
                    <option value="Karyawan Tetap" @selected(($kategori ?? '' )==='Karyawan Tetap')>Karyawan Tetap</option>
                    <option value="Karyawan Kontrak" @selected(($kategori ?? '' )==='Karyawan Kontrak')>Karyawan Kontrak</option>
                </select>

                <select
                    name="position_id"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:0.85rem;min-width:180px;background:#fff;">
                    <option value="">Semua Jabatan</option>
                    @foreach($positionOptions as $position)
                    <option value="{{ $position->id }}" @selected(($positionId ?? '' )==$position->id)>
                        {{ $position->name }}
                    </option>
                    @endforeach
                </select>

                <label style="display:inline-flex;align-items:center;gap:4px;font-size:0.8rem;padding:4px 10px;border-radius:999px;border:1px solid #ddd;background:#f9fafb;cursor:pointer;">
                    <input
                        type="checkbox"
                        name="near_expiry"
                        value="1"
                        onchange="this.form.submit()"
                        @checked($nearExpiry ?? false)
                        style="margin:0;width:14px;height:14px;cursor:pointer;">
                    <span>Kontrak mendekati habis</span>
                </label>

                <button
                    type="submit"
                    style="padding:6px 10px;border-radius:999px;border:none;background:#1e4a8d;color:#fff;font-size:0.85rem;cursor:pointer;white-space:nowrap;">
                    Cari
                </button>

                @if(($search ?? null) || ($pt ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false))
                <a href="{{ route('hr.employees.index') }}"
                    style="padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:0.8rem;text-decoration:none;white-space:nowrap;">
                    Reset
                </a>
                @endif
            </form>

            <a href="{{ route('hr.employees.create') }}"
                style="padding:6px 12px;border-radius:999px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
                + Tambah Karyawan
            </a>
        </div>

    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;white-space:nowrap;">
            <table style="min-width:1200px;width:100%;border-collapse:collapse;table-layout:auto;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Nama
                        </th>
                        <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Jabatan
                        </th>
                        <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            PT
                        </th>
                        <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Kategori
                        </th>
                        <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Masa Kerja
                        </th>
                        <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Tgl Bergabung
                        </th>
                        <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Selesai Percobaan
                        </th>
                        <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Status
                        </th>
                        <th style="text-align:right;padding:9px 12px;border-bottom:1px solid #e5e7eb;width:190px;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $emp)
                        @php
                            $statusRaw = $emp->status ?? '-';
                            $statusText = $statusRaw;
                            $statusBg = '#e5f3ff';
                            $statusColor = '#1d4ed8';

                            if ($statusRaw === 'INACTIVE') {
                                $labelMap = [
                                    'RESIGN' => 'Resign',
                                    'HABIS_KONTRAK' => 'Habis kontrak',
                                    'PHK' => 'PHK',
                                    'PENSIUN' => 'Pensiun',
                                    'MENINGGAL' => 'Meninggal',
                                    'LAINNYA' => 'Nonaktif',
                                ];
                                $code = $emp->profile?->exit_reason_code;
                                if ($code) {
                                    $reasonLabel = $labelMap[$code] ?? $code;
                                    $statusText = 'INACTIVE (' . $reasonLabel . ')';
                                } else {
                                    $statusText = 'INACTIVE';
                                }
                                $statusBg = '#fee2e2';
                                $statusColor = '#b91c1c';
                            }
                        @endphp
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:9px 12px;vertical-align:middle;">
                                <div style="display:flex;flex-direction:column;gap:2px;max-width:260px;">
                                    <span style="font-size:0.9rem;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $emp->name }}
                                    </span>
                                    @if($emp->division?->name)
                                    <span style="font-size:0.78rem;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $emp->division->name }}
                                    </span>
                                    @endif
                                </div>
                            </td>

                            <td style="padding:9px 12px;vertical-align:middle;">
                                <span style="font-size:0.85rem;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;display:inline-block;">
                                    {{ $emp->position?->name ?? '-' }}
                                </span>
                            </td>

                            <td style="padding:9px 12px;vertical-align:middle;white-space:nowrap;">
                                <span style="font-size:0.85rem;color:#374151;">
                                    {{ $emp->profile?->pt?->name ?? '-' }}
                                </span>
                            </td>

                            <td style="padding:9px 12px;vertical-align:middle;white-space:nowrap;">
                                <span style="font-size:0.85rem;color:#374151;">
                                    {{ $emp->profile?->kategori ?? '-' }}
                                </span>
                            </td>

                            <td style="padding:9px 12px;vertical-align:middle;white-space:nowrap;">
                                <span style="font-size:0.85rem;color:#374151;">
                                    {{ $emp->masa_kerja_label ?? '-' }}
                                </span>
                            </td>

                            <td style="padding:9px 12px;vertical-align:middle;white-space:nowrap;">
                                <span style="font-size:0.85rem;color:#374151;">
                                    {{ $emp->join_date_label ?? '-' }}
                                </span>
                            </td>

                            <td style="padding:9px 12px;vertical-align:middle;white-space:nowrap;">
                                <span style="font-size:0.85rem;color:#374151;">
                                    {{ $emp->probation_end_label ?? '-' }}
                                </span>
                            </td>

                            <td style="padding:9px 12px;vertical-align:middle;white-space:nowrap;">
                                <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:.78rem;font-weight:500;background:{{ $statusBg }};color:{{ $statusColor }};">
                                    {{ $statusText }}
                                </span>
                            </td>

                            <td style="padding:9px 12px;vertical-align:middle;text-align:right;white-space:nowrap;">
                                <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                    <a href="{{ route('hr.employees.edit', $emp->id) }}"
                                        style="padding:5px 10px;border-radius:999px;border:1px solid #d1d5db;font-size:.8rem;text-decoration:none;color:#111827;background:#fff;">
                                        Edit
                                    </a>

                                    @if(($emp->status ?? 'ACTIVE') === 'ACTIVE')
                                    <button type="button"
                                        data-modal-target="exit-employee-{{ $emp->id }}"
                                        style="padding:5px 10px;border-radius:999px;border:1px solid #fecaca;background:#fee2e2;color:#b91c1c;font-size:.8rem;cursor:pointer;">
                                        Keluarkan
                                    </button>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="padding:16px;text-align:center;font-size:0.9rem;opacity:.7;">
                            Belum ada karyawan terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

    @foreach($items as $emp)
        @if(($emp->status ?? 'ACTIVE') === 'ACTIVE')
        <x-modal
            id="exit-employee-{{ $emp->id }}"
            title="Keluarkan Karyawan?"
            type="confirm"
            confirmLabel="Simpan"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.employees.exit', $emp->id)"
            confirmFormMethod="PUT">
            <div style="margin-bottom:8px;">
                <p style="margin:0 0 4px 0;">
                    Tentukan tanggal dan alasan karyawan berikut keluar dari perusahaan.
                </p>
                <p style="margin:0;font-weight:600;">
                    {{ $emp->name }}
                </p>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;">
                <div>
                    <div style="font-size:0.85rem;margin-bottom:4px;">Tanggal keluar</div>
                    <input
                        type="date"
                        name="exit_date"
                        value="{{ $emp->profile?->exit_date?->format('Y-m-d') }}"
                        style="width:100%;padding:6px 8px;border-radius:8px;border:1px solid #d1d5db;font-size:0.85rem;">
                </div>

                <div>
                    <div style="font-size:0.85rem;margin-bottom:4px;">Alasan keluar</div>
                    <select
                        name="exit_reason_code"
                        style="width:100%;padding:6px 8px;border-radius:8px;border:1px solid #d1d5db;font-size:0.85rem;background:#fff;">
                        <option value="">Pilih alasan</option>
                        <option value="RESIGN" @selected($emp->profile?->exit_reason_code === 'RESIGN')>Resign</option>
                        <option value="HABIS_KONTRAK" @selected($emp->profile?->exit_reason_code === 'HABIS_KONTRAK')>Habis kontrak</option>
                        <option value="PHK" @selected($emp->profile?->exit_reason_code === 'PHK')>PHK</option>
                        <option value="PENSIUN" @selected($emp->profile?->exit_reason_code === 'PENSIUN')>Pensiun</option>
                        <option value="MENINGGAL" @selected($emp->profile?->exit_reason_code === 'MENINGGAL')>Meninggal</option>
                        <option value="LAINNYA" @selected($emp->profile?->exit_reason_code === 'LAINNYA')>Lainnya</option>
                    </select>
                </div>

                <div>
                    <div style="font-size:0.85rem;margin-bottom:4px;">Catatan</div>
                    <textarea
                        name="exit_reason_note"
                        rows="3"
                        style="width:100%;padding:6px 8px;border-radius:8px;border:1px solid #d1d5db;font-size:0.85rem;resize:vertical;">{{ $emp->profile?->exit_reason_note }}</textarea>
                </div>
            </div>
        </x-modal>
        @endif
    @endforeach

    <div style="margin-top:12px;">
        <x-pagination :items="$items" />
    </div>

</x-app>
