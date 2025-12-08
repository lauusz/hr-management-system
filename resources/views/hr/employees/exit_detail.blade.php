<x-app title="Detail Keluar Karyawan">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;gap:10px;flex-wrap:wrap;">
        <div>
            <p style="margin:0;font-size:0.9rem;opacity:.75;">
                Ringkasan data karyawan yang sudah dinonaktifkan dari perusahaan.
            </p>
        </div>

        <a href="{{ route('hr.employees.index') }}"
           style="font-size:.9rem;padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#111827;background:#fff;">
            ← Kembali ke daftar
        </a>
    </div>

    <div class="card" style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:18px;align-items:flex-start;">
        <div style="border-right:1px solid #e5e7eb;padding-right:12px;">
            <div style="margin-bottom:14px;">
                <div style="font-size:0.78rem;font-weight:600;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;margin-bottom:4px;">
                    Data Karyawan
                </div>
                <div style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:2px;">
                    {{ $employee->name }}
                </div>
                <div style="font-size:0.85rem;color:#4b5563;">
                    @if($employee->position?->name)
                        {{ $employee->position->name }}
                    @else
                        -
                    @endif
                    @if($employee->division?->name)
                        • {{ $employee->division->name }}
                    @endif
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;font-size:0.85rem;color:#374151;">
                <div>
                    <div style="opacity:.7;margin-bottom:2px;">PT</div>
                    <div style="font-weight:500;">
                        {{ $profile?->pt?->name ?? '-' }}
                    </div>
                </div>
                <div>
                    <div style="opacity:.7;margin-bottom:2px;">Kategori</div>
                    <div style="font-weight:500;">
                        {{ $profile?->kategori ?? '-' }}
                    </div>
                </div>
                <div>
                    <div style="opacity:.7;margin-bottom:2px;">Tanggal bergabung</div>
                    <div style="font-weight:500;">
                        {{ $joinDateLabel ?? '-' }}
                    </div>
                </div>
                <div>
                    <div style="opacity:.7;margin-bottom:2px;">Masa kerja</div>
                    <div style="font-weight:500;">
                        {{ $masaKerjaLabel ?? '-' }}
                    </div>
                </div>
            </div>

            <div style="margin-top:16px;padding-top:10px;border-top:1px dashed #e5e7eb;font-size:0.85rem;">
                <div style="opacity:.7;margin-bottom:4px;">Status saat ini</div>
                @php
                    $statusRaw = $employee->status ?? '-';
                    $badgeBg = '#e5f3ff';
                    $badgeColor = '#1d4ed8';
                    $statusText = $statusRaw;

                    if ($statusRaw === 'INACTIVE') {
                        $label = $reasonLabel ? 'INACTIVE (' . $reasonLabel . ')' : 'INACTIVE';
                        $statusText = $label;
                        $badgeBg = '#fee2e2';
                        $badgeColor = '#b91c1c';
                    }
                @endphp
                <span style="display:inline-flex;align-items:center;padding:4px 12px;border-radius:999px;font-size:.8rem;font-weight:500;background:{{ $badgeBg }};color:{{ $badgeColor }};">
                    {{ $statusText }}
                </span>
            </div>
        </div>

        <div style="padding-left:4px;font-size:0.85rem;color:#374151;">
            <div style="margin-bottom:10px;">
                <div style="font-size:0.78rem;font-weight:600;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;margin-bottom:4px;">
                    Informasi Keluar
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;">
                <div>
                    <div style="opacity:.7;margin-bottom:2px;">Tanggal keluar</div>
                    <div style="font-weight:500;">
                        {{ $exitDateLabel ?? '-' }}
                    </div>
                </div>
                <div>
                    <div style="opacity:.7;margin-bottom:2px;">Alasan keluar</div>
                    <div style="font-weight:500;">
                        {{ $reasonLabel ?? '-' }}
                    </div>
                </div>
                <div>
                    <div style="opacity:.7;margin-bottom:4px;">Catatan</div>
                    <div style="padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;min-height:48px;">
                        @if($profile && $profile->exit_reason_note)
                            {!! nl2br(e($profile->exit_reason_note)) !!}
                        @else
                            <span style="opacity:.6;">Tidak ada catatan tambahan.</span>
                        @endif
                    </div>
                </div>

                <div>
                    <div style="opacity:.7;margin-bottom:4px;">Dokumen pendukung</div>
                    @if($profile && $profile->exit_document_path)
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <a href="{{ asset('storage/' . $profile->exit_document_path) }}" target="_blank"
                               style="display:inline-flex;align-items:center;gap:6px;font-size:0.85rem;color:#1d4ed8;text-decoration:none;padding:6px 10px;border-radius:999px;border:1px solid #bfdbfe;background:#eff6ff;">
                                <span>Lihat / unduh dokumen keluar</span>
                            </a>
                            <span style="font-size:0.78rem;opacity:.7;">
                                Simpan dokumen ini sebagai bukti resmi terkait keluarnya karyawan.
                            </span>
                        </div>
                    @else
                        <div style="padding:8px 10px;border-radius:8px;border:1px dashed #e5e7eb;background:#fafafa;opacity:.8;">
                            Belum ada dokumen pendukung yang diunggah.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app>
