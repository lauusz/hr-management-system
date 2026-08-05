# ATK Report Dashboard Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Merombak `/v2/atk/admin/reports` menjadi dashboard ringkas yang mudah dibaca pada mobile dan desktop tanpa mengubah data laporan.

**Architecture:** Pertahankan query dan controller yang ada. Ubah hanya struktur Blade serta CSS lokal halaman report, lalu kunci perilaku penting melalui feature test yang sudah tersedia.

**Tech Stack:** Laravel Blade, CSS native, elemen HTML `<details>`, Pest feature tests.

## Global Constraints

- Mobile-first dan tetap mengikuti warna serta komponen modul ATK.
- Tidak menambah dependency, JavaScript chart, migrasi, atau perubahan database.
- Tidak mengubah definisi data maupun isi Export Excel.
- Riwayat pengambilan tertutup secara default dan dapat dibuka tanpa JavaScript.
- Filter aktif tetap diteruskan ke tautan Export Excel.

---

### Task 1: Kunci Struktur Dashboard Ringkas dengan Feature Test

**Files:**
- Modify: `tests/Feature/AtkV2Test.php:900-990`

**Interfaces:**
- Consumes: route `v2.atk.admin.reports.index` dan HTML dari view report.
- Produces: assertion untuk copy, bar PT, riwayat native, dan penghapusan elemen lama.

- [ ] **Step 1: Ubah test dashboard agar menuntut struktur baru**

```php
->assertSeeInOrder([
    'Rekap ATK',
    'Pengajuan per PT',
    'Barang Terbanyak',
    'Sering Mengambil',
    'Lihat Riwayat Pengambilan',
])
->assertSee('atk-report-pt-bars')
->assertSee('<details class="atk-card atk-report-history">', false)
->assertDontSee('atk-report-donut')
->assertDontSee('Ringkasan Eksekutif')
->assertDontSee('Laporan Manajemen');
```

- [ ] **Step 2: Jalankan test untuk memastikan gagal terhadap tampilan lama**

Run: `php artisan test tests\Feature\AtkV2Test.php --filter="filters usage report by pt|renders an informative pt report dashboard"`

Expected: FAIL karena copy dan struktur dashboard ringkas belum tersedia.

- [ ] **Step 3: Commit test merah bersama implementasi Task 2**

Test belum di-commit sendiri agar branch utama tidak ditinggalkan dalam keadaan gagal.

---

### Task 2: Rombak Blade dan CSS Halaman Report

**Files:**
- Modify: `resources/views/atk/admin/reports/index.blade.php:1-410`
- Test: `tests/Feature/AtkV2Test.php`

**Interfaces:**
- Consumes: `$periodLabel`, `$selectedPtName`, `$generatedAt`, `$summary`, `$ptRows`, `$itemRows`, `$requesterRows`, dan `$detailRows` dari `ReportController@index`.
- Produces: HTML dashboard ringkas tanpa mengubah kontrak controller.

- [ ] **Step 1: Ganti hero dan filter dengan header ringkas**

Gunakan judul `Rekap ATK`, tampilkan periode/cakupan sebagai teks ringkas, pindahkan `Unduh Excel` ke header, dan pertahankan form GET bulan/PT dengan tombol `Tampilkan` serta `Reset`.

- [ ] **Step 2: Sederhanakan kartu ringkasan**

Pertahankan empat nilai `$summary`, tetapi gunakan label `Pengajuan`, `Pengambil`, `PT`, dan `Jenis Barang` tanpa header bernomor atau keterangan berulang.

- [ ] **Step 3: Ganti donut PT menjadi bar horizontal**

```blade
@php($maxPtRequests = max(1, (int) $ptRows->max('request_count')))
<div class="atk-report-pt-bars">
    @foreach($ptRows as $row)
        <div class="atk-report-bar-row" data-pt-name="{{ $row->pt_name_snapshot ?? '-' }}">
            <div class="atk-report-bar-label">
                <strong>{{ $row->pt_name_snapshot ?? '-' }}</strong>
                <span>{{ $row->request_count }} pengajuan · {{ number_format($row->percentage, 1, ',', '.') }}%</span>
            </div>
            <div class="atk-report-bar-track" aria-hidden="true">
                <span style="--bar-width: {{ round(((int) $row->request_count / $maxPtRequests) * 100, 1) }}%"></span>
            </div>
        </div>
    @endforeach
</div>
```

- [ ] **Step 4: Ringkas grafik barang dan ranking nama**

Ubah judul menjadi `Barang Terbanyak`, pertahankan maksimal 10 bar dari controller, dan pertahankan ranking nama beserta jumlah pengajuannya.

- [ ] **Step 5: Bungkus riwayat dalam elemen native**

```blade
<details class="atk-card atk-report-history">
    <summary>Lihat Riwayat Pengambilan</summary>
    <div class="atk-report-history-content">
        <div class="atk-table-wrap atk-report-table-wrap">
            <table class="atk-table atk-report-table">
                <thead><tr><th>Tanggal</th><th>No. Request</th><th>Nama Pengambil</th><th>PT</th><th>Barang</th><th>Qty</th></tr></thead>
                <tbody>
                    @forelse($detailRows as $row)
                        <tr>
                            <td data-label="Tanggal">{{ $row->approved_at ? \Carbon\Carbon::parse($row->approved_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td data-label="No. Request">{{ $row->request_number }}</td>
                            <td data-label="Nama Pengambil">{{ $row->user_name_snapshot }}</td>
                            <td data-label="PT">{{ $row->pt_name_snapshot ?? '-' }}</td>
                            <td data-label="Barang"><strong>{{ $row->item_name_snapshot }}</strong></td>
                            <td data-label="Qty">{{ $row->qty }} {{ $row->unit_name_snapshot }}</td>
                        </tr>
                    @empty
                        <tr class="atk-report-empty"><td colspan="6">Belum ada riwayat pengambilan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</details>
```

- [ ] **Step 6: Tulis CSS responsif minimum**

Hapus style donut, legenda, hero besar, nomor bagian, dan card bertingkat. Pertahankan breakpoint mobile `max-width: 639px`, grid desktop `min-width: 768px`, tinggi kontrol minimum 44px, serta pola tabel-ke-kartu yang sudah ada.

- [ ] **Step 7: Jalankan test terarah sampai hijau**

Run: `php artisan test tests\Feature\AtkV2Test.php --filter="filters usage report by pt|renders an informative pt report dashboard"`

Expected: PASS, 2 tests.

- [ ] **Step 8: Periksa format**

Run: `vendor\bin\pint --test tests\Feature\AtkV2Test.php`

Expected: PASS.

- [ ] **Step 9: Commit implementasi**

```bash
git add resources/views/atk/admin/reports/index.blade.php tests/Feature/AtkV2Test.php
git commit -m "feat: simplify atk report dashboard"
```

---

### Task 3: Verifikasi Regresi Modul ATK

**Files:**
- Verify: `resources/views/atk/admin/reports/index.blade.php`
- Verify: `tests/Feature/AtkV2Test.php`

**Interfaces:**
- Consumes: hasil Task 2.
- Produces: bukti bahwa perubahan report tidak merusak fitur ATK lain.

- [ ] **Step 1: Jalankan seluruh feature test ATK**

Run: `php artisan test tests\Feature\AtkV2Test.php --compact`

Expected: seluruh test lulus tanpa failure.

- [ ] **Step 2: Periksa istilah dan struktur lama**

Run: `rg -n "atk-report-donut|Laporan Manajemen|Ringkasan Eksekutif|Barang Paling Banyak Diambil|Detail Transaksi" resources\views\atk\admin\reports\index.blade.php tests\Feature\AtkV2Test.php`

Expected: tidak ada hasil.

- [ ] **Step 3: Periksa diff**

Run: `git diff --check`

Expected: exit code 0 tanpa output.
