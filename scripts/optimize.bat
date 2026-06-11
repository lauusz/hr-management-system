@echo off
setlocal EnableExtensions
title Laravel HRD System - Optimizer
color 0B

cd /d "%~dp0.."

set "PROJECT_ROOT=%CD%"
set "TASK_NAME=HRD System Queue Worker"
set "PHP_EXE="

for /f "delims=" %%I in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%I"

echo ==========================================
echo    MAINTENANCE HRD-SYSTEM
echo ==========================================
echo Project: %PROJECT_ROOT%
echo.

if not defined PHP_EXE (
    echo [GAGAL] php.exe tidak ditemukan di PATH.
    goto :failed
)

if not exist "%PROJECT_ROOT%\artisan" (
    echo [GAGAL] File artisan tidak ditemukan.
    goto :failed
)

echo PHP: %PHP_EXE%
echo.

echo [1/6] Validasi dukungan HEIC/HEIF...
"%PHP_EXE%" "%~dp0check_heic.php"
if errorlevel 1 (
    echo [PERINGATAN] Upload HEIC akan ditolak sampai Imagick dan codec HEIC/HEIF aktif.
) else (
    echo [OK] Konversi HEIC/HEIF tersedia.
)
echo.

echo [2/6] Membersihkan cache lama...
"%PHP_EXE%" artisan optimize:clear
if errorlevel 1 goto :failed
echo.

echo [3/6] Membuat cache production...
rem Route cache dikecualikan karena project masih memiliki Closure route.
"%PHP_EXE%" artisan optimize --except=routes
if errorlevel 1 goto :failed
echo.

echo [4/6] Meminta worker lama restart dengan aman...
"%PHP_EXE%" artisan queue:restart
if errorlevel 1 goto :failed
echo.

echo [5/6] Mendaftarkan worker agar otomatis aktif setelah server restart...
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0register_queue_worker.ps1" -PhpPath "%PHP_EXE%" -Start
if errorlevel 1 (
    echo [GAGAL] Scheduled Task worker tidak berhasil dibuat.
    echo Jalankan optimize.bat dengan Run as Administrator.
    goto :failed
)
echo.

echo [6/6] Selesai.
echo ------------------------------------------
echo Optimasi Laravel selesai.
echo Worker aktif melalui Scheduled Task:
echo %TASK_NAME%
echo Log worker:
echo %PROJECT_ROOT%\storage\logs\queue-worker.log
echo ------------------------------------------
echo.
pause
exit /b 0

:failed
echo.
echo ------------------------------------------
echo Proses dihentikan karena terjadi kesalahan.
echo ------------------------------------------
echo.
pause
exit /b 1
