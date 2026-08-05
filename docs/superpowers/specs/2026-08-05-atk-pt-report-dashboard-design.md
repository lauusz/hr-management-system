# Desain Dashboard Ringkas Rekap ATK

## Tujuan

Menyederhanakan halaman Rekap PT menjadi dashboard yang dapat dibaca cepat oleh pengguna awam. Halaman harus menjawab tiga pertanyaan utama:

1. Berapa pengajuan disetujui pada setiap PT?
2. Barang apa yang paling banyak diambil secara keseluruhan?
3. Siapa yang paling sering mengambil barang?

Dashboard tetap mobile-first, memakai data ATK yang sudah ada, dan tidak mengubah database maupun isi Export Excel.

## Definisi Data

Semua visual memakai periode bulan dan filter PT yang sudah tersedia. Data hanya mencakup pengajuan ATK berstatus `APPROVED` atau `PARTIAL`, serta item berstatus `APPROVED`.

- **Pengajuan per PT:** jumlah pengajuan unik yang memiliki minimal satu item disetujui. PT tanpa aktivitas tidak ditampilkan.
- **Barang paling banyak diambil:** total kuantitas setiap jenis barang dari seluruh pengajuan yang masuk dalam filter. Satuan ditampilkan bersama nilainya karena satuan antarbarang dapat berbeda.
- **Sering mengambil:** jumlah pengajuan unik per nama pengambil. Satu pengajuan tetap dihitung satu kali walaupun berisi beberapa barang.

## Prinsip Penyederhanaan

- Hilangkan hero besar, label `Laporan Manajemen`, dan nomor bagian `01–05`.
- Hindari card di dalam card jika pemisahan dapat dilakukan dengan jarak dan judul.
- Gunakan istilah singkat: `Rekap ATK`, `Pengajuan`, `Pengambil`, `PT`, dan `Jenis Barang`.
- Tampilkan informasi dari ringkasan menuju detail.
- Pertahankan satu halaman tanpa tab atau library chart baru.

## Struktur Halaman

Urutan halaman:

1. Header ringkas berisi judul `Rekap ATK`, periode aktif, dan tombol `Unduh Excel`.
2. Filter bulan dan PT dalam satu panel ringkas.
3. Empat kartu angka utama: Pengajuan, Pengambil, PT, dan Jenis Barang.
4. Grafik `Pengajuan per PT`.
5. Grafik `Barang Terbanyak`.
6. Ranking `Sering Mengambil`.
7. `Riwayat Pengambilan` yang tertutup secara default dan dapat dibuka dengan kontrol native `<details>`.

Detail transaksi tetap tersedia untuk audit, tetapi tidak mendominasi tampilan awal. Filter berlaku untuk seluruh ringkasan, grafik, ranking, riwayat, dan file Excel.

## Fondasi Visual

### Warna

Warna utama mengikuti modul ATK:

- Primary: `#7C4DDE`
- Primary dark: `#5B35B7`
- Primary soft: `#F3EEFF`
- Surface: `#FFFFFF`
- Background: `#F7F7FA`
- Text: `#111827`
- Muted: `#6B7280`
- Border: `#E5E7EB`

Semua bar memakai warna utama ATK. Nama, jumlah, dan persentase menjadi identitas data sehingga pengguna tidak perlu mengingat arti warna.

### Tipografi dan Angka

- Judul visual: 15px, berat 800.
- Keterangan visual: 11px, warna muted.
- Nilai utama: 18–24px, berat 800.
- Label data: 11–12px, berat 600–700.
- Angka memakai format bilangan bulat tanpa desimal.
- Istilah yang tampil kepada pengguna memakai bahasa sederhana: `pengajuan`, `barang diambil`, dan `sering mengambil`.

### Bentuk dan Jarak

- Card memakai radius 16–18px dan border standar ATK.
- Jarak antarkomponen 10–14px pada mobile dan 14–18px pada desktop.
- Tinggi sentuh minimum tombol/filter tetap 44px.
- Tidak memakai animasi chart agar tampilan stabil dan ringan.

## Komponen

### 1. Bar Horizontal Pengajuan per PT

Gunakan bar horizontal yang diurutkan dari jumlah pengajuan terbesar. Bar lebih mudah dibandingkan daripada donut ketika jumlah PT bertambah dan lebih nyaman dibaca pada layar kecil.

Setiap baris menampilkan nama PT, jumlah pengajuan, persentase, dan bar proporsional. Semua PT yang memiliki aktivitas ditampilkan; PT tanpa aktivitas tidak dirender. Warna ungu ATK cukup digunakan secara konsisten sehingga tidak memerlukan legenda warna terpisah.

### 2. Bar Horizontal Barang Terbanyak

Bar horizontal dipilih karena nama barang dapat panjang. Panjang bar dihitung terhadap barang dengan total tertinggi pada hasil filter.

Setiap baris menampilkan:

- nama barang;
- total kuantitas dan satuan;
- bar proporsional sebagai bantuan perbandingan.

Tampilkan maksimal 10 barang teratas. Data lengkap tetap tersedia melalui detail transaksi dan Export Excel.

### 3. Daftar Sering Mengambil

Daftar ranking lebih tepat daripada chart karena fokus utamanya nama. Tampilkan maksimal 10 nama berdasarkan jumlah pengajuan unik terbanyak.

Setiap baris berisi nomor urut, nama, dan keterangan kecil seperti `8 pengajuan`. Baris pertama dapat diberi penekanan ringan melalui nomor urut dan warna utama. Tidak menampilkan foto, avatar, atau data tambahan yang tidak dibutuhkan.

### 4. Riwayat Pengambilan

Gunakan elemen native `<details>` dengan judul `Lihat Riwayat Pengambilan`. Komponen tertutup secara default agar halaman awal tetap ringkas. Setelah dibuka, desktop menampilkan tabel dan mobile menampilkan kartu responsif yang sudah digunakan saat ini.

### 5. Empty State

Jika seluruh laporan kosong, setiap visual menampilkan pesan `Belum ada pengajuan disetujui pada periode ini.` tanpa merender chart kosong.

Jika hanya salah satu dataset kosong, komponen lain tetap tampil normal dan hanya komponen tersebut yang memakai empty state.

## Layout Responsif

### Mobile, kurang dari 768px

- Semua komponen satu kolom.
- Header menumpuk secara alami; tombol `Unduh Excel` memakai lebar penuh.
- Filter bulan, PT, dan tombol `Tampilkan` memakai lebar penuh.
- Empat angka utama tetap dalam grid dua kolom.
- Grafik PT dan barang memakai bar horizontal selebar layar.
- Bar memakai lebar penuh dan label dapat membungkus maksimal dua baris.
- Daftar nama memakai baris ringkas dengan area sentuh yang nyaman.
- Riwayat hanya mengambil ruang setelah dibuka dan tetap memakai kartu responsif.

### Desktop, mulai 768px

- Header menempatkan judul dan periode di kiri serta tombol `Unduh Excel` di kanan.
- Filter berada dalam satu baris.
- Empat angka utama berada dalam empat kolom.
- Grafik PT dan ranking nama berdampingan.
- Grafik barang memakai satu baris penuh agar nama dan skala mudah dibandingkan.
- Riwayat memakai lebar penuh.

## Aksesibilitas

- Setiap bar selalu disertai nama dan angka aktual; panjang dan warna bukan satu-satunya pembeda.
- Komponen chart diberi judul yang terhubung melalui `aria-labelledby`.
- Data tetap dapat dipahami ketika CSS gagal dimuat karena nama dan angka berada di HTML.
- Tidak menambahkan interaksi hover-only atau tooltip yang tidak tersedia di perangkat sentuh.
- Kontrol riwayat memakai `<summary>` yang dapat diakses dengan keyboard.

## Batas Implementasi

- Tidak menambahkan Chart.js atau dependency baru.
- Tidak mengubah tabel database, migrasi, atau data lama.
- Tidak mengubah isi Export Excel pada tahap ini.
- Tidak menambahkan tren antarbulan atau drill-down; fitur tersebut baru diperlukan jika analisis satu bulan belum mencukupi.
- Tidak memindahkan CSS halaman ke sistem komponen baru; perombakan dibatasi pada halaman report.

## Pengujian

- PT tanpa aktivitas tidak muncul pada dataset/chart PT.
- Jumlah pengajuan dihitung unik per request, bukan per item.
- Ranking pengambil menghitung satu request satu kali.
- Barang diurutkan berdasarkan total kuantitas dan dibatasi 10 item.
- Empty state tampil tanpa error saat periode tidak memiliki data.
- Struktur label chart tetap tersedia pada desktop dan mobile.
- Riwayat transaksi tertutup secara default dan dapat dibuka tanpa JavaScript.
- Filter yang dipilih tetap diteruskan ke tautan Export Excel.
