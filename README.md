# HRD System

Aplikasi web manajemen sumber daya manusia berbasis **Laravel 12.36.1** untuk kebutuhan internal perusahaan logistik.

## Fitur Utama

- **Absensi**: Clock in/out WFO & remote dengan foto dan lokasi GPS.
- **Cuti & Izin**: Pengajuan multi-level approval (supervisor/manager → HRD/HR STAFF).
- **Lembur**: Pengajuan dan approval lembur.
- **Pinjaman/Kasbon**: Pengajuan pinjaman karyawan dengan pencatatan cicilan.
- **Payroll**: Manajemen payslip, import/export Excel, PDF, dan pengiriman email via queue.
- **Master Data**: Divisi, jabatan, shift, lokasi kerja, PT, karyawan, supervisor, dokumen.
- **Robot**: Scheduler harian untuk update saldo cuti dan backup database.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12.36.1 (PHP ^8.2) |
| Frontend | Vite 7.1.12, Tailwind CSS 4.1.16 |
| Database | MySQL (dev/prod), SQLite (testing) |
| Queue / Cache / Session | database driver |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/excel |
| Testing | Pest PHP 3.x |
| Code Style | Laravel Pint |

## Development

```bash
# Setup awal
composer run setup

# Jalankan development server (Laravel + queue + Vite)
composer run dev

# Build production
npm run build

# Jalankan test
composer run test

# Format code
./vendor/bin/pint
```

## Dokumentasi & Audit

Dokumentasi teknis, audit report, dan panduan perbaikan terpusat di:

- [`AGENTS.md`](AGENTS.md) — Panduan umum untuk AI coding agent.
- [`KIMI.md`](KIMI.md) — Entry point khusus Kimi (urutan baca file, aturan UI/UX).
- [`memory.md`](memory.md) — Referensi UI/UX dan design tokens yang sudah fix.
- [`DESIGN.md`](DESIGN.md) — Spesifikasi lengkap design system.
- [`.kimi/HISTORY.md`](.kimi/HISTORY.md) — Status terkini, progress, bug queue, dan rencana perbaikan.
- [`PROJECT_AUDIT_AND_DOCUMENTATION.md`](PROJECT_AUDIT_AND_DOCUMENTATION.md) — Struktur database, flow modul, dan seluruh audit report.

## Lisensi

Internal use only.
