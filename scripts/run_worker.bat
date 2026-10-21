@echo off
setlocal EnableExtensions
title Laravel HRD System - Queue Worker

cd /d "%~dp0.."

echo ==========================================
echo    QUEUE WORKER HRD-SYSTEM
echo ==========================================
echo Worker emails dan default sedang berjalan.
echo Jangan tutup terminal ini selama worker diperlukan.
echo.

php artisan queue:work --queue=emails,default

echo.
echo Worker berhenti.
pause
