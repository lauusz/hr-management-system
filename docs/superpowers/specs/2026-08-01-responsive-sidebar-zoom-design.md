# Responsive Sidebar untuk Browser Zoom

## Tujuan

Menjaga area konten utama tetap lapang ketika pengguna memperbesar browser, tanpa melawan fitur zoom dan tanpa mengubah perilaku setiap halaman secara terpisah.

## Desain

- Perubahan diterapkan satu kali pada layout global `resources/views/layouts/app.blade.php`.
- Lebar sidebar desktop menggunakan `clamp(224px, 17vw, 264px)` agar proporsional pada layar desktop yang berbeda.
- Sidebar penuh digunakan mulai lebar viewport efektif 1280px.
- Di bawah 1280px, sidebar memakai mekanisme drawer, tombol hamburger, dan backdrop yang sudah tersedia.
- Tidak ada JavaScript pendeteksi zoom. Browser zoom secara alami mengurangi lebar viewport CSS dan memicu breakpoint responsif.
- Pada acuan layar 1920x1080 dengan Windows Scale 125%, browser 100% tetap menampilkan sidebar desktop. Ketika browser dinaikkan ke 125%, lebar efektif turun sehingga sidebar berpindah menjadi drawer dan tidak menutupi konten.

## Batas Perubahan

- Tidak mengubah ukuran font secara paksa; aksesibilitas browser zoom tetap dipertahankan.
- Tidak mengubah UI atau fungsi halaman individual.
- Tidak menambah dependency, komponen, atau mekanisme sidebar baru.

## Verifikasi

- Memastikan aturan breakpoint dan lebar sidebar global ter-render pada halaman aplikasi.
- Memastikan tampilan desktop tetap menggunakan sidebar penuh pada viewport minimal 1280px.
- Memastikan viewport di bawah 1280px menggunakan drawer yang sudah ada.
- Menjalankan pengujian terkait layout dan pemeriksaan regresi proyek yang tersedia.
