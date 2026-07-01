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

        Livewire::test('pages::settings.about')
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

    public function test_fansite_information_can_be_updated_and_gallery_limited_to_five_uploads(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::settings.about')
            ->set('activeTab', 'fansite')
            ->set('fanbaseName', 'Wota Nusantara')
            ->set('fanbaseDescription', 'Komunitas fanbase')
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
                UploadedFile::fake()->image('gallery-3.jpg'),
                UploadedFile::fake()->image('gallery-4.jpg'),
                UploadedFile::fake()->image('gallery-5.jpg'),
            ])
            ->call('saveFansite')
            ->assertHasNoErrors();

        $settings = DB::table('about_settings')->pluck('value', 'key');
        $gallery = json_decode($settings['fanbase_gallery'] ?? '[]', true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('Wota Nusantara', $settings['fanbase_name']);
        $this->assertSame('wota-nusantara', $settings['fanbase_slug']);
        $this->assertSame('true', $settings['fanbase_cta_enabled']);
        $this->assertCount(5, $gallery);

        foreach ($gallery as $path) {
            Storage::disk('public')->assertExists($path);
        }

        Livewire::test('pages::settings.about')
            ->set('activeTab', 'fansite')
            ->set('fanbaseName', 'Wota Nusantara')
            ->set('fanbaseGalleryUploads', [
                UploadedFile::fake()->image('gallery-1.jpg'),
                UploadedFile::fake()->image('gallery-2.jpg'),
                UploadedFile::fake()->image('gallery-3.jpg'),
                UploadedFile::fake()->image('gallery-4.jpg'),
                UploadedFile::fake()->image('gallery-5.jpg'),
                UploadedFile::fake()->image('gallery-6.jpg'),
            ])
            ->call('saveFansite')
            ->assertHasErrors(['fanbaseGalleryUploads']);
    }
}
