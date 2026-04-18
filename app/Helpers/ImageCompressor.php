<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageCompressor
{
    private float $maxSizeMB;

    public function __construct(float $maxSizeMB = 1.9)
    {
        $this->maxSizeMB = $maxSizeMB;
    }

    public function compress(UploadedFile $file): UploadedFile
    {
        if (!str_starts_with($file->getMimeType(), 'image/')) {
            return $file;
        }

        $fileSizeMB = $file->getSize() / 1024 / 1024;
        if ($fileSizeMB <= $this->maxSizeMB) {
            return $file;
        }

        $sourceImage = $this->createImageFromFile($file);
        if (!$sourceImage) {
            return $file;
        }

        $targetSizeBytes = $this->maxSizeMB * 1024 * 1024;
        $tolerance = 0.05 * 1024 * 1024;

        $minQuality = 50;
        $maxQuality = 98;
        $bestPath = null;

        $tempDir = sys_get_temp_dir();

        for ($i = 0; $i < 8; $i++) {
            $quality = (int) (($minQuality + $maxQuality) / 2);

            $tempPath = $tempDir . '/' . uniqid('test_') . '.jpg';
            imagejpeg($sourceImage, $tempPath, $quality);

            $encodedSize = filesize($tempPath);

            if ($encodedSize <= $targetSizeBytes) {
                if ($bestPath && file_exists($bestPath)) {
                    @unlink($bestPath);
                }
                $bestPath = $tempPath;
                $minQuality = $quality;

                if ($encodedSize >= $targetSizeBytes - $tolerance) {
                    break;
                }
            } else {
                @unlink($tempPath);
                $maxQuality = $quality;
            }
        }

        if (!$bestPath) {
            $bestPath = $tempDir . '/' . uniqid('compressed_') . '.jpg';
            imagejpeg($sourceImage, $bestPath, 50);
        }

        $finalPath = $tempDir . '/' . uniqid('final_') . '_' . $file->getClientOriginalName();
        rename($bestPath, $finalPath);

        return new UploadedFile(
            $finalPath,
            $file->getClientOriginalName(),
            'image/jpeg',
            null,
            true
        );
    }

    private function createImageFromFile(UploadedFile $file)
    {
        $mimeType = $file->getMimeType();

        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/gif' => imagecreatefromgif($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default => false,
        };
    }

    public static function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
