# HR Leave Request Card Grid Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menampilkan tiga card pengajuan cuti per baris pada desktop lebar.

**Architecture:** Gunakan CSS grid dan breakpoint yang sudah ada pada view. Tambahkan satu media query tanpa mengubah markup atau alur backend.

**Tech Stack:** Laravel Blade, CSS.

## Global Constraints

- Mobile tetap satu kolom.
- Mulai `1024px` tetap dua kolom.
- Mulai `1280px` menjadi tiga kolom.
- Tidak ada perubahan database.

---

### Task 1: Tambahkan breakpoint desktop lebar

**Files:**
- Modify: `resources/views/hr/leave_requests/index.blade.php`

- [ ] **Step 1: Tambahkan media query minimal**

```css
@media (min-width: 1280px) {
    .apv-list {
        grid-template-columns: repeat(3, 1fr);
    }
}
```

- [ ] **Step 2: Verifikasi halaman**

Run: `php artisan test tests/Feature/HrLeaveControllerTest.php`

Expected: seluruh pengujian file lulus.

- [ ] **Step 3: Periksa diff**

Run: `git diff --check`

Expected: tidak ada error whitespace.

