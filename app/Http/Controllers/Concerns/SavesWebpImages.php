<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait SavesWebpImages
{
    /**
     * Memproses, mengompres, dan menyimpan gambar ke format WebP menggunakan GD bawaan PHP.
     */
    protected function storeImageAsWebp(UploadedFile $file, string $subDirectory, int $maxWidth = 1200, string $prefix = ''): string
    {
        $dir = storage_path('app/public/' . $subDirectory);
        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $slugPrefix = $prefix ? Str::limit(Str::slug($prefix), 60, '') . '-' : '';
        $filename = $slugPrefix . time() . '-' . Str::random(5) . '.webp';
        $targetPath = $dir . '/' . $filename;
        $pathname = $file->getPathname();
        $mime = $file->getMimeType();

        $source = null;
        try {
            $source = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($pathname),
                'image/png' => @imagecreatefrompng($pathname),
                'image/webp' => @imagecreatefromwebp($pathname),
                default => null,
            };
        } catch (\Throwable $e) {
            $source = null;
        }

        if ($source) {
            $origWidth = imagesx($source);
            $origHeight = imagesy($source);

            if ($origWidth > $maxWidth) {
                $newHeight = (int) round(($origHeight / $origWidth) * $maxWidth);
                $target = imagecreatetruecolor($maxWidth, $newHeight);
                imagealphablending($target, false);
                imagesavealpha($target, true);
                imagecopyresampled($target, $source, 0, 0, 0, 0, $maxWidth, $newHeight, $origWidth, $origHeight);
                imagewebp($target, $targetPath, 82);
                imagedestroy($target);
            } else {
                imagealphablending($source, false);
                imagesavealpha($source, true);
                imagewebp($source, $targetPath, 82);
            }

            imagedestroy($source);
        } else {
            // Fallback: simpan file asli dengan ekstensi berdasarkan MIME (bukan input klien)
            $ext = match ($mime) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            $filename = $slugPrefix . time() . '-' . Str::random(5) . '.' . $ext;
            $file->move($dir, $filename);
        }

        return '/storage/' . $subDirectory . '/' . $filename;
    }

    /**
     * Hapus gambar lama hanya jika tersimpan di storage lokal.
     */
    protected function deleteLocalImage(?string $url): void
    {
        if ($url && str_starts_with($url, '/storage/')) {
            Storage::disk('public')->delete(substr($url, strlen('/storage/')));
        }
    }
}
