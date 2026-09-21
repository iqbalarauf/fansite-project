<?php

namespace Tests\Feature;

use App\Models\Photobooth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PhotoboothTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_is_restricted_to_super_admin(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('photobooth.edit'))->assertOk();

        $this->actingAs(User::factory()->contentCreator()->create());
        $this->get(route('photobooth.edit'))->assertForbidden();
    }

    public function test_photobooth_can_be_saved_with_frame_and_schedule(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::photobooth.manage')
            ->set('slug', 'photobooth')
            ->set('columns', 2)
            ->set('rows', 3)
            ->set('isFullOpen', false)
            ->set('startAt', '2026-01-01T08:00')
            ->set('endAt', '2026-01-31T20:00')
            ->set('frameUpload', UploadedFile::fake()->image('frame.png'))
            ->call('save')
            ->assertHasNoErrors();

        $photobooth = Photobooth::query()->firstOrFail();

        $this->assertSame('photobooth', $photobooth->slug);
        $this->assertSame(2, $photobooth->columns);
        $this->assertSame(3, $photobooth->rows);
        $this->assertFalse($photobooth->is_full_open);
        $this->assertSame(6, $photobooth->poseCount());
        Storage::disk('public')->assertExists($photobooth->frame);
    }

    public function test_photobooth_schedule_is_converted_from_local_input_to_utc(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::photobooth.manage')
            ->set('slug', 'photobooth')
            ->set('isFullOpen', false)
            ->set('startAt', '2026-01-01T08:00')
            ->set('endAt', '2026-01-31T20:00')
            ->set('frameUpload', UploadedFile::fake()->image('frame.png'))
            ->call('save')
            ->assertHasNoErrors();

        $photobooth = Photobooth::query()->firstOrFail();

        // 08:00 & 20:00 WIB (UTC+7) => 01:00 & 13:00 UTC
        $this->assertSame('2026-01-01 01:00:00', $photobooth->start_at?->toDateTimeString());
        $this->assertSame('2026-01-31 13:00:00', $photobooth->end_at?->toDateTimeString());

        // Re-mount menampilkan kembali waktu lokal.
        Livewire::test('pages::photobooth.manage')->assertSet('startAt', '2026-01-01T08:00');
    }

    public function test_frame_is_required_when_creating(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::photobooth.manage')
            ->set('slug', 'photobooth')
            ->call('save')
            ->assertHasErrors('frameUpload');
    }

    public function test_public_photobooth_displays_the_booth_when_open(): void
    {
        $this->makePhotobooth();

        $this->get(route('photobooth.show', 'photobooth'))
            ->assertOk()
            ->assertSee('id="photobooth-app"', false)
            ->assertSee('data-columns="2"', false)
            ->assertSee('data-rows="3"', false)
            ->assertSee('id="pb-camera-stage"', false)
            ->assertSee('Take a Photo');
    }

    public function test_public_photobooth_shows_consent_popup_when_open(): void
    {
        $this->makePhotobooth();

        $this->get(route('photobooth.show', 'photobooth'))
            ->assertOk()
            ->assertSee('id="pb-welcome"', false)
            ->assertSee('tidak akan disimpan di server');
    }

    public function test_admin_can_position_photos_per_slot(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::photobooth.manage')
            ->set('columns', 1)
            ->set('rows', 2)
            ->set('photoSlots', [
                ['x' => 5, 'y' => 10, 'width' => 40, 'height' => 30],
                ['x' => 55, 'y' => 60, 'width' => 40, 'height' => 30],
            ])
            ->set('frameUpload', UploadedFile::fake()->image('frame.png'))
            ->call('save')
            ->assertHasNoErrors();

        $photobooth = Photobooth::query()->firstOrFail();
        $slots = $photobooth->resolvedSlots();

        $this->assertCount(2, $slots);
        $this->assertSame(5.0, $slots[0]['x']);
        $this->assertSame(10.0, $slots[0]['y']);
        $this->assertSame(55.0, $slots[1]['x']);
        $this->assertSame(30.0, $slots[1]['height']);
    }

    public function test_admin_can_enable_transparent_frame_overlay(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::photobooth.manage')
            ->set('frameOverlay', true)
            ->set('frameUpload', UploadedFile::fake()->image('frame.png'))
            ->call('save')
            ->assertHasNoErrors();

        $photobooth = Photobooth::query()->firstOrFail();

        $this->assertTrue($photobooth->frame_overlay);
    }

    public function test_public_photobooth_exposes_overlay_mode(): void
    {
        $this->makePhotobooth(['frame_overlay' => true]);

        $this->get(route('photobooth.show', 'photobooth'))
            ->assertOk()
            ->assertSee('data-overlay="1"', false);
    }

    public function test_slots_default_to_uniform_grid_when_not_configured(): void
    {
        $photobooth = $this->makePhotobooth();

        $slots = $photobooth->resolvedSlots();

        $this->assertCount(6, $slots);
        $this->assertArrayHasKey('x', $slots[0]);
        $this->assertArrayHasKey('y', $slots[0]);
        $this->assertArrayHasKey('width', $slots[0]);
        $this->assertArrayHasKey('height', $slots[0]);
    }

    public function test_public_photobooth_exposes_slots_configuration(): void
    {
        $this->makePhotobooth([
            'slots' => [
                ['x' => 5, 'y' => 5, 'width' => 40, 'height' => 40],
                ['x' => 55, 'y' => 5, 'width' => 40, 'height' => 40],
            ],
        ]);

        $this->get(route('photobooth.show', 'photobooth'))
            ->assertOk()
            ->assertSee('data-slots="', false);
    }

    public function test_public_photobooth_shows_countdown_when_scheduled(): void
    {
        $this->makePhotobooth([
            'is_full_open' => false,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
        ]);

        $this->get(route('photobooth.show', 'photobooth'))
            ->assertOk()
            ->assertSee('Photobooth akan dibuka')
            ->assertSee('data-countdown-target', false);
    }

    public function test_public_photobooth_shows_closed_message_after_schedule(): void
    {
        $this->makePhotobooth([
            'is_full_open' => false,
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDay(),
        ]);

        $this->get(route('photobooth.show', 'photobooth'))
            ->assertOk()
            ->assertSee('Photobooth sudah ditutup');
    }

    public function test_empty_slug_defaults_to_photobooth(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::photobooth.manage')
            ->set('slug', '')
            ->set('frameUpload', UploadedFile::fake()->image('frame.png'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('photobooths', ['slug' => 'photobooth']);
    }

    public function test_public_photobooth_is_available_without_slug(): void
    {
        $this->makePhotobooth();

        $this->get(route('photobooth.show'))
            ->assertOk()
            ->assertSee('id="photobooth-app"', false);
    }

    public function test_public_photobooth_returns_404_for_unknown_slug(): void
    {
        $this->makePhotobooth();

        $this->get(route('photobooth.show', 'tidak-ada'))->assertNotFound();
    }

    public function test_public_photobooth_returns_404_when_disabled(): void
    {
        $this->makePhotobooth();

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'photobooth_enabled'],
            ['value' => 'false', 'updated_at' => now()],
        );
        Cache::forget('app_settings');

        $this->get(route('photobooth.show', 'photobooth'))->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makePhotobooth(array $attributes = []): Photobooth
    {
        return Photobooth::query()->create(array_merge([
            'slug' => 'photobooth',
            'frame' => 'photobooth/frame.png',
            'columns' => 2,
            'rows' => 3,
            'is_full_open' => true,
            'is_active' => true,
        ], $attributes));
    }
}
