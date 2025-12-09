<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageCompressor
{
    public function storeLeaveSupportingFile(UploadedFile $file, bool $compress = false): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $dir = 'leave_photos';
        $disk = Storage::disk('public');
        $gdLoaded = extension_loaded('gd');

        Log::info('Leave storeSupportingFile called', [
            'compress_flag' => $compress,
            'ext' => $ext,
            'gd_loaded' => $gdLoaded,
        ]);

        if (
            $compress
            && $gdLoaded
            && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)
        ) {
            Log::info('Leave compression branch entered', [
                'ext' => $ext,
            ]);

            try {
                $sourcePath = $file->getPathname();
                $info = getimagesize($sourcePath);
                if ($info === false) {
                    throw new \RuntimeException('Invalid image.');
                }

                $width = $info[0];
                $height = $info[1];

                $maxSide = 720;
                $scale = min($maxSide / max($width, 1), $maxSide / max($height, 1), 1);
                $newWidth = (int) round($width * $scale);
                $newHeight = (int) round($height * $scale);

                switch ($ext) {
                    case 'jpg':
                    case 'jpeg':
                        $srcImage = imagecreatefromjpeg($sourcePath);
                        break;
                    case 'png':
                        $srcImage = imagecreatefrompng($sourcePath);
                        break;
                    case 'webp':
                        if (!function_exists('imagecreatefromwebp')) {
                            throw new \RuntimeException('WEBP not supported.');
                        }
                        $srcImage = imagecreatefromwebp($sourcePath);
                        break;
                    default:
                        $srcImage = null;
                }

                if (!$srcImage) {
                    throw new \RuntimeException('Failed to create image resource.');
                }

                $dstImage = imagecreatetruecolor($newWidth, $newHeight);

                if ($ext === 'png' || $ext === 'webp') {
                    imagealphablending($dstImage, false);
                    imagesavealpha($dstImage, true);
                    $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
                    imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
                }

                imagecopyresampled(
                    $dstImage,
                    $srcImage,
                    0,
                    0,
                    0,
                    0,
                    $newWidth,
                    $newHeight,
                    $width,
                    $height
                );

                $filename = 'leave_' . uniqid('', true) . '.jpg';

                ob_start();
                imagejpeg($dstImage, null, 70);
                $contents = ob_get_clean();

                imagedestroy($srcImage);
                imagedestroy($dstImage);

                if ($contents === false) {
                    throw new \RuntimeException('Failed to encode JPEG.');
                }

                $disk->put($dir . '/' . $filename, $contents);

                Log::info('Leave compression success', [
                    'filename' => $filename,
                    'size_bytes' => strlen($contents),
                ]);

                return $filename;
            } catch (\Throwable $e) {
                Log::warning('Leave photo GD compression failed, fallback to original store', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Leave store fallback original', [
            'ext' => $ext,
        ]);

        $stored = $file->store($dir, 'public');

        Log::info('Leave store fallback stored', [
            'stored' => $stored,
        ]);

        return basename($stored);
    }
}
