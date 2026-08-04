# Riwayat Stok Gabungan ATK dan OPS

## Tujuan

Admin ATK dapat melihat seluruh pergerakan stok barang ATK dan OPS dari halaman riwayat stok ATK. Admin OPS tetap hanya melihat pergerakan stok OPS.

## Perilaku

- Riwayat ATK memuat mutasi barang dengan modul `ATK` dan `OPS`.
- Semua mutasi diurutkan berdasarkan waktu terbaru secara global. Data tidak dikelompokkan berdasarkan modul.
- Setiap baris desktop dan kartu mobile menampilkan label modul `ATK` atau `OPS`.
- Filter modul menyediakan pilihan `Semua`, `ATK`, dan `OPS`.
- Filter barang tetap tersedia dan memuat barang ATK serta OPS.
- Filter tipe mutasi tetap seperti saat ini.
- Riwayat OPS tidak berubah dan tetap dibatasi ke modul `OPS`.

## Implementasi

Controller riwayat ATK tidak lagi membatasi relasi barang ke satu modul. Query sumber pengajuan dan daftar barang menggunakan kedua modul. Urutan tetap memakai waktu pembuatan terbaru sehingga kejadian ATK dan OPS tampil secara natural sesuai waktu.

Tampilan riwayat ATK menambahkan filter modul dan kolom/field modul. Warna label mengikuti identitas masing-masing modul: ungu untuk ATK dan hijau untuk OPS.

## Data dan Keamanan

Tidak ada perubahan skema atau data database. Perubahan hanya pada query baca dan tampilan. Middleware admin ATK yang sudah ada tetap menjadi pembatas akses halaman.

## Pengujian

- Riwayat ATK menampilkan mutasi ATK dan OPS.
- Urutan mutasi mengikuti waktu terbaru secara global, bukan kelompok modul.
- Filter modul membatasi hasil dengan benar.
- Label modul tampil pada desktop dan mobile.
- Riwayat OPS tetap tidak menampilkan mutasi ATK.
