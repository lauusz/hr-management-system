<table>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>DATA GAJI BULAN</td>
        <td></td>
        <td></td>
        <td>{{ $startMonth }} sd {{ $endMonth }} TAHUN {{ $year }}</td>
    </tr>
    <tr>
        <td></td>
        <td>JUMLAH KARYAWAN</td>
        <td></td>
        <td></td>
        <td>{{ $totalEmployees }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>NO</td>
        <td>NAMA</td>
        <td>BULAN</td>
        <td>NO INDUK PEGAWAI (NIK)</td>
        <td>TANGGAL MASUK</td>
        <td>JABATAN</td>
        <td>DIVISI</td>
        <td>PENDAPATAN</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>TOTAL PENDAPATAN</td>
        <td>POTONGAN</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>TOTAL PENGELUARAN</td>
        <td></td>
        <td>SISA UTANG</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>GAJI POKOK</td>
        <td>TUNJANGAN JABATAN</td>
        <td>TUNJANGAN MAKAN</td>
        <td>FEE MARKETING</td>
        <td>BONUS BULANAN</td>
        <td>TELEKOMUNIKASI</td>
        <td>TUNJANGAN LAINNYA</td>
        <td>TUNJANGAN PENEMPATAN</td>
        <td>TUNJANGAN ASURANSI</td>
        <td>TUNJANGAN KELANCARAN</td>
        <td>PENDAPATAN LAIN</td>
        <td>TUNJANGAN TRANSPORTASI</td>
        <td>LEMBUR</td>
        <td>THR</td>
        <td>BONUS</td>
        <td></td>
        <td>POTONGAN BPJS TK</td>
        <td>POTONGAN PPH21</td>
        <td>POTONGAN HUTANG</td>
        <td>POTONGAN BPJS KES</td>
        <td>POTONGAN TERLAMBAT</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>1</td>
        <td>2</td>
        <td>3</td>
        <td>4</td>
        <td>5</td>
        <td>6</td>
        <td>7</td>
        <td>8</td>
        <td>9</td>
        <td>10</td>
        <td>11</td>
        <td>12</td>
        <td>13</td>
        <td>14</td>
        <td>15</td>
        <td>16</td>
        <td>17</td>
        <td>18</td>
        <td>19</td>
        <td>20</td>
        <td>21</td>
        <td>22</td>
        <td>23</td>
        <td>24</td>
        <td>25</td>
        <td>26</td>
        <td>27</td>
        <td>28</td>
        <td>29</td>
        <td>30</td>
        <td>31</td>
    </tr>
    @foreach($payrollRows as $index => $row)
    @php
    $emp = $row['user'];
    $slip = $row['latest_payslip'];
    $m = $row['month_number'];
    $totalPendapatanAdjusted =
        (optional($slip)->gaji_pokok ?? 0) +
        (optional($slip)->tunjangan_jabatan ?? 0) +
        (optional($slip)->tunjangan_makan ?? 0) +
        (optional($slip)->fee_marketing ?? 0) +
        (optional($slip)->bonus_bulanan ?? 0) +
        (optional($slip)->tunjangan_telekomunikasi ?? 0) +
        (optional($slip)->tunjangan_lainnya ?? 0) +
        (optional($slip)->tunjangan_penempatan ?? 0) +
        (optional($slip)->tunjangan_asuransi ?? 0) +
        (optional($slip)->tunjangan_kelancaran ?? 0) +
        (optional($slip)->pendapatan_lain ?? 0) +
        (optional($slip)->tunjangan_transportasi ?? 0) +
        (optional($slip)->lembur ?? 0) +
        (optional($slip)->thr ?? 0) +
        (optional($slip)->bonus ?? 0);
    $totalPotongan = optional($slip)->total_potongan ?? 0;
    @endphp
    <tr>
        <td></td>
        <td>{{ $index + 1 }}</td>
        <td>{{ $emp->name }}</td>
        <td>{{ \Carbon\Carbon::create()->month((int) $m)->locale('id')->translatedFormat('F') }} {{ $year }}</td>
        <td>{{ $emp->profile->nik ?? '' }}</td>
        <td>{{ $emp->profile->tgl_bergabung ? \Carbon\Carbon::parse($emp->profile->tgl_bergabung)->format('d-m-Y') : '' }}</td>
        <td>{{ $emp->position->name ?? $emp->profile->jabatan ?? '' }}</td>
        <td>{{ $emp->division->name ?? '' }}</td>
        <td>{{ optional($slip)->gaji_pokok ?? 0 }}</td>
        <td>{{ optional($slip)->tunjangan_jabatan ?? 0 }}</td>
        <td>{{ optional($slip)->tunjangan_makan ?? 0 }}</td>
        <td>{{ optional($slip)->fee_marketing ?? 0 }}</td>
        <td>{{ optional($slip)->bonus_bulanan ?? 0 }}</td>
        <td>{{ optional($slip)->tunjangan_telekomunikasi ?? 0 }}</td>
        <td>{{ optional($slip)->tunjangan_lainnya ?? 0 }}</td>
        <td>{{ optional($slip)->tunjangan_penempatan ?? 0 }}</td>
        <td>{{ optional($slip)->tunjangan_asuransi ?? 0 }}</td>
        <td>{{ optional($slip)->tunjangan_kelancaran ?? 0 }}</td>
        <td>{{ optional($slip)->pendapatan_lain ?? 0 }}</td>
        <td>{{ optional($slip)->tunjangan_transportasi ?? 0 }}</td>
        <td>{{ optional($slip)->lembur ?? 0 }}</td>
        <td>{{ optional($slip)->thr ?? 0 }}</td>
        <td>{{ optional($slip)->bonus ?? 0 }}</td>
        <td>{{ $totalPendapatanAdjusted }}</td>
        <td>{{ optional($slip)->potongan_bpjs_tk ?? 0 }}</td>
        <td>{{ optional($slip)->potongan_pph21 ?? 0 }}</td>
        <td>{{ optional($slip)->potongan_hutang ?? 0 }}</td>
        <td>{{ optional($slip)->potongan_bpjs_kes ?? 0 }}</td>
        <td>{{ optional($slip)->potongan_terlambat ?? 0 }}</td>
        <td>{{ $totalPotongan }}</td>
        <td>{{ $totalPendapatanAdjusted - $totalPotongan }}</td>
        <td>{{ optional($slip)->sisa_utang ?? '' }}</td>
    </tr>
    @endforeach
</table>