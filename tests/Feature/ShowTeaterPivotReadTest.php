<?php

namespace Tests\Feature;

use App\Models\ShowTeater;
use App\Models\ShowTeaterCategories;
use App\Models\User;
use App\Support\IdolTheaterStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTeaterPivotReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_accessors_prefer_pivot_over_text_columns(): void
    {
        $setlist = ShowTeaterCategories::query()->create([
            'type' => 'setlist',
            'name' => 'Pajama Drive',
            'jp_name' => 'Pajama Drive',
        ]);

        $unitSong = ShowTeaterCategories::query()->create([
            'type' => 'unit_song',
            'setlist_id' => $setlist->id,
            'name' => 'Ekor Malaikat',
            'jp_name' => 'Tenshi no Shippo',
        ]);

        $show = ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => now()->format('Y/m/d'),
            'setlist_id' => $setlist->id,
            'setlist' => '',
            'unit_song' => '',
        ]);

        $show->unitSongCategories()->attach($unitSong->id, ['position' => 0]);

        $fresh = $show->fresh(['setlistCategory', 'unitSongCategories']);

        $this->assertSame('Pajama Drive', $fresh->setlistName());
        $this->assertSame(['Ekor Malaikat'], $fresh->unitSongNames());
        $this->assertSame('Ekor Malaikat', $fresh->unitSongString());
    }

    public function test_idol_stats_count_unit_songs_from_pivot_without_text(): void
    {
        $setlist = ShowTeaterCategories::query()->create([
            'type' => 'setlist',
            'name' => 'Pajama Drive',
            'is_active' => true,
        ]);

        $unitSong = ShowTeaterCategories::query()->create([
            'type' => 'unit_song',
            'setlist_id' => $setlist->id,
            'name' => 'Ekor Malaikat',
        ]);

        $show = ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => now()->format('Y/m/d'),
            'setlist_id' => $setlist->id,
            'setlist' => '',
            'unit_song' => '',
            'is_member_show' => 1,
        ]);

        $show->unitSongCategories()->attach($unitSong->id, ['position' => 0]);

        $stats = app(IdolTheaterStats::class)->build();

        $this->assertSame('Pajama Drive', $stats['setlists'][0]['name']);
        $this->assertSame('Ekor Malaikat', $stats['unit_songs'][0]['name']);
        $this->assertSame(1, $stats['unit_songs'][0]['count_all']);
    }

    public function test_admin_index_displays_unit_song_from_pivot_when_text_is_empty(): void
    {
        $setlist = ShowTeaterCategories::query()->create([
            'type' => 'setlist',
            'name' => 'Pajama Drive',
            'is_active' => true,
        ]);

        $unitSong = ShowTeaterCategories::query()->create([
            'type' => 'unit_song',
            'setlist_id' => $setlist->id,
            'name' => 'Ekor Malaikat',
            'jp_name' => 'Tenshi no Shippo',
            'is_active' => true,
        ]);

        $show = ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => now()->format('Y/m/d'),
            'setlist_id' => $setlist->id,
            'setlist' => '',
            'unit_song' => '',
        ]);

        $show->unitSongCategories()->attach($unitSong->id, ['position' => 0]);

        $this->actingAs(User::factory()->create())
            ->get(route('show-teater.index'))
            ->assertOk()
            ->assertSee('Pajama Drive')
            ->assertSee('Ekor Malaikat (Tenshi no Shippo)');
    }
}
