@echo off
setlocal EnableExtensions
title Laravel HRD System - Optimizer

cd /d "%~dp0.."

echo ==========================================
echo    OPTIMASI HRD-SYSTEM
echo ==========================================
echo.

echo [1/2] Membersihkan cache lama...
php artisan optimize:clear
if errorlevel 1 goto :failed
echo.

echo [2/2] Membuat cache konfigurasi...
php artisan config:cache
if errorlevel 1 goto :failed
echo.

echo ------------------------------------------
echo Optimasi selesai.
echo ------------------------------------------
pause
exit /b 0

:failed
echo.
echo [GAGAL] Optimasi dihentikan.
pause
exit /b 1
