<x-app title="Master Izin / Cuti">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
        <div style="font-size:0.9rem;opacity:.7;">
            Data seluruh pengajuan izin/cuti karyawan. HR dapat melakukan pengecekan riwayat dan rekap.
        </div>
    </div>

    @php
        $statusLabels = [
            \App\Models\LeaveRequest::PENDING_SUPERVISOR => 'Menunggu Supervisor',
            \App\Models\LeaveRequest::PENDING_HR         => 'Menunggu HRD',
            \App\Models\LeaveRequest::STATUS_APPROVED    => 'Disetujui',
            \App\Models\LeaveRequest::STATUS_REJECTED    => 'Ditolak',
        ];
    @endphp

    <div class="card" style="margin-bottom:14px;">
        <form method="GET"
              action="{{ route('hr.leave.master') }}"
              style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">

            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Tanggal Pengajuan</label>
                <input
                    type="text"
                    id="submitted_range"
                    name="submitted_range"
                    value="{{ $submittedRange ?? '' }}"
                    placeholder="Pilih rentang tanggal"
                    style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;min-width:220px;"
                    autocomplete="off">
            </div>

            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Jenis Pengajuan</label>
                <select name="type"
                        style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;min-width:200px;">
                    <option value="">Semua jenis</option>
                    @foreach($typeOptions as $case)
                        @php
                            $value = $case->value;
                            $label = $case->label();
                            if ($value === \App\Enums\LeaveType::CUTI_KHUSUS->value) {
                                $label = 'Cuti Khusus';
                            }
                        @endphp
                        <option value="{{ $value }}" @selected($typeFilter === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Status</label>
                <select name="status"
                        style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;min-width:180px;">
                    <option value="">Semua status</option>
                    @foreach($statusOptions as $opt)
                        <option value="{{ $opt }}" @selected($status === $opt)">
                            {{ $statusLabels[$opt] ?? $opt }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Nama Karyawan</label>
                <input type="text"
                       name="q"
                       value="{{ $q ?? '' }}"
                       placeholder="Cari nama..."
                       style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;min-width:200px;">
            </div>

            <button type="submit"
                    style="padding:8px 14px;background:#1e4a8d;color:#fff;border:none;
                           border-radius:999px;cursor:pointer;font-size:0.85rem;white-space:nowrap;">
                Filter
            </button>

            <a href="{{ route('hr.leave.master') }}"
               style="padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:0.8rem;text-decoration:none;white-space:nowrap;">
                Reset
            </a>
        </form>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="width:100%;overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:900px;">
                <thead>
                    <tr>
                        <th style="padding:10px 12px;text-align:left;font-weight:600;font-size:.8rem;
                                   text-transform:uppercase;letter-spacing:.04em;color:#6b7280;
                                   border-bottom:1px solid #e5e7eb;width:56px;">
                            #
                        </th>
                        <th style="padding:10px 12px;text-align:left;font-weight:600;font-size:.8rem;
                                   text-transform:uppercase;letter-spacing:.04em;color:#6b7280;
                                   border-bottom:1px solid #e5e7eb;">
                            Karyawan
                        </th>
                        <th style="padding:10px 12px;text-align:left;font-weight:600;font-size:.8rem;
                                   text-transform:uppercase;letter-spacing:.04em;color:#6b7280;
                                   border-bottom:1px solid #e5e7eb;">
                            Tgl Pengajuan
                        </th>
                        <th style="padding:10px 12px;text-align:left;font-weight:600;font-size:.8rem;
                                   text-transform:uppercase;letter-spacing:.04em;color:#6b7280;
                                   border-bottom:1px solid #e5e7eb;">
                            Periode Izin
                        </th>
                        <th style="padding:10px 12px;text-align:left;font-weight:600;font-size:.8rem;
                                   text-transform:uppercase;letter-spacing:.04em;color:#6b7280;
                                   border-bottom:1px solid #e5e7eb;">
                            Jenis
                        </th>
                        <th style="padding:10px 12px;text-align:left;font-weight:600;font-size:.8rem;
                                   text-transform:uppercase;letter-spacing:.04em;color:#6b7280;
                                   border-bottom:1px solid #e5e7eb;">
                            Status
                        </th>
                        <th style="padding:10px 12px;text-align:right;font-weight:600;font-size:.8rem;
                                   text-transform:uppercase;letter-spacing:.04em;color:#6b7280;
                                   border-bottom:1px solid #e5e7eb;width:110px;">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $i => $row)
                        @php
                            $rowStatus = $row->status;
                            if ($rowStatus === \App\Models\LeaveRequest::STATUS_APPROVED) {
                                $badgeClass = 'badge-disetujui';
                            } elseif ($rowStatus === \App\Models\LeaveRequest::STATUS_REJECTED) {
                                $badgeClass = 'badge-ditolak';
                            } elseif (in_array($rowStatus, [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR], true)) {
                                $badgeClass = 'badge-menunggu';
                            } else {
                                $badgeClass = 'badge-neutral';
                            }
                        @endphp

                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:10px 12px;color:#6b7280;font-size:0.85rem;vertical-align:middle;">
                                {{ $items->firstItem() + $i }}
                            </td>

                            <td style="padding:10px 12px;vertical-align:middle;">
                                <div style="display:flex;flex-direction:column;gap:2px;max-width:220px;">
                                    <span style="font-size:0.9rem;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $row->user->name }}
                                    </span>
                                </div>
                            </td>

                            <td style="padding:10px 12px;vertical-align:middle;font-size:0.85rem;color:#374151;">
                                {{ $row->created_at?->format('d M Y H:i') ?? '-' }}
                            </td>

                            <td style="padding:10px 12px;vertical-align:middle;font-size:0.85rem;color:#111827;">
                                {{ $row->start_date->format('d M Y') }}
                                @if($row->end_date && $row->end_date->ne($row->start_date))
                                    – {{ $row->end_date->format('d M Y') }}
                                @endif
                            </td>

                            <td style="padding:10px 12px;vertical-align:middle;font-size:0.85rem;color:#374151;">
                                {{ \Illuminate\Support\Str::contains($row->type_label, 'Cuti Khusus') ? 'Cuti Khusus' : $row->type_label }}
                            </td>

                            <td style="padding:10px 12px;vertical-align:middle;">
                                <span class="badge {{ $badgeClass }}">
                                    {{ $row->status_label }}
                                </span>
                            </td>

                            <td style="padding:10px 12px;vertical-align:middle;text-align:right;">
                                <a href="{{ route('hr.leave.show', $row) }}"
                                   style="text-decoration:none;display:inline-flex;align-items:center;
                                          padding:6px 12px;border-radius:999px;border:1px solid #d1d5db;
                                          font-size:.8rem;color:#111827;background:#fff;">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                style="padding:16px;text-align:center;color:#6b7280;font-size:0.9rem;">
                                Belum ada data izin/cuti.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:12px;">
        <x-pagination :items="$items" />
    </div>

    <style>
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 13px;
            min-width: 90px;
            white-space: nowrap;
            text-align: center;
        }

        .badge-disetujui {
            background: #dcfce7;
            color: #166534;
        }

        .badge-menunggu {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-ditolak {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-neutral {
            background: #e5e7eb;
            color: #374151;
        }

        table th,
        table td {
            vertical-align: middle;
        }

        table tr:hover td {
            background: #f9fafb;
        }

        @media(max-width:600px) {
            table th,
            table td {
                padding: 8px;
                font-size: 13px;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#submitted_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            allowInput: true,
            locale: {
                rangeSeparator: " sampai "
            }
        });
    </script>

</x-app>
