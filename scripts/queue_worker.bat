@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HRD System Queue Worker

cd /d "%~dp0.."

set "PROJECT_ROOT=%CD%"
set "PHP_EXE=%~1"
set "LOG_DIR=%PROJECT_ROOT%\storage\logs"
set "LOG_FILE=%LOG_DIR%\queue-worker.log"

if not defined PHP_EXE (
    for /f "delims=" %%I in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%I"
)

if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

if not defined PHP_EXE (
    >> "%LOG_FILE%" echo [%DATE% %TIME%] GAGAL: php.exe tidak ditemukan.
    exit /b 1
)

if not exist "%PROJECT_ROOT%\artisan" (
    >> "%LOG_FILE%" echo [%DATE% %TIME%] GAGAL: File artisan tidak ditemukan.
    exit /b 1
)

:worker_loop
>> "%LOG_FILE%" echo [%DATE% %TIME%] Memulai queue worker...

"%PHP_EXE%" artisan queue:work ^
    --queue=emails,default ^
    --sleep=3 ^
    --tries=3 ^
    --backoff=10 ^
    --timeout=60 ^
    --memory=256 ^
    --max-time=3600 >> "%LOG_FILE%" 2>&1

set "WORKER_EXIT=!ERRORLEVEL!"
>> "%LOG_FILE%" echo [%DATE% %TIME%] Worker berhenti dengan exit code !WORKER_EXIT!. Restart 10 detik lagi.

timeout /t 10 /nobreak >nul
goto :worker_loop
