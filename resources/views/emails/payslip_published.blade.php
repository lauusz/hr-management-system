<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - PT Express Lintas Indonesia</title>
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
                    <!-- Logo Placeholer -->
                    <div style="width: 80px; height: 80px; border: 1px dashed #999; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666;">LOGO</div>
                </td>
                <td style="width: 60%; vertical-align: middle;">
                    <h3 style="font-size: 16px; margin-bottom: 3px;">PT. EXPRESS LINTAS INDONESIA</h3>
                    <p>Jl. Tanjung Batu 15Q</p>
                    <p>Surabaya</p>
                    <p>Jawa Timur</p>
                </td>
                <td style="width: 25%; text-align: right; vertical-align: top;">
                    <h3 style="font-size: 16px; margin-bottom: 3px;">SLIP GAJI</h3>
                    <p class="font-bold">{{ \Carbon\Carbon::createFromDate($payslip->period_year, $payslip->period_month, 1)->format('M-y') }}</p>
                </td>
            </tr>
        </table>

        <div class="garis-tebal"></div>

        <table style="margin-bottom: 10px;">
            <tr>
                <td style="width: 15%;">NIP</td>
                <td style="width: 35%;">: {{ $payslip->user->nik ?? '-' }}</td>
                <td style="width: 15%;">Jabatan</td>
                <td style="width: 35%;">: {{ $payslip->user->position->name ?? $payslip->user->profile->jabatan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama Karyawan</td>
                <td>: {{ strtoupper($payslip->user->name) }}</td>
                <td>NPWP</td>
                <td>: {{ $payslip->user->profile->npwp ?? '-' }}</td>
            </tr>
        </table>

        <table style="margin-bottom: 10px;">
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
                <td colspan="3">POTONGAN</td>
            </tr>

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
                <td>FEE MARKETING</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->fee_marketing, 2, ',', '.') }}</td>
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
                <td>TUNJANGAN PENEMPATAN/PO</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->tunjangan_penempatan, 2, ',', '.') }}</td>
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

            <tr class="bg-gray border-top-thick border-bottom-double font-bold">
                <td>JUMLAH PENDAPATAN</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->total_pendapatan, 0, ',', '.') }}</td>
                <td style="background-color: #fff;"></td>
                <td>JUMLAH POTONGAN</td>
                <td>Rp</td>
                <td class="text-right">{{ number_format($payslip->total_potongan, 0, ',', '.') }}</td>
            </tr>
        </table>

        <table>
            <tr>
                <td style="width: 60%; padding-top: 5px;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="font-bold" style="width: 30%;">ket/sisa utang &nbsp; &nbsp;:</td>
                            <td class="bg-cyan font-bold" style="width: 5%; border: 1px solid #000; border-right: none; padding: 2px 4px;">Rp</td>
                            <td class="bg-cyan text-right font-bold" style="width: 40%; border: 1px solid #000; border-left: none; padding: 2px 4px;">{{ number_format($payslip->sisa_utang, 2, ',', '.') }}</td>
                            <td style="width: 25%;"></td>
                        </tr>

                        <tr>
                            <td colspan="4" style="height: 10px;"></td>
                        </tr>

                        <tr>
                            <td class="font-bold">GAJI BERSIH &nbsp; &nbsp; &nbsp;:</td>
                            <td class="bg-yellow font-bold" style="border: 1px solid #000; border-right: none; padding: 2px 4px;">Rp</td>
                            <td class="bg-yellow text-right font-bold" style="border: 1px solid #000; border-left: none; padding: 2px 4px;">{{ number_format($payslip->gaji_bersih, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="3" class="font-bold font-italic" style="padding-top: 3px;">
                                {{ App\Helpers\TerbilangHelper::convert($payslip->gaji_bersih) }} Rupiah
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="width: 40%; text-align: center; vertical-align: bottom;">
                    <p>HR-DEPARTMENT</p>
                    <div style="width: 130px; height: 70px; border: 1px dashed #999; margin: 5px auto; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999;">
                        STEMPEL<br>&<br>TTD DI SINI
                    </div>
                    <p class="font-bold" style="text-decoration: underline;">RIDA CHOLIDHATUS S</p>
                </td>
            </tr>
        </table>

    </div>

</body>

</html>