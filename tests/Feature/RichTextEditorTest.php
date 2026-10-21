<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\CustomPageStatistic;
use App\Support\HtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RichTextEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_toolbar_renders_icon_buttons_instead_of_text_labels(): void
    {
        $html = Blade::render('<x-rich-text-editor name="content" />');

        $commands = [
            'bold', 'italic', 'underline', 'strike', 'h2', 'h3',
            'bulletList', 'orderedList', 'blockquote', 'codeBlock',
            'link', 'image', 'undo', 'redo', 'clear',
        ];

        foreach ($commands as $command) {
            $this->assertStringContainsString('data-rich-text-command="'.$command.'"', $html);
        }

        // Satu ikon SVG per tombol.
        $this->assertSame(count($commands), substr_count($html, '<svg'));

        // Label berbasis karakter tidak lagi ditampilkan.
        foreach (['>Daftar<', '>Kutipan<', '>Kode<', '>Tautan<', '>Gambar<', '>Bersihkan<'] as $label) {
            $this->assertStringNotContainsString($label, $html);
        }

        // Gambar diunggah lewat input berkas (bukan prompt link).
        $this->assertStringContainsString('data-rich-text-image-input', $html);
        $this->assertStringContainsString('data-image-upload-url=', $html);
        $this->assertStringContainsString('content/editor/image', $html);
    }

    public function test_statistic_card_is_opt_in(): void
    {
        $without = Blade::render('<x-rich-text-editor name="content" />');
        $with = Blade::render('<x-rich-text-editor name="content" :statistics="true" />');

        $this->assertStringNotContainsString('data-rich-text-command="statisticCard"', $without);
        $this->assertStringNotContainsString('data-stat-dialog', $without);

        $this->assertStringContainsString('data-rich-text-command="statisticCard"', $with);
        $this->assertStringContainsString('data-stat-dialog', $with);
        $this->assertStringContainsString('content/editor/statistic', $with);
        $this->assertStringContainsString('data-stat-field="metric"', $with);
    }

    public function test_statistic_setlist_options_exclude_inactive_categories(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/01/01', 'setlist' => 'Ramune', 'unit_song' => ''],
            ['show_id' => 2, 'show_date' => '2026/01/02', 'setlist' => 'Lorong Waktu', 'unit_song' => ''],
        ]);

        DB::table('show_teater_categories')->insert([
            ['id' => 1, 'type' => 'setlist', 'name' => 'Ramune', 'setlist_id' => null, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'type' => 'setlist', 'name' => 'Lorong Waktu', 'setlist_id' => null, 'is_active' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $html = Blade::render('<x-rich-text-editor name="content" :statistics="true" />');

        $this->assertStringContainsString('Ramune', $html);
        $this->assertStringNotContainsString('Lorong Waktu', $html);
    }

    public function test_statistic_endpoint_returns_the_computed_value(): void
    {
        $this->actingAs(User::factory()->create());

        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/01/01', 'setlist' => 'Ramune', 'unit_song' => 'Nice to Meet You!'],
            ['show_id' => 2, 'show_date' => '2026/01/02', 'setlist' => 'Ramune', 'unit_song' => ''],
        ]);

        $response = $this->postJson(route('content.editor.statistic'), [
            'metric' => 'show_teater_all',
        ]);

        $response->assertOk()->assertJsonPath('value', CustomPageStatistic::value(['metric' => 'show_teater_all']));
        $this->assertSame(2, $response->json('value'));

        $this->postJson(route('content.editor.statistic'), [
            'metric' => 'unit_song_all',
        ])->assertOk()->assertJsonPath('value', 1);

        $this->postJson(route('content.editor.statistic'), [
            'metric' => 'show_teater_setlist',
            'setlist' => 'Ramune',
        ])->assertOk()->assertJsonPath('value', 2);
    }

    public function test_statistic_endpoint_rejects_unknown_metrics(): void
    {
        $this->actingAs(User::factory()->create());

        $this->postJson(route('content.editor.statistic'), [
            'metric' => 'not_a_metric',
        ])->assertStatus(422);
    }

    public function test_statistic_endpoint_returns_list_items_for_list_metrics(): void
    {
        $this->actingAs(User::factory()->create());

        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/01/01', 'setlist' => 'Ramune'],
            ['show_id' => 2, 'show_date' => '2026/01/02', 'setlist' => 'Ramune'],
            ['show_id' => 3, 'show_date' => '2026/01/03', 'setlist' => 'Aitakatta'],
            ['show_id' => 4, 'show_date' => '2026/01/04', 'setlist' => 'Nonaktif'],
        ]);

        DB::table('show_teater_categories')->insert([
            ['id' => 1, 'type' => 'setlist', 'name' => 'Ramune', 'setlist_id' => null, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'type' => 'setlist', 'name' => 'Aitakatta', 'setlist_id' => null, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'type' => 'setlist', 'name' => 'Nonaktif', 'setlist_id' => null, 'is_active' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->postJson(route('content.editor.statistic'), [
            'metric' => 'show_teater_active_setlist_list',
        ])
            ->assertOk()
            ->assertJsonPath('type', 'list')
            ->assertJsonPath('value', 2)
            ->assertJsonPath('items.0.label', 'Aitakatta')
            ->assertJsonPath('items.0.count', 1)
            ->assertJsonPath('items.1.label', 'Ramune')
            ->assertJsonPath('items.1.count', 2);
    }

    public function test_sanitizer_keeps_statistic_list_items_attribute(): void
    {
        $html = '<div class="rich-stat-card" data-stat-metric="show_teater_active_setlist_list" data-stat-items="[{&quot;label&quot;:&quot;Ramune&quot;,&quot;count&quot;:2}]">'.
            '<ul class="rich-stat-card-list"><li><span>Ramune</span><span>2</span></li></ul></div>';

        $clean = HtmlSanitizer::article($html);

        $this->assertStringContainsString('data-stat-items=', $clean);
        $this->assertStringContainsString('Ramune', $clean);
    }

    public function test_sanitizer_keeps_statistic_card_data_attributes_but_strips_dangerous_ones(): void
    {
        $html = '<div class="rich-stat-card" data-stat-metric="show_teater_all" data-stat-label="Total" data-stat-value="42" onclick="alert(1)" style="color:red">'.
            '<p class="rich-stat-card-label">Total</p><p class="rich-stat-card-value">42</p></div>';

        $clean = HtmlSanitizer::article($html);

        $this->assertStringContainsString('data-stat-metric="show_teater_all"', $clean);
        $this->assertStringContainsString('data-stat-label="Total"', $clean);
        $this->assertStringContainsString('data-stat-value="42"', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('style=', $clean);
    }
}
