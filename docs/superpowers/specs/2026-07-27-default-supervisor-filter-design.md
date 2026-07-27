# Default Filter Supervisor

## Tujuan

Halaman `/hr/supervisors` secara default menampilkan Supervisor aktif karena role tersebut paling sering dibutuhkan.

## Perilaku

- Jika query `roles` tidak dikirim, role terpilih hanya `SUPERVISOR`.
- Checkbox Supervisor aktif secara default; checkbox Manager tidak aktif.
- Jika Manager dicentang, Manager aktif ikut ditampilkan.
- Pilihan Manager saja dan Supervisor bersama Manager tetap didukung.
- Tombol reset kembali ke halaman tanpa query sehingga menghasilkan default Supervisor-only.
- Pencarian nama, pagination, analytics, aksi, dan filter karyawan aktif tidak berubah.

## Data dan pengujian

- Tidak ada perubahan database.
- Test memastikan request tanpa filter hanya memuat Supervisor aktif.
- Test filter role yang sudah ada tetap membuktikan Manager dapat ditampilkan saat dipilih.
- Seluruh test pengelolaan supervisor dan kompilasi Blade harus lulus.
