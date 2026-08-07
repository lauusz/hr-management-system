# ATK Report Hide Request Number Design

## Tujuan

Menyederhanakan tabel **Lihat Riwayat Pengambilan** pada `/v2/atk/admin/reports` dengan menghapus kolom **No. Request**.

## Desain

- Tabel halaman menampilkan lima kolom: Tanggal, Nama Pengambil, PT, Barang, dan Qty.
- `request_number` tidak lagi diambil oleh query detail halaman karena tidak digunakan pada tampilan.
- Empty state menggunakan `colspan="5"`.
- Nomor request tetap tersimpan di database dan tetap ditampilkan pada export Excel.

## Verifikasi

- Halaman laporan tidak menampilkan `No. Request` atau nomor request pada tabel riwayat.
- Export Excel tetap menampilkan nomor request.
- Pengujian laporan ATK tetap lulus.

## Di Luar Scope

- Tidak mengubah database, proses pengajuan, tabel lain, atau format export Excel.

