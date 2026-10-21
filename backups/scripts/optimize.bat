@echo off
title Laravel HRD System - Optimizer
:: Berpindah ke folder utama (mundur 1 tingkat dari folder scripts)
cd /d "%~dp0.."

echo ==========================================
echo    MAINTENANCE HRD-SYSTEM
echo ==========================================
echo.

:: 1. Membersihkan Cache & Optimasi
echo [1/3] Menjalankan optimize:clear...
php artisan optimize:clear
echo.
echo [2/3] Menjalankan config:cache...
php artisan config:cache
echo.

:: 2. Menghapus antrean yang gagal
echo [3/3] Menjalankan queue:flush...
php artisan queue:flush
echo.

echo ------------------------------------------
echo Optimasi Dasar Selesai!
echo ------------------------------------------
echo.

:: 3. Menjalankan Queue Work
echo Memulai Queue Worker (emails, default)...
echo (Terminal ini akan terus terbuka selama worker aktif)
echo.
php artisan queue:work --queue=emails,default

pause