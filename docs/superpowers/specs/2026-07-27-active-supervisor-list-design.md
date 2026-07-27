# Daftar Supervisor dan Manager Aktif

## Tujuan

Halaman `/hr/supervisors` hanya menampilkan Supervisor dan Manager yang status karyawannya aktif.

## Perubahan

- Tambahkan scope `active()` pada query daftar di `SupervisorDataController::index()`.
- Pertahankan role Supervisor dan Manager sebagai sumber daftar.
- Pertahankan pencarian nama, filter role, pagination, analytics, dan seluruh aksi yang sudah ada.
- User nonaktif tidak ditampilkan meskipun role-nya masih Supervisor atau Manager.

## Data dan pengujian

- Tidak ada perubahan database.
- Tambahkan test halaman yang membuktikan user aktif tampil dan user nonaktif tidak tampil.
- Jalankan seluruh test pengelolaan OFF SPV dan kompilasi Blade.
