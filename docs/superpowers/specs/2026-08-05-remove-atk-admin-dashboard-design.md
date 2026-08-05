# Desain Penghapusan Dashboard Admin ATK

## Tujuan

Menghapus dashboard `/v2/atk/admin` karena fungsi analisis sudah tersedia pada halaman Rekap PT. Master Barang menjadi tujuan utama dan menu pertama pada bagian Admin ATK.

## Perilaku URL

- `/v2/atk/admin` tetap hanya dapat diakses pengguna dengan hak Admin ATK.
- Admin ATK yang membuka URL tersebut diarahkan ke `/v2/atk/admin/items`.
- Pengguna tanpa hak Admin ATK tetap menerima respons `403` dari middleware sebelum redirect dijalankan.
- Nama route `v2.atk.admin.dashboard` dipertahankan sebagai redirect agar bookmark dan referensi lama tidak rusak.

## Sidebar

Menu `Dashboard Admin` dihapus. Urutan bagian Admin ATK menjadi:

1. Master Barang
2. Request Masuk
3. Pengajuan Barang
4. Riwayat Stok
5. Rekap PT
6. Akses

Ikon dashboard yang tidak lagi digunakan oleh komponen ATK dihapus dari SVG sprite.

## Penghapusan Kode

- Hapus `app/Http/Controllers/Atk/Admin/DashboardController.php`.
- Hapus `resources/views/atk/admin/dashboard.blade.php`.
- Hapus import controller dashboard dari `routes/web.php`.
- Ganti handler route root admin dengan redirect ke route Master Barang.
- Hapus test ringkasan dashboard dan ganti dengan test otorisasi serta redirect.

## Batasan

- Tidak mengubah database, migrasi, data, atau permission Admin ATK.
- Tidak mengubah halaman Master Barang maupun Rekap PT.
- Tidak menambahkan controller atau dependency baru.

## Pengujian

- Pengguna biasa menerima `403` ketika membuka `/v2/atk/admin`.
- Admin ATK menerima redirect menuju `/v2/atk/admin/items`.
- Sidebar tidak menampilkan `Dashboard Admin`.
- `Master Barang` muncul sebelum `Request Masuk` pada sidebar admin.
