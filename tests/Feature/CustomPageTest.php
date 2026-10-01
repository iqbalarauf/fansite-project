<?php

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\User;
use App\Support\CustomPageStatistic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CustomPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_index_only_shows_the_page_list_and_create_button(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('pages.index'))
            ->assertOk()
            ->assertSee('Pages')
            ->assertSee('Tambah Halaman Baru')
            ->assertSee(route('pages.create'));
    }

    public function test_page_editor_groups_information_in_the_aside_and_collapsible_sections(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->assertSee('Page Information')
            ->assertSee('Add element')
            ->assertSee('Edit element')
            ->assertSee('Custom slug (optional)')
            ->assertSee('Title alignment')
            ->assertSee('Page display')
            ->assertSee('Page background')
            ->call('toggleAside', 'pageInfo')
            ->assertSet('pageInfoOpen', false)
            ->assertDontSee('Custom slug (optional)')
            ->call('toggleAside', 'addElement')
            ->assertSet('addElementOpen', false)
            ->assertDontSee("addBlock('container')", escape: false)
            ->call('toggleAside', 'addElement')
            ->assertSet('addElementOpen', true)
            ->assertSee("addBlock('container')", escape: false);
    }

    public function test_authenticated_user_can_save_and_publish_a_custom_page_with_a_generated_slug(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Profil Oshimen')
            ->set('blocks.0.data.background', 'accent')
            ->call('addBlock', 'text')
            ->set('blocks.1.data.text', 'Tentang halaman ini')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('profil-oshimen', $page->slug);
        $this->assertSame('published', $page->status);
        $this->assertCount(2, $page->blocks);
        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('Tentang halaman ini');
        $this->assertSame('/profil-oshimen', parse_url(route('custom-pages.show', $page), PHP_URL_PATH));
    }

    public function test_user_can_edit_and_delete_a_custom_page(): void
    {
        $user = User::factory()->create();
        $page = CustomPage::query()->create([
            'title' => 'Halaman Lama',
            'slug' => 'halaman-lama',
            'blocks' => [['id' => 'block-1', 'type' => 'text', 'data' => ['text' => 'Isi lama']]],
        ]);

        $this->actingAs($user)
            ->get(route('pages.edit', $page))
            ->assertOk()
            ->assertSee('Halaman Lama');

        $this->actingAs($user)
            ->delete(route('pages.destroy', $page))
            ->assertRedirect(route('pages.index'));

        $this->assertSoftDeleted($page);
        $this->get(route('pages.index'))->assertDontSee('Halaman Lama');
        $this->get(route('custom-pages.show', ['customPage' => 'halaman-lama']))->assertNotFound();
    }

    public function test_user_can_customize_the_slug_and_draft_pages_are_not_public(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Baru')
            ->set('slug', 'koleksi-spesial')
            ->call('save')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('koleksi-spesial', $page->slug);
        $this->get(route('custom-pages.show', $page))->assertNotFound();
    }

    public function test_user_can_set_a_hexadecimal_page_background_and_it_renders_publicly(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Hex')
            ->call('applyPageBackground', '#AABBCC')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('#aabbcc', $page->background_color);
        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('background-color: #aabbcc', false);
    }

    public function test_user_can_set_a_hexadecimal_container_background_and_it_renders_publicly(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Container Hex')
            ->call('applyBlockBackground', '#123456')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('#123456', $page->blocks[0]['data']['background']);
        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('background-color: #123456', false);
    }

    public function test_invalid_page_background_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Salah')
            ->set('backgroundColor', 'neon')
            ->call('save', 'published')
            ->assertHasErrors(['backgroundColor']);

        $this->assertDatabaseCount('custom_pages', 0);
    }

    public function test_invalid_container_background_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Container Salah')
            ->set('blocks.0.data.background', 'mint')
            ->call('save', 'published')
            ->assertHasErrors(['blocks.0.data.background']);

        $this->assertDatabaseCount('custom_pages', 0);
    }

    public function test_nested_blocks_are_validated_before_a_page_is_published(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Galeri')
            ->call('addBlockToContainer', 0, 0, 'text')
            ->call('addBlockToContainer', 0, 0, 'image')
            ->set('blocks.0.data.columns.0.blocks.0.data.text', '')
            ->set('blocks.0.data.columns.0.blocks.1.data.url', 'invalid-url')
            ->call('save', 'published')
            ->assertHasErrors([
                'blocks.0.data.columns.0.blocks.0.data.text',
                'blocks.0.data.columns.0.blocks.1.data.url',
            ]);

        $this->assertDatabaseCount('custom_pages', 0);
    }

    public function test_nested_blocks_can_be_reordered_before_saving(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Tersortir')
            ->call('addBlockToContainer', 0, 0, 'text')
            ->set('blocks.0.data.columns.0.blocks.0.id', 'nested-first')
            ->call('addBlockToContainer', 0, 0, 'text')
            ->set('blocks.0.data.columns.0.blocks.1.id', 'nested-second')
            ->call('sortNestedBlock', 'nested-second', 0)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('nested-second', $page->blocks[0]['data']['columns'][0]['blocks'][0]['id']);
    }

    public function test_top_level_image_with_an_invalid_url_fails_publishing(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Galeri Gambar')
            ->call('addBlock', 'image')
            ->set('blocks.1.data.url', 'not-a-valid-url')
            ->call('save', 'published')
            ->assertHasErrors(['blocks.1.data.url']);

        $this->assertDatabaseCount('custom_pages', 0);
    }

    public function test_user_can_upload_an_image_to_a_top_level_image_block(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Upload')
            ->call('addBlock', 'image')
            ->set('imageUpload', UploadedFile::fake()->image('foto.jpg', 100, 100))
            ->call('uploadImage')
            ->assertHasNoErrors('imageUpload');

        $path = $component->get('blocks.1.data.storage_path');

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_uploaded_image_is_stored_and_saved_with_the_page(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Upload')
            ->call('addBlock', 'image')
            ->set('imageUpload', UploadedFile::fake()->image('foto.jpg', 100, 100))
            ->call('uploadImage')
            ->set('blocks.1.data.alt', 'Foto oshimen')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();
        $imageData = $page->blocks[1]['data'];

        $this->assertNotEmpty($imageData['storage_path']);
        $this->assertStringStartsWith('pages/', $imageData['storage_path']);
        Storage::disk('public')->assertExists($imageData['storage_path']);

        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('/storage/pages/', false)
            ->assertSee('Foto oshimen');
    }

    public function test_uploading_over_an_existing_image_deletes_the_previous_file(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Ganti Foto')
            ->call('addBlock', 'image')
            ->set('imageUpload', UploadedFile::fake()->image('lama.jpg', 100, 100))
            ->call('uploadImage')
            ->assertHasNoErrors('imageUpload');

        $firstPath = $component->get('blocks.1.data.storage_path');
        Storage::disk('public')->assertExists($firstPath);

        $component
            ->set('imageUpload', UploadedFile::fake()->image('baru.jpg', 100, 100))
            ->call('uploadImage')
            ->assertHasNoErrors('imageUpload');

        $secondPath = $component->get('blocks.1.data.storage_path');

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }

    public function test_user_can_upload_an_image_to_a_nested_image_block(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Nested')
            ->call('addBlockToContainer', 0, 0, 'image')
            ->set('imageUpload', UploadedFile::fake()->image('nested.jpg', 100, 100))
            ->call('uploadImage')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();
        $nestedImage = $page->blocks[0]['data']['columns'][0]['blocks'][0]['data'];

        $this->assertNotEmpty($nestedImage['storage_path']);
        Storage::disk('public')->assertExists($nestedImage['storage_path']);

        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('/storage/pages/', false);
    }

    public function test_removing_an_uploaded_image_deletes_the_stored_file(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Hapus Foto')
            ->call('addBlock', 'image')
            ->set('imageUpload', UploadedFile::fake()->image('hapus.jpg', 100, 100))
            ->call('uploadImage');

        $path = $component->get('blocks.1.data.storage_path');
        Storage::disk('public')->assertExists($path);

        $component->call('removeImage');

        Storage::disk('public')->assertMissing($path);
        $this->assertSame('', $component->get('blocks.1.data.url'));
        $this->assertNull($component->get('blocks.1.data.storage_path'));
    }

    public function test_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->call('addBlock', 'image')
            ->set('imageUpload', UploadedFile::fake()->create('dokumen.txt', 100))
            ->call('uploadImage')
            ->assertHasErrors(['imageUpload']);
    }

    public function test_deleting_a_page_removes_its_uploaded_images(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Hapus')
            ->call('addBlock', 'image')
            ->set('imageUpload', UploadedFile::fake()->image('saya.jpg', 100, 100))
            ->call('uploadImage')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();
        $path = $page->blocks[1]['data']['storage_path'];
        Storage::disk('public')->assertExists($path);

        $component->call('deletePage');

        Storage::disk('public')->assertMissing($path);
        $this->assertSoftDeleted($page);
    }

    public function test_deleting_a_page_removes_uploaded_images_inside_containers(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Container')
            ->call('addBlockToContainer', 0, 0, 'image')
            ->set('imageUpload', UploadedFile::fake()->image('nested.png', 100, 100))
            ->call('uploadImage')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();
        $path = $page->blocks[0]['data']['columns'][0]['blocks'][0]['data']['storage_path'];
        Storage::disk('public')->assertExists($path);

        $component->call('deletePage');

        Storage::disk('public')->assertMissing($path);
        $this->assertSoftDeleted($page);
    }

    public function test_top_level_video_requires_a_youtube_url(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Video Sesi')
            ->call('addBlock', 'video')
            ->set('blocks.1.data.url', 'https://example.com/video.mp4')
            ->call('save', 'published')
            ->assertHasErrors(['blocks.1.data.url']);

        $this->assertDatabaseCount('custom_pages', 0);
    }

    public function test_nested_button_requires_a_label_and_a_valid_url(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Tombol')
            ->call('addBlockToContainer', 0, 0, 'button')
            ->set('blocks.0.data.columns.0.blocks.0.data.label', '')
            ->set('blocks.0.data.columns.0.blocks.0.data.url', 'invalid-url')
            ->call('save', 'published')
            ->assertHasErrors([
                'blocks.0.data.columns.0.blocks.0.data.label',
                'blocks.0.data.columns.0.blocks.0.data.url',
            ]);

        $this->assertDatabaseCount('custom_pages', 0);
    }

    public function test_nested_embed_requires_html(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Embed')
            ->call('addBlockToContainer', 0, 0, 'embed')
            ->set('blocks.0.data.columns.0.blocks.0.data.html', '')
            ->call('save', 'published')
            ->assertHasErrors(['blocks.0.data.columns.0.blocks.0.data.html']);

        $this->assertDatabaseCount('custom_pages', 0);
    }

    public function test_nested_youtube_video_rejects_a_non_youtube_url(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Video Nested')
            ->call('addBlockToContainer', 0, 0, 'video')
            ->set('blocks.0.data.columns.0.blocks.0.data.url', 'https://vimeo.com/12345')
            ->call('save', 'published')
            ->assertHasErrors(['blocks.0.data.columns.0.blocks.0.data.url']);

        $this->assertDatabaseCount('custom_pages', 0);
    }

    public function test_nested_blocks_in_a_second_column_can_be_reordered(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Dua Kolom')
            ->call('setContainerColumns', 0, 2)
            ->call('addBlockToContainer', 0, 1, 'text')
            ->set('blocks.0.data.columns.1.blocks.0.id', 'col2-first')
            ->set('blocks.0.data.columns.1.blocks.0.data.text', 'Kolom kedua')
            ->call('addBlockToContainer', 0, 1, 'text')
            ->set('blocks.0.data.columns.1.blocks.1.id', 'col2-second')
            ->set('blocks.0.data.columns.1.blocks.1.data.text', 'Kedua')
            ->call('sortNestedBlock', 'col2-second', 0)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();
        $blocks = $page->blocks[0]['data']['columns'][1]['blocks'];

        $this->assertSame('col2-second', $blocks[0]['id']);
        $this->assertSame('col2-first', $blocks[1]['id']);
    }

    public function test_nested_block_can_be_moved_downward_within_a_column(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Urutan Turun')
            ->call('addBlockToContainer', 0, 0, 'text')
            ->set('blocks.0.data.columns.0.blocks.0.id', 'first')
            ->set('blocks.0.data.columns.0.blocks.0.data.text', 'Pertama')
            ->call('addBlockToContainer', 0, 0, 'text')
            ->set('blocks.0.data.columns.0.blocks.0.id', 'first')
            ->set('blocks.0.data.columns.0.blocks.1.id', 'second')
            ->set('blocks.0.data.columns.0.blocks.1.data.text', 'Kedua')
            ->call('addBlockToContainer', 0, 0, 'text')
            ->set('blocks.0.data.columns.0.blocks.0.id', 'first')
            ->set('blocks.0.data.columns.0.blocks.1.id', 'second')
            ->set('blocks.0.data.columns.0.blocks.2.id', 'third')
            ->set('blocks.0.data.columns.0.blocks.2.data.text', 'Ketiga')
            ->call('sortNestedBlock', 'first', 1)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();
        $blocks = array_column($page->blocks[0]['data']['columns'][0]['blocks'], 'id');

        $this->assertSame(['second', 'first', 'third'], $blocks);
    }

    public function test_nested_text_block_requires_content_but_draft_container_body_is_allowed(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::page-builder.index')
            ->set('title', 'Konten Kosong')
            ->call('addBlockToContainer', 0, 0, 'text')
            ->set('blocks.0.data.columns.0.blocks.0.data.text', '');

        $component->call('save', 'published')
            ->assertHasErrors(['blocks.0.data.columns.0.blocks.0.data.text']);
    }

    public function test_page_title_and_text_block_presentation_settings_are_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Berformat')
            ->set('titleAlignment', 'center')
            ->set('blocks.0.type', 'text')
            ->set('blocks.0.data.text', 'Teks berformat')
            ->set('blocks.0.data.alignment', 'justify')
            ->set('blocks.0.data.color', '#E5605C')
            ->set('blocks.0.data.bold', true)
            ->set('blocks.0.data.italic', true)
            ->set('blocks.0.data.underline', true)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('center', $page->title_alignment);
        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('text-center', false)
            ->assertSee('text-justify', false)
            ->assertSee('font-bold', false)
            ->assertSee('italic', false)
            ->assertSee('underline', false)
            ->assertSee('color: #E5605C', false);
    }

    public function test_container_vertical_alignment_is_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Rata Tengah')
            ->set('blocks.0.data.vertical_alignment', 'middle')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('middle', $page->blocks[0]['data']['vertical_alignment']);
        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('items-center', false);
    }

    public function test_statistic_block_counts_all_supported_data_filters(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026-01-10', 'setlist' => 'A', 'unit_song' => 'Song A', 'is_us_center' => 1, 'is_global_center' => 1],
            ['show_id' => 2, 'show_date' => '2026-01-15', 'setlist' => 'A', 'unit_song' => 'Song B', 'is_us_center' => null, 'is_global_center' => 0],
            ['show_id' => 3, 'show_date' => '2026-02-01', 'setlist' => 'B', 'unit_song' => null, 'is_us_center' => 1, 'is_global_center' => 1],
        ]);
        DB::table('live_streaming')->insert([
            ['platform' => 'Showroom', 'live_date' => '2026-01-10', 'created_at' => now(), 'updated_at' => now()],
            ['platform' => 'IDN App', 'live_date' => '2026-01-20', 'created_at' => now(), 'updated_at' => now()],
            ['platform' => 'Showroom', 'live_date' => '2026-02-01', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $dateRange = ['date_from' => '2026-01-01', 'date_to' => '2026-01-31'];

        foreach ([
            ['metric' => 'show_teater_all', 'expected' => 3],
            ['metric' => 'show_teater_date_range', ...$dateRange, 'expected' => 2],
            ['metric' => 'show_teater_setlist', 'setlist' => 'A', 'expected' => 2],
            ['metric' => 'unit_song_all', 'expected' => 2],
            ['metric' => 'unit_song_date_range', ...$dateRange, 'expected' => 2],
            ['metric' => 'unit_song_setlist', 'setlist' => 'A', 'expected' => 2],
            ['metric' => 'center_unit_song_all', 'expected' => 2],
            ['metric' => 'center_unit_song_unit_song', 'unit_song' => 'Song A', 'expected' => 1],
            ['metric' => 'center_unit_song_setlist', 'setlist' => 'A', 'expected' => 1],
            ['metric' => 'center_unit_song_date_range', ...$dateRange, 'expected' => 1],
            ['metric' => 'global_center_date_range', ...$dateRange, 'expected' => 1],
            ['metric' => 'global_center_setlist', 'setlist' => 'B', 'expected' => 1],
            ['metric' => 'live_streaming_time', ...$dateRange, 'expected' => 2],
            ['metric' => 'live_streaming_row', 'expected' => 3],
            ['metric' => 'live_streaming_platform', 'platform' => 'Showroom', 'expected' => 2],
        ] as $data) {
            $this->assertSame($data['expected'], CustomPageStatistic::value($data));
        }
    }

    public function test_statistic_block_renders_its_live_value_on_a_public_page(): void
    {
        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026-01-10',
            'setlist' => 'A',
        ]);

        $page = CustomPage::query()->create([
            'title' => 'Statistik',
            'slug' => 'statistik',
            'status' => 'published',
            'blocks' => [['id' => 'statistic-1', 'type' => 'statistic', 'data' => ['metric' => 'show_teater_all', 'label' => 'Total Show']]],
        ]);

        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('Total Show')
            ->assertSee('1');
    }

    public function test_statistic_block_shows_date_fields_and_show_teater_dropdown_options(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026-01-10', 'setlist' => 'Setlist A', 'unit_song' => 'Unit Song A'],
            ['show_id' => 2, 'show_date' => '2026-01-11', 'setlist' => 'Setlist B', 'unit_song' => 'Unit Song B'],
        ]);

        Livewire::test('pages::page-builder.index')
            ->call('addBlock', 'statistic')
            ->set('blocks.1.data.metric', 'show_teater_date_range')
            ->assertSee('Start date')
            ->assertSee('End date')
            ->set('blocks.1.data.metric', 'show_teater_setlist')
            ->assertSee('Setlist A')
            ->assertSee('Setlist B')
            ->set('blocks.1.data.metric', 'center_unit_song_unit_song')
            ->assertSee('Unit Song A')
            ->assertSee('Unit Song B');
    }

    public function test_statistic_filters_are_saved_and_rendered_with_their_matching_count(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026-01-10', 'setlist' => 'Setlist A', 'unit_song' => 'Unit Song A'],
            ['show_id' => 2, 'show_date' => '2026-02-10', 'setlist' => 'Setlist B', 'unit_song' => 'Unit Song B'],
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Statistik Filter')
            ->call('addBlock', 'statistic')
            ->set('blocks.1.data.metric', 'show_teater_setlist')
            ->set('blocks.1.data.setlist', 'Setlist A')
            ->set('blocks.1.data.label', 'Show Setlist A')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('Setlist A', $page->blocks[1]['data']['setlist']);
        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('Show Setlist A')
            ->assertSee('1');
    }

    public function test_page_display_mode_is_saved_and_welcome_mode_renders_header_and_footer(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Welcome')
            ->set('displayMode', 'welcome')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('welcome', $page->display_mode);
        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('data-site-header', false)
            ->assertSee('©', false);
    }

    public function test_page_display_mode_defaults_to_full_without_header_and_footer(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Full')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('full', $page->display_mode);
        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertDontSee('data-site-header', false)
            ->assertDontSee('©', false);
    }

    public function test_invalid_page_display_mode_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Salah Mode')
            ->set('displayMode', 'sidebar')
            ->call('save', 'published')
            ->assertHasErrors(['displayMode']);

        $this->assertDatabaseCount('custom_pages', 0);
    }

    public function test_page_builder_preview_renders_container_background_and_image_placeholder(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Preview Blok')
            ->set('blocks.0.data.background', '#F1F5F9')
            ->assertSee('background-color: #F1F5F9', false)
            ->call('addBlock', 'image')
            ->assertSee('Tambahkan URL gambar atau upload');
    }

    public function test_button_alignment_and_colors_are_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Tombol')
            ->call('addBlock', 'button')
            ->set('blocks.1.data.label', 'Klik Saya')
            ->set('blocks.1.data.url', 'https://example.com')
            ->set('blocks.1.data.alignment', 'center')
            ->set('blocks.1.data.bg_color', '#112233')
            ->set('blocks.1.data.text_color', '#445566')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('text-center', false)
            ->assertSee('background-color: #112233', false)
            ->assertSee('color: #445566', false)
            ->assertSee('Klik Saya');
    }

    public function test_image_display_modes_render_expected_classes(): void
    {
        $render = fn (string $display): string => Blade::render('<x-custom-page-block :block="$block" />', [
            'block' => ['type' => 'image', 'data' => ['url' => 'https://example.com/a.jpg', 'source' => 'url', 'display' => $display]],
        ]);

        $this->assertStringContainsString('object-cover', $render('fit'));
        $this->assertStringContainsString('object-contain', $render('contain'));
        $this->assertStringContainsString('h-auto w-full', $render('auto'));
        $this->assertStringContainsString('max-w-none', $render('original'));
    }

    public function test_text_heading_and_font_size_are_rendered(): void
    {
        $html = Blade::render('<x-custom-page-block :block="$block" />', [
            'block' => ['type' => 'text', 'data' => ['text' => 'Judul Halaman', 'heading' => 'h2', 'font_size' => '3xl']],
        ]);

        $this->assertStringContainsString('<h2', $html);
        $this->assertStringContainsString('text-3xl', $html);
        $this->assertStringContainsString('Judul Halaman', $html);
    }

    public function test_transparent_element_background_renders_without_background_class(): void
    {
        $html = Blade::render('<x-custom-page-block :block="$block" />', [
            'block' => ['type' => 'container', 'data' => ['background' => 'transparent', 'padding' => 'medium', 'columns' => [['blocks' => []]]]],
        ]);

        $this->assertStringNotContainsString('bg-white', $html);
    }

    public function test_transparent_page_background_can_be_saved(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Transparan')
            ->set('backgroundColor', 'transparent')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('transparent', $page->background_color);
        $this->get(route('custom-pages.show', $page))->assertOk();
    }

    public function test_preview_toggle_renders_the_page_blocks(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Pratinjau')
            ->set('blocks.0.type', 'text')
            ->set('blocks.0.data.text', 'Konten pratinjau')
            ->assertDontSee('Close')
            ->set('showPreview', true)
            ->assertSee('Close')
            ->assertSee('Konten pratinjau');
    }

    public function test_upload_button_is_hidden_until_a_file_is_selected(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->call('addBlock', 'image')
            ->set('blocks.1.data.source', 'upload')
            ->assertDontSee('wire:click="uploadImage"', false)
            ->set('imageUpload', UploadedFile::fake()->image('foto.jpg', 50, 50))
            ->assertSee('wire:click="uploadImage"', false);
    }

    public function test_image_source_radio_switches_between_url_and_upload(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->call('addBlock', 'image')
            ->set('blocks.1.data.source', 'url')
            ->assertSee('Image URL')
            ->set('blocks.1.data.source', 'upload')
            ->assertDontSee('Image URL')
            ->assertSee('type="file"', false);
    }

    public function test_multiple_text_blocks_keep_independent_content_and_format(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Dua Teks')
            ->set('blocks.0.type', 'text')
            ->set('blocks.0.data.text', 'Teks Pertama')
            ->set('blocks.0.data.font_size', 'lg')
            ->call('addBlock', 'text')
            ->assertSee('wire:model.live="blocks.1.data.text"', false)
            ->assertSee('wire:model.live="blocks.1.data.font_size"', false)
            ->set('blocks.1.data.text', 'Teks Kedua')
            ->set('blocks.1.data.font_size', '3xl')
            ->call('save', 'draft')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('Teks Pertama', $page->blocks[0]['data']['text']);
        $this->assertSame('lg', $page->blocks[0]['data']['font_size']);
        $this->assertSame('Teks Kedua', $page->blocks[1]['data']['text']);
        $this->assertSame('3xl', $page->blocks[1]['data']['font_size']);
    }

    public function test_custom_page_can_save_a_hero_image_below_the_title(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Hero')
            ->set('heroEnabled', true)
            ->set('heroImageUpload', UploadedFile::fake()->image('hero.jpg', 1200, 600))
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertTrue($page->hero_enabled);
        $this->assertNotNull($page->hero_image);
        Storage::disk('public')->assertExists($page->hero_image);

        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('Halaman Hero')
            ->assertSee('/storage/'.$page->hero_image, false);
    }

    public function test_custom_page_title_color_adapts_to_background(): void
    {
        $darkPage = CustomPage::query()->create([
            'title' => 'Halaman Gelap',
            'slug' => 'halaman-gelap',
            'status' => 'published',
            'background_color' => '#111827',
            'blocks' => [],
        ]);

        $lightPage = CustomPage::query()->create([
            'title' => 'Halaman Terang',
            'slug' => 'halaman-terang',
            'status' => 'published',
            'background_color' => '#F8FAFC',
            'blocks' => [],
        ]);

        $this->get(route('custom-pages.show', $darkPage))
            ->assertOk()
            ->assertSee('color: #F8FAFC', false);

        $this->get(route('custom-pages.show', $lightPage))
            ->assertOk()
            ->assertSee('color: #0F172A', false);
    }

    public function test_user_can_duplicate_a_page_from_the_index_as_a_draft(): void
    {
        $this->actingAs(User::factory()->create());

        $page = CustomPage::query()->create([
            'title' => 'Profil Oshimen',
            'slug' => 'profil-oshimen',
            'status' => 'published',
            'display_mode' => 'welcome',
            'background_color' => 'indigo',
            'title_alignment' => 'center',
            'hero_enabled' => false,
            'blocks' => [
                [
                    'id' => 'block-1',
                    'type' => 'text',
                    'data' => ['text' => 'Isi asli', 'alignment' => 'left', 'color' => '#2E2F3E', 'font_size' => 'base', 'heading' => 'none', 'bold' => false, 'italic' => false, 'underline' => false],
                ],
            ],
        ]);

        $response = $this->post(route('pages.duplicate', $page));

        $duplicate = CustomPage::query()->where('id', '!=', $page->id)->firstOrFail();

        $response->assertRedirect(route('pages.edit', $duplicate));

        $this->assertSame(2, CustomPage::query()->count());
        $this->assertSame('Profil Oshimen (Copy)', $duplicate->title);
        $this->assertSame('profil-oshimen-copy', $duplicate->slug);
        $this->assertSame('draft', $duplicate->status);
        $this->assertSame('welcome', $duplicate->display_mode);
        $this->assertSame('indigo', $duplicate->background_color);
        $this->assertCount(1, $duplicate->blocks);
        $this->assertSame('Isi asli', $duplicate->blocks[0]['data']['text']);

        // The duplicate gets its own block ids.
        $this->assertNotSame('block-1', $duplicate->blocks[0]['id']);

        // The original page is untouched.
        $this->assertSame('published', $page->fresh()->status);
        $this->assertSame('block-1', $page->fresh()->blocks[0]['id']);
    }

    public function test_duplicating_a_page_twice_produces_unique_slugs(): void
    {
        $this->actingAs(User::factory()->create());

        $page = CustomPage::query()->create([
            'title' => 'Profil Oshimen',
            'slug' => 'profil-oshimen',
            'status' => 'published',
            'blocks' => [
                ['id' => 'block-1', 'type' => 'text', 'data' => ['text' => 'Isi', 'alignment' => 'left', 'color' => '#2E2F3E', 'font_size' => 'base', 'heading' => 'none', 'bold' => false, 'italic' => false, 'underline' => false]],
            ],
        ]);

        $this->post(route('pages.duplicate', $page));
        $this->post(route('pages.duplicate', $page));

        $this->assertSame(3, CustomPage::query()->count());
        $this->assertDatabaseHas('custom_pages', ['slug' => 'profil-oshimen-copy']);
        $this->assertDatabaseHas('custom_pages', ['slug' => 'profil-oshimen-copy-2']);
    }

    public function test_view_only_user_cannot_duplicate_a_page(): void
    {
        $this->actingAs(User::factory()->viewOnly()->create());

        $page = CustomPage::query()->create([
            'title' => 'Halaman',
            'slug' => 'halaman',
            'status' => 'published',
            'blocks' => [
                ['id' => 'block-1', 'type' => 'text', 'data' => ['text' => 'Isi', 'alignment' => 'left', 'color' => '#2E2F3E', 'font_size' => 'base', 'heading' => 'none', 'bold' => false, 'italic' => false, 'underline' => false]],
            ],
        ]);

        $this->post(route('pages.duplicate', $page))->assertForbidden();

        $this->assertSame(1, CustomPage::query()->count());
    }

    public function test_pages_index_shows_the_duplicate_action(): void
    {
        $this->actingAs(User::factory()->create());

        $page = CustomPage::query()->create([
            'title' => 'Halaman Publik',
            'slug' => 'halaman-publik',
            'status' => 'published',
            'blocks' => [],
        ]);

        $this->get(route('pages.index'))
            ->assertOk()
            ->assertSee(route('pages.duplicate', $page), false)
            ->assertSee('Duplicate page', false);
    }

    public function test_user_can_duplicate_a_container_block_with_new_ids(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Container')
            ->set('blocks.0.data.background', 'accent')
            ->set('blocks.0.data.vertical_alignment', 'middle')
            ->call('addBlockToContainer', 0, 0, 'text')
            ->set('blocks.0.data.columns.0.blocks.0.data.text', 'Isi di dalam container')
            ->call('duplicateBlock', 0)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertCount(2, $page->blocks);

        [$original, $clone] = $page->blocks;

        $this->assertSame('container', $clone['type']);
        $this->assertSame('accent', $clone['data']['background']);
        $this->assertSame('middle', $clone['data']['vertical_alignment']);
        $this->assertSame('Isi di dalam container', $clone['data']['columns'][0]['blocks'][0]['data']['text']);

        // All ids are regenerated for the clone.
        $this->assertNotSame($original['id'], $clone['id']);
        $this->assertNotSame($original['data']['columns'][0]['id'], $clone['data']['columns'][0]['id']);
        $this->assertNotSame(
            $original['data']['columns'][0]['blocks'][0]['id'],
            $clone['data']['columns'][0]['blocks'][0]['id'],
        );
    }

    public function test_duplicating_a_container_copies_its_uploaded_image_files(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Gambar')
            ->call('addBlockToContainer', 0, 0, 'image')
            ->set('imageUpload', UploadedFile::fake()->image('gift.png'))
            ->call('uploadImage')
            ->assertHasNoErrors()
            ->call('duplicateBlock', 0)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertCount(2, $page->blocks);

        [$original, $clone] = $page->blocks;

        $originalPath = $original['data']['columns'][0]['blocks'][0]['data']['storage_path'];
        $clonePath = $clone['data']['columns'][0]['blocks'][0]['data']['storage_path'];

        $this->assertNotSame($originalPath, $clonePath);
        Storage::disk('public')->assertExists($originalPath);
        Storage::disk('public')->assertExists($clonePath);
    }

    public function test_user_can_add_up_to_eight_gallery_images(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Gallery')
            ->call('addBlock', 'gallery')
            ->set('galleryUploads', [
                UploadedFile::fake()->image('a.png'),
                UploadedFile::fake()->image('b.png'),
                UploadedFile::fake()->image('c.png'),
            ])
            ->call('uploadGalleryImages')
            ->assertHasNoErrors()
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertCount(2, $page->blocks);
        $this->assertSame('gallery', $page->blocks[1]['type']);
        $this->assertCount(3, $page->blocks[1]['data']['images']);

        foreach ($page->blocks[1]['data']['images'] as $image) {
            Storage::disk('public')->assertExists($image['storage_path']);
        }
    }

    public function test_gallery_rejects_more_than_eight_images(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $uploads = [];
        for ($i = 0; $i < 9; $i++) {
            $uploads[] = UploadedFile::fake()->image("img-{$i}.png");
        }

        Livewire::test('pages::page-builder.index')
            ->call('addBlock', 'gallery')
            ->set('galleryUploads', $uploads)
            ->call('uploadGalleryImages')
            ->assertHasErrors('galleryUploads');
    }

    public function test_gallery_requires_an_initial_count_not_exceeding_its_images(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Gallery')
            ->call('addBlock', 'gallery')
            ->set('galleryUploads', [UploadedFile::fake()->image('a.png')])
            ->call('uploadGalleryImages')
            ->set('blocks.1.data.initial_count', 5)
            ->call('save', 'published')
            ->assertHasErrors('blocks.1.data.initial_count');
    }

    public function test_gallery_requires_at_least_one_image_before_publishing(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Gallery')
            ->call('addBlock', 'gallery')
            ->call('save', 'published')
            ->assertHasErrors('blocks.1.data.images');
    }

    public function test_removing_a_gallery_image_deletes_the_stored_file(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        $component = Livewire::test('pages::page-builder.index')
            ->call('addBlock', 'gallery')
            ->set('galleryUploads', [
                UploadedFile::fake()->image('a.png'),
                UploadedFile::fake()->image('b.png'),
            ])
            ->call('uploadGalleryImages')
            ->assertHasNoErrors();

        $path = $component->get('blocks.1.data.images.0.storage_path');
        Storage::disk('public')->assertExists($path);

        $component->call('removeGalleryImage', 0)->assertHasNoErrors();

        Storage::disk('public')->assertMissing($path);
        $this->assertCount(1, $component->get('blocks.1.data.images'));
    }

    public function test_gallery_renders_initial_images_and_a_carousel_trigger_publicly(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Gallery')
            ->call('addBlock', 'gallery')
            ->set('galleryUploads', [
                UploadedFile::fake()->image('a.png'),
                UploadedFile::fake()->image('b.png'),
                UploadedFile::fake()->image('c.png'),
                UploadedFile::fake()->image('d.png'),
                UploadedFile::fake()->image('e.png'),
            ])
            ->call('uploadGalleryImages')
            ->assertHasNoErrors()
            ->set('blocks.1.data.initial_count', 2)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('data-page-gallery-track', false)
            ->assertSee('data-page-gallery-item', false)
            ->assertSee('data-page-gallery-next', false)
            ->assertSee('data-page-gallery-prev', false);
    }

    public function test_gallery_hides_carousel_arrows_when_images_fit_the_initial_count(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Gallery')
            ->call('addBlock', 'gallery')
            ->set('galleryUploads', [
                UploadedFile::fake()->image('a.png'),
                UploadedFile::fake()->image('b.png'),
            ])
            ->call('uploadGalleryImages')
            ->set('blocks.1.data.initial_count', 2)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('data-page-gallery-track', false)
            ->assertDontSee('data-page-gallery-nav', false);
    }

    public function test_deleting_a_page_removes_its_gallery_files(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Gallery')
            ->call('addBlock', 'gallery')
            ->set('galleryUploads', [UploadedFile::fake()->image('a.png')])
            ->call('uploadGalleryImages')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();
        $path = $page->blocks[1]['data']['images'][0]['storage_path'];
        Storage::disk('public')->assertExists($path);

        Livewire::test('pages::page-builder.index', ['pageId' => $page->id])
            ->call('deletePage');

        Storage::disk('public')->assertMissing($path);
        $this->assertSame(0, CustomPage::query()->count());
    }

    public function test_duplicating_a_page_copies_gallery_files(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Gallery')
            ->call('addBlock', 'gallery')
            ->set('galleryUploads', [UploadedFile::fake()->image('a.png')])
            ->call('uploadGalleryImages')
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();
        $originalPath = $page->blocks[1]['data']['images'][0]['storage_path'];

        $this->post(route('pages.duplicate', $page));

        $duplicate = CustomPage::query()->where('id', '!=', $page->id)->firstOrFail();
        $clonePath = $duplicate->blocks[1]['data']['images'][0]['storage_path'];

        $this->assertNotSame($originalPath, $clonePath);
        Storage::disk('public')->assertExists($originalPath);
        Storage::disk('public')->assertExists($clonePath);
    }

    public function test_gallery_block_can_be_added_inside_a_container(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Gallery')
            ->call('addBlockToContainer', 0, 0, 'gallery')
            ->set('galleryUploads', [UploadedFile::fake()->image('a.png')])
            ->call('uploadGalleryImages')
            ->assertHasNoErrors()
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();
        $nested = $page->blocks[0]['data']['columns'][0]['blocks'][0];

        $this->assertSame('gallery', $nested['type']);
        $this->assertCount(1, $nested['data']['images']);
    }

    public function test_youtube_playlist_block_requires_a_valid_playlist_url(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Playlist')
            ->call('addBlock', 'youtube_playlist')
            ->call('save', 'published')
            ->assertHasErrors('blocks.1.data.playlist_url');

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Playlist')
            ->call('addBlock', 'youtube_playlist')
            ->set('blocks.1.data.playlist_url', 'https://www.youtube.com/watch?v=abc')
            ->call('save', 'published')
            ->assertHasErrors('blocks.1.data.playlist_url');
    }

    public function test_youtube_playlist_block_validates_visible_count(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Playlist')
            ->call('addBlock', 'youtube_playlist')
            ->set('blocks.1.data.playlist_url', 'https://www.youtube.com/playlist?list=PL1234567890')
            ->set('blocks.1.data.visible_count', 9)
            ->call('save', 'published')
            ->assertHasErrors('blocks.1.data.visible_count');
    }

    public function test_youtube_playlist_block_renders_cards_and_carousel_publicly(): void
    {
        Http::fake([
            'www.youtube.com/feeds/videos.xml*' => Http::response($this->youtubeFeed(5), 200),
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Playlist')
            ->call('addBlock', 'youtube_playlist')
            ->set('blocks.1.data.playlist_url', 'https://www.youtube.com/playlist?list=PL1234567890')
            ->set('blocks.1.data.visible_count', 3)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('data-page-youtube-track', false)
            ->assertSee('data-page-youtube-item', false)
            ->assertSee('data-page-youtube-next', false);
    }

    public function test_youtube_playlist_block_hides_arrows_when_videos_fit(): void
    {
        Http::fake([
            'www.youtube.com/feeds/videos.xml*' => Http::response($this->youtubeFeed(2), 200),
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test('pages::page-builder.index')
            ->set('title', 'Halaman Playlist')
            ->call('addBlock', 'youtube_playlist')
            ->set('blocks.1.data.playlist_url', 'https://www.youtube.com/playlist?list=PL1234567890')
            ->set('blocks.1.data.visible_count', 3)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->get(route('custom-pages.show', $page))
            ->assertOk()
            ->assertSee('data-page-youtube-track', false)
            ->assertDontSee('data-page-youtube-nav', false);
    }

    private function youtubeFeed(int $count): string
    {
        $entries = '';

        for ($i = 1; $i <= $count; $i++) {
            $entries .= <<<XML
            <entry>
                <yt:videoId>VID{$i}</yt:videoId>
                <title>Video {$i}</title>
                <published>2026-02-0{$i}T00:00:00+00:00</published>
                <media:group>
                    <media:title>Video {$i}</media:title>
                    <media:description>Deskripsi {$i}</media:description>
                    <media:thumbnail url="https://i.ytimg.com/vi/VID{$i}/hqdefault.jpg" width="480" height="360"/>
                </media:group>
            </entry>
            XML;
        }

        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xmlns:yt="http://www.youtube.com/xml/schemas/2015">
        {$entries}
        </feed>
        XML;
    }
}
