@echo off
setlocal EnableExtensions
title Laravel HRD System - Cek Queue Worker

echo ==========================================
echo    CEK QUEUE WORKER
echo ==========================================
echo Pemeriksaan read-only seluruh worker Artisan di komputer ini.
echo Tidak menjalankan atau menghentikan worker maupun mengirim email.
echo.

powershell.exe -NoProfile -Command ^
  "$ErrorActionPreference = 'Stop'; try {" ^
  "  $workers = @(Get-CimInstance Win32_Process -Filter 'Name = ''php.exe''' | Where-Object { $_.CommandLine -match 'artisan\s+queue:(work|listen)\b' });" ^
  "  if ($workers.Count -eq 0) { Write-Host 'Tidak ada worker Artisan yang terdeteksi.' } else {" ^
  "    Write-Host ('Ditemukan {0} worker.' -f $workers.Count);" ^
  "    $workers | Select-Object ProcessId, ParentProcessId, CreationDate, CommandLine | Format-List" ^
  "  }" ^
  "} catch { Write-Host ('Gagal membaca proses: ' + $_.Exception.Message); exit 1 }"

set "QUEUE_CHECK_EXIT=%ERRORLEVEL%"
echo.
pause
exit /b %QUEUE_CHECK_EXIT%
