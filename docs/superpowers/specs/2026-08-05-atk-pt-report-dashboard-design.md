# Design System Dashboard Rekap PT ATK

## Tujuan

Mengubah halaman Rekap PT dari kumpulan tabel menjadi dashboard yang dapat menjawab tiga pertanyaan dengan cepat:

1. PT mana yang memiliki banyak pengajuan disetujui?
2. Barang apa yang paling banyak diambil secara keseluruhan?
3. Siapa yang paling sering mengambil barang?

Dashboard tetap mobile-first, memakai data ATK yang sudah ada, dan tidak mengubah database.

## Definisi Data

Semua visual memakai periode bulan dan filter PT yang sudah tersedia. Data hanya mencakup pengajuan ATK berstatus `APPROVED` atau `PARTIAL`, serta item berstatus `APPROVED`.

- **Banyak pengajuan per PT:** jumlah pengajuan unik yang memiliki minimal satu item disetujui. PT tanpa aktivitas tidak ditampilkan.
- **Barang paling banyak diambil:** total kuantitas setiap jenis barang dari seluruh pengajuan yang masuk dalam filter. Satuan ditampilkan bersama nilainya karena satuan antarbarang dapat berbeda.
- **Sering mengambil:** jumlah pengajuan unik per nama pengambil. Satu pengajuan tetap dihitung satu kali walaupun berisi beberapa barang.

## Struktur Halaman

Urutan konten dibuat dari informasi paling ringkas menuju detail:

1. Header laporan dan parameter periode/PT.
2. Empat kartu ringkasan yang sudah ada.
3. Visual `Banyak Pengajuan per PT`.
4. Visual `Barang Paling Banyak Diambil`.
5. Daftar `Sering Mengambil`.
6. Detail transaksi untuk penelusuran.

Tabel rekap PT dan tabel konsumsi barang digantikan oleh visual. Detail transaksi tetap dipertahankan karena diperlukan untuk audit dan pengecekan data.

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

Palet pembeda PT:

1. `#7C4DDE`
2. `#0F766E`
3. `#2563EB`
4. `#D97706`
5. `#DB2777`
6. `#4F46E5`
7. `#16A34A`
8. `#DC2626`

Jika jumlah PT lebih dari delapan, warna dapat berulang. Nama dan angka pada legenda tetap menjadi identitas utama sehingga informasi tidak bergantung pada warna saja.

### Tipografi dan Angka

- Judul visual: 15px, berat 800.
- Keterangan visual: 11px, warna muted.
- Nilai utama: 18–24px, berat 800.
- Label data: 11–12px, berat 600–700.
- Angka memakai format bilangan bulat tanpa desimal.
- Istilah yang tampil kepada pengguna memakai bahasa sederhana: `banyak pengajuan`, `barang diambil`, dan `sering mengambil`.

### Bentuk dan Jarak

- Card memakai radius 16–18px dan border standar ATK.
- Jarak antarkomponen 10–14px pada mobile dan 14–18px pada desktop.
- Tinggi sentuh minimum tombol/filter tetap 44px.
- Tidak memakai animasi chart agar tampilan stabil dan ringan.

## Komponen

### 1. Donut Banyak Pengajuan per PT

Donut dibuat dengan CSS `conic-gradient`, tanpa library JavaScript. Bagian tengah menampilkan total seluruh pengajuan dan teks `banyak pengajuan`.

Legenda berada di bawah donut pada mobile dan di samping donut pada desktop. Setiap baris legenda berisi titik warna, nama PT, jumlah pengajuan, dan persentase. Semua PT yang memiliki aktivitas ditampilkan; PT tanpa aktivitas tidak dirender.

### 2. Bar Horizontal Barang Paling Banyak Diambil

Bar horizontal dipilih karena nama barang dapat panjang. Panjang bar dihitung terhadap barang dengan total tertinggi pada hasil filter.

Setiap baris menampilkan:

- nama barang;
- total kuantitas dan satuan;
- bar proporsional sebagai bantuan perbandingan.

Tampilkan maksimal 10 barang teratas. Data lengkap tetap tersedia melalui detail transaksi dan Export Excel.

### 3. Daftar Sering Mengambil

Daftar ranking lebih tepat daripada chart karena fokus utamanya nama. Tampilkan maksimal 10 nama berdasarkan jumlah pengajuan unik terbanyak.

Setiap baris berisi nomor urut, nama, dan keterangan kecil seperti `8 banyak pengajuan`. Tidak menampilkan avatar atau data tambahan yang tidak dibutuhkan.

### 4. Empty State

Jika seluruh laporan kosong, setiap visual menampilkan pesan `Belum ada pengajuan disetujui pada periode ini.` tanpa merender chart kosong.

Jika hanya salah satu dataset kosong, komponen lain tetap tampil normal dan hanya komponen tersebut yang memakai empty state.

## Layout Responsif

### Mobile, kurang dari 768px

- Semua komponen satu kolom.
- Donut berada di tengah dengan legenda di bawahnya.
- Bar memakai lebar penuh dan label dapat membungkus maksimal dua baris.
- Daftar nama memakai baris ringkas dengan area sentuh yang nyaman.
- Detail transaksi tetap memakai kartu responsif yang sudah ada.

### Desktop, mulai 768px

- Donut dan daftar sering mengambil berada dalam grid dua kolom.
- Bar barang memakai satu baris penuh agar nama dan skala mudah dibandingkan.
- Filter dan kartu ringkasan mempertahankan layout saat ini.

## Aksesibilitas

- Donut memiliki ringkasan teks dan legenda lengkap; warna bukan satu-satunya pembeda.
- Bar selalu disertai angka aktual.
- Komponen chart diberi judul yang terhubung melalui `aria-labelledby`.
- Data tetap dapat dipahami ketika CSS gagal dimuat karena nama dan angka berada di HTML.
- Tidak menambahkan interaksi hover-only atau tooltip yang tidak tersedia di perangkat sentuh.

## Batas Implementasi

- Tidak menambahkan Chart.js atau dependency baru.
- Tidak mengubah tabel database, migrasi, atau data lama.
- Tidak mengubah isi Export Excel pada tahap ini.
- Tidak menambahkan tren antarbulan atau drill-down; fitur tersebut baru diperlukan jika analisis satu bulan belum mencukupi.

## Pengujian

- PT tanpa aktivitas tidak muncul pada dataset/chart PT.
- Jumlah pengajuan dihitung unik per request, bukan per item.
- Ranking pengambil menghitung satu request satu kali.
- Barang diurutkan berdasarkan total kuantitas dan dibatasi 10 item.
- Empty state tampil tanpa error saat periode tidak memiliki data.
- Struktur label chart tetap tersedia pada desktop dan mobile.
