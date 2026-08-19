# Project Instructions — HRD System

Dokumen ini ditujukan untuk AI coding agent yang bekerja di repositori ini. Anggap pembaca belum tahu apa-apa tentang proyek ini.

## Aturan wajib (strict rules)

Sebelum menjalankan atau merekomendasikan perintah apa pun, baca dan patuhi [`STRICT-RULES.md`](STRICT-RULES.md).

Larangan terhadap perintah migrasi database Artisan bersifat absolut. Jangan pernah mengeksekusi, menyarankan, atau memanggil secara tidak langsung `php artisan migrate` atau perintah Artisan lain yang menyentuh skema database (`migrate:fresh`, `migrate:refresh`, `migrate:reset`, `migrate:rollback`, `migrate:status`, `db:wipe`, dll). Ini berlaku untuk semua lingkungan, termasuk melalui script, alias, atau command yang dirangkai.

Konsekuensi praktis dari aturan ini:

- **Jangan jalankan `composer run setup`** — script tersebut di dalamnya memanggil `php artisan migrate --force`.
- **Jangan gunakan `RefreshDatabase` / `LazilyRefreshDatabase` di test** — akan memicu `migrate:fresh`. Lihat bagian Testing di bawah.
- Bila perubahan kode membutuhkan perubahan skema, buat/edit file migrasi (atau file SQL di `database/sql/`, `version/sql-updates/`, atau `database/deployment/`) sebagai artefak saja; penerapannya ke database adalah proses manual terpisah milik pemilik repositori.

## Gambaran proyek

HRD System adalah aplikasi web manajemen SDM internal (multi-perusahaan/PT, berbahasa Indonesia) yang mencakup:

- **Kepegawaian**: profil karyawan, dokumen karyawan, organisasi (PT, divisi, jabatan), shift & jadwal kerja.
- **Cuti (leave)**: pengajuan, persetujuan berjenjang (supervisor → HR), saldo cuti dengan ledger transaksi, kuota "OFF SPV" untuk supervisor, hari libur kantor.
- **Absensi (attendance)**: check-in/out berbasis lokasi, approval absensi, lembur (overtime).
- **Pinjaman karyawan (loan)**: pengajuan dan cicilan.
- **Slip gaji (payslip)**: import dari Excel, distribusi via email (queue job).
- **ATK / OPS / ATK-MKS**: tiga modul inventaris alat tulis/operasional yang paralel (katalog, keranjang, pengajuan, approval admin, stok & mutasi stok, akses per-PT/divisi). ATK-MKS adalah varian untuk lokasi Makassar.
- **Aset perusahaan**: kategori aset dan mutasi aset.

## Teknologi

- **Backend**: PHP ^8.2, Laravel 12. Autoload PSR-4: `App\` → `app/`.
- **Frontend**: Blade (server-rendered, `resources/views/`), Tailwind CSS 4 via `@tailwindcss/vite`, Vite 7, axios. Tidak ada framework JS SPA; interaktivitas ditulis inline di Blade.
- **Database**: MySQL (`hrd_system`) di lingkungan nyata; SQLite untuk test. Skema didefinisikan di `database/migrations/` (jangan dieksekusi — lihat aturan wajib). Ada juga file SQL manual di `database/sql/`, `version/sql-updates/`, `database/deployment/`, dan backup di `backups/`.
- **Paket PHP utama**: `maatwebsite/excel` (import/export Excel), `barryvdh/laravel-dompdf` (PDF), `intervention/image` (kompresi gambar), `doctrine/dbal`.
- **Queue & cache**: driver `database` di development; test memakai `sync`/`array`.
- **PWA**: manifest dan service worker di `public/`; layout utama `resources/views/layouts/app.blade.php`.

## Struktur kode

- `app/Models/` — model Eloquent per domain (`LeaveRequest`, `Attendance`, `AtkItem`, `Asset`, `Payslip`, dll). Trait bersama di `app/Models/Concerns/`.
- `app/Enums/` — `UserRole` (HRD, HR STAFF, MANAGER, SUPERVISOR, EMPLOYEE, ADMIN ATK, OPS, ADMIN OPS, ADMIN ATK MKS) dan `LeaveType`.
- `app/Http/Controllers/` — controller per fitur; sub-namespace `HR/`, `Atk/`, `AtkMks/`, `Ops/` (masing-masing dengan sub-folder `Admin/`).
- `app/Services/` — logika bisnis inti: `LeaveBalanceService`, `LeaveRequestStateMachine`, `LeaveRequestWorkflowService`, `OffSpvQuotaService`, `LeaveRequestDuplicateCleanupService`, `Image/ImageCompressor`.
- `app/Console/Commands/` — command ledger saldo cuti; command closure (termasuk scheduler) ada di `routes/console.php`.
- `app/Exports/`, `app/Imports/` — integrasi maatwebsite/excel. `app/Jobs/SendPayslipEmailJob`, `app/Mail/PayslipPublishedMail` — distribusi slip gaji.
- `app/Helpers/` — `TerbilangHelper` (angka → teks Bahasa Indonesia), `CompanyAssetHelper`.
- `app/Policies/LeaveRequestPolicy.php` + `App\Providers\AuthServiceProvider` — otorisasi.
- `routes/web.php` — semua route web didefinisikan di satu file ini.
- `resources/views/` — Blade per modul: `hr/`, `admin/`, `atk/`, `atk-mks/`, `ops/`, `attendance/`, `leave_requests/`, `loan_requests/`, `overtime_requests/`, `supervisor/`, `emails/`, `layouts/`, `components/`, `v2/`.

## Perintah build & development

```bash
npm install
npm run build        # build aset via Vite
npm run dev          # Vite dev server

composer run dev     # serve + queue:listen + vite (concurrently)
php artisan serve    # hanya web server
```

Jangan jalankan `composer run setup` (memicu migrasi — dilarang).

## Testing

- Framework: **Pest 3** (`pestphp/pest` + plugin Laravel). Test ada di `tests/Unit/` dan `tests/Feature/`.
- Jalankan dengan `php artisan test` atau `vendor/bin/pest` (bisa difilter: `php artisan test --filter=NamaTest`).
- `phpunit.xml` mengatur `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`.
- `tests/TestCase.php` membangun **file SQLite sekali pakai dari migrasi** sebelum siklus test — skema test tidak menyentuh MySQL maupun `database/testing.sqlite`.
- `tests/Pest.php` mewajibkan `DatabaseTransactions` untuk semua Feature test. **Dilarang** memakai `RefreshDatabase`/`LazilyRefreshDatabase` karena memicu `migrate:fresh`.
- Seeder penting untuk data uji: `ImportEmployeesFromCsvSeeder` (ada Feature test-nya).

## Konvensi

- Bahasa utama di kode (komentar, label UI, pesan error, enum label) adalah **Bahasa Indonesia**; pertahankan itu untuk kode baru.
- Code style: **Laravel Pint** (`vendor/bin/pint`) — ikuti default preset Laravel.
- Penamaan controller mengikuti pola yang ada (mis. `HrLeaveController`, `HREmployeeController` — perhatikan kapitalisasi tidak selalu konsisten; jangan mengganti nama kelas yang sudah ada tanpa diminta).
- Modul ATK / ATK-MKS / OPS sengaja paralel/duplikatif — perubahan pada satu modul sering perlu direplikasi ke modul pasangannya; cek keduanya sebelum mengubah.
- Saldo cuti dikelola lewat ledger (`leave_balance_transactions`) melalui `LeaveBalanceService`; jangan mengubah kolom saldo langsung.
- Scheduler (`routes/console.php`, timezone `Asia/Jakarta`): `leave:update-balances` harian 00:01, `off-spv:initialize-periods` tiap 1 Januari, `db:backup` harian 23:59.

## Keamanan

- Jangan pernah membaca, menyalin, atau menuliskan isi `.env` ke output. File contoh yang aman: `.env.example`.
- Kredensial database dibaca dari config/env; `db:backup` menjalankan `mysqldump` via `exec` — hati-hati saat mengubahnya (risiko command injection bila input tidak terkontrol).
- Otorisasi berbasis role (`UserRole`) dan policy; jangan bypass middleware/policy yang ada saat menambah route baru.
- Upload gambar selalu lewat `App\Services\Image\ImageCompressor`; ada Feature test khusus upload (`HrEmployeeImageUploadTest`).

## Code review graph

Ketika pengguna meminta "cek program secara keseluruhan" atau review proyek luas yang setara:

1. Cek `.code-review-graph/` dulu (berisi `graph.db` dan `graph.html`) dan perbarui bila basi.
2. Gunakan graph untuk mengidentifikasi modul, dependensi, test, dan blast radius sebelum memindai file secara luas.
3. Verifikasi temuan graph terhadap source code dan test aktual; jangan jadikan graph satu-satunya sumber kebenaran.
4. Bila graph atau integrasi MCP tidak tersedia, jatuhkan ke pencarian repositori biasa dan sebutkan fallback tersebut secara singkat.
