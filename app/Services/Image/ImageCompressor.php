<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ImageCompressor
{
    private const COMPRESSIBLE_IMAGE_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'heic',
        'heif',
    ];

    private const HEIC_EXTENSIONS = ['heic', 'heif'];

    private const MAX_SIDE = 1280;

    private const JPEG_QUALITY = 75;

    private const MAX_FILE_SIZE_BYTES = 70 * 1024;

    private const JPEG_QUALITIES = [self::JPEG_QUALITY, 65, 55, 45, 35];

    private const MAX_SIDE_CANDIDATES = [1280, 1024, 896, 768, 640, 512, 384, 256, 192, 128];

    /**
     * Simpan dokumen secara original atau normalisasi foto menjadi JPEG.
     */
    public function compressAndStore(
        UploadedFile $file,
        string $type = 'photo',
        string $folderName = 'uploads',
        string $prefix = 'img_',
        string $disk = 'public'
    ): string {
        $extension = strtolower($file->getClientOriginalExtension());
        $shouldCompress = $type === 'photo'
            && in_array($extension, self::COMPRESSIBLE_IMAGE_EXTENSIONS, true);

        Log::info("ImageCompressor: Processing file for {$folderName}", [
            'type' => $type,
            'extension' => $extension,
            'compress' => $shouldCompress,
        ]);

        if (! $shouldCompress) {
            return $file->store($folderName, $disk);
        }

        try {
            $contents = in_array($extension, self::HEIC_EXTENSIONS, true)
                ? $this->compressHeicToJpeg($file->getPathname())
                : $this->compressRasterToJpeg($file->getPathname(), $extension);

            $fullPath = $folderName.'/'.$prefix.uniqid('', true).'.jpg';

            if (! Storage::disk($disk)->put($fullPath, $contents)) {
                throw new \RuntimeException('Gagal menyimpan hasil kompresi gambar.');
            }

            return $fullPath;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::warning('ImageCompressor gagal memproses gambar.', [
                'extension' => $extension,
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'photo' => 'Foto gagal diproses. Gunakan file gambar yang valid atau ubah ke JPG terlebih dahulu.',
            ]);
        }
    }

    private function compressRasterToJpeg(string $sourcePath, string $extension): string
    {
        if (! extension_loaded('gd')) {
            throw new \RuntimeException('Ekstensi GD belum aktif.');
        }

        $info = getimagesize($sourcePath);

        if ($info === false) {
            throw new \RuntimeException('File bukan gambar yang valid.');
        }

        [$width, $height] = $info;

        $sourceImage = match ($extension) {
            'jpg', 'jpeg' => imagecreatefromjpeg($sourcePath),
            'png' => imagecreatefrompng($sourcePath),
            'webp' => function_exists('imagecreatefromwebp')
                ? imagecreatefromwebp($sourcePath)
                : false,
            default => false,
        };

        if (! $sourceImage) {
            throw new \RuntimeException('Format gambar tidak didukung GD.');
        }

        try {
            return $this->compressRasterImageToTarget($sourceImage, $width, $height);
        } finally {
            imagedestroy($sourceImage);
        }
    }

    private function compressRasterImageToTarget($sourceImage, int $width, int $height): string
    {
        $lastDimensions = null;
        $smallestContents = '';

        foreach (self::MAX_SIDE_CANDIDATES as $maxSide) {
            [$newWidth, $newHeight] = $this->calculateDimensions($width, $height, $maxSide);

            if ($lastDimensions === [$newWidth, $newHeight]) {
                continue;
            }

            $lastDimensions = [$newWidth, $newHeight];

            foreach (self::JPEG_QUALITIES as $quality) {
                $contents = $this->encodeRasterJpeg($sourceImage, $width, $height, $newWidth, $newHeight, $quality);

                if ($smallestContents === '' || strlen($contents) < strlen($smallestContents)) {
                    $smallestContents = $contents;
                }

                if (strlen($contents) <= self::MAX_FILE_SIZE_BYTES) {
                    return $contents;
                }
            }
        }

        return $this->ensureMaximumFileSize($smallestContents);
    }

    private function encodeRasterJpeg($sourceImage, int $width, int $height, int $newWidth, int $newHeight, int $quality): string
    {
        $destinationImage = imagecreatetruecolor($newWidth, $newHeight);
        $white = imagecolorallocate($destinationImage, 255, 255, 255);
        imagefilledrectangle($destinationImage, 0, 0, $newWidth, $newHeight, $white);

        imagecopyresampled(
            $destinationImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        ob_start();
        imagejpeg($destinationImage, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($destinationImage);

        if ($contents === false) {
            throw new \RuntimeException('Gagal menghasilkan file JPEG.');
        }

        return $contents;
    }

    private function compressHeicToJpeg(string $sourcePath): string
    {
        if (! $this->supportsHeicConversion()) {
            throw ValidationException::withMessages([
                'photo' => 'File HEIC belum dapat diproses karena server belum memiliki Imagick dengan dukungan HEIC/HEIF.',
            ]);
        }

        $source = new \Imagick;

        try {
            $source->readImage($sourcePath);
            $source->setIteratorIndex(0);
            $image = $source->getImage();

            if (method_exists($image, 'autoOrientImage')) {
                $image->autoOrientImage();
            }

            $contents = $this->compressImagickImageToTarget($image);
            $image->clear();
            $image->destroy();

            if ($contents === '') {
                throw new \RuntimeException('Gagal menghasilkan JPEG dari HEIC.');
            }

            return $contents;
        } finally {
            $source->clear();
            $source->destroy();
        }
    }

    private function compressImagickImageToTarget(\Imagick $image): string
    {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $lastDimensions = null;
        $smallestContents = '';

        foreach (self::MAX_SIDE_CANDIDATES as $maxSide) {
            [$newWidth, $newHeight] = $this->calculateDimensions($width, $height, $maxSide);

            if ($lastDimensions === [$newWidth, $newHeight]) {
                continue;
            }

            $lastDimensions = [$newWidth, $newHeight];

            foreach (self::JPEG_QUALITIES as $quality) {
                $candidate = clone $image;
                $candidate->thumbnailImage($newWidth, $newHeight, true);
                $candidate->setImageBackgroundColor('white');
                $candidate->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $candidate->stripImage();
                $candidate->setImageFormat('jpeg');
                $candidate->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $candidate->setImageCompressionQuality($quality);
                $contents = $candidate->getImageBlob();
                $candidate->clear();
                $candidate->destroy();

                if ($smallestContents === '' || strlen($contents) < strlen($smallestContents)) {
                    $smallestContents = $contents;
                }

                if (strlen($contents) <= self::MAX_FILE_SIZE_BYTES) {
                    return $contents;
                }
            }
        }

        return $this->ensureMaximumFileSize($smallestContents);
    }

    private function ensureMaximumFileSize(string $contents): string
    {
        if ($contents === '' || strlen($contents) > self::MAX_FILE_SIZE_BYTES) {
            throw new \RuntimeException('Foto tidak dapat dikompres hingga ukuran maksimum 70 KB.');
        }

        return $contents;
    }

    protected function supportsHeicConversion(): bool
    {
        if (! class_exists(\Imagick::class)) {
            return false;
        }

        $formats = array_map('strtoupper', \Imagick::queryFormats('HEI*'));

        return in_array('HEIC', $formats, true) || in_array('HEIF', $formats, true);
    }

    /**
     * @return array{int, int}
     */
    private function calculateDimensions(int $width, int $height, int $maxSide = self::MAX_SIDE): array
    {
        if ($width <= $maxSide && $height <= $maxSide) {
            return [$width, $height];
        }

        $scale = min(
            $maxSide / max($width, 1),
            $maxSide / max($height, 1)
        );

        return [
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale)),
        ];
    }

    /**
     * Wrapper kompatibilitas untuk leave request lama.
     */
    public function storeLeaveSupportingFile(UploadedFile $file, bool $compress = false): string
    {
        $type = $compress ? 'photo' : 'document';
        $fullPath = $this->compressAndStore($file, $type, 'leave_photos', 'leave_');

        return basename($fullPath);
    }
}
