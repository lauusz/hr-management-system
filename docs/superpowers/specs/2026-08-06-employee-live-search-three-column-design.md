# Desain Live Search dan Grid Karyawan Tiga Kolom

## Tujuan

Membuat halaman `/hr/employees` lebih cepat digunakan dengan pencarian otomatis tanpa tombol Cari dan daftar karyawan yang lebih ringkas pada desktop.

## Pencarian

- Input tetap memakai parameter GET `q` dan query server yang sudah tersedia.
- Tombol `Cari` dihapus.
- Pencarian berjalan 350 ms setelah pengguna berhenti mengetik.
- Request membawa seluruh nilai form agar filter PT, jabatan, kategori, dan filter khusus tetap dipertahankan.
- Hasil yang diganti hanya bagian jumlah hasil, daftar card, dan pagination; halaman tidak dimuat ulang penuh.
- `AbortController` membatalkan request lama ketika pengguna melanjutkan mengetik.
- URL browser diperbarui dengan `history.replaceState` agar query dapat disalin atau dimuat ulang.
- Jika request gagal, hasil lama dipertahankan dan form masih dapat dikirim dengan tombol Enter.

## Layout Card

- Kurang dari 1024px: satu kolom seperti kondisi sekarang.
- Mulai 1024px: dua kolom.
- Mulai 1280px: tiga kolom.
- Pada tiga kolom, padding, avatar, gap, chip, dan teks kontak dipadatkan secukupnya tanpa menghapus informasi atau kontrol shift.
- Empty state tetap memenuhi seluruh lebar grid.

## Format Tanggal Card

- Tanggal bergabung pada card memakai format singkat bahasa Indonesia seperti `1 Des 2025`.
- Tanggal berakhir percobaan/kontrak yang muncul pada filter terkait memakai format singkat yang sama.
- Perubahan hanya berlaku pada card `/hr/employees`; format pada halaman detail, edit, dan data tersimpan tidak berubah.

## Aksesibilitas dan Umpan Balik

- Input memakai `type="search"`, `autocomplete="off"`, dan label aksesibel.
- Area hasil memakai `aria-live="polite"` agar perubahan jumlah hasil dapat diketahui teknologi bantu.
- Selama request, area hasil diberi status loading ringan tanpa memblokir kontrol filter.
- Pencarian dengan Enter tetap tersedia sebagai fallback native form.

## Batasan

- Tidak mengubah database, route, controller, pagination size, atau query pencarian.
- Tidak menambahkan library JavaScript.
- Pencarian tetap server-side agar mencakup seluruh karyawan, bukan hanya halaman pagination aktif.
- Pengaturan shift inline tetap bekerja setelah daftar card diganti.

## Pengujian

- Halaman tidak lagi merender tombol Cari.
- Input memiliki penanda live search.
- Container hasil memiliki target yang dapat diganti dan status `aria-live`.
- CSS memuat grid tiga kolom pada breakpoint 1280px.
- Card merender nama bulan singkat untuk tanggal bergabung dan tanggal berakhir yang tampil.
- Query `q` yang sudah ada tetap memfilter hasil server-side.
