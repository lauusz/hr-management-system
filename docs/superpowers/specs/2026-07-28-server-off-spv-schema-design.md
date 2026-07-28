# Server OFF SPV Schema Design

## Tujuan

Menambahkan struktur database OFF SPV yang sudah digunakan di development ke database server berdasarkan dump `hrd_system_20260728_0845.sql`.

## Kondisi Saat Ini

- Server belum memiliki tabel `off_spv_periods`.
- Server belum memiliki tabel `off_spv_changes`.
- Server belum memiliki kolom `leave_requests.off_spv_period_id`.
- Development sudah memiliki seluruh struktur tersebut.
- Data `OFF_SPV` lama pada server harus tetap utuh.

## SQL Server

Script deployment akan:

1. Membuat `off_spv_periods` dengan relasi `user_id` memakai `ON DELETE RESTRICT`.
2. Membuat `off_spv_changes` dengan relasi periode memakai `ON DELETE RESTRICT` dan pelaku perubahan memakai `ON DELETE SET NULL`.
3. Menambahkan `leave_requests.off_spv_period_id` yang nullable dengan `ON DELETE SET NULL`.

Nama index, foreign key, check constraint, charset, dan collation mengikuti struktur development.

## Development

Tidak ada SQL yang dijalankan karena struktur sudah tersedia.

## Batasan

- Tidak ada `INSERT`, `UPDATE`, atau `DELETE`.
- Tidak ada backfill otomatis.
- Data OFF SPV lama akan dihubungkan melalui fitur input historis.
- Script ditujukan untuk dump server yang diperiksa pada 28 Juli 2026 dan dijalankan satu kali.

## Verifikasi

Setelah deployment, verifikasi dilakukan dengan membaca `information_schema` untuk memastikan dua tabel, satu kolom, dan empat foreign key tersedia dengan aturan penghapusan yang sesuai.
