# Desain Request Barang OPS oleh Admin OPS

## Tujuan

Admin OPS dapat mengajukan penambahan stok atau barang baru dari area admin OPS. Admin OPS hanya membuat dan memantau pengajuan. Hanya Admin ATK yang dapat menyelesaikan atau menolak pengajuan tersebut.

## Pendekatan

Pendekatan yang dipilih adalah memakai tabel dan alur `atk_need_requests` yang sudah ada, lalu membedakan sumber pengajuan dengan kolom `module` (`ATK` atau `OPS`). Ini menjaga satu antrean pemrosesan bagi Admin ATK tanpa membuat tabel dan logika approval baru.

Alternatif yang tidak dipilih:

- Tabel OPS baru: pemisahan lebih tegas, tetapi menggandakan model, status, tampilan, dan proses approval.
- Menentukan modul dari `atk_item_id`: tidak aman karena request barang baru boleh tidak memiliki item.

## Data

Tambahkan kolom berikut pada `atk_need_requests`:

```sql
module VARCHAR(10) NOT NULL DEFAULT 'ATK'
```

Tambahkan indeks `(module, status, created_at)`. Data lama otomatis tetap menjadi `ATK`. Tidak ada data yang dihapus. Perubahan server diberikan dalam file SQL manual dan tidak dijalankan otomatis oleh aplikasi.

## Route dan akses

Route baru berada di grup `ops.access` dan `ops.admin`:

- `GET /v2/ops/admin/need-requests` — daftar seluruh request barang OPS.
- `GET /v2/ops/admin/need-requests/create` — form request barang OPS.
- `POST /v2/ops/admin/need-requests` — menyimpan request dengan `module = OPS`.

Tidak ada route proses di OPS. Admin OPS tidak dapat menyelesaikan atau menolak request.

Route Admin ATK yang sudah ada tetap menjadi satu-satunya tempat pemrosesan. Daftarnya menampilkan request ATK dan OPS secara kronologis dengan label modul. Route ATK umum tetap hanya menampilkan dan membuat request `module = ATK`.

## Alur

1. Admin OPS membuka menu **Request Barang**.
2. Admin OPS memilih barang OPS existing atau menulis nama barang baru, jumlah, satuan, dan alasan.
3. Sistem menyimpan snapshot pemohon dan PT dengan `module = OPS` serta status `PENDING`.
4. Semua Admin OPS dapat melihat status request OPS, tetapi tidak melihat tombol proses.
5. Admin ATK melihat request tersebut di `/v2/atk/admin/need-requests` dengan label hijau `OPS`.
6. Admin ATK menandai `DONE` atau `REJECTED` dan dapat menambahkan catatan.

## Tampilan

Form dan daftar OPS mengikuti struktur mobile-first request ATK, memakai warna hijau OPS dan kata sederhana. Menu **Request Barang** diletakkan di bagian **Admin OPS** pada sidebar. Daftar Admin ATK mempertahankan tampilan existing dan hanya mendapat label `ATK`/`OPS`.

## Penanganan kesalahan

- Item yang dipilih wajib merupakan item aktif dari modul OPS.
- Jumlah minimal satu.
- Nama barang, satuan, dan alasan wajib diisi.
- Pengguna tanpa hak Admin OPS mendapat 403.
- Proses ulang request yang bukan `PENDING` tetap ditolak oleh guard existing.

## Pengujian

- Admin OPS dapat membuka form, membuat request `OPS`, dan melihat statusnya.
- Pengguna OPS biasa tidak dapat membuka route admin request barang.
- Admin OPS tidak memiliki endpoint untuk memproses request.
- Request OPS muncul pada daftar Admin ATK dengan label `OPS` dan dapat diproses Admin ATK.
- Request OPS tidak muncul pada riwayat request barang ATK umum.
- Request ATK existing tetap tersimpan sebagai `ATK` dan alurnya tidak berubah.

## Batas MVP

Tidak ada notifikasi email, lampiran, level approval tambahan, atau pembuatan master barang otomatis. Status selesai hanya mencatat bahwa Admin ATK telah menangani kebutuhan; penambahan stok tetap dilakukan melalui Master Barang.
