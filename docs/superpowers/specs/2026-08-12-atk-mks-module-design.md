# Desain Modul Stok ATK MKS

## Tujuan

Membuat modul `/v2/atk-mks` dengan alur yang sama persis seperti modul OPS, tetapi memiliki stok, keranjang, pengajuan, akses, dan tampilan tersendiri. Modul menggunakan warna kuning keemasan agar mudah dibedakan dari ATK ungu dan OPS hijau.

## Identitas dan Tampilan

- Nama tampilan: `Stok ATK MKS`.
- URL utama: `/v2/atk-mks`.
- Nilai internal module: `ATK_MKS`.
- Palet utama: amber gelap `#A16207`, amber gelap sekunder `#78350F`, latar lembut `#FEF3C7`, dan border `#F5D77C`.
- Struktur halaman, mobile view, sidebar, form, dan bahasa mengikuti OPS.

## Fitur Pengguna

- Katalog.
- Keranjang yang terpisah dari ATK dan OPS.
- Pengajuan Saya beserta detail.

## Fitur Admin

- Request Masuk dan detail review.
- Input pengambilan manual.
- Request Barang ke Admin ATK.
- Master Barang ATK MKS.
- Riwayat Stok.
- Akses berdasarkan divisi dan pengelolaan admin per orang.

## Hak Akses

- Pengguna umum memperoleh akses berdasarkan `atk_mks_access_divisions`.
- Admin ATK MKS diberikan melalui `user_access_roles` dengan nilai `ADMIN ATK MKS`.
- Admin ATK menjadi master: selalu dapat membuka dan mengelola ATK MKS.
- Card pada `/v2/access` hanya tampil bagi pengguna yang memiliki akses.

## Data dan Pemisahan Modul

- Gunakan tabel existing `atk_items`, `atk_requests`, `atk_request_items`, `atk_stock_movements`, dan `atk_need_requests`.
- Pisahkan data menggunakan `module = ATK_MKS` pada tabel yang memiliki discriminator module.
- Keranjang menggunakan session key khusus dan tidak berbagi isi dengan ATK atau OPS.
- Riwayat stok mengikuti barang sehingga tetap terpisah berdasarkan module barang.
- Request Barang ATK MKS dibuat oleh Admin ATK MKS dan hanya diproses Admin ATK, sama seperti OPS.

## Keamanan Data

- Seluruh perubahan stok dijalankan dalam transaksi dan menggunakan row lock yang sama dengan OPS.
- Barang dihapus secara soft delete dengan alasan; data riwayat tidak dihapus.
- Controller dan route selalu memeriksa module untuk mencegah akses silang ATK, OPS, dan ATK MKS.
- Tidak menambah dependency baru.

## Verifikasi

- Tes akses card dan middleware.
- Tes pemisahan katalog, keranjang, request, dan master barang.
- Tes Admin ATK MKS serta hak master Admin ATK.
- Tes stok, soft delete, dan riwayat.
- Tes Request Barang hanya dapat diproses Admin ATK.
- Regresi ATK dan OPS tetap dijalankan.
