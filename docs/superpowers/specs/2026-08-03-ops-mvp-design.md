# Desain MVP Kebutuhan Operasional

## Tujuan

Membangun modul Kebutuhan Operasional yang sederhana, mobile-first, dan mengikuti alur Kebutuhan Kantor (ATK) tanpa mengubah tampilan maupun alur ATK pada tahap MVP.

Modul tersedia di `/v2/ops`. Pengguna hanya melihat kata dan tindakan yang penting. Struktur data memakai tabel ATK yang sudah ada dengan penanda sumber `ATK` atau `OPS`.

## Batas MVP

### Pengguna OPS

- Katalog
- Keranjang
- Pengajuan Saya
- Detail pengajuan

### Admin OPS

- Request Masuk
- Detail dan proses request
- Master Barang OPS
- Akses

### Tidak Termasuk MVP

- Dashboard admin OPS
- Kategori OPS
- Foto barang
- Harga barang
- Pengajuan barang baru di luar katalog
- Rekap pemakaian
- Halaman riwayat stok OPS
- Penggabungan request OPS ke halaman admin ATK
- Penggabungan riwayat stok OPS ke halaman ATK
- Pemulihan barang yang sudah dihapus
- Perubahan tampilan atau alur modul ATK

Integrasi ke tampilan ATK dilakukan pada tahap terpisah setelah MVP OPS selesai.

## Hak Akses

Tabel `user_access_roles` yang sudah ada tetap menjadi sumber akses tambahan. Tidak diperlukan tabel akses baru.

Role tambahan:

- `OPS`: boleh melihat card Kebutuhan Operasional dan memakai halaman pengguna OPS.
- `ADMIN OPS`: otomatis memiliki akses pengguna OPS dan seluruh halaman admin OPS.
- `ADMIN ATK`: menjadi master dan otomatis memiliki seluruh akses pengguna serta admin OPS tanpa perlu role OPS tambahan.

Aturan keamanan:

- Card OPS di `/v2/access` hanya muncul untuk `OPS`, `ADMIN OPS`, atau `ADMIN ATK`.
- Pengguna tanpa akses menerima respons 403 ketika membuka URL OPS secara manual.
- Route pengguna dan route admin memakai middleware berbeda.
- Admin OPS awal dimasukkan manual ke `user_access_roles` melalui database.
- Setelah tersedia, halaman Akses dapat memberikan atau mencabut role `OPS` dan `ADMIN OPS`.
- Admin tidak boleh mencabut akses admin miliknya sendiri.

## Struktur Data Bersama

Data OPS memakai tabel ATK existing agar alur request, stok, dan integrasi tahap berikutnya tetap berada dalam satu program.

Perubahan schema yang diperlukan:

- `atk_items.module`: nilai `ATK` atau `OPS`, default `ATK`, dan memiliki index.
- `atk_requests.module`: nilai `ATK` atau `OPS`, default `ATK`, dan memiliki index.
- `atk_items.deleted_at`: waktu soft delete.
- `atk_items.deleted_by`: pengguna yang menghapus.
- `atk_items.deletion_note`: alasan penghapusan.

Tabel berikut tidak memerlukan kolom modul:

- `atk_request_items`, karena sumber mengikuti request dan barang terkait.
- `atk_stock_movements`, karena sumber mengikuti barang terkait.

Semua query OPS wajib membatasi data dengan `module = OPS`. Nilai default `ATK` mempertahankan data existing sebagai data ATK.

Perubahan schema diberikan sebagai file SQL MySQL terpisah. Tidak dibuat Laravel migration dan SQL tidak dijalankan oleh aplikasi atau Codex. Pengguna menjalankan SQL secara manual setelah meninjaunya.

## Barang dan Stok

Field awal barang OPS:

- Nama barang
- Satuan
- Stok awal
- Keterangan opsional

Form tambah barang sengaja dibuat minimal. Field dan susunannya akan disesuaikan setelah alur inti MVP selesai.

Admin dapat:

- Menambah barang
- Mengubah barang
- Menambah stok
- Mengurangi stok
- Menghapus barang

Stok tidak boleh kurang dari nol. Setiap penambahan dan pengurangan stok dicatat pada `atk_stock_movements`.

Delete selalu berupa soft delete:

- Baris database tidak dihapus.
- Alasan hapus wajib diisi.
- Waktu dan pengguna yang menghapus dicatat.
- Barang terhapus tidak muncul pada katalog, keranjang baru, atau daftar barang aktif.
- Pengajuan dan riwayat lama tetap dapat membaca barang tersebut.
- Barang terhapus yang masih berada dalam session keranjang ditolak saat keranjang diperbarui atau diajukan.

## Keranjang dan Pengajuan

- Session keranjang OPS terpisah dari keranjang ATK.
- Keranjang tidak dapat mencampur barang ATK dan OPS.
- Pengguna dapat menambah, mengubah jumlah, dan menghapus barang dari keranjang.
- Jumlah harus lebih dari nol dan tidak boleh melebihi stok tersedia.
- Pengajuan memakai nomor dengan awalan `OPS`.
- Request OPS menyimpan `module = OPS`.
- Pengguna hanya dapat melihat pengajuannya sendiri.
- Status mengikuti alur ATK existing: menunggu, disetujui, disetujui sebagian, atau ditolak.

## Proses Admin

- Admin melihat hanya request dengan `module = OPS` pada panel OPS.
- Admin dapat menyetujui atau menolak setiap barang pada request.
- Alasan wajib ketika menolak barang.
- Request hanya dapat diselesaikan setelah seluruh barang ditinjau.
- Stok berkurang hanya untuk barang yang disetujui ketika request diselesaikan.
- Request yang sudah selesai tidak dapat diproses kembali.
- `ADMIN OPS` dan `ADMIN ATK` memakai alur admin OPS yang sama pada tahap MVP.

## Halaman Akses

Halaman Akses dapat dibuka oleh `ADMIN OPS` dan `ADMIN ATK`.

- Pencarian pengguna berdasarkan nama, username, email, atau PT.
- Admin dapat memberikan akses pengguna OPS.
- Admin dapat memberikan akses Admin OPS.
- Admin dapat mencabut masing-masing akses.
- `ADMIN OPS` selalu dianggap memiliki akses pengguna OPS.
- Tampilan menggunakan label sederhana: “Pengguna OPS”, “Admin OPS”, “Beri Akses”, dan “Cabut”.

## Desain Mobile-First

Layout mengikuti pola ATK existing dengan identitas OPS:

- Hijau utama: `#0F766E`.
- Hijau gelap: `#115E59`.
- Hijau muda: `#E8F8F3`.
- Border hijau: `#BFE8DD`.
- Nama aplikasi: “Kebutuhan Operasional”.
- Keterangan singkat: “Kebutuhan operasional internal”.

Navigasi mobile memakai topbar, tombol hamburger, shortcut keranjang, dan drawer menu. Target sentuh minimal 44 piksel. Daftar utama memakai card satu kolom; tabel lebar tidak ditampilkan pada mobile. Desktop memakai konten yang sama dan hanya memperluas card menjadi beberapa kolom.

Kata tindakan yang digunakan:

- Tambah
- Ubah
- Hapus
- Tambah Stok
- Kurangi Stok
- Ajukan
- Setujui
- Tolak
- Selesaikan
- Kembali

Istilah teknis berbahasa Inggris tidak ditampilkan kepada pengguna.

## Penanganan Kesalahan

- Akses tanpa izin menghasilkan 403.
- Stok tidak cukup menampilkan pesan singkat dan tidak mengubah data.
- Input tidak valid kembali ke form dengan satu pesan yang jelas.
- Pemrosesan request dan perubahan stok dilakukan dalam transaksi database.
- Pengecekan stok diulang ketika request diselesaikan untuk mencegah stok negatif.

## Pengujian

Feature test mencakup:

- Card OPS hanya terlihat oleh pengguna berakses.
- Route pengguna dan admin menolak pengguna tanpa akses.
- Admin ATK dapat membuka seluruh halaman OPS.
- Katalog hanya menampilkan barang OPS aktif dan belum dihapus.
- Keranjang OPS terpisah dari ATK.
- Pengguna hanya melihat pengajuannya sendiri.
- Approval mengurangi stok sekali saja.
- Stok tidak dapat menjadi negatif.
- Soft delete menyimpan alasan, waktu, dan pelaku tanpa menghapus baris.
- Halaman utama tetap responsif dan memakai struktur card pada mobile.

## Kriteria Selesai

- Pengguna terpilih dapat menjalankan alur katalog sampai melihat hasil pengajuan.
- Admin OPS dan Admin ATK dapat mengelola barang serta menyelesaikan request OPS.
- Admin dapat mengelola akses OPS.
- Pengguna tanpa akses tidak melihat card dan tidak dapat membuka modul.
- Seluruh halaman OPS memakai tema hijau dan bahasa sederhana.
- Modul ATK existing tidak berubah secara tampilan maupun alur pada tahap MVP.
