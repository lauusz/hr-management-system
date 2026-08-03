# Desain Akses Pengguna OPS Berdasarkan Divisi

## Tujuan

Mengubah pemberian akses pengguna OPS dari pilihan per orang menjadi pilihan divisi. Admin OPS tetap ditetapkan per orang, sedangkan Admin ATK tetap memiliki akses penuh sebagai master.

## Penyimpanan

Tambahkan tabel `ops_access_divisions` dengan kolom `id`, `division_id`, `created_by`, dan timestamps. `division_id` wajib unik dan memiliki foreign key ke `divisions`. `created_by` boleh kosong dan menggunakan foreign key ke `users` dengan `ON DELETE SET NULL`.

Perubahan database diberikan sebagai SQL manual. Aplikasi tidak menjalankan migration atau menulis struktur database secara otomatis.

## Aturan Akses

Pengguna dapat membuka OPS bila memenuhi salah satu kondisi:

- memiliki `ADMIN ATK`;
- memiliki `ADMIN OPS`; atau
- `division_id` pengguna tercatat di `ops_access_divisions`.

Role individual `OPS` tidak lagi menjadi sumber akses. Data role `OPS` lama boleh tetap tersimpan sementara, tetapi tidak memengaruhi keputusan akses.

## Halaman Akses

Halaman `/v2/ops/admin/access` dibagi menjadi dua bagian:

1. **Akses Pengguna** menampilkan seluruh divisi sebagai checkbox beserta jumlah pengguna aktif. Tombol `Simpan Akses Pengguna` menyinkronkan pilihan: divisi dicentang mendapat akses dan divisi yang dilepas langsung kehilangan akses.
2. **Admin OPS** tetap menampilkan pencarian pengguna dan tombol berikan/cabut Admin OPS per orang. Admin tidak dapat mencabut akses admin dirinya sendiri.

Tidak ada tombol `Jadikan Pengguna` atau `Cabut Pengguna` pada kartu pengguna.

## Perilaku Sinkronisasi

Penyimpanan divisi dilakukan dalam transaksi. Input wajib berupa ID divisi yang masih ada. Baris divisi yang tidak dipilih dihapus dan baris baru dibuat tanpa duplikasi. Karena akses dibaca langsung dari divisi pengguna, karyawan baru atau karyawan yang berpindah divisi otomatis mengikuti pengaturan terbaru.

## Pengujian

- Pengguna dari divisi terpilih dapat melihat kartu OPS dan membuka `/v2/ops`.
- Pengguna dari divisi tidak terpilih ditolak.
- Menambah dan melepas pilihan divisi langsung mengubah akses seluruh anggota.
- Admin OPS dan Admin ATK tetap dapat membuka OPS tanpa bergantung pada divisi.
- Halaman akses menampilkan checkbox divisi dan tidak lagi menampilkan tombol akses pengguna per orang.
