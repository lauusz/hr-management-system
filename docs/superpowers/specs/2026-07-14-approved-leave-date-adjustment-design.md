# Desain Perubahan Tanggal Pengajuan yang Sudah Disetujui

## Tujuan

HRD dan HR Staff dapat memindahkan tanggal pengajuan satu hari yang berstatus `APPROVED` tanpa mengubah tipe, pemohon, status, approver, atau waktu approval. Nilai potongan saldo cuti mengikuti tanggal baru.

Contoh utama: pengajuan CUTI 18 Juli 2026 (Sabtu, 0,5 hari) dipindahkan ke 20 Juli 2026 (Senin, 1 hari). Saldo berkurang lagi 0,5 hari.

## Batas Scope

- Hanya HRD dan HR Staff.
- Hanya pengajuan tipe `CUTI` berstatus `APPROVED` dengan `start_date` sama dengan `end_date`.
- Form hanya menerima tanggal baru dan alasan perubahan wajib.
- Tipe, alasan pengajuan, pemohon, status, `approved_by`, dan `approved_at` tidak berubah.
- Tidak menambah tabel atau kolom baru.
- Pengajuan tanpa ledger `DEDUCT` tidak boleh diubah sebelum ledger historisnya di-backfill.

## Alur UI

Pada halaman detail HR, tombol `Ubah Tanggal` tersedia untuk pengajuan satu hari yang sudah disetujui. Aksi membuka form ringkas berisi tanggal lama, input tanggal baru, alasan perubahan, serta ringkasan potongan lama dan potongan baru.

Form memakai input tanggal native. Endpoint khusus digunakan agar pembatasan edit umum untuk status final tetap aktif.

## Alur Backend

Endpoint perubahan tanggal menjalankan satu database transaction:

1. Lock pengajuan dan user.
2. Pastikan actor adalah HRD atau HR Staff, status tetap `APPROVED`, dan pengajuan hanya satu hari.
3. Pastikan tanggal baru tidak tumpang tindih dengan pengajuan aktif lain milik user.
4. Ambil total potongan aktif pengajuan dari ledger `DEDUCT` dan adjustment tanggal sebelumnya.
5. Hitung hari efektif tanggal baru melalui `LeaveBalanceService`, termasuk aturan lima/enam hari kerja dan libur kantor.
6. Hitung selisih potongan baru terhadap potongan aktif.
7. Jika selisih menambah potongan dan saldo tidak cukup, batalkan transaction.
8. Ubah `start_date` dan `end_date` menjadi tanggal baru.
9. Ubah saldo hanya sebesar selisih.
10. Catat `ADJUSTMENT` yang terhubung ke pengajuan, berisi saldo sebelum/sesudah, tanggal lama/baru, nilai lama/baru, alasan, dan pelaku.
11. Tambahkan catatan sistem pada riwayat pengajuan tanpa mengubah data approval.

Perubahan ke tanggal dengan nilai potongan sama tetap mencatat riwayat, tetapi tidak membuat perubahan saldo.

## Ledger dan Pembatalan

Transaksi `DEDUCT:LEAVE:{id}` dan `REFUND:LEAVE:{id}` yang ada tidak digunakan ulang untuk edit tanggal. Adjustment memakai prefix idempotency key `ADJUST_DATE:LEAVE:{id}:`. Nilai `amount` positif berarti potongan tambahan; nilai negatif berarti pengembalian saldo. Perubahan saldo mengikuti `balance_after = balance_before - amount`.

Saat pengajuan dibatalkan, refund mengembalikan total potongan aktif: nominal `DEDUCT` awal ditambah seluruh adjustment perubahan tanggal yang relevan. Ini memastikan pembatalan setelah satu atau beberapa perubahan tanggal tetap mengembalikan nilai terakhir.

## Data Historis

Schema saat ini sudah cukup. Backup `backups/hrd_system_20260713_1314.sql` menunjukkan pengajuan contoh `#1032` belum memiliki ledger `DEDUCT`.

Backfill ledger dilakukan melalui command khusus yang default-nya dry-run, setelah backup database dan persetujuan eksplisit. Backfill:

- default dry-run;
- hanya mengisi pengajuan `APPROVED` yang belum memiliki `DEDUCT`;
- menghitung nominal berdasarkan tanggal yang disetujui;
- tidak mengubah `users.leave_balance`;
- aman dijalankan ulang melalui idempotency key.

Sampai backfill selesai, endpoint menolak perubahan tanggal untuk pengajuan tanpa ledger agar tidak menebak saldo historis.

## Penanganan Error

- Actor bukan HR: `403`.
- Status berubah ketika diproses: perubahan ditolak tanpa efek parsial.
- Pengajuan lebih dari satu hari: perubahan ditolak.
- Tanggal baru sama dengan tanggal lama: validasi menolak karena tidak ada perubahan.
- Tanggal bertabrakan: tampilkan pengajuan yang bentrok.
- Ledger `DEDUCT` tidak ditemukan: minta HR menjalankan proses inisialisasi ledger.
- Saldo tambahan tidak cukup: tanggal dan saldo tetap seperti semula.

## Verifikasi

Feature test minimal mencakup:

- Sabtu 0,5 hari menjadi Senin 1 hari.
- Senin 1 hari menjadi Sabtu 0,5 hari.
- Perubahan dengan nilai hari sama.
- Penolakan ketika saldo tambahan tidak cukup.
- Penolakan tanggal yang tumpang tindih.
- Penolakan actor non-HR.
- Penolakan pengajuan tanpa ledger.
- Refund setelah adjustment mengembalikan total potongan terbaru.
- Perubahan berulang tetap menghasilkan saldo dan refund yang benar.

Tidak ada migration atau perubahan database aktif saat implementasi kode dan test. Backfill production merupakan langkah operasional terpisah yang membutuhkan backup dan approval user.
