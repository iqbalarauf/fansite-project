<?php

namespace Tests\Feature;

use App\Models\Timeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_timeline_page_displays_entries(): void
    {
        Timeline::query()->create([
            'date' => '2026-01-15',
            'description' => 'Debut Oniel',
        ]);

        $this->get(route('timeline.index'))
            ->assertOk()
            ->assertSee('Timeline')
            ->assertSee('Debut Oniel')
            ->assertSee('15 Januari 2026');
    }

    public function test_idol_page_has_timeline_button(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Freya', 'created_at' => now(), 'updated_at' => now()],
        ], ['key'], ['value', 'updated_at']);

        $this->get(route('about.show'))
            ->assertOk()
            ->assertSee('Lihat Timeline')
            ->assertSee('href="'.route('timeline.index').'"', false);
    }

    public function test_admin_can_open_timeline_index(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('content.timeline.index'))->assertOk();
    }

    public function test_admin_can_create_update_and_delete_timeline(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->post(route('content.timeline.store'), [
            'date' => '2026-02-01',
            'description' => 'Konser pertama',
            'image' => UploadedFile::fake()->image('timeline.jpg'),
        ])->assertRedirect(route('content.timeline.index'));

        $timeline = Timeline::query()->firstOrFail();
        Storage::disk('public')->assertExists($timeline->image);

        $this->put(route('content.timeline.update', $timeline), [
            'date' => '2026-02-02',
            'description' => 'Konser pertama (revisi)',
        ])->assertRedirect(route('content.timeline.index'));

        $timeline->refresh();
        $this->assertSame('Konser pertama (revisi)', $timeline->description);

        $image = $timeline->image;

        $this->delete(route('content.timeline.destroy', $timeline))
            ->assertRedirect(route('content.timeline.index'));

        $this->assertDatabaseMissing('timelines', ['id' => $timeline->id]);
        Storage::disk('public')->assertMissing($image);
    }
}
