# Kebutuhan Operasional Access Card Design

## Tujuan

Menyiapkan entry awal modul `Kebutuhan Operasional` pada portal `/v2/access` tanpa membuat modul, route, permission, atau penyimpanan data sebelum kebutuhan lanjutannya disepakati.

## Perubahan

- Badge `Kebutuhan Kantor` diubah dari `V2 Testing` menjadi `Existing`.
- Portal menampilkan kartu ketiga bernama `Kebutuhan Operasional` dengan badge `Testing`.
- Kartu baru belum menjadi tautan karena halaman tujuan belum tersedia.
- Deskripsi kartu menjelaskan bahwa layanan ditujukan untuk kebutuhan penyimpanan divisi OPS.
- Grid tetap responsif: tiga kolom jika ruang cukup dan satu kolom pada layar kecil.

## Pengujian

Regression test portal memastikan pengguna terautentikasi melihat:

- `HRD System`;
- `Kebutuhan Kantor` dengan status `Existing`;
- `Kebutuhan Operasional` dengan status `Testing`.

## Di Luar Scope

- Clone fitur ATK.
- Route dan halaman Kebutuhan Operasional.
- Pembatasan akses berdasarkan divisi.
- Model, migration, stok, request, approval, dan laporan operasional.
