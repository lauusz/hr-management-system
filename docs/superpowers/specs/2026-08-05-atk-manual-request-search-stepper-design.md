# Desain Pencarian dan Stepper Admin ATK

## Tujuan

Menyederhanakan `/v2/atk/admin/requests/manual/create` agar admin dapat mencari lalu memilih nama pengguna, mencari barang, dan mengatur jumlah dengan stepper seperti katalog. Pola stepper yang sama juga diterapkan pada jumlah `Tambah Stok` di `/v2/atk/admin/items`.

## Ruang Lingkup

- Mengganti dropdown pengguna dengan pencarian nama dan hasil yang dapat diklik.
- Menambahkan pencarian barang langsung tanpa tombol `Cari`.
- Mengganti input jumlah biasa dengan stepper `− / jumlah / +`.
- Mengganti input jumlah `Tambah Stok` di Master Barang dengan stepper.
- Mempertahankan catatan, tombol `Buat dan Review`, validasi hak akses, serta alur penyimpanan pengajuan yang sudah ada.
- Mempertahankan input harga/unit, tombol `Tambah`, dan tombol `Edit` di Master Barang.
- Tidak mengubah database, route, atau struktur tabel.

## Pencarian Nama

Kolom `Cari nama` menggantikan dropdown pengguna secara visual. Daftar hasil memakai data pengguna aktif yang sudah dikirim controller ke halaman.

- Hasil menampilkan nama dan PT jika tersedia.
- Admin memilih pengguna dengan mengklik satu hasil.
- Pilihan disimpan dalam input tersembunyi `user_id` agar kontrak form tetap sama.
- Setelah dipilih, tampilkan nama dan PT dalam panel `Nama terpilih` beserta tombol `Ganti`.
- Tombol `Ganti` menghapus pilihan dan mengaktifkan kembali kolom pencarian.
- Jika tidak ada hasil, tampilkan `Nama tidak ditemukan.`
- Form tidak dapat dikirim tanpa pengguna terpilih; validasi server `user_id` tetap menjadi pemeriksaan utama.

Pencarian berjalan di browser terhadap daftar yang sudah dimuat. Tidak membuat endpoint baru karena controller saat ini memang memuat seluruh pengguna aktif dan pencarian lokal menjaga pilihan barang tetap utuh.

## Pencarian Barang

Kolom `Cari barang ATK...` berada tepat sebelum daftar barang dan tidak memiliki tombol pencarian.

- Daftar difilter langsung saat admin mengetik.
- Pencarian tidak memuat ulang halaman dan tidak menghapus jumlah yang telah dipilih.
- Jika tidak ada hasil, tampilkan `Barang tidak ditemukan.`
- Menghapus isi pencarian menampilkan kembali seluruh barang.

## Stepper Jumlah

Setiap barang memakai tampilan yang sama dengan stepper katalog:

- tombol minus;
- input angka di tengah;
- tombol plus.

Aturan jumlah:

- Nilai awal `0`, yang berarti barang belum dipilih.
- Nilai minimum `0` dan maksimum mengikuti stok barang.
- Tombol minus nonaktif pada nilai `0`.
- Tombol plus nonaktif ketika nilai mencapai stok.
- Input manual tetap diperbolehkan dan dinormalisasi ke rentang `0` sampai stok.
- Jumlah `0` dikirim sebagai bagian form tetapi diabaikan oleh proses pembuatan pengajuan.
- Minimal satu barang harus memiliki jumlah lebih dari `0`.

## Layout Responsif

### Mobile

- Pencarian nama, hasil nama, nama terpilih, catatan, dan pencarian barang memakai lebar penuh.
- Informasi barang berada di atas stepper jika ruang horizontal tidak cukup.
- Tombol `Buat dan Review` memakai lebar penuh.
- Seluruh tombol memiliki tinggi sentuh minimum 44px.

### Desktop

- Informasi barang dan stepper berada dalam satu baris.
- Hasil pencarian nama dibatasi tinggi dan dapat digulir agar form tidak terlalu panjang.
- Lebar form tetap mengikuti konten modul ATK.

## Stepper Tambah Stok di Master Barang

Pada `/v2/atk/admin/items`, hanya input jumlah stok masuk yang diubah menjadi stepper. Input harga/unit tetap berupa input angka biasa.

- Stepper menampilkan tombol minus, angka, dan tombol plus.
- Nilai awal dan minimum tetap `1` agar kontrak penambahan stok tidak berubah.
- Tombol minus nonaktif pada nilai `1`.
- Tombol plus menambah satu tanpa batas maksimum baru karena stok masuk tidak dibatasi stok saat ini.
- Input angka manual tetap diperbolehkan dan nilai di bawah `1` dinormalisasi menjadi `1`.
- Pada mobile, stepper memakai lebar yang nyaman; input harga dan tombol `Tambah` tetap berada dalam form stok barang yang sama.
- Form tetap mengirim `movement_type=IN`, `qty`, dan `unit_price` ke route yang sudah ada.

## Aksesibilitas

- Input pencarian memiliki label yang terlihat.
- Hasil nama memakai tombol asli agar dapat dipilih dengan keyboard.
- Nama pengguna yang dipilih diumumkan melalui teks yang terlihat, bukan warna saja.
- Tombol stepper memiliki `aria-label` yang menyebut nama barang.
- Status nonaktif stepper memakai atribut `disabled` dan `aria-disabled`.
- Stepper stok masuk juga memakai label tombol yang menyebut nama barang.
- Pesan tidak ditemukan tetap berupa teks yang dapat dibaca pembaca layar.

## Penanganan Error

- Error `user_id` tampil dekat bagian pencarian nama.
- Error `quantities` tampil dekat judul daftar barang.
- Nilai lama pengguna dan jumlah barang dipulihkan setelah validasi gagal.
- JavaScript hanya membantu interaksi; validasi server tetap menentukan pengguna aktif dan jumlah yang sah.

## Pengujian

- Hanya admin ATK dapat membuka form.
- Form merender pencarian nama, hasil pengguna, pencarian barang, dan stepper.
- Memilih pengguna mengisi `user_id`.
- Pencarian nama dan barang memfilter hasil tanpa submit atau tombol pencarian.
- Tombol stepper memakai batas `0` sampai stok.
- Jumlah `0` tidak membuat item request dan jumlah positif tetap dibuat.
- Pengajuan tanpa jumlah positif tetap ditolak.
- Master Barang merender stepper pada jumlah stok masuk tanpa mengubah input harga/unit.
- Stepper stok masuk tidak dapat diturunkan di bawah `1`.
