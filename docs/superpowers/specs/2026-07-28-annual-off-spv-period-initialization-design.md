# Annual OFF SPV Period Initialization Design

## Tujuan

Membuat seluruh periode OFF SPV Januari–Desember secara otomatis satu kali per tahun, dengan aturan cutoff yang sama untuk jatah default dan pengajuan `leave-request`.

## Sumber Perhitungan

`OffSpvQuotaService::periodForDate()` tetap menjadi satu-satunya sumber perhitungan:

- Label bulan ditentukan dari cutoff tanggal 26–25.
- Jumlah hari Sabtu dihitung dari kalender periode.
- Jatah dasar adalah jumlah Sabtu dikurangi 2.
- Jatah minimum adalah 0.

Tidak ada angka jatah bulanan yang di-hardcode.

## Inisialisasi Tahunan

Service menyediakan proses inisialisasi berdasarkan tahun periode:

- Membuat Januari–Desember untuk seluruh Supervisor aktif.
- Dapat dibatasi ke satu Supervisor ketika terjadi perubahan jabatan.
- Hanya membuat periode yang belum ada.
- Tidak menimpa `effective_quota` atau perubahan manual HR.
- Aman dijalankan ulang.

Command menerima tahun opsional:

```bash
php artisan off-spv:initialize-periods 2026
```

Jika tahun tidak diberikan, sistem memakai tahun label periode aktif berdasarkan cutoff.

## Otomatisasi

- Scheduler menjalankan command setiap 1 Januari pukul 00:05 zona waktu Asia/Jakarta.
- Jadwal bulanan tanggal 26 dihapus.
- Supervisor baru memperoleh periode Januari–Desember pada tahun label aktif saat perannya diubah menjadi Supervisor.
- Pembuatan periode aktif secara lazy tetap dipertahankan sebagai fallback jika scheduler gagal.

Khusus rollout 2026, command dijalankan satu kali setelah deployment untuk membuat Januari–Desember bagi seluruh Supervisor aktif yang sudah ada.

## Integrasi Leave Request

Saat Supervisor mengajukan `OFF_SPV`:

1. Tanggal harus hari Sabtu.
2. Tanggal dipetakan ke periode cutoff melalui perhitungan yang sama.
3. Pengajuan dihubungkan melalui `leave_requests.off_spv_period_id`.
4. Ketersediaan diperiksa terhadap `effective_quota` periode tersebut.
5. Pemakaian dan sisa hanya berlaku untuk periode itu; tidak ada carry-over.

## Database dan Audit

- Tidak memerlukan tabel atau kolom baru.
- Periode tersimpan di `off_spv_periods`.
- Inisialisasi dicatat di `off_spv_changes`.
- Data lama dan perubahan manual tidak dihapus atau ditimpa.

## Verifikasi

- Dua belas periode terbentuk dengan cutoff dan jatah yang benar.
- Menjalankan command dua kali tidak membuat duplikat.
- Perubahan manual tetap sama setelah command dijalankan ulang.
- Supervisor baru memperoleh seluruh periode tahun label aktif.
- Pengajuan OFF SPV tetap terhubung ke periode yang sesuai.
