# Indikator Kelebihan OFF SPV

## Tujuan

Menampilkan anomali pemakaian OFF SPV yang melebihi jatah efektif pada tabel detail supervisor tanpa mengubah struktur database.

## Aturan

- `kelebihan = max(0, approved - effective_quota)`.
- Hanya pengajuan berstatus `APPROVED` yang dihitung sebagai pemakaian; `pending` tidak dihitung sebagai kelebihan.
- Status pada kolom ditampilkan dengan prioritas:
  1. `N kelebihan` jika kelebihan lebih dari nol.
  2. `N hangus` jika periode sudah lewat dan tidak ada kelebihan.
  3. `N tersisa` jika periode belum lewat dan tidak ada kelebihan.
- Nilai tidak disimpan sebagai kolom baru karena selalu dapat dihitung dari `off_spv_periods.effective_quota` dan `leave_requests`.

## Implementasi

- Tambahkan nilai `excess` pada hasil analytics `OffSpvQuotaService::stats()`.
- Ubah judul kolom menjadi `Sisa/Hangus/Kelebihan`.
- Tambahkan badge merah untuk kondisi kelebihan pada halaman detail supervisor.
- Pertahankan perhitungan sisa, hangus, dan aturan input historis yang sudah ada.

## Pengujian

- Test service membuktikan `excess` hanya berasal dari pemakaian approved yang melampaui jatah.
- Test halaman membuktikan badge `N kelebihan` tampil dan mengalahkan badge hangus/tersisa.
- Seluruh test OFF SPV dan kompilasi Blade harus lulus.
