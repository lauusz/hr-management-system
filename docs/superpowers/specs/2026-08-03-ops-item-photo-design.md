# Desain Foto Barang OPS

## Tujuan

Menambahkan foto opsional pada barang OPS agar pengguna dapat mengenali barang dari katalog.

## Penyimpanan dan Validasi

Gunakan kolom `atk_items.image_path` yang sudah tersedia dan layanan `App\Services\Image\ImageCompressor` yang sama dengan modul ATK. Tidak ada perubahan database.

Input foto bersifat opsional dengan batas 2 MB. Format yang diterima mengikuti ATK: JPG, JPEG, PNG, WebP, HEIC, HEIF, GIF, BMP, TIF, TIFF, dan AVIF. Foto disimpan ke disk `public` dalam folder `ops-items` setelah dikompresi. HEIC/HEIF menggunakan fallback penyimpanan asli bila kompresor tidak dapat memprosesnya.

## Form Admin

Form tambah dan edit menggunakan `multipart/form-data` serta input file berlabel `Foto barang (opsional)`. Halaman edit menampilkan foto yang sedang digunakan. Jika admin menyimpan edit tanpa foto baru, `image_path` lama tidak berubah. Perubahan foto tidak mengubah stok.

Foto lama tidak dihapus otomatis ketika diganti, mengikuti perilaku modul ATK saat ini.

## Katalog

Setiap kartu katalog menampilkan area foto dengan rasio 4:3 di atas nama barang. Jika `image_path` kosong atau gambar gagal dimuat, tampilkan pengganti sederhana bertuliskan `Tanpa foto`. Gambar memiliki teks alternatif berupa nama barang.

Tampilan tetap mobile-first dan memakai warna hijau OPS.

## Pengujian

- Admin dapat menambah barang OPS dengan foto terkompresi dan `image_path` tersimpan.
- Admin dapat mengedit data tanpa menghapus foto lama.
- Form tambah dan edit memakai encoding upload file.
- Katalog menampilkan foto barang dan fallback untuk barang tanpa foto.
