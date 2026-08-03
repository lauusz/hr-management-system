# Halaman Persiapan Kebutuhan Operasional

## Tujuan

Menyiapkan halaman awal Kebutuhan Operasional yang dapat dibuka dari kartu layanan pada `/v2/access`.

## Desain

- URL pengguna: `/v2/ops`.
- Nama route internal: `v2.ops.index`.
- Halaman hanya dapat dibuka oleh pengguna yang sudah login, mengikuti grup route V2 yang ada.
- Kartu “Kebutuhan Operasional” di `/v2/access` menjadi tautan menuju `/v2/ops` dan tetap berbadge “Testing”.
- Halaman memakai layout aplikasi yang sudah ada dan menampilkan:
  - judul “Kebutuhan Operasional”;
  - pesan “Halaman sedang disiapkan”;
  - tombol “Kembali” menuju `/v2/access`.

## Implementasi Minimal

- Tambahkan satu route closure pada grup `/v2` yang mengembalikan view placeholder.
- Tambahkan satu Blade view untuk halaman placeholder.
- Ubah kartu OPS dari elemen nonaktif menjadi tautan.
- Tambahkan feature test untuk akses halaman dan tujuan tautan kartu.

## Di Luar Cakupan

Belum membuat controller, database, model, permission OPS, sidebar khusus, atau fitur pengajuan dan penyimpanan.

## Kriteria Selesai

- Pengguna yang sudah login dapat mengeklik kartu OPS dan membuka `/v2/ops`.
- Pengguna yang belum login diarahkan ke halaman login.
- Halaman menampilkan konten placeholder dan tombol kembali ke `/v2/access`.
