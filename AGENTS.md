# Project Instructions — HRD System

Dokumen ini ditujukan untuk AI coding agent yang bekerja di repositori ini. Anggap pembaca belum tahu apa-apa tentang proyek ini. Dokumentasi lengkap untuk manusia ada di `README.md`; `KIMI.md` hanyalah pointer kompatibilitas yang mengarah ke file ini.

## Aturan wajib (strict rules)

Sebelum menjalankan atau merekomendasikan perintah apa pun, baca dan patuhi [`STRICT-RULES.md`](STRICT-RULES.md).

Larangan terhadap perintah migrasi database Artisan bersifat absolut. Jangan pernah mengeksekusi, menyarankan, atau memanggil secara tidak langsung `php artisan migrate` atau perintah Artisan lain yang menyentuh skema database (`migrate:fresh`, `migrate:refresh`, `migrate:reset`, `migrate:rollback`, `migrate:status`, `db:wipe`, dll). Ini berlaku untuk semua lingkungan, termasuk melalui script, alias, atau command yang dirangkai.

Konsekuensi praktis dari aturan ini:

- **Jangan jalankan `composer run setup`** — script tersebut di dalamnya memanggil `php artisan migrate --force`.
- **Jangan gunakan `RefreshDatabase` / `LazilyRefreshDatabase` di test** — akan memicu `migrate:fresh`. Lihat bagian Testing di bawah. (Harness test di `tests/TestCase.php` menjalankan migrasi secara internal ke file SQLite sekali pakai yang terisolasi — itu mekanisme yang sudah disahkan pemilik repo; jangan menambahkan cara lain.)
- Bila perubahan kode membutuhkan perubahan skema, buat/edit file migrasi (atau file SQL di `database/sql/`, `version/sql-updates/`, atau `database/deployment/`) sebagai artefak saja; penerapannya ke database adalah proses manual terpisah milik pemilik repositori. Jangan pernah mencantumkan perintah migrasi sebagai langkah deployment.

## Gambaran proyek

HRD System adalah aplikasi web manajemen SDM internal (multi-perusahaan/PT, berbahasa Indonesia, repositori privat) yang mencakup:

- **Kepegawaian**: profil karyawan, dokumen karyawan, organisasi (PT, divisi, jabatan), shift & jadwal kerja.
- **Cuti (leave)**: pengajuan, persetujuan berjenjang (supervisor → HR), saldo cuti dengan ledger transaksi, kuota "OFF SPV" untuk supervisor, hari libur kantor.
- **Absensi (attendance)**: check-in/out berbasis lokasi (dengan foto), approval absensi, lembur (overtime).
- **Pinjaman karyawan (loan)**: pengajuan dan cicilan.
- **Slip gaji (payslip)**: import dari Excel, distribusi via email (queue job).
- **ATK / OPS / ATK-MKS**: tiga modul inventaris alat tulis/operasional yang paralel (katalog, keranjang, pengajuan, approval admin, stok & mutasi stok, akses per-PT/divisi). ATK-MKS adalah varian untuk lokasi Makassar.
- **Aset perusahaan**: kategori aset dan mutasi aset.

## Teknologi

- **Backend**: PHP ^8.2, Laravel 12. Autoload PSR-4: `App\` → `app/`.
- **Frontend**: Blade (server-rendered, `resources/views/`), Tailwind CSS 4 via `@tailwindcss/vite`, Vite 7, axios. Tidak ada framework JS SPA; interaktivitas ditulis inline di Blade. Entry Vite: `resources/css/app.css` dan `resources/js/app.js`.
- **Database**: MySQL (`hrd_system`) di lingkungan nyata; SQLite untuk test. Skema didefinisikan di `database/migrations/` (jangan dieksekusi — lihat aturan wajib). Ada juga file SQL manual di `database/sql/`, `version/sql-updates/` (format Markdown per perubahan), `database/deployment/`, dan backup manual di `backups/`.
- **Paket PHP utama**: `maatwebsite/excel` (import/export Excel), `barryvdh/laravel-dompdf` (PDF), `intervention/image` (kompresi gambar), `doctrine/dbal`.
- **Queue & cache**: driver `database` di development; test memakai `sync`/`array`. Queue terutama untuk pengiriman email slip gaji.
- **PWA**: manifest dan service worker di `public/` (`manifest.json`, `sw.js`, plus varian `-server`); layout utama `resources/views/layouts/app.blade.php`.
- **Environment lokal**: Laragon di Windows (lihat `scripts/` untuk helper `.bat`/`.ps1` seperti `db_backup.bat`, `queue_worker.bat`, `register_queue_worker.ps1`).

## Struktur kode

- `app/Models/` — model Eloquent per domain (`LeaveRequest`, `Attendance`, `AtkItem`, `Asset`, `Payslip`, `OffSpvPeriod`, dll). Trait bersama di `app/Models/Concerns/` (`HasMasaKerja`).
- `app/Enums/` — `UserRole` (HRD, HR STAFF, MANAGER, SUPERVISOR, EMPLOYEE, ADMIN ATK, OPS, ADMIN OPS, ADMIN ATK MKS) dan `LeaveType`.
- `app/Http/Controllers/` — controller per fitur; sub-namespace `HR/`, `Atk/`, `AtkMks/`, `Ops/` (masing-masing dengan sub-folder `Admin/`).
- `app/Http/Middleware/` — otorisasi berbasis role/akses: `EnsureRole`, `EnsureAtkAdmin`, `EnsureAtkMksAccess`, `EnsureAtkMksAdmin`, `EnsureOpsAccess`, `EnsureOpsAdmin`, `EnsureLoanRequestEligibility`, `HasSubordinates`, `PreventBrowserCache`.
- `app/Services/` — logika bisnis inti: `LeaveBalanceService`, `LeaveRequestStateMachine`, `LeaveRequestWorkflowService`, `OffSpvQuotaService`, `LeaveRequestDuplicateCleanupService`, `Image/ImageCompressor`.
- `app/Console/Commands/` — command ledger saldo cuti (`InitializeBalanceLedgerCommand`, `BackfillLeaveDeductLedgerCommand`, `LeaveBalanceAuditReportCommand`); command closure (termasuk scheduler) ada di `routes/console.php`.
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
php artisan queue:listen --tries=1   # worker queue development
```

Jangan jalankan `composer run setup` (memicu migrasi — dilarang).

## Testing

- Framework: **Pest 3** (`pestphp/pest` + plugin Laravel). Test ada di `tests/Unit/` dan `tests/Feature/` (ada juga `tests/js/` dan `tests/Support/`).
- Jalankan dengan `php artisan test` atau `vendor/bin/pest` (bisa difilter: `php artisan test --filter=NamaTest`). `composer run test` aman — isinya hanya `config:clear` + `artisan test`.
- `phpunit.xml` mengatur `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `QUEUE_CONNECTION=sync`, `CACHE_STORE=array`, `MAIL_MAILER=array`.
- `tests/TestCase.php` membangun **file SQLite sekali pakai dari migrasi** di `storage/framework/testing-disposable-<kelas>-<pid>.sqlite` sebelum siklus test, memaksa koneksi default ke sqlite, memverifikasi driver/path saat runtime, lalu menghapus file tersebut setelah kelas test selesai — skema test tidak menyentuh MySQL maupun `database/testing.sqlite`. Ada guard yang melempar exception bila env tidak sesuai.
- `tests/Pest.php` mewajibkan `DatabaseTransactions` untuk semua Feature test. **Dilarang** memakai `RefreshDatabase`/`LazilyRefreshDatabase` karena memicu `migrate:fresh`.
- Seeder penting untuk data uji: `ImportEmployeesFromCsvSeeder` (ada Feature test-nya, `ImportEmployeesFromCsvSeederTest`).

## Konvensi

- Bahasa utama di kode (komentar, label UI, pesan error, enum label) adalah **Bahasa Indonesia**; pertahankan itu untuk kode baru.
- Code style: **Laravel Pint** (`vendor/bin/pint`) — tidak ada `pint.json`, ikuti default preset Laravel.
- Penamaan controller mengikuti pola yang ada (mis. `HrLeaveController`, `HREmployeeController` — perhatikan kapitalisasi tidak selalu konsisten; jangan mengganti nama kelas yang sudah ada tanpa diminta).
- Modul ATK / ATK-MKS / OPS sengaja paralel/duplikatif — perubahan pada satu modul sering perlu direplikasi ke modul pasangannya; cek keduanya sebelum mengubah.
- Saldo cuti dikelola lewat ledger (`leave_balance_transactions`) melalui `LeaveBalanceService`; jangan mengubah kolom saldo langsung.
- Hak cuti: hak pertama prorata diberikan tepat saat anniversary pertama (`12 - bulan anniversary`); reset tahunan 12 hari setiap 1 Januari untuk yang sudah lewat anniversary pertama — logika di command `leave:update-balances`.
- Scheduler (`routes/console.php`, timezone `Asia/Jakarta`): `leave:update-balances` harian 00:01, `off-spv:initialize-periods` tiap 1 Januari 00:05, `db:backup` harian 23:59.

## Deployment

Tidak ada CI/CD di repositori ini (`.github/workflows` tidak ada). Deployment adalah proses manual/internal oleh personel berotorisasi, dijelaskan di `README.md` bagian "Deployment Internal": unggah hanya kelompok file yang berubah (`app/`, `resources/`, `routes/`, `config/`, `public/` hasil build, lock file), jalankan test yang relevan, `npm run build` bila sumber CSS/JS berubah, backup dulu, jaga permission `storage/` dan `bootstrap/cache/`, dan pastikan queue worker memakai kode terbaru. File `database/` dan `version/` dikirim hanya sebagai artefak skema untuk proses manual terpisah — bukan untuk dieksekusi lewat Artisan.

## Keamanan

- Jangan pernah membaca, menyalin, atau menuliskan isi `.env` ke output. File contoh yang aman: `.env.example`.
- Kredensial database dibaca dari config/env; command `db:backup` menjalankan `mysqldump` via `exec` dan menulis ke `storage/app/backups/backup-latest.sql` (mode overwrite) — hati-hati saat mengubahnya (risiko command injection bila input tidak terkontrol; ada deteksi path Windows XAMPP hard-coded).
- Otorisasi berbasis role (`UserRole`), middleware `app/Http/Middleware/`, dan policy; jangan bypass middleware/policy yang ada saat menambah route baru.
- Upload gambar selalu lewat `App\Services\Image\ImageCompressor`; ada Feature test khusus upload (`HrEmployeeImageUploadTest`).
- Repositori ini privat; jangan mengekspos kode, konfigurasi, atau data ke layanan publik.

## Aturan dokumentasi fitur (lintas model/agent)

Sebelum membuat atau mengubah PRD, rencana implementasi, atau catatan keputusan, wajib baca dan patuhi [`docs/RULES.md`](docs/RULES.md). Aturan ini berlaku untuk semua model/agent yang bekerja di repositori ini, termasuk saat melanjutkan sesi sebelumnya.

Aturan global ada di [`docs/README.md`](docs/README.md). PRD adalah kontrak WHAT + WHY; detail HOW masuk PLAN. Gunakan ID requirement stabil, jangan mengasumsikan pertanyaan TBD, dan pertahankan perilaku existing kecuali PRD secara eksplisit mengubahnya. Sebelum implementasi, baca dokumentasi sistem dan versi PRD yang disetujui, periksa source/test, lalu buat atau perbarui PLAN. Perubahan requirement harus tercatat dan disetujui, bukan dilakukan diam-diam.

- PRD: `docs/prd/PRD-NNN-nama-fitur.md`.
- Rencana: `docs/plans/PLAN-NNN-nama-fitur.md`.
- Keputusan: `docs/decisions/ADR-NNN-topik-keputusan.md`.
- `NNN` menggunakan nomor mulai `001`; cek dokumen dan register sebelum menetapkan nomor. Ketentuan hubungan nomor PLAN dan PRD ada di `docs/RULES.md`.
- Dokumen `docs/system/` tidak memakai kode bernomor. File template dan indeks bukan dokumen fitur bernomor.
- Simpan dokumen di struktur `docs/`, perbarui register `docs/README.md`, dan pertahankan pengecualian Git yang diminta pengguna.

## Code review graph

Ketika pengguna meminta "cek program secara keseluruhan" atau review proyek luas yang setara:

1. Cek `.code-review-graph/` dulu (berisi `graph.db` dan `graph.html`) dan perbarui bila basi.
2. Gunakan graph untuk mengidentifikasi modul, dependensi, test, dan blast radius sebelum memindai file secara luas.
3. Verifikasi temuan graph terhadap source code dan test aktual; jangan jadikan graph satu-satunya sumber kebenaran.
4. Bila graph atau integrasi MCP tidak tersedia, jatuhkan ke pencarian repositori biasa dan sebutkan fallback tersebut secara singkat.
