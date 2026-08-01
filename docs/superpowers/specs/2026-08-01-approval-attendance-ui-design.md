# Desain UI Approval Absensi

## Tujuan

Menyelaraskan tampilan `/hr/approval-attendance` dengan pola visual `/hr/leave/master` tanpa mengubah business logic persetujuan absensi Dinas Luar.

## Ruang Lingkup

- Menggunakan header halaman yang konsisten dengan halaman HR lainnya.
- Menampilkan ringkasan jumlah pengajuan yang sedang menunggu.
- Merapikan tabel pengajuan dengan urutan informasi: karyawan, waktu, bukti foto, lokasi, keperluan, dan keputusan.
- Menampilkan identitas karyawan dengan avatar inisial serta jabatan/divisi ketika tersedia.
- Mempertahankan image viewer global untuk bukti foto dan tautan Google Maps untuk lokasi.
- Mempertahankan aksi Terima dan Tolak sebagai aksi utama yang selalu terlihat.
- Menyamakan modal konfirmasi dengan pola modal aplikasi, termasuk alasan wajib ketika menolak.
- Menyediakan empty state, flash message sukses/error, focus state, dan tampilan responsif.

## Batasan

- Tidak menambahkan pencarian, filter, pagination, statistik riwayat, atau fitur baru.
- Tidak mengubah controller, route, database, status, validasi, maupun alur persetujuan.
- URL aksi Terima dan Tolak dibentuk melalui helper route Laravel agar aman pada deployment subfolder atau virtual directory.

## Struktur Tampilan

1. Header berikon dengan judul `Approval Absensi` dan deskripsi antrean Dinas Luar.
2. Ringkasan tunggal berisi jumlah pengajuan yang menunggu keputusan.
3. Table card bergaya halaman master dengan enam kolom utama.
4. Modal Terima dan Tolak yang menggunakan bahasa tindakan yang jelas.
5. Empty state ketika tidak ada pengajuan pending.

## Perilaku dan Data

Halaman tetap menerima koleksi `$pendingAttendances` dari controller saat ini. Setiap aksi mengirim POST beserta CSRF token menuju route yang sudah tersedia. Bukti foto tetap dibuka melalui image viewer global; lokasi tetap dibuka pada tab baru.

## Penanganan Kondisi

- Foto atau koordinat yang tidak tersedia ditampilkan sebagai tanda `-`.
- Relasi jabatan atau divisi yang kosong tidak menyebabkan error dan ditampilkan secara ringkas.
- Pesan sukses dan error dari session ditampilkan di atas tabel.
- Nama panjang dibatasi secara visual tanpa menghilangkan nilai aslinya.
- Keperluan/notes ditampilkan dalam satu baris dan otomatis menggunakan elipsis (`…`) berdasarkan lebar kolom, bukan jumlah karakter.
- Seluruh notes, termasuk notes pendek, dapat diklik untuk membuka modal `Detail Keperluan` yang menampilkan teks lengkap.
- Notes kosong tetap ditampilkan sebagai `Tidak ada keterangan` dan tidak membuka modal.

## Verifikasi

- Feature test approval yang sudah ada tetap lulus.
- Route Terima dan Tolak tetap berupa POST dan menghasilkan URL dari helper Laravel.
- Test memastikan trigger dan modal detail notes dirender tanpa mengubah isi notes.
- Pemeriksaan manual dilakukan pada kondisi data tersedia, data kosong, modal Terima, modal Tolak, foto, lokasi, nama panjang, dan viewport mobile.
