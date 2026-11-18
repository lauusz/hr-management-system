@echo off
setlocal EnableExtensions DisableDelayedExpansion
title Laravel HRD System - Cek Antrean Jobs

echo ==========================================
echo    CEK TABEL JOBS - HRD_SYSTEM
echo ==========================================
echo Hanya membaca antrean pada database lokal.
echo Tidak menjalankan worker, memproses job, atau mengirim email.
echo.

set "QUEUE_MYSQL_EXE=C:\Program Files\MariaDB 11.8\bin\mysql.exe"
if not exist "%QUEUE_MYSQL_EXE%" set "QUEUE_MYSQL_EXE=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe"
if not exist "%QUEUE_MYSQL_EXE%" (
    echo Client MySQL/MariaDB tidak ditemukan. Sesuaikan QUEUE_MYSQL_EXE pada script ini.
    pause
    exit /b 1
)

echo Masukkan password database root. Jika kosong, langsung tekan Enter.
echo.

set "QUEUE_JOBS_SQL=SELECT DATABASE() AS database_name, @@hostname AS database_host, @@session.time_zone AS session_timezone, @@system_time_zone AS system_timezone;"
set "QUEUE_JOBS_SQL=%QUEUE_JOBS_SQL% SELECT COUNT(*) AS total_jobs FROM jobs;"
set "QUEUE_JOBS_SQL=%QUEUE_JOBS_SQL% SELECT queue, COUNT(*) AS total, SUM(reserved_at IS NULL AND available_at <= UNIX_TIMESTAMP()) AS siap_diproses, SUM(reserved_at IS NULL AND available_at > UNIX_TIMESTAMP()) AS menunggu_jadwal, SUM(reserved_at IS NOT NULL) AS direservasi FROM jobs GROUP BY queue ORDER BY queue;"
set "QUEUE_JOBS_SQL=%QUEUE_JOBS_SQL% SELECT id, queue, attempts, CASE WHEN reserved_at IS NOT NULL THEN 'Direservasi' WHEN available_at > UNIX_TIMESTAMP() THEN 'Menunggu jadwal' ELSE 'Siap diproses' END AS status_antrean, FROM_UNIXTIME(created_at) AS dibuat, FROM_UNIXTIME(available_at) AS tersedia_mulai, FROM_UNIXTIME(reserved_at) AS direservasi_pada FROM jobs ORDER BY id DESC LIMIT 50;"

"%QUEUE_MYSQL_EXE%" --host=localhost --port=3306 --user=root --password --database=hrd_system --table --execute="%QUEUE_JOBS_SQL%"
set "QUEUE_JOBS_EXIT=%ERRORLEVEL%"

echo.
if not "%QUEUE_JOBS_EXIT%"=="0" echo Pemeriksaan gagal. Periksa pesan koneksi atau SQL di atas.
echo Daftar dibatasi 50 job terbaru; ringkasan mencakup seluruh tabel jobs.
echo Status direservasi tidak menjamin worker masih aktif.
echo Job yang selesai biasanya dihapus dari jobs; ini bukan riwayat email terkirim.
echo Waktu mengikuti zona sesi database yang ditampilkan di atas.
echo.
pause
exit /b %QUEUE_JOBS_EXIT%
