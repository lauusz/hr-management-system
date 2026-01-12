<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageCompressor
{
    /**
     * FUNGSI UTAMA: Kompresi Gambar ala WhatsApp.
     * Digunakan oleh AttendanceController (dan kode baru lainnya).
     * * @param UploadedFile $file File dari request
     * @param string $type 'photo' (Resize & Compress) atau 'document' (Original)
     * @param string $folderName Nama folder tujuan (contoh: 'attendance_photos', 'leave_photos')
     * @param string $prefix Prefix nama file (contoh: 'att_', 'leave_')
     * @return string Path lengkap file yang disimpan (contoh: 'attendance_photos/att_123.jpg')
     */
    public function compressAndStore(
        UploadedFile $file, 
        string $type = 'photo', 
        string $folderName = 'uploads',
        string $prefix = 'img_'
    ): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $disk = Storage::disk('public');
        $gdLoaded = extension_loaded('gd');

        // Logic: Compress hanya jika GD ada, tipe 'photo', dan ekstensi gambar valid
        $shouldCompress = ($type === 'photo' 
            && $gdLoaded 
            && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true));

        Log::info("ImageCompressor: Processing file for {$folderName}", [
            'type' => $type,
            'compress' => $shouldCompress
        ]);

        if ($shouldCompress) {
            try {
                $sourcePath = $file->getPathname();
                $info = getimagesize($sourcePath);
                if ($info === false) throw new \RuntimeException('Invalid image.');

                $width = $info[0];
                $height = $info[1];

                // --- SETTINGAN ALA WHATSAPP (1280px) ---
                $maxSide = 1280; 

                // Hitung skala resize (hanya downscale, jangan upscale)
                if ($width > $maxSide || $height > $maxSide) {
                    $scale = min($maxSide / max($width, 1), $maxSide / max($height, 1), 1);
                    $newWidth = (int) round($width * $scale);
                    $newHeight = (int) round($height * $scale);
                } else {
                    $newWidth = $width;
                    $newHeight = $height;
                }

                switch ($ext) {
                    case 'jpg': case 'jpeg': $srcImage = imagecreatefromjpeg($sourcePath); break;
                    case 'png': $srcImage = imagecreatefrompng($sourcePath); break;
                    case 'webp': 
                        if (!function_exists('imagecreatefromwebp')) throw new \RuntimeException('WEBP not supported.');
                        $srcImage = imagecreatefromwebp($sourcePath); break;
                    default: $srcImage = null;
                }

                if (!$srcImage) throw new \RuntimeException('Failed to create resource.');

                $dstImage = imagecreatetruecolor($newWidth, $newHeight);

                // Handle transparency (PNG/WebP) -> Ubah jadi background putih sebelum convert JPG
                if ($ext === 'png' || $ext === 'webp') {
                    $white = imagecolorallocate($dstImage, 255, 255, 255);
                    imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $white);
                }

                imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                // Generate nama file baru (JPG)
                $filename = $prefix . uniqid('', true) . '.jpg';

                ob_start();
                // Quality 75 (Standard WA: Hemat tapi tajam)
                imagejpeg($dstImage, null, 75); 
                $contents = ob_get_clean();

                imagedestroy($srcImage);
                imagedestroy($dstImage);

                if ($contents === false) throw new \RuntimeException('Failed to encode JPEG.');

                // Simpan hasil kompresi
                $fullPath = $folderName . '/' . $filename;
                $disk->put($fullPath, $contents);

                return $fullPath;

            } catch (\Throwable $e) {
                Log::warning('ImageCompressor failed, fallback to original', ['msg' => $e->getMessage()]);
            }
        }

        // --- FALLBACK / DOCUMENT MODE ---
        // Simpan file asli tanpa ubah apa-apa
        $storedPath = $file->store($folderName, 'public');
        return $storedPath; // Mengembalikan path lengkap
    }

    /**
     * WRAPPER UNTUK LEAVE REQUEST (Backward Compatibility).
     * Agar LeaveRequestController yang lama TIDAK ERROR.
     * * @param UploadedFile $file
     * @param bool $compress (Mapping: true -> 'photo', false -> 'document')
     * @return string Hanya mengembalikan nama file (basename), bukan full path.
     */
    public function storeLeaveSupportingFile(UploadedFile $file, bool $compress = false): string
    {
        // 1. Map parameter lama (boolean) ke parameter baru (string)
        $type = $compress ? 'photo' : 'document';
        
        // 2. Panggil fungsi utama yang baru
        $fullPath = $this->compressAndStore($file, $type, 'leave_photos', 'leave_');

        // 3. Karena Controller Cuti hanya menyimpan nama file di database (bukan path), 
        // kita ambil basename-nya saja.
        return basename($fullPath);
    }
}