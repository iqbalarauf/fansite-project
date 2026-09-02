<?php

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\User;
use App\Support\CustomPageStatistic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
            ->call('sortNestedBlock', 0, 0, 'nested-second', 0)
            ->call('save', 'published')
            ->assertHasNoErrors();

        $page = CustomPage::query()->firstOrFail();

        $this->assertSame('nested-second', $page->blocks[0]['data']['columns'][0]['blocks'][0]['id']);
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
}
