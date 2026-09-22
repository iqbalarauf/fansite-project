<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Optimasi gambar saat upload: auto-orient (EXIF), perkecil bila melebihi lebar
 * maksimum, lalu encode ke WebP. Non-gambar/animasi (mis. SVG, GIF) disimpan apa adanya.
 */
final class ImageOptimizer
{
    public const DEFAULT_MAX_WIDTH = 1920;

    public const DEFAULT_QUALITY = 82;

    /**
     * @var array<int, string>
     */
    private const OPTIMIZABLE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'bmp'];

    public static function store(
        UploadedFile $file,
        string $directory,
        int $maxWidth = self::DEFAULT_MAX_WIDTH,
        int $quality = self::DEFAULT_QUALITY,
        string $disk = 'public',
    ): string {
        $directory = trim($directory, '/');

        if (! self::isOptimizable($file)) {
            return $file->store($directory, $disk);
        }

        try {
            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->decodePath($file->getRealPath())->orient();

            if ($maxWidth > 0 && $image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            $encoded = $image->encode(new WebpEncoder(quality: $quality));
            $path = ($directory !== '' ? $directory.'/' : '').Str::ulid().'.webp';

            Storage::disk($disk)->put($path, $encoded->toString());

            return $path;
        } catch (Throwable $exception) {
            // Fallback: simpan berkas asli bila pemrosesan gagal.
            return $file->store($directory, $disk);
        }
    }

    private static function isOptimizable(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, self::OPTIMIZABLE_EXTENSIONS, true)
            && @getimagesize($file->getRealPath()) !== false;
    }
}
