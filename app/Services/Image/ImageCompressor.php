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

    /**
     * Simpan dokumen secara original atau normalisasi foto menjadi JPEG.
     */
    public function compressAndStore(
        UploadedFile $file,
        string $type = 'photo',
        string $folderName = 'uploads',
        string $prefix = 'img_'
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
            return $file->store($folderName, 'public');
        }

        try {
            $contents = in_array($extension, self::HEIC_EXTENSIONS, true)
                ? $this->compressHeicToJpeg($file->getPathname())
                : $this->compressRasterToJpeg($file->getPathname(), $extension);

            $fullPath = $folderName.'/'.$prefix.uniqid('', true).'.jpg';

            if (! Storage::disk('public')->put($fullPath, $contents)) {
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
        [$newWidth, $newHeight] = $this->calculateDimensions($width, $height);

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
        imagejpeg($destinationImage, null, self::JPEG_QUALITY);
        $contents = ob_get_clean();

        imagedestroy($sourceImage);
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

            [$newWidth, $newHeight] = $this->calculateDimensions(
                $image->getImageWidth(),
                $image->getImageHeight()
            );

            $image->thumbnailImage($newWidth, $newHeight, true);
            $image->setImageBackgroundColor('white');
            $image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
            $image->stripImage();
            $image->setImageFormat('jpeg');
            $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $image->setImageCompressionQuality(self::JPEG_QUALITY);

            $contents = $image->getImageBlob();
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
    private function calculateDimensions(int $width, int $height): array
    {
        if ($width <= self::MAX_SIDE && $height <= self::MAX_SIDE) {
            return [$width, $height];
        }

        $scale = min(
            self::MAX_SIDE / max($width, 1),
            self::MAX_SIDE / max($height, 1)
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
