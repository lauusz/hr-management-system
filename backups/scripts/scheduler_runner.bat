@echo off
setlocal EnableExtensions

cd /d "%~dp0.."
set "PROJECT_ROOT=%CD%"
set "PHP_EXE=%~1"

if not defined PHP_EXE (
    for /f "delims=" %%I in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%I"
)

if not defined PHP_EXE (
    echo [GAGAL] php.exe tidak ditemukan di PATH.
    exit /b 1
)

if not exist "%PHP_EXE%" (
    echo [GAGAL] php.exe tidak ditemukan: %PHP_EXE%
    exit /b 1
)

if not exist "%PROJECT_ROOT%\artisan" (
    echo [GAGAL] File artisan tidak ditemukan di %PROJECT_ROOT%.
    exit /b 1
)

"%PHP_EXE%" artisan schedule:run
exit /b %ERRORLEVEL%
