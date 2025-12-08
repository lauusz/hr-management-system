<x-app title="Detail Hutang Karyawan">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <p style="font-size:.9rem;opacity:.75;margin:0;">
            Detail pengajuan hutang karyawan beserta riwayat cicilan yang telah dicatat.
        </p>

        <a href="{{ route('hr.loan_requests.index') }}"
           style="font-size:.85rem;padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#111827;">
            ← Kembali
        </a>
    </div>

    @php
        $months = $loan->repayment_term ? (int) $loan->repayment_term : 0;
        $monthlyInstallment = $months > 0 ? floor($loan->amount / $months) : null;
    @endphp

    <div style="display:grid;grid-template-columns:2fr 1.5fr;gap:12px;align-items:flex-start;flex-wrap:wrap;">
        <div class="card" style="padding:14px;display:flex;flex-direction:column;gap:10px;">
            <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
                Data Karyawan
            </div>

            <div style="display:grid;grid-template-columns:1fr;gap:6px;font-size:.85rem;">
                <div>
                    <div style="font-weight:500;">Nama</div>
                    <div>{{ $loan->snapshot_name }}</div>
                </div>
                <div>
                    <div style="font-weight:500;">Nomor Induk Karyawan (NIK)</div>
                    <div>{{ $loan->snapshot_nik ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-weight:500;">Jabatan</div>
                    <div>{{ $loan->snapshot_position ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-weight:500;">Departemen / Divisi</div>
                    <div>{{ $loan->snapshot_division ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-weight:500;">Perusahaan</div>
                    <div>{{ $loan->snapshot_company ?? '-' }}</div>
                </div>
            </div>

            <hr style="border:none;border-top:1px solid #e5e7eb;margin:10px 0;">

            <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
                Detail Pengajuan Hutang
            </div>

            <div style="display:grid;grid-template-columns:1fr;gap:6px;font-size:.85rem;">
                <div>
                    <div style="font-weight:500;">Tanggal Pengajuan</div>
                    <div>{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->format('d/m/Y') }}</div>
                </div>

                <div>
                    <div style="font-weight:500;">Besar Pinjaman</div>
                    <div style="font-weight:600;">
                        Rp {{ number_format($loan->amount, 0, ',', '.') }}
                    </div>
                </div>

                <div>
                    <div style="font-weight:500;">Keperluan</div>
                    <div>{{ $loan->purpose ?: '-' }}</div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;">
                    <div>
                        <div style="font-weight:500;">Tanggal Pinjam</div>
                        <div>
                            @if($loan->disbursement_date)
                                {{ \Illuminate\Support\Carbon::parse($loan->disbursement_date)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="font-weight:500;">Jangka Waktu Cicilan</div>
                        <div>
                            @if($months > 0)
                                {{ $months }} bulan
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <div style="font-weight:500;">Perkiraan Cicilan Per Bulan</div>
                    <div>
                        @if($monthlyInstallment)
                            Rp {{ number_format($monthlyInstallment, 0, ',', '.') }} per bulan
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div>
                    <div style="font-weight:500;">Cara Pengembalian</div>
                    <div>
                        @if($loan->payment_method === 'TUNAI')
                            Tunai
                        @elseif($loan->payment_method === 'CICILAN')
                            Cicilan (transfer ke rekening perusahaan)
                        @elseif($loan->payment_method === 'POTONG_GAJI')
                            Pemotongan gaji
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div>
                    <div style="font-weight:500;">Status</div>
                    @php
                        $statusLabel = $loan->status;
                        $bg = '#e5e7eb';
                        $color = '#374151';

                        if ($loan->status === 'PENDING_HRD') {
                            $statusLabel = 'Menunggu persetujuan HRD';
                            $bg = '#fef3c7';
                            $color = '#92400e';
                        } elseif ($loan->status === 'APPROVED') {
                            $statusLabel = 'Disetujui HRD';
                            $bg = '#dcfce7';
                            $color = '#166534';
                        } elseif ($loan->status === 'REJECTED') {
                            $statusLabel = 'Ditolak HRD';
                            $bg = '#fee2e2';
                            $color = '#b91c1c';
                        } elseif ($loan->status === 'LUNAS') {
                            $statusLabel = 'Lunas';
                            $bg = '#bbf7d0';
                            $color = '#166534';
                        }
                    @endphp
                    <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:500;background:{{ $bg }};color:{{ $color }};">
                        {{ $statusLabel }}
                    </span>
                    @if($loan->hrd_decided_at)
                        <div style="font-size:.8rem;color:#6b7280;margin-top:4px;">
                            Diproses: {{ $loan->hrd_decided_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>

                <div>
                    <div style="font-weight:500;">Bukti Dokumen</div>
                    @if($loan->document_path)
                        <a href="{{ asset('storage/' . $loan->document_path) }}"
                           target="_blank"
                           style="font-size:.8rem;color:#1e4a8d;text-decoration:underline;">
                            Lihat dokumen
                        </a>
                    @else
                        <span>-</span>
                    @endif
                </div>

                <div>
                    <div style="font-weight:500;">Catatan HRD</div>
                    <div>{{ $loan->hrd_note ?: '-' }}</div>
                </div>
            </div>
        </div>

        @php
            $totalPaid = $loan->repayments->sum('amount');
            $remaining = max(0, $loan->amount - $totalPaid);
        @endphp

        <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="card" style="padding:12px;display:flex;flex-direction:column;gap:8px;">
                <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
                    Tindakan HRD
                </div>

                @if($loan->status === 'PENDING_HRD')
                    <form action="{{ route('hr.loan_requests.approve', $loan->id) }}" method="POST" style="margin-bottom:8px;">
                        @csrf
                        <div style="display:flex;flex-direction:column;gap:6px;font-size:.85rem;margin-bottom:8px;">
                            <label for="hrd_note_approve" style="font-weight:500;">Catatan (opsional)</label>
                            <textarea id="hrd_note_approve"
                                      name="hrd_note"
                                      rows="2"
                                      style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;resize:vertical;"></textarea>
                        </div>
                        <button type="submit"
                                style="width:100%;padding:8px 12px;border-radius:8px;border:none;background:#16a34a;color:#fff;font-size:.85rem;cursor:pointer;">
                            Setujui Pengajuan
                        </button>
                    </form>

                    <form action="{{ route('hr.loan_requests.reject', $loan->id) }}" method="POST">
                        @csrf
                        <div style="display:flex;flex-direction:column;gap:6px;font-size:.85rem;margin-bottom:8px;">
                            <label for="hrd_note_reject" style="font-weight:500;">Alasan penolakan</label>
                            <textarea id="hrd_note_reject"
                                      name="hrd_note"
                                      rows="2"
                                      required
                                      style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;resize:vertical;"></textarea>
                        </div>
                        <button type="submit"
                                style="width:100%;padding:8px 12px;border-radius:8px;border:none;background:#b91c1c;color:#fff;font-size:.85rem;cursor:pointer;">
                            Tolak Pengajuan
                        </button>
                    </form>
                @else
                    <div style="font-size:.85rem;color:#4b5563;">
                        Status pengajuan sudah diproses. Jika diperlukan perubahan, lakukan melalui prosedur internal HRD.
                    </div>
                @endif
            </div>

            <div class="card" style="padding:12px;display:flex;flex-direction:column;gap:8px;">
                <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
                    Riwayat Cicilan / Potongan
                </div>

                <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:6px;">
                    <div>
                        <div style="font-weight:500;">Total dibayar</div>
                        <div>Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div style="font-weight:500;">Sisa hutang (per catatan sistem)</div>
                        <div>Rp {{ number_format($remaining, 0, ',', '.') }}</div>
                    </div>
                </div>

                @if($loan->status === 'LUNAS')
                    <div style="margin-bottom:6px;font-size:.8rem;color:#16a34a;">
                        Hutang ini sudah dinyatakan lunas. Tidak dapat mencatat cicilan atau potongan tambahan.
                    </div>
                @endif

                <div style="margin-top:6px;margin-bottom:8px;">
                    @if($loan->repayments->isEmpty())
                        <div style="font-size:.8rem;color:#6b7280;">
                            Belum ada cicilan atau potongan yang dicatat.
                        </div>
                    @else
                        <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
                            <thead>
                            <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                                <th style="text-align:left;padding:6px 8px;">Cicilan ke</th>
                                <th style="text-align:left;padding:6px 8px;">Tanggal</th>
                                <th style="text-align:left;padding:6px 8px;">Nominal</th>
                                <th style="text-align:left;padding:6px 8px;">Metode</th>
                                <th style="text-align:left;padding:6px 8px;">Catatan</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($loan->repayments as $index => $repayment)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:6px 8px;">{{ $index + 1 }}</td>
                                    <td style="padding:6px 8px;">
                                        {{ \Illuminate\Support\Carbon::parse($repayment->paid_at)->format('d/m/Y') }}
                                    </td>
                                    <td style="padding:6px 8px;">
                                        Rp {{ number_format($repayment->amount, 0, ',', '.') }}
                                    </td>
                                    <td style="padding:6px 8px;">
                                        @if($repayment->method === 'TUNAI')
                                            Tunai
                                        @elseif($repayment->method === 'TRANSFER')
                                            Transfer
                                        @elseif($repayment->method === 'POTONG_GAJI')
                                            Potong gaji
                                        @endif
                                    </td>
                                    <td style="padding:6px 8px;max-width:200px;">
                                        {{ $repayment->note ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                @if($loan->status !== 'LUNAS')
                    <form action="{{ route('hr.loan_requests.repayments.store', $loan->id) }}" method="POST" style="margin-top:8px;">
                        @csrf
                        <div style="font-size:.85rem;font-weight:500;margin-bottom:4px;">
                            Catat Cicilan / Potongan Baru
                        </div>

                        <div style="display:flex;flex-direction:column;gap:6px;font-size:.85rem;">
                            <div>
                                <label for="paid_at" style="font-weight:500;">Tanggal</label>
                                <input
                                    id="paid_at"
                                    type="date"
                                    name="paid_at"
                                    value="{{ old('paid_at', now()->toDateString()) }}"
                                    required
                                    style="width:100%;padding:6px 8px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;">
                            </div>

                            <div>
                                <label for="repayment_amount_display" style="font-weight:500;">Nominal cicilan/potongan</label>
                                <input
                                    id="repayment_amount_display"
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    value="{{ old('amount') ? 'Rp' . number_format(old('amount'), 0, ',', '.') : '' }}"
                                    required
                                    style="width:100%;padding:6px 8px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;">
                                <input
                                    id="repayment_amount"
                                    type="hidden"
                                    name="amount"
                                    value="{{ old('amount') }}">
                            </div>

                            <div>
                                <label for="method" style="font-weight:500;">Metode</label>
                                <select
                                    id="method"
                                    name="method"
                                    required
                                    style="width:100%;padding:6px 8px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;">
                                    <option value="POTONG_GAJI" @selected(old('method', $loan->payment_method === 'POTONG_GAJI' ? 'POTONG_GAJI' : null) === 'POTONG_GAJI')>
                                        Potong gaji
                                    </option>
                                    <option value="TRANSFER" @selected(old('method', $loan->payment_method === 'CICILAN' ? 'TRANSFER' : null) === 'TRANSFER')>
                                        Transfer
                                    </option>
                                    <option value="TUNAI" @selected(old('method') === 'TUNAI')>
                                        Tunai
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="note" style="font-weight:500;">Catatan</label>
                                <textarea
                                    id="note"
                                    name="note"
                                    rows="2"
                                    style="width:100%;padding:6px 8px;border-radius:8px;border:1px solid #d1d5db;font-size:.85rem;resize:vertical;">{{ old('note') }}</textarea>
                            </div>
                        </div>

                        <button type="submit"
                                style="margin-top:8px;width:100%;padding:8px 12px;border-radius:8px;border:none;background:#1e4a8d;color:#fff;font-size:.85rem;cursor:pointer;">
                            Simpan Cicilan / Potongan
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function formatRupiahNumber(value) {
                if (!value || isNaN(value)) return '';
                return Number(value).toLocaleString('id-ID');
            }

            function updateRepaymentAmountFormatting() {
                var displayInput = document.getElementById('repayment_amount_display');
                var hiddenInput = document.getElementById('repayment_amount');
                if (!displayInput || !hiddenInput) return;

                var raw = displayInput.value || '';
                var digits = raw.replace(/\D/g, '');
                if (digits.length === 0) {
                    hiddenInput.value = '';
                    displayInput.value = '';
                    return;
                }

                var numeric = parseInt(digits);
                hiddenInput.value = numeric;
                displayInput.value = 'Rp' + formatRupiahNumber(numeric);
            }

            document.addEventListener('DOMContentLoaded', function () {
                var displayInput = document.getElementById('repayment_amount_display');

                if (displayInput) {
                    displayInput.addEventListener('input', updateRepaymentAmountFormatting);
                    displayInput.addEventListener('blur', updateRepaymentAmountFormatting);
                }

                var hiddenInput = document.getElementById('repayment_amount');
                if (hiddenInput && hiddenInput.value && displayInput) {
                    displayInput.value = 'Rp' + formatRupiahNumber(hiddenInput.value);
                }
            });
        </script>
    @endpush

</x-app>
