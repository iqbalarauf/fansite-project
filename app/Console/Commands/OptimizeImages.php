<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

class OptimizeImages extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:optimize-images
        {--disk=public : Disk yang dipindai}
        {--max-width=1920 : Lebar maksimum (px)}
        {--quality=82 : Kualitas kompresi}
        {--min-size=102400 : Ukuran minimum (byte) agar diproses}
        {--dry-run : Tampilkan tanpa menulis}';

    /**
     * @var string
     */
    protected $description = 'Optimalkan gambar lama di storage (perkecil & re-encode, path tetap sama)';

    public function handle(): int
    {
        $disk = Storage::disk((string) $this->option('disk'));
        $maxWidth = (int) $this->option('max-width');
        $quality = (int) $this->option('quality');
        $minSize = (int) $this->option('min-size');
        $dryRun = (bool) $this->option('dry-run');

        $manager = ImageManager::usingDriver(Driver::class);

        $optimized = 0;
        $saved = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($disk->allFiles() as $path) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }

            $fullPath = $disk->path($path);

            if (! is_file($fullPath) || filesize($fullPath) < $minSize) {
                $skipped++;

                continue;
            }

            try {
                $image = $manager->decodePath($fullPath)->orient();

                if ($maxWidth > 0 && $image->width() > $maxWidth) {
                    $image->scaleDown(width: $maxWidth);
                }

                $content = $this->encode($image, $extension, $quality)->toString();

                $sizeBefore = (int) filesize($fullPath);
                $sizeAfter = strlen($content);

                if ($sizeAfter >= $sizeBefore) {
                    $skipped++;

                    continue;
                }

                if (! $dryRun) {
                    $disk->put($path, $content);
                }

                $optimized++;
                $saved += $sizeBefore - $sizeAfter;

                $this->line(sprintf(
                    '%s %s: %s -> %s',
                    $dryRun ? '[dry]' : 'OK',
                    $path,
                    $this->human($sizeBefore),
                    $this->human($sizeAfter),
                ));
            } catch (Throwable $exception) {
                $failed++;
                $this->warn("Gagal: {$path} - {$exception->getMessage()}");
            }
        }

        $this->info(sprintf(
            '%s%d file dioptimalkan, %s dihemat, %d dilewati, %d gagal.',
            $dryRun ? 'DRY RUN: ' : '',
            $optimized,
            $this->human($saved),
            $skipped,
            $failed,
        ));

        return self::SUCCESS;
    }

    private function encode(mixed $image, string $extension, int $quality): object
    {
        return match ($extension) {
            'png' => $image->encode(new PngEncoder),
            'webp' => $image->encode(new WebpEncoder(quality: $quality)),
            default => $image->encode(new JpegEncoder(quality: $quality, progressive: true)),
        };
    }

    private function human(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
