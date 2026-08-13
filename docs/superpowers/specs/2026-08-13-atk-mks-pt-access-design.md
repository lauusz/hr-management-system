# Desain Akses ATK MKS Berdasarkan PT

## Tujuan

Mengubah akses pengguna modul ATK MKS dari basis divisi menjadi basis PT agar pengaturan Cabang Makassar dapat mengikuti pembagian perusahaan. Perubahan ini hanya berlaku untuk ATK MKS. Akses OPS tetap berdasarkan divisi.

## Aturan Akses

- Admin memilih satu atau beberapa PT di `/v2/atk-mks/admin/access`.
- Semua pengguna aktif yang memiliki `employee_profiles.pt_id` sesuai PT terpilih dapat melihat card `Stok ATK MKS` di `/v2/access` dan membuka bagian pengguna `/v2/atk-mks`.
- Pengguna tanpa employee profile, tanpa PT, atau berada pada PT yang tidak dipilih tidak mendapat akses pengguna ATK MKS.
- Admin ATK MKS tetap diberikan dan dicabut per pengguna melalui role akses `ADMIN ATK MKS`.
- Admin ATK tetap menjadi master dan selalu dapat mengakses serta mengelola ATK MKS.
- Status aktif hanya digunakan untuk jumlah pengguna yang ditampilkan pada pilihan PT. Pemeriksaan akses tetap mensyaratkan akun dapat melewati autentikasi aplikasi yang berlaku.

## Penyimpanan Database

Buat tabel baru `atk_mks_access_pts`:

- `id`: primary key.
- `pt_id`: PT yang diberi akses, unik, foreign key ke `pts.id`, cascade saat PT dihapus.
- `created_by`: pengguna yang menambahkan pilihan, nullable, foreign key ke `users.id`, menjadi null jika pengguna dihapus.
- `created_at` dan `updated_at`.

Tabel lama `atk_mks_access_divisions` tidak dihapus dan tidak dimodifikasi, tetapi tidak lagi dibaca oleh kode ATK MKS. Pendekatan ini menghindari salah interpretasi ID divisi sebagai ID PT dan menjaga perubahan database tetap aman.

SQL disediakan sebagai file incremental baru agar dapat dijalankan manual pada server yang mungkin sudah pernah menjalankan SQL awal ATK MKS. Aplikasi tidak menjalankan migration atau perubahan database otomatis.

## Perubahan Aplikasi

### Model dan otorisasi

- Tambahkan model `AtkMksAccessPt` untuk tabel `atk_mks_access_pts`.
- Ubah `User::canAccessAtkMks()` agar memeriksa `employee_profiles.pt_id` terhadap tabel akses PT.
- `User::canManageAtkMks()` tidak berubah: Admin ATK atau role `ADMIN ATK MKS`.

### Controller dan route

- Halaman akses mengambil daftar PT beserta jumlah pengguna aktif melalui employee profile.
- Pilihan PT disimpan secara bulk dalam transaksi database.
- Endpoint penyimpanan menjadi `POST /v2/atk-mks/admin/access/pts` dengan nama route `v2.atk-mks.admin.access.pts.sync`.
- Endpoint lama berbasis divisi tidak digunakan oleh ATK MKS.

### Antarmuka

- Judul penjelas menjadi `Pilih PT pengguna dan tentukan Admin ATK MKS.`
- Bagian Akses Pengguna menampilkan checkbox PT dan jumlah pengguna aktif.
- Pesan kosong menggunakan `PT belum tersedia.`
- Pengelolaan Admin ATK MKS per pengguna tetap seperti sekarang.
- Warna amber/emas ATK MKS tidak berubah.

## Alur Data

1. Admin ATK MKS atau Admin ATK membuka halaman akses.
2. Server mengambil seluruh PT dan menghitung pengguna aktif yang profilnya terkait dengan masing-masing PT.
3. Admin mencentang PT dan menyimpan.
4. Server menyinkronkan isi `atk_mks_access_pts` dalam transaksi.
5. Saat pengguna membuka `/v2/access` atau `/v2/atk-mks`, sistem membaca PT dari employee profile pengguna dan mengecek apakah PT tersebut terpilih.

## Penanganan Kondisi Khusus

- Kiriman `pt_ids` harus berupa array ID unik yang benar-benar ada di tabel `pts`.
- Jika tidak ada PT yang dicentang, seluruh akses pengguna berbasis PT ATK MKS dikosongkan. Hak Admin ATK dan Admin ATK MKS tetap berlaku.
- Data PT dari modul lain dan konfigurasi akses OPS tidak diubah.
- Tabel divisi ATK MKS lama tidak menjadi fallback agar aturan akses tidak bercampur.

## Pengujian

- Pengguna aktif pada PT terpilih dapat melihat dan membuka ATK MKS.
- Pengguna pada PT yang tidak dipilih ditolak.
- Pengguna tanpa profile atau tanpa PT ditolak.
- Admin ATK MKS dan Admin ATK tetap memiliki akses penuh tanpa bergantung pada PT.
- Sinkronisasi checkbox menambah dan mencabut akses PT dengan benar.
- Halaman menampilkan nama PT, jumlah pengguna aktif, dan field `pt_ids[]`.
- Akses OPS berbasis divisi tetap lulus regression test.
- SQL hanya membuat tabel baru dan tidak berisi perintah penghapusan atau perubahan data lama.

## Deploy

1. Backup database server.
2. Jalankan file SQL incremental untuk membuat `atk_mks_access_pts`.
3. Upload perubahan `app`, `resources`, dan `routes`.
4. Bersihkan cache aplikasi jika diperlukan.
5. Pilih PT yang diizinkan melalui `/v2/atk-mks/admin/access`.

SQL harus dijalankan sebelum kode baru digunakan. Jika kode diunggah lebih dulu, pemeriksaan akses ATK MKS akan gagal karena tabel `atk_mks_access_pts` belum tersedia.
