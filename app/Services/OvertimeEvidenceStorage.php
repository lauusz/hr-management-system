<?php

namespace App\Services;

use App\Services\Image\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OvertimeEvidenceStorage
{
    public function __construct(private ImageCompressor $imageCompressor) {}

    public function store(UploadedFile $file): array
    {
        $temporaryPath = null;
        $storedPath = null;

        try {
            $temporaryPath = $this->imageCompressor->compressAndStore(
                $file, 'photo', 'overtime-evidence-tmp', 'overtime_', 'local'
            );
            $contents = Storage::disk('local')->get($temporaryPath);
            $storedPath = 'overtime_evidence/'.Str::uuid().'.enc';

            if (! Storage::disk('overtime_evidence')->put($storedPath, Crypt::encryptString($contents))) {
                throw new \RuntimeException('Gagal menyimpan bukti lembur.');
            }

            $result = [
                'file_path' => $storedPath,
                'original_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255),
                'mime_type' => str_ends_with($temporaryPath, '.jpg') ? 'image/jpeg' : ($file->getMimeType() ?: 'application/octet-stream'),
            ];

            if (! Storage::disk('local')->delete($temporaryPath)) {
                throw new \RuntimeException('Gagal membersihkan file sementara bukti lembur.');
            }
            $temporaryPath = null;

            return $result;
        } catch (\Throwable $exception) {
            if ($storedPath !== null) {
                try {
                    Storage::disk('overtime_evidence')->delete($storedPath);
                } catch (\Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        } finally {
            if ($temporaryPath !== null) {
                Storage::disk('local')->delete($temporaryPath);
            }
        }
    }

    public function read(string $path): string
    {
        return Crypt::decryptString(Storage::disk('overtime_evidence')->get($path));
    }

    public function delete(string $path): void
    {
        Storage::disk('overtime_evidence')->delete($path);
    }
}
