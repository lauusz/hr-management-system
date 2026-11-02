<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class AttendanceMessageController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'page' => ['required', 'string', Rule::in([
                'attendance.dashboard', 'attendance.history', 'attendance.clockIn.form', 'attendance.clockOut.form',
                'remote-attendance.index', 'remote-attendance.purpose', 'remote-attendance.photo', 'remote-attendance.clockOut.form',
                'hr.attendances.index', 'hr.attendance-access.index', 'hr.approval_attendance.index', 'supervisor.attendances.index',
            ])],
            'type' => ['required', 'string', Rule::in(['error', 'warning'])],
            'message' => ['required', 'string', 'max:500'],
        ]);

        $messages = [
            'Jadwal rotasi hari ini belum diproses. Hubungi HR atau IT.',
            'Alasan Clock In di luar lokasi absen wajib diisi.',
            'Alasan Clock Out di luar lokasi absen wajib diisi.',
            'Jadwal shift belum diatur. Hubungi HR.',
            'Pembaruan absensi belum tersedia. Hubungi HR atau IT.',
            'Lokasi absen Fleksibel belum diatur. Hubungi HR.',
            'Lokasi kantor belum diatur.',
            'Jadwal presensi berubah. Muat ulang halaman dan coba lagi.',
            'Presensi masuk gagal. Silakan coba lagi.',
            'Presensi keluar gagal. Silakan coba lagi.',
            'Gagal melakukan presensi.',
            'Gagal melakukan presensi keluar.',
            'Terjadi kesalahan pada server.',
            'Terjadi kesalahan koneksi.',
            'Gagal terhubung ke server.',
            'Failed to fetch',
            'Load failed',
            'NetworkError when attempting to fetch resource.',
            'Data gagal dikirim.',
            'The given data was invalid.',
            'Anda tidak berhak menyetujui absensi ini.',
            'Anda tidak berhak mengoreksi absensi ini.',
            'Anda tidak berhak menolak absensi ini.',
            'Absensi yang dipilih tidak lagi dalam status menunggu.',
            'Absensi ini tidak tersedia untuk persetujuan.',
            'Event absensi yang sudah diputus tidak dapat dikoreksi.',
            'Perubahan tipe event gagal.',
            'Pencarian absensi gagal',
            'Hasil pencarian tidak lengkap',
            'Pencarian persetujuan absensi gagal',
            'Hasil pencarian persetujuan absensi tidak lengkap',
            'Keterangan wajib diisi.',
            'Kamera belum siap.',
            'Foto gagal disiapkan.',
            'Kamera hanya dapat diakses melalui HTTPS.',
            'Akses kamera ditolak. Izinkan kamera di browser, lalu muat ulang halaman.',
            'Kamera tidak ditemukan. Sambungkan atau aktifkan kamera, lalu muat ulang halaman.',
            'Kamera tidak tersedia. Periksa kamera dan izin browser, lalu muat ulang halaman.',
            'Gagal akses kamera. Pastikan izin diberikan.',
            'Izinkan akses kamera untuk melanjutkan.',
            'Lokasi hanya dapat diakses melalui HTTPS.',
            'Akses lokasi ditolak. Izinkan lokasi di browser, lalu muat ulang halaman.',
            'Lokasi perangkat tidak tersedia. Aktifkan Layanan Lokasi, lalu muat ulang halaman.',
            'Lokasi belum diperoleh. Pastikan Layanan Lokasi aktif, lalu muat ulang halaman.',
            'Lokasi belum dapat diakses. Periksa izin browser, lalu muat ulang halaman.',
            'Browser ini tidak mendukung akses lokasi.',
            'Browser tidak support GPS.',
            'GPS tidak tersedia',
            'Aktifkan GPS & Izin Lokasi di Browser.',
            'Izinkan akses lokasi untuk melanjutkan.',
            'Aktifkan Lokasi Presisi sebelum melakukan presensi.',
            'Akurasi lokasi terlalu lemah. Aktifkan Lokasi Presisi, lalu coba lagi.',
            'Aktifkan Lokasi Presisi agar akurasi lokasi maksimal ±100 m.',
            'Aktifkan Lokasi Presisi agar akurasi lokasi maksimal ±100 m, lalu tekan Coba Lagi.',
        ];

        $message = $data['message'];
        if (! in_array($message, $messages, true)
            && ! preg_match('/\AAnda (?:berada di luar radius kantor|harus berada di kantor untuk melakukan presensi keluar) \(\d{1,8} m\)\.\z/u', $message)
            && ! preg_match('/\AAnda sudah Clock (?:In|Out) pada pukul \d{2}:\d{2}\.\z/u', $message)) {
            $message = 'Pesan kendala absensi lainnya.';
        }

        $user = $request->user();
        $name = preg_replace('/[\x00-\x1F\x7F|]+/u', ' ', $user->name);
        $path = parse_url(route($data['page'], [], false), PHP_URL_PATH);
        $line = sprintf("[%s WIB] %s | %s (ID %s) | %s | %s\n", now('Asia/Jakarta')->format('d-m-Y H:i:s'), $path, $name, $user->id, strtoupper($data['type']), $message);

        try {
            file_put_contents(storage_path('logs/messages.log'), $line, FILE_APPEND | LOCK_EX);
        } catch (Throwable) {
            // Kegagalan log tidak boleh menghambat presensi.
        }

        return response()->noContent();
    }
}
