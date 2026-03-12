<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ !empty($thrOnly) ? 'Slip THR' : 'Slip Gaji' }} - PT Express Lintas Indonesia</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            width: 800px;
            background-color: #fff;
            padding: 20px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Reset margins untuk elemen teks */
        p,
        h3 {
            margin: 0;
            padding: 0;
        }

        /* CSS Umum */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 2px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .font-italic {
            font-style: italic;
        }

        /* Styling Garis */
        .garis-tebal {
            border-bottom: 2px solid #000;
            margin: 10px 0;
        }

        .border-top-thick {
            border-top: 2px solid #000;
        }

        .border-bottom-thick {
            border-bottom: 2px solid #000;
        }

        .border-bottom-double {
            border-bottom: 3px double #000;
        }

        /* Warna Background */
        .bg-gray {
            background-color: #d9d9d9;
        }

        .bg-cyan {
            background-color: #00b0f0;
        }

        .bg-yellow {
            background-color: #ffff00;
        }
    </style>
</head>

<body>

    <div class="container">

        <table style="margin-bottom: 5px;">
            <tr>
                <td style="width: 15%;">
                    <!-- Logo Dinamis untuk Email -->
                    @if($payslip->getLogoPath($ptName ?? null) && file_exists($payslip->getLogoPath($ptName ?? null)))
                    @php
                    $logoPath = $payslip->getLogoPath($ptName ?? null);
                    @endphp
                    <img src="{{ $message->embed($logoPath) }}" height="65" style="height: 65px; width: auto; max-width: 150px;" alt="Logo">
                    @else
                    <table style="width:80px;height:80px;border:1px dashed #999;margin:0;">
                        <tr>
                            <td valign="middle" align="center" style="font-size:10px;color:#666;">LOGO</td>
                        </tr>
                    </table>
                    @endif
                </td>
                <td style="width: 60%; vertical-align: middle;">
                    <h3 style="font-size: 16px; margin-bottom: 3px;">{{ $ptName ?? $payslip->user->profile->pt->name ?? 'PT. EXPRESS LINTAS INDONESIA' }}</h3>
                    <p>Jl. Tanjung Batu 15Q</p>
                    <p>Surabaya</p>
                    <p>Jawa Timur</p>
                </td>
                <td style="width: 25%; text-align: right; vertical-align: top;">
                    <h3 style="font-size: 16px; margin-bottom: 3px;">{{ !empty($thrOnly) ? 'SLIP THR' : 'SLIP GAJI' }}</h3>
                    <p class="font-bold">{{ \Carbon\Carbon::createFromDate((int)$payslip->period_year, (int)$payslip->period_month, 1)->format('M-y') }}</p>
                </td>
            </tr>
        </table>

        <div class="garis-tebal"></div>

        <table style="margin-bottom: 10px;">
            <tr>
                <td style="width: 15%;">NIP</td>
                <td style="width: 35%;">: {{ $payslip->user->profile->nik ?? '-' }}</td>
                <td style="width: 15%;">Jabatan</td>
                <td style="width: 35%;">: {{ $payslip->user->position->name ?? $payslip->user->profile->jabatan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama Karyawan</td>
                <td>: {{ strtoupper($payslip->user->name) }}</td>
                <td>NPWP</td>
                <td>: {{ $payslip->user->profile->nomor_npwp ?? '-' }}</td>
            </tr>
        </table>

        <table style="margin-bottom: 10px;">
            @php
                $thrOnlyMode = !empty($thrOnly);
                $totalPendapatanAdjusted =
                    ($payslip->gaji_pokok ?? 0) +
                    ($payslip->tunjangan_jabatan ?? 0) +
                    ($payslip->tunjangan_makan ?? 0) +
                    ($payslip->fee_marketing ?? 0) +
                    ($payslip->bonus_bulanan ?? 0) +
                    ($payslip->tunjangan_telekomunikasi ?? 0) +
                    ($payslip->tunjangan_lainnya ?? 0) +
                    ($payslip->tunjangan_penempatan ?? 0) +
                    ($payslip->tunjangan_asuransi ?? 0) +
                    ($payslip->tunjangan_kelancaran ?? 0) +
                    ($payslip->pendapatan_lain ?? 0) +
                    ($payslip->tunjangan_transportasi ?? 0) +
                    ($payslip->lembur ?? 0) +
                    ($payslip->thr ?? 0) +
                    ($payslip->bonus ?? 0);

                if ($thrOnlyMode) {
                    $totalPendapatanAdjusted = (float) ($payslip->thr ?? 0);
                }

                $totalPotonganDisplay = $thrOnlyMode ? 0 : (float) ($payslip->total_potongan ?? 0);
                $gajiBersihDisplay = $thrOnlyMode ? (float) ($payslip->thr ?? 0) : (float) ($payslip->gaji_bersih ?? 0);
            @endphp
            <colgroup>
                <col style="width: 28%;">
                <col style="width: 4%;">
                <col style="width: 14%;">
                <col style="width: 8%;">
                <col style="width: 28%;">
                <col style="width: 4%;">
                <col style="width: 14%;">
            </colgroup>

            <tr class="bg-gray border-top-thick border-bottom-thick font-bold">
                <td colspan="3">PENDAPATAN</td>
                <td style="background-color: #fff;"></td>
                <td colspan="3">{{ $thrOnlyMode ? '' : 'POTONGAN' }}</td>
            </tr>

            @if($thrOnlyMode)
            <tr>
                <td>THR</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->thr ?? 0, 2, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @else
            <tr>
                <td>GAJI POKOK</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->gaji_pokok, 2, ',', '.') }}</td>
                <td></td>
                <td>BPJS.TK</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->potongan_bpjs_tk, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>TUNJANGAN JABATAN</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->tunjangan_jabatan, 2, ',', '.') }}</td>
                <td></td>
                <td>PPH 21</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->potongan_pph21, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>TUNJANGAN UANG MAKAN</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->tunjangan_makan, 2, ',', '.') }}</td>
                <td></td>
                <td>HUTANG</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->potongan_hutang, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>{{ (float) ($payslip->fee_marketing ?? 0) > 0 ? 'FEE MARKETING' : 'BONUS BULANAN' }}</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format((float) ($payslip->fee_marketing ?? 0) > 0 ? ($payslip->fee_marketing ?? 0) : ($payslip->bonus_bulanan ?? 0), 2, ',', '.') }}</td>
                <td></td>
                <td>BPJS. KES</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->potongan_bpjs_kes, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>TELEKOMUNIKASI</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->tunjangan_telekomunikasi, 2, ',', '.') }}</td>
                <td></td>
                <td>KETERLAMBATAN</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->potongan_terlambat, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>{{ (float) ($payslip->tunjangan_lainnya ?? 0) > 0 ? 'TUNJANGAN LAINNYA' : 'TUNJANGAN PENEMPATAN/PO' }}</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format((float) ($payslip->tunjangan_lainnya ?? 0) > 0 ? ($payslip->tunjangan_lainnya ?? 0) : ($payslip->tunjangan_penempatan ?? 0), 2, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>ASURANSI</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->tunjangan_asuransi, 2, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>KELANCARAN</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->tunjangan_kelancaran, 2, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>LAIN-LAIN</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->pendapatan_lain, 2, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>TRANSPORTASI</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->tunjangan_transportasi, 2, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="padding-bottom: 5px;">LEMBUR</td>
                <td style="padding-bottom: 5px;">Rp</td>
                <td class="text-right" style="padding-bottom: 5px;">{{ number_format($payslip->lembur, 2, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @if((float) ($payslip->thr ?? 0) > 0)
            <tr>
                <td>THR</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->thr ?? 0, 2, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endif
            @if((float) ($payslip->bonus ?? 0) > 0)
            <tr>
                <td>BONUS</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->bonus ?? 0, 2, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endif
            @endif

            <tr class="bg-gray border-top-thick border-bottom-double font-bold">
                <td>JUMLAH PENDAPATAN</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($totalPendapatanAdjusted, 0, ',', '.') }}</td>
                <td style="background-color: #fff;"></td>
                <td>JUMLAH POTONGAN</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($totalPotonganDisplay, 0, ',', '.') }}</td>
            </tr>
        </table>

        <table style="width: 100%;">
            <tr>
                <td style="width: 65%; padding-top: 5px; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="font-bold" style="width: 35%;">ket/sisa utang &nbsp; &nbsp;:</td>
                            <td class="bg-cyan" style="width: 5%; border: 1px solid #000; border-right: none; padding: 2px 4px;"></td>
                            <td class="bg-cyan text-right font-bold" style="width: 40%; border: 1px solid #000; border-left: none; padding: 2px 4px;">{{ $payslip->display_sisa_utang }}</td>
                            <td style="width: 20%;"></td>
                        </tr>

                        <tr>
                            <td colspan="4" style="height: 5px;"></td>
                        </tr>

                        <tr>
                            <td class="font-bold">GAJI BERSIH &nbsp; &nbsp; &nbsp;:</td>
                            <td class="bg-yellow font-bold" style="border: 1px solid #000; border-right: none; padding: 2px 4px;">Rp</td>
                            <td class="bg-yellow text-right font-bold" style="border: 1px solid #000; border-left: none; padding: 2px 4px;">{{ number_format($gajiBersihDisplay, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="2" class="font-bold font-italic" style="padding-top: 3px;">
                                {{ App\Helpers\TerbilangHelper::convert($gajiBersihDisplay) }} Rupiah
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </td>

                <td style="width: 35%; text-align: center; vertical-align: bottom;">
                    <br>
                    <p style="margin-bottom: 5px;">HR-DEPARTMENT</p>
                    <table style="width:100%;">
                        <tr>
                            <td align="center">
                                @if($payslip->getStampPath($ptName ?? null) && file_exists($payslip->getStampPath($ptName ?? null)))
                                @php
                                $stampPath = $payslip->getStampPath($ptName ?? null);
                                @endphp
                                <img src="{{ $message->embed($stampPath) }}" style="max-width: 130px; max-height: 70px; width: auto; height: auto;" alt="Stamp">
                                @else
                                <table style="width:110px;height:60px;border:1px dashed #999;margin:0 auto;">
                                    <tr>
                                        <td valign="middle" align="center" style="font-size:10px;color:#999;line-height:1.2;">STEMPEL<br>&<br>TTD DI SINI</td>
                                    </tr>
                                </table>
                                @endif
                            </td>
                        </tr>
                    </table>
                    <p class="font-bold" style="text-decoration: underline;">RIDA CHOLIDHATUS S</p>
                </td>
            </tr>
        </table>

    </div>

</body>

</html>