# HR Leave Request Card Grid Design

## Tujuan

Membuat daftar card pada `/hr/leave-requests` lebih ringkas di desktop tanpa menyempitkan isi card.

## Desain

- Mobile tetap satu kolom.
- Tablet dan desktop kecil mulai `1024px` tetap dua kolom.
- Desktop lebar mulai `1280px` menjadi tiga kolom.
- Isi, aksi, data, controller, route, dan database tidak berubah.

## Verifikasi

- Pastikan CSS memiliki breakpoint tiga kolom pada `1280px`.
- Jalankan pengujian fitur `HrLeaveControllerTest` untuk memastikan halaman tetap dapat dirender.

