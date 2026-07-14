@echo off
title MariaDB Backup - HRD System
color 0E

:: --- CONFIGURATION ---
set DB_USER=root
set DB_PASS=%HRD_DB_PASS%
set DB_NAME=hrd_system
set DUMP_PATH="C:\Program Files\MariaDB 11.8\bin\mysqldump.exe"
set BACKUP_DIR=C:\apps\hrd-system\backups
:: ---------------------

if "%DB_PASS%"=="" (
    echo [GAGAL] Environment variable HRD_DB_PASS belum diatur.
    exit /b 1
)

:: Buat folder backup jika belum ada
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

:: Membuat format nama file: hrd_system_YYYYMMDD_HHMM.sql
set TIMESTAMP=%DATE:~10,4%%DATE:~4,2%%DATE:~7,2%_%TIME:~0,2%%TIME:~3,2%
set TIMESTAMP=%TIMESTAMP: =0%
set FILENAME=%DB_NAME%_%TIMESTAMP%.sql

echo ==========================================
echo    MEMULAI BACKUP DATABASE: %DB_NAME%
echo ==========================================
echo Lokasi: %BACKUP_DIR%\%FILENAME%
echo.

:: Eksekusi backup
%DUMP_PATH% -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%BACKUP_DIR%\%FILENAME%"

if %ERRORLEVEL% equ 0 (
    echo.
    echo [BERHASIL] Database telah diexport!
) else (
    echo.
    echo [GAGAL] Terjadi kesalahan saat backup.
)

echo ==========================================
echo.
pause
