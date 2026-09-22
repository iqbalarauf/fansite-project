<?php

namespace Tests\Feature;

use App\Support\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    public function test_it_stores_uploaded_image_as_webp_and_scales_down(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.jpg', 2400, 1200);

        $path = ImageOptimizer::store($file, 'gallery/photos', maxWidth: 800);

        Storage::disk('public')->assertExists($path);
        $this->assertStringEndsWith('.webp', $path);

        $size = getimagesize(Storage::disk('public')->path($path));

        $this->assertSame('image/webp', $size['mime']);
        $this->assertSame(800, $size[0]);
    }

    public function test_it_keeps_small_images_without_upscaling(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('small.png', 320, 200);

        $path = ImageOptimizer::store($file, 'app', maxWidth: 1920);

        $size = getimagesize(Storage::disk('public')->path($path));

        $this->assertSame(320, $size[0]);
        $this->assertSame(200, $size[1]);
    }

    public function test_non_image_files_are_stored_as_is(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('doc.svg', 5, 'image/svg+xml');

        $path = ImageOptimizer::store($file, 'pages');

        $this->assertStringEndsWith('.svg', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_optimize_images_command_rewrites_existing_files_in_place(): void
    {
        Storage::fake('public');

        $path = 'gallery/photos/big.jpg';
        Storage::disk('public')->put($path, $this->noisyJpeg(2400, 1200));
        $before = Storage::disk('public')->size($path);

        $this->artisan('app:optimize-images', ['--min-size' => 1])->assertExitCode(0);

        Storage::disk('public')->assertExists($path);
        $after = Storage::disk('public')->size($path);

        $this->assertLessThan($before, $after);

        $size = getimagesize(Storage::disk('public')->path($path));

        $this->assertSame('image/jpeg', $size['mime']);
        $this->assertSame(1920, $size[0]);
    }

    private function noisyJpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        for ($i = 0; $i < 8000; $i++) {
            $color = imagecolorallocate($image, random_int(0, 255), random_int(0, 255), random_int(0, 255));
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $color);
        }

        ob_start();
        imagejpeg($image, null, 100);
        $data = (string) ob_get_clean();
        imagedestroy($image);

        return $data;
    }
}
