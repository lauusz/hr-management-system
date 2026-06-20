# Desain Script Deployment Database Server

## Tujuan

Menyediakan satu script SQL MariaDB yang dapat dijalankan manual untuk menyiapkan perubahan database fitur baru tanpa menjalankan migration Laravel dan tanpa menghapus data existing.

## Target

- Database: `hrd_system`
- Engine target: MariaDB 11.8.x
- Referensi schema server: `backups/hrd_system_20260620_0845.sql`
- Referensi schema aplikasi: migration repository per 20 Juni 2026

## Ruang Lingkup

Script mencakup:

1. Validasi awal untuk tabel dan kolom yang dibutuhkan.
2. Pembuatan tabel `leave_balance_transactions` sesuai migration terbaru.
3. Pembuatan tabel `office_holidays` untuk Kalender Kantor global.
4. Penambahan index performa dari daftar deployment yang masih relevan.
5. Penambahan unique key periode payslip setelah memastikan tidak ada data duplikat.
6. Penambahan foreign key `users.shift_id` jika constraint belum tersedia dan tidak ada referensi yatim.
7. Perubahan user ID `101` menjadi role `HR STAFF` dengan `can_manage_payroll = 1`.
8. Query verifikasi hasil deployment.

## Perbaikan dari Draft Notepad

- Seluruh escape `\\\` pada nama tabel, kolom, index, dan constraint dihapus.
- Kolom angka ledger menggunakan `DECIMAL(8,2)`, bukan `DECIMAL(5,1)`.
- `idempotency_key` dibuat nullable dan unique agar sesuai migration serta mendukung data opening balance.
- Jenis transaksi mencakup `OPENING_BALANCE`, `DEDUCT`, `REFUND`, dan `ADJUSTMENT` pada dokumentasi kolom.
- Nama dan susunan index diselaraskan dengan query aplikasi dan migration aktif.

## Strategi Idempotensi

- `CREATE TABLE IF NOT EXISTS` digunakan untuk tabel baru.
- Keberadaan index dan constraint diperiksa melalui `information_schema` sebelum dibuat.
- Stored procedure deployment sementara menangani percabangan dan dihapus setelah selesai.
- Script dapat dijalankan ulang jika deployment terputus.
- Script tidak mengubah tabel `migrations`.

## Validasi dan Penanganan Error

Script berhenti menggunakan `SIGNAL SQLSTATE '45000'` jika:

- database aktif bukan `hrd_system`;
- tabel/kolom dasar yang dibutuhkan tidak tersedia;
- terdapat duplikasi payslip pada kombinasi user dan periode;
- user ID `101` tidak tersedia;
- terdapat `users.shift_id` yang tidak memiliki pasangan di tabel `shifts`.

DDL MariaDB melakukan implicit commit. Karena itu script menjalankan seluruh preflight sebelum perubahan schema, kemudian menjalankan perubahan idempotent, dan menempatkan update user di bagian akhir.

## Batasan

- Script tidak menghapus atau menggabungkan data duplikat.
- Script tidak menginisialisasi ledger dari saldo lama; proses backfill ledger tetap merupakan pekerjaan terpisah.
- Script tidak menjalankan migration, seeder, atau command Artisan.
- Backup database server wajib dibuat sebelum script dijalankan.

## Kriteria Berhasil

- Kedua tabel baru tersedia dengan kolom, index, dan foreign key yang benar.
- Index deployment tersedia tanpa duplikasi nama.
- Unique key payslip berhasil dibuat.
- Foreign key shift user tersedia.
- User ID `101` berstatus `HR STAFF` dan memiliki akses payroll.
- Query verifikasi akhir tidak melaporkan preflight error atau referensi yatim.
