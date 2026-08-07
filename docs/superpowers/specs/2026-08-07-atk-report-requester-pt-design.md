# ATK Report Requester PT Design

## Tujuan

Menampilkan asal PT pada daftar **Sering Mengambil** di `/v2/atk/admin/reports`.

## Desain

- Bentuk ranking yang sudah ada tetap dipakai.
- Baris utama menampilkan `NAMA · PT. NAMA PT` dengan pemisah titik tengah.
- Prefix `PT` yang sudah ada pada snapshot dinormalkan agar tidak tampil ganda.
- Jumlah pengajuan tetap ditampilkan pada baris di bawahnya.
- PT menggunakan `atk_requests.pt_name_snapshot` agar sesuai dengan PT saat pengajuan dibuat.
- Pengelompokan dilakukan berdasarkan nama dan PT. Nama yang tercatat pada dua PT berbeda ditampilkan sebagai dua baris terpisah.
- Nilai PT kosong ditampilkan sebagai `-`.

## Perubahan Teknis

- Tambahkan `pt_name_snapshot` pada query ranking dan `GROUP BY`.
- Tampilkan PT di view ranking.
- Tambahkan pengujian yang memastikan nama, PT, dan jumlah pengajuan muncul berurutan.

## Di Luar Scope

- Tidak mengubah filter, donut chart, riwayat pengambilan, export, database, atau laporan OPS.
