<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - PT Express Lintas Indonesia</title>
    <style>
        @page {
            margin: 15px 20px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            /* Dikecilkan lagi agar muat di A5 1 halaman */
            color: #000;
            background-color: #ffffff;
            /* Ubah jadi putih murni */
            padding: 0;
            margin: 0;
        }

        .container {
            width: 100%;
            /* Ubah dari 800px menjadi 100% agar menempel pas di ujung margin kertas PDF */
            background-color: #ffffff;
            padding: 0;
            margin: 0;
            box-shadow: none;
            /* Hilangkan shadow karena ini kertas PDF */
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
            padding: 1px 2px;
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
            margin: 5px 0;
            height: 2px;
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

        .box-ttd {
            width: 110px;
            height: 60px;
            border: 1px dashed #999;
            margin: 2px auto;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>

<body>

    <div class="container">

        <table style="margin-bottom: 5px; border:none; width: 100%;">
            <tr>
                <td style="width: 15%; padding: 0;">
                    <!-- Logo Placeholer untuk PDF -->
                    <div style="width: 80px; height: 80px; border: 1px dashed #999; text-align: center; line-height: 80px; font-size: 10px; color: #666; margin: 0 auto;">LOGO</div>
                </td>
                <td style="width: 60%; vertical-align: middle; padding: 0 10px;">
                    <h3 style="font-size: 16px; margin-bottom: 3px; font-family: sans-serif;">PT. EXPRESS LINTAS INDONESIA</h3>
                    <p style="font-family: sans-serif;">Jl. Tanjung Batu 15Q</p>
                    <p style="font-family: sans-serif;">Surabaya</p>
                    <p style="font-family: sans-serif;">Jawa Timur</p>
                </td>
                <td style="width: 25%; text-align: right; vertical-align: top; padding: 0;">
                    <h3 style="font-size: 16px; margin-bottom: 3px; font-family: sans-serif;">SLIP GAJI</h3>
                    <p class="font-bold" style="font-family: sans-serif;">{{ \Carbon\Carbon::createFromDate($payslip->period_year, $payslip->period_month, 1)->format('M-y') }}</p>
                </td>
            </tr>
        </table>

        <div class="garis-tebal"></div>

        <table style="margin-bottom: 4px; width: 100%;">
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

        <table style="margin-bottom: 4px; width: 100%;">
            <!-- colgroup terkadang tidak bekerja sempurna di DomPDF, set max min secara manual -->
            <tr class="bg-gray border-top-thick border-bottom-thick font-bold">
                <td colspan="3" style="width: 48%; padding: 5px 2px;">PENDAPATAN</td>
                <td style="background-color: #fff; width: 4%;"></td>
                <td colspan="3" style="width: 48%; padding: 5px 2px;">POTONGAN</td>
            </tr>

            <tr>
                <td style="width: 30%;">GAJI POKOK</td>
                <td style="width: 3%;">Rp</td>
                <td class="text-right" style="width: 15%;">{{ number_format($payslip->gaji_pokok, 2, ',', '.') }}</td>

                <td style="width: 4%;"></td>

                <td style="width: 30%;">BPJS.TK</td>
                <td style="width: 3%;">Rp</td>
                <td class="text-right" style="width: 15%;">{{ number_format($payslip->potongan_bpjs_tk, 2, ',', '.') }}</td>
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
                <td style="padding: 5px 2px;">JUMLAH PENDAPATAN</td>
                <td style="padding: 5px 2px;">Rp</td>
                <td class="text-right" style="padding: 5px 2px;">{{ number_format($payslip->total_pendapatan, 0, ',', '.') }}</td>
                <td style="background-color: #fff;"></td>
                <td style="padding: 5px 2px;">JUMLAH POTONGAN</td>
                <td style="padding: 5px 2px;">Rp</td>
                <td class="text-right" style="padding: 5px 2px;">{{ number_format($payslip->total_potongan, 0, ',', '.') }}</td>
            </tr>
        </table>

        <table style="width: 100%;">
            <tr>
                <td style="width: 65%; padding-top: 5px; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="font-bold" style="width: 35%;">ket/sisa utang &nbsp; &nbsp;:</td>
                            <td class="bg-cyan font-bold" style="width: 5%; border: 1px solid #000; border-right: none; padding: 2px 4px;">Rp</td>
                            <td class="bg-cyan text-right font-bold" style="width: 40%; border: 1px solid #000; border-left: none; padding: 2px 4px;">{{ number_format($payslip->sisa_utang, 2, ',', '.') }}</td>
                            <td style="width: 20%;"></td>
                        </tr>

                        <tr>
                            <td colspan="4" style="height: 5px;"></td>
                        </tr>

                        <tr>
                            <td class="font-bold">GAJI BERSIH &nbsp; &nbsp; &nbsp;:</td>
                            <td class="bg-yellow font-bold" style="border: 1px solid #000; border-right: none; padding: 2px 4px;">Rp</td>
                            <td class="bg-yellow text-right font-bold" style="border: 1px solid #000; border-left: none; padding: 2px 4px;">{{ number_format($payslip->gaji_bersih, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="3" class="font-bold font-italic" style="padding-top: 5px;">
                                {{ App\Helpers\TerbilangHelper::convert($payslip->gaji_bersih) }} Rupiah
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="width: 35%; text-align: center; vertical-align: bottom;">
                    <br>
                    <p style="margin-bottom: 5px;">HR-DEPARTMENT</p>
                    <table style="width:100%;">
                        <td align="center">
                            <div class="box-ttd">
                                <table style="width:100%;height:100%;">
                                    <tr>
                                        <td valign="middle" align="center" style="font-size:10px;color:#999;line-height:1.2;">STEMPEL<br>&<br>TTD DI SINI</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </table>
                    <p class="font-bold" style="text-decoration: underline;">RIDA CHOLIDHATUS S</p>
                </td>
            </tr>
        </table>

    </div>

</body>

</html>