<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SettingBag;
use App\Support\ShowTeaterUnitSongPredictor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShowTeaterUnitSongPredictorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_predicts_unit_song_and_centers_from_the_latest_show_of_the_same_setlist(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/01/01', 'setlist' => 'Pajama Drive', 'unit_song' => 'Prinsip Kesucian Hati', 'is_global_center' => 1, 'is_us_center' => 0],
            ['show_id' => 2, 'show_date' => '2026/02/01', 'setlist' => 'Pajama Drive', 'unit_song' => 'Tenshi no Shippo; Prinsip Kesucian Hati', 'is_global_center' => 1, 'is_us_center' => 0],
            ['show_id' => 3, 'show_date' => '2026/01/15', 'setlist' => 'Aturan Anti Cinta', 'unit_song' => 'Malaikat Hitam', 'is_global_center' => 0, 'is_us_center' => 1],
        ]);

        $predictor = new ShowTeaterUnitSongPredictor;
        $map = $predictor->mapBySetlist();

        $this->assertSame('Tenshi no Shippo; Prinsip Kesucian Hati', $map['Pajama Drive']['unit_song']);
        $this->assertTrue($map['Pajama Drive']['is_global_center']);
        $this->assertFalse($map['Pajama Drive']['is_us_center']);

        $this->assertSame('Malaikat Hitam', $map['Aturan Anti Cinta']['unit_song']);
        $this->assertFalse($map['Aturan Anti Cinta']['is_global_center']);
        $this->assertTrue($map['Aturan Anti Cinta']['is_us_center']);

        $this->assertSame('Tenshi no Shippo; Prinsip Kesucian Hati', $predictor->predictFor('Pajama Drive'));
    }

    public function test_it_carries_center_flags_and_unit_song_from_the_last_filled_show(): void
    {
        DB::table('show_teater')->insert([
            ['show_id' => 1, 'show_date' => '2026/01/01', 'setlist' => 'Set X', 'unit_song' => 'Song A', 'is_global_center' => 0, 'is_us_center' => 1],
            // Show terbaru (mis. hasil scrape): center NULL & unit song kosong.
            ['show_id' => 2, 'show_date' => '2026/02/01', 'setlist' => 'Set X', 'unit_song' => null, 'is_global_center' => null, 'is_us_center' => null],
        ]);

        $map = (new ShowTeaterUnitSongPredictor)->mapBySetlist();

        $this->assertSame('Song A', $map['Set X']['unit_song']);
        $this->assertTrue($map['Set X']['is_us_center']);
        $this->assertFalse($map['Set X']['is_global_center']);
    }

    public function test_index_passes_unit_song_predictions_to_the_view(): void
    {
        DB::table('show_teater')->insert([
            'show_id' => 1,
            'show_date' => '2026/01/01',
            'setlist' => 'Pajama Drive',
            'unit_song' => 'Prinsip Kesucian Hati',
            'is_global_center' => 1,
        ]);

        $this->actingAs(User::factory()->create());

        $this->get(route('show-teater.index'))
            ->assertOk()
            ->assertViewHas('predictorEnabled', true)
            ->assertViewHas(
                'setlistUnitSongPredictions',
                fn ($predictions): bool => ($predictions['Pajama Drive']['unit_song'] ?? null) === 'Prinsip Kesucian Hati'
                    && ($predictions['Pajama Drive']['is_global_center'] ?? null) === true,
            );
    }

    public function test_predictor_setting_can_be_toggled(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('show-teater.predictor'), ['enabled' => '0'])
            ->assertRedirect(route('show-teater.index'));

        $this->assertFalse(SettingBag::showTeaterPredictorEnabled());

        $this->get(route('show-teater.index'))
            ->assertOk()
            ->assertViewHas('predictorEnabled', false)
            ->assertViewHas('setlistUnitSongPredictions', []);

        $this->post(route('show-teater.predictor'), ['enabled' => '1'])
            ->assertRedirect(route('show-teater.index'));

        $this->assertTrue(SettingBag::showTeaterPredictorEnabled());
    }
}
