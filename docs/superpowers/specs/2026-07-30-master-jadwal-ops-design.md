# Master Jadwal OPS Design

## Tujuan

Master Jadwal OPS mempercepat pengaturan shift dan lokasi untuk karyawan operasional yang mengalami rolling bulanan. HR tetap menentukan setiap perubahan secara manual; sistem tidak melakukan shuffle atau memilih jadwal secara otomatis.

Master Jadwal Karyawan tetap tersedia untuk seluruh karyawan dan perubahan individual. Master Jadwal OPS menjadi tampilan khusus untuk anggota operasional serta menyediakan perubahan jadwal secara bulk.

## Keputusan Utama

- Anggota OPS dikelola manual oleh HR, bukan ditentukan dari nama jabatan atau hardcode ID karyawan.
- Keanggotaan OPS tetap aktif sampai HR mengeluarkan karyawan tersebut.
- Master Shift tetap menjadi satu-satunya sumber pola hari, jam masuk, jam pulang, hari libur, dan catatan shift.
- Master Jadwal OPS dan Master Jadwal Karyawan mengelola sumber jadwal aktif yang sama agar alur absensi tidak bercabang.
- HR dapat menerapkan perubahan sekarang atau menjadwalkannya mulai tanggal 1 bulan berikutnya.
- Jadwal aktif tetap disimpan pada `employee_shifts`.
- Jadwal langsung, pending, dan batal dicatat pada `employee_shift_changes`.
- Tidak ada halaman riwayat rolling pada tahap ini, walaupun catatan perubahan tetap tersedia di database.

## Struktur Halaman

Halaman Master Jadwal OPS berisi:

1. Pencarian nama karyawan.
2. Filter PT, jabatan, shift aktif, dan status jadwal berikutnya.
3. Aksi untuk menambah atau mengeluarkan anggota OPS.
4. Tabel anggota OPS dengan kolom:
   - checkbox;
   - nama dan jabatan;
   - PT;
   - shift aktif;
   - lokasi aktif;
   - jadwal berikutnya dan tanggal mulai, jika ada;
   - aksi individual.
5. Checkbox pada header untuk memilih seluruh baris yang sedang tampil.
6. Panel bulk yang aktif setelah satu atau lebih karyawan dipilih.

Panel bulk meminta:

- shift;
- lokasi presensi;
- waktu berlaku:
  - **Berlaku sekarang**; atau
  - **Mulai 1 [bulan berikutnya]**.

Waktu berlaku menggunakan pilihan radio agar HR tidak dapat memilih dua kondisi sekaligus. Sebelum penyimpanan, sistem menampilkan jumlah karyawan yang akan diubah dan meminta konfirmasi.

## Mekanisme Jadwal

### Berlaku Sekarang

Sistem memperbarui shift dan lokasi aktif karyawan yang dipilih. Perubahan langsung digunakan oleh proses clock-in berikutnya. Jadwal bulan depan yang belum jatuh tempo tetap dipertahankan.

### Mulai Bulan Depan

Sistem menyimpan satu jadwal berikutnya dengan tanggal efektif pada hari pertama bulan berikutnya berdasarkan timezone aplikasi `Asia/Jakarta`.

Contoh: perubahan dibuat pada 30 Juli 2026. Jadwal berikutnya memiliki tanggal efektif 1 Agustus 2026. Sampai 31 Juli, jadwal aktif lama tetap digunakan. Mulai 1 Agustus, pembacaan jadwal memilih jadwal berikutnya karena tanggal efektifnya sudah tercapai.

Aktivasi tidak bergantung pada proses yang harus berjalan tepat pukul 00:00. Setiap pembacaan jadwal menentukan jadwal efektif berdasarkan tanggal aplikasi. Karena itu, pergantian tetap berlaku setelah server restart atau scheduler terlambat.

Jika HR menyimpan ulang jadwal bulan depan untuk karyawan yang sama, jadwal berikutnya yang lama diganti setelah konfirmasi.

## Integrasi Absensi

Clock-in harus menggunakan jadwal efektif pada tanggal absensi:

1. Ambil penugasan jadwal karyawan.
2. Jika jadwal berikutnya memiliki tanggal efektif yang sudah tercapai, gunakan shift dan lokasi berikutnya.
3. Jika belum, gunakan shift dan lokasi aktif.
4. Ambil detail hari dan jam dari Master Shift berdasarkan hari berjalan.
5. Simpan snapshot shift, jam normal, lokasi, dan referensi penugasan ke record absensi.

Snapshot pada absensi memastikan perubahan jadwal berikutnya tidak mengubah data absensi yang sudah tercatat.

## Penyimpanan Data

Keanggotaan OPS disimpan pada `users.is_ops_schedule_member`. Jadwal aktif tetap memakai satu record `employee_shifts` per karyawan agar alur absensi yang sudah ada tidak bercabang.

Perubahan jadwal disimpan pada `employee_shift_changes` dengan pengguna, shift, lokasi, tanggal efektif, status, pembuat perubahan, snapshot nama shift/lokasi, serta waktu penerapan atau pembatalan.

Setiap karyawan hanya boleh mempunyai satu perubahan berstatus `PENDING`. Perubahan langsung memperbarui `employee_shifts` dan dicatat sebagai `APPLIED`. Perubahan bulan depan disimpan sebagai `PENDING`; ketika tanggal efektif tercapai, perubahan tersebut diterapkan ke `employee_shifts` dan statusnya menjadi `APPLIED`.

Record jadwal aktif belum wajib tersedia ketika anggota OPS baru ditambahkan. Dalam kondisi tersebut, clock-in tetap ditolak sampai HR menerapkan jadwal aktif atau jadwal pending mencapai tanggal efektif.

Catatan perubahan tetap disimpan untuk konsistensi dan audit internal, tetapi UI riwayat rolling tidak dibuat pada tahap ini.

## Validasi dan Keamanan

- Hanya role HR yang sudah berwenang mengelola master presensi dapat membuka dan mengubah Master Jadwal OPS.
- Karyawan, shift, dan lokasi harus ada serta masih valid.
- Bulk update harus memiliki minimal satu karyawan.
- Semua karyawan terpilih harus merupakan anggota OPS saat transaksi dijalankan.
- Perubahan bulk dijalankan dalam satu transaksi: seluruh perubahan berhasil atau seluruhnya dibatalkan.
- Request perubahan memakai POST/PUT dengan CSRF.
- Pengulangan submit tidak boleh membuat penugasan ganda untuk satu karyawan.
- Server dan konfigurasi Laravel harus menggunakan timezone `Asia/Jakarta`.

## Kondisi Gagal

- Tidak ada karyawan dipilih: tampilkan pesan dan jangan mengirim perubahan.
- Shift atau lokasi tidak valid/nonaktif: tolak seluruh bulk update.
- Karyawan dikeluarkan dari OPS setelah halaman dibuka: tolak perubahan untuk menjaga konsistensi transaksi.
- Jadwal berikutnya sudah ada: minta konfirmasi sebelum menggantinya.
- Karyawan belum mempunyai jadwal aktif dan memilih berlaku bulan depan: simpan sebagai jadwal berikutnya; clock-in tetap belum tersedia sebelum tanggal efektif.

## Pengujian

Pengujian minimum mencakup:

- hanya anggota OPS tampil pada halaman;
- tambah dan keluarkan anggota OPS;
- bulk update berlaku sekarang untuk beberapa karyawan;
- bulk update bulan depan tidak mengubah jadwal sebelum tanggal efektif;
- jadwal bulan depan otomatis terbaca pada atau setelah tanggal efektif;
- pergantian bulan mengikuti timezone `Asia/Jakarta`;
- pending lama terganti setelah konfirmasi;
- absensi lama tidak berubah setelah rolling;
- karyawan tanpa jadwal aktif ditolak clock-in sebelum tanggal efektif;
- validasi anggota, shift, lokasi, otorisasi, CSRF, dan transaksi bulk.

## Di Luar Scope

- Shuffle atau pembagian shift otomatis.
- Pola rolling mingguan.
- Riwayat dan laporan rolling bulanan.
- Approval berlapis atas perubahan jadwal.
- Notifikasi kepada karyawan.
- Perubahan struktur hari dan jam pada Master Shift.
