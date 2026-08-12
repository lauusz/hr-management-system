# Desain Schema Akses ATK MKS

## Tujuan

Menyiapkan akses modul `/v2/atk-mks` dengan pola yang sama seperti OPS: pengguna umum memperoleh akses berdasarkan divisi, sedangkan admin diberikan per orang melalui `user_access_roles` existing.

## Perubahan Database

Tambahkan tabel `atk_mks_access_divisions` yang menyimpan satu baris untuk setiap divisi terpilih. Tabel berelasi ke `divisions` dan mencatat pengguna yang melakukan pengaturan melalui `created_by`.

Tidak diperlukan perubahan pada `atk_items`, `atk_requests`, `atk_stock_movements`, atau `user_access_roles`. Nilai modul `ATK_MKS` muat pada kolom `module VARCHAR(10)`, dan role `ADMIN ATK MKS` muat pada `user_access_roles.role VARCHAR(50)`.

## Keamanan Data

SQL deployment hanya menggunakan `CREATE TABLE`. Tidak ada `DROP`, `DELETE`, `TRUNCATE`, atau `UPDATE`, sehingga data existing tidak diubah.
