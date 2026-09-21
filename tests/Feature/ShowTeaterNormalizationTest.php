<?php

namespace Tests\Feature;

use App\Models\ShowTeater;
use App\Models\ShowTeaterCategories;
use App\Models\User;
use App\Support\ShowTeaterCategoryResolver;
use App\Support\ShowTeaterNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShowTeaterNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_sets_setlist_id_and_pivot_rows(): void
    {
        [$setlist, $unitSong] = $this->seedCategories();

        $this->insertShow(1, 'Gadis Gadis Remaja', 'Kinjirareta Futari; Something Else');

        $result = (new ShowTeaterNormalizer)->backfill();

        $this->assertSame(1, $result['setlist_id_updated']);
        $this->assertSame(1, $result['unit_song_rows']);
        $this->assertSame(1, $result['unmatched_unit_songs']);

        $this->assertSame($setlist->id, (int) DB::table('show_teater')->where('show_id', 1)->value('setlist_id'));

        $pivot = DB::table('show_teater_unit_song')->where('show_id', 1)->get();
        $this->assertCount(1, $pivot);
        $this->assertSame($unitSong->id, (int) $pivot->first()->show_teater_categories_id);
        $this->assertSame(0, (int) $pivot->first()->position);
    }

    public function test_backfill_matches_japanese_titles_and_is_idempotent(): void
    {
        $this->seedCategories();

        $this->insertShow(1, 'Gadis Gadis Remaja', 'Kinjirareta Futari');

        $normalizer = new ShowTeaterNormalizer;
        $normalizer->backfill();
        $normalizer->backfill();

        $this->assertSame(1, DB::table('show_teater_unit_song')->where('show_id', 1)->count());
    }

    public function test_dry_run_does_not_write(): void
    {
        $this->seedCategories();

        $this->insertShow(1, 'Gadis Gadis Remaja', 'Kinjirareta Futari');

        $result = (new ShowTeaterNormalizer)->backfill(dryRun: true);

        $this->assertTrue($result['dry_run']);
        $this->assertSame(1, $result['setlist_id_updated']);
        $this->assertNull(DB::table('show_teater')->where('show_id', 1)->value('setlist_id'));
        $this->assertSame(0, DB::table('show_teater_unit_song')->count());
    }

    public function test_model_relations_expose_normalized_categories(): void
    {
        [$setlist, $unitSong] = $this->seedCategories();

        $this->insertShow(1, 'Gadis Gadis Remaja', 'Kinjirareta Futari');
        (new ShowTeaterNormalizer)->backfill();

        $show = ShowTeater::query()->findOrFail(1);

        $this->assertTrue($show->setlistCategory->is($setlist));
        $this->assertTrue($show->unitSongCategories->first()->is($unitSong));
    }

    public function test_index_prefers_normalized_unit_songs_after_backfill(): void
    {
        $this->seedCategories();

        $this->insertShow(1, 'Gadis Gadis Remaja', 'Kinjirareta Futari; Something Else');
        (new ShowTeaterNormalizer)->backfill();

        $this->actingAs(User::factory()->create());

        $this->get(route('show-teater.index'))
            ->assertOk()
            ->assertSee('Dua Orang yang Terlarang (Kinjirareta Futari)', false);
    }

    public function test_storing_a_show_syncs_setlist_id_and_pivot(): void
    {
        [$setlist, $unitSong] = $this->seedCategories();

        $this->actingAs(User::factory()->create());

        $this->post(route('show-teater.store'), [
            'show_id' => 1,
            'show_date' => '2026-06-22',
            'setlist' => 'Gadis Gadis Remaja',
            'unit_song' => 'Kinjirareta Futari',
        ])->assertRedirect(route('show-teater.index'));

        $this->assertSame($setlist->id, (int) DB::table('show_teater')->where('show_id', 1)->value('setlist_id'));
        $this->assertSame(1, DB::table('show_teater_unit_song')->where('show_id', 1)->count());
        $this->assertSame($unitSong->id, (int) DB::table('show_teater_unit_song')->where('show_id', 1)->value('show_teater_categories_id'));
    }

    public function test_resolver_matches_by_name_or_jp_name_scoped_to_setlist(): void
    {
        [$setlist, $unitSong] = $this->seedCategories();

        $resolver = new ShowTeaterCategoryResolver;

        $this->assertSame($setlist->id, $resolver->setlistId('seishun girls'));
        $this->assertSame($unitSong->id, $resolver->unitSongId($setlist->id, 'Kinjirareta Futari'));
        $this->assertSame($unitSong->id, $resolver->unitSongId($setlist->id, 'dua orang yang terlarang'));
        $this->assertNull($resolver->unitSongId($setlist->id, 'Unknown Song'));
    }

    /**
     * @return array{0: ShowTeaterCategories, 1: ShowTeaterCategories}
     */
    private function seedCategories(): array
    {
        $setlist = ShowTeaterCategories::factory()->create([
            'name' => 'Gadis Gadis Remaja',
            'jp_name' => 'Seishun Girls',
        ]);

        $unitSong = ShowTeaterCategories::factory()->unitSong($setlist)->create([
            'name' => 'Dua Orang yang Terlarang',
            'jp_name' => 'Kinjirareta Futari',
        ]);

        return [$setlist, $unitSong];
    }

    private function insertShow(int $showId, string $setlist, ?string $unitSong): void
    {
        DB::table('show_teater')->insert([
            'show_id' => $showId,
            'show_date' => '2026/01/01',
            'setlist' => $setlist,
            'unit_song' => $unitSong,
        ]);
    }
}
