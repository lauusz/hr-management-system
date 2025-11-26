<x-app title="Pengajuan Menunggu HRD">
    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;min-width:720px;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Karyawan
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Jenis
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Tanggal
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Alasan
                        </th>
                        <th style="text-align:right;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;width:110px;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $lv)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:10px 12px;vertical-align:top;">
                                <div style="display:flex;flex-direction:column;gap:2px;max-width:220px;">
                                    <span style="font-size:0.9rem;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $lv->user->name }}
                                    </span>
                                    <span style="font-size:0.8rem;color:#6b7280;">
                                        {{ $lv->user->division->name ?? 'Divisi tidak diatur' }}
                                    </span>
                                </div>
                            </td>
                            <td style="padding:10px 12px;vertical-align:top;">
                                <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;font-size:0.78rem;font-weight:500;
                                    background:
                                        {{ $lv->type === \App\Enums\LeaveType::CUTI->value ? '#eff6ff' :
                                           ($lv->type === \App\Enums\LeaveType::IZIN_TELAT->value ? '#fef3c7' : '#f3f4f6') }};
                                    color:
                                        {{ $lv->type === \App\Enums\LeaveType::CUTI->value ? '#1d4ed8' :
                                           ($lv->type === \App\Enums\LeaveType::IZIN_TELAT->value ? '#92400e' : '#374151') }};
                                ">
                                    {{ $lv->type_label ?? $lv->type }}
                                </span>
                            </td>
                            <td style="padding:10px 12px;vertical-align:top;">
                                <div style="font-size:0.85rem;color:#111827;">
                                    {{ $lv->start_date->format('d M Y') }}
                                    @if($lv->end_date && $lv->end_date->ne($lv->start_date))
                                        – {{ $lv->end_date->format('d M Y') }}
                                    @endif
                                </div>
                                <div style="font-size:0.78rem;color:#6b7280;margin-top:2px;">
                                    Diajukan: {{ $lv->created_at->format('d M Y H:i') }}
                                </div>
                            </td>
                            <td style="padding:10px 12px;vertical-align:top;">
                                <div style="font-size:0.85rem;color:#374151;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ Str::limit($lv->reason, 80) }}
                                </div>
                            </td>
                            <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                                <a href="{{ route('hr.leave.show', $lv) }}"
                                   style="display:inline-flex;align-items:center;justify-content:center;padding:6px 12px;border-radius:999px;border:1px solid #d1d5db;font-size:0.8rem;text-decoration:none;color:#111827;background:#fff;white-space:nowrap;">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:16px;text-align:center;font-size:0.9rem;opacity:.7;">
                                Tidak ada pengajuan menunggu HRD.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:12px;">
        <x-pagination :items="$leaves" />
    </div>
</x-app>
