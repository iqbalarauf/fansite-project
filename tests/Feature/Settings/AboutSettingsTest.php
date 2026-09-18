<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AboutSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_settings_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('about.edit'))->assertOk();
    }

    public function test_idol_information_can_be_updated(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::about.manage')
            ->set('idolName', 'Freya')
            ->set('idolDescription', 'Deskripsi idol')
            ->set('idolAchievements', 'Achievement list')
            ->set('idolDiscography', 'Discography list')
            ->set('idolJikoshoukai', 'Perkenalan singkat')
            ->set('idolBirthDate', '2004-02-13')
            ->set('idolBirthPlace', 'Tangerang')
            ->set('idolBloodType', 'A')
            ->set('idolHoroscope', 'Aquarius')
            ->set('idolInstagram', 'https://instagram.com/freya')
            ->set('idolTiktok', 'https://tiktok.com/@freya')
            ->set('idolTwitter', 'https://x.com/freya')
            ->set('idolShowOnWelcome', true)
            ->set('idolPhotoUpload', UploadedFile::fake()->image('idol.jpg'))
            ->call('saveIdol')
            ->assertHasNoErrors();

        $settings = DB::table('about_settings')->pluck('value', 'key');

        $this->assertSame('Freya', $settings['idol_name']);
        $this->assertSame('freya', $settings['idol_slug']);
        $this->assertSame('A', $settings['idol_blood_type']);
        $this->assertSame('Aquarius', $settings['idol_horoscope']);
        $this->assertSame('true', $settings['idol_show_on_welcome']);
        $this->assertNotNull($settings['idol_photo']);
        Storage::disk('public')->assertExists($settings['idol_photo']);
    }

    public function test_kabesha_items_can_be_created_and_updated_per_photo(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::about.manage')
            ->set('idolName', 'Freya')
            ->set('kabeshaPhotoUploads', [
                UploadedFile::fake()->image('kabesha-1.jpg'),
                UploadedFile::fake()->image('kabesha-2.jpg'),
            ])
            ->call('saveIdol')
            ->assertHasNoErrors();

        $this->assertCount(2, $component->get('kabeshaItems'));

        $component
            ->set('kabeshaItems.0.title', 'Kabesha Satu')
            ->set('kabeshaItems.0.duration_from', '2026-01-01')
            ->set('kabeshaItems.0.duration_to', '2026-01-31')
            ->set('kabeshaItems.1.title', 'Kabesha Dua')
            ->call('saveIdol')
            ->assertHasNoErrors();

        $settings = DB::table('about_settings')->pluck('value', 'key');
        $stored = json_decode((string) $settings['kabesha_items'], true);

        $this->assertCount(2, $stored);
        $this->assertSame('Kabesha Satu', $stored[0]['title']);
        $this->assertSame('2026-01-01', $stored[0]['duration_from']);
        $this->assertSame('2026-01-31', $stored[0]['duration_to']);
        $this->assertSame('Kabesha Dua', $stored[1]['title']);
        Storage::disk('public')->assertExists($stored[0]['photo']);
        Storage::disk('public')->assertExists($stored[1]['photo']);
    }

    public function test_kabesha_item_duration_to_must_not_be_before_its_from(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::about.manage')
            ->set('idolName', 'Freya')
            ->set('kabeshaItems', [
                ['photo' => null, 'title' => 'Kabesha', 'duration_from' => '2026-02-01', 'duration_to' => '2026-01-01'],
            ])
            ->call('saveIdol')
            ->assertHasErrors('kabeshaItems.0.duration_to');
    }

    public function test_kabesha_item_can_be_removed(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Storage::disk('public')->put('about/kabesha/a.jpg', 'x');
        Storage::disk('public')->put('about/kabesha/b.jpg', 'x');

        Livewire::test('pages::about.manage')
            ->set('kabeshaItems', [
                ['photo' => 'about/kabesha/a.jpg', 'title' => 'A', 'duration_from' => null, 'duration_to' => null],
                ['photo' => 'about/kabesha/b.jpg', 'title' => 'B', 'duration_from' => null, 'duration_to' => null],
            ])
            ->call('removeKabeshaItem', 0)
            ->assertSet('kabeshaItems', [
                ['photo' => 'about/kabesha/b.jpg', 'title' => 'B', 'duration_from' => null, 'duration_to' => null],
            ]);

        Storage::disk('public')->assertMissing('about/kabesha/a.jpg');
        Storage::disk('public')->assertExists('about/kabesha/b.jpg');

        $settings = DB::table('about_settings')->pluck('value', 'key');
        $stored = json_decode((string) $settings['kabesha_items'], true);

        $this->assertCount(1, $stored);
        $this->assertSame('about/kabesha/b.jpg', $stored[0]['photo']);
    }

    public function test_kabesha_items_can_be_reordered(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::about.manage')
            ->set('kabeshaItems', [
                ['photo' => 'about/kabesha/a.jpg', 'title' => 'A', 'duration_from' => null, 'duration_to' => null],
                ['photo' => 'about/kabesha/b.jpg', 'title' => 'B', 'duration_from' => null, 'duration_to' => null],
                ['photo' => 'about/kabesha/c.jpg', 'title' => 'C', 'duration_from' => null, 'duration_to' => null],
            ])
            ->call('reorderKabeshaItems', [2, 0, 1])
            ->assertSet('kabeshaItems', [
                ['photo' => 'about/kabesha/c.jpg', 'title' => 'C', 'duration_from' => null, 'duration_to' => null],
                ['photo' => 'about/kabesha/a.jpg', 'title' => 'A', 'duration_from' => null, 'duration_to' => null],
                ['photo' => 'about/kabesha/b.jpg', 'title' => 'B', 'duration_from' => null, 'duration_to' => null],
            ]);

        $stored = json_decode((string) DB::table('about_settings')->where('key', 'kabesha_items')->value('value'), true);

        $this->assertSame(['C', 'A', 'B'], array_column($stored, 'title'));
    }

    public function test_fansite_information_can_be_updated_with_gallery_captions_and_structure(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::about.manage')
            ->set('activeTab', 'fansite')
            ->set('fanbaseName', 'Wota Nusantara')
            ->set('fanbaseDescription', 'Komunitas fanbase')
            ->set('fanbaseStructure', "Ketua: A\nWakil: B")
            ->set('fanbaseActivities', 'Nobar, project, dan event')
            ->set('fanbaseCtaEnabled', true)
            ->set('fanbaseCtaTitle', 'Gabung sekarang')
            ->set('fanbaseCtaButton1Text', 'Join Discord')
            ->set('fanbaseCtaButton1Link', 'https://discord.gg/example')
            ->set('fanbaseCtaButton2Text', 'Follow X')
            ->set('fanbaseCtaButton2Link', 'https://x.com/example')
            ->set('fanbaseLogoUpload', UploadedFile::fake()->image('logo.jpg'))
            ->set('fanbaseCtaBackgroundUpload', UploadedFile::fake()->image('cta.jpg'))
            ->set('fanbaseGalleryUploads', [
                UploadedFile::fake()->image('gallery-1.jpg'),
                UploadedFile::fake()->image('gallery-2.jpg'),
            ])
            ->call('saveFansite')
            ->assertHasNoErrors();

        $this->assertCount(2, $component->get('fanbaseGalleryItems'));

        $component
            ->set('fanbaseGalleryItems.0.caption', 'Foto bersama')
            ->set('fanbaseGalleryItems.1.caption', 'Nobar')
            ->call('saveFansite')
            ->assertHasNoErrors();

        $settings = DB::table('about_settings')->pluck('value', 'key');
        $items = json_decode((string) $settings['fanbase_gallery_items'], true);
        $legacy = json_decode((string) $settings['fanbase_gallery'], true);

        $this->assertSame('Wota Nusantara', $settings['fanbase_name']);
        $this->assertSame('wota-nusantara', $settings['fanbase_slug']);
        $this->assertSame("Ketua: A\nWakil: B", $settings['fanbase_structure']);
        $this->assertSame('true', $settings['fanbase_cta_enabled']);
        $this->assertSame(['Foto bersama', 'Nobar'], array_column($items, 'caption'));
        $this->assertCount(2, $legacy);

        foreach ($items as $item) {
            Storage::disk('public')->assertExists($item['photo']);
        }
    }

    public function test_fansite_gallery_is_limited_to_twenty_items(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::about.manage')
            ->set('activeTab', 'fansite')
            ->set('fanbaseName', 'Wota Nusantara')
            ->set('fanbaseGalleryItems', array_fill(0, 19, ['photo' => null, 'caption' => '']))
            ->set('fanbaseGalleryUploads', [
                UploadedFile::fake()->image('gallery-a.jpg'),
                UploadedFile::fake()->image('gallery-b.jpg'),
                UploadedFile::fake()->image('gallery-c.jpg'),
            ])
            ->call('saveFansite')
            ->assertHasNoErrors();

        $this->assertCount(20, $component->get('fanbaseGalleryItems'));
    }

    public function test_fansite_gallery_rejects_more_than_twenty_uploads_in_one_batch(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::about.manage')
            ->set('activeTab', 'fansite')
            ->set('fanbaseName', 'Wota Nusantara')
            ->set('fanbaseGalleryUploads', array_map(
                fn (int $index): UploadedFile => UploadedFile::fake()->image("gallery-{$index}.jpg"),
                range(1, 21),
            ))
            ->call('saveFansite')
            ->assertHasErrors('fanbaseGalleryUploads');
    }

    public function test_fansite_gallery_item_can_be_removed(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Storage::disk('public')->put('about/fansite/gallery/a.jpg', 'x');
        Storage::disk('public')->put('about/fansite/gallery/b.jpg', 'x');

        Livewire::test('pages::about.manage')
            ->set('fanbaseGalleryItems', [
                ['photo' => 'about/fansite/gallery/a.jpg', 'caption' => 'A'],
                ['photo' => 'about/fansite/gallery/b.jpg', 'caption' => 'B'],
            ])
            ->call('removeFanbaseGalleryItem', 0)
            ->assertSet('fanbaseGalleryItems', [
                ['photo' => 'about/fansite/gallery/b.jpg', 'caption' => 'B'],
            ]);

        Storage::disk('public')->assertMissing('about/fansite/gallery/a.jpg');
        Storage::disk('public')->assertExists('about/fansite/gallery/b.jpg');
    }
}
