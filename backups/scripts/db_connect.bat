@echo off
title MariaDB Console - HRD System
echo ==========================================
echo    MENGHUBUNGKAN KE DATABASE HRD_SYSTEM
echo ==========================================
echo.

:: Parameter -D digunakan untuk langsung memilih database (USE hrd_system)
"C:\Program Files\MariaDB 11.8\bin\mysql.exe" -u root -p123456 -D hrd_system

pause