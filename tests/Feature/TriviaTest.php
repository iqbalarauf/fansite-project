<?php

namespace Tests\Feature;

use App\Models\Trivia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TriviaTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_trivia_page_is_displayed_with_cards(): void
    {
        Trivia::query()->create(['title' => 'Fakta Menarik', 'description' => 'Tentang Oniel']);

        $this->get(route('trivia.index'))
            ->assertOk()
            ->assertSee('Fakta Menarik')
            ->assertSee('Tentang Oniel')
            ->assertSee('id="media-lightbox"', false)
            ->assertSee('data-lightbox-title', false);
    }

    public function test_trivia_can_be_searched_by_keyword(): void
    {
        Trivia::query()->create(['title' => 'Trivia Alpha', 'description' => 'isi alpha']);
        Trivia::query()->create(['title' => 'Trivia Beta', 'description' => 'isi beta']);

        $this->get(route('trivia.index', ['q' => 'Alpha']))
            ->assertOk()
            ->assertSee('Trivia Alpha')
            ->assertDontSee('Trivia Beta');
    }

    public function test_trivia_page_returns_404_when_disabled(): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'trivia_enabled'],
            ['value' => 'false', 'updated_at' => now()],
        );
        Cache::forget('app_settings');

        $this->get(route('trivia.index'))->assertNotFound();
    }

    public function test_admin_can_manage_trivia(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->get(route('content.trivia.index'))->assertOk();

        $this->post(route('content.trivia.store'), [
            'title' => 'Trivia Baru',
            'description' => 'Deskripsi trivia',
            'image' => UploadedFile::fake()->image('trivia.jpg'),
        ])->assertRedirect(route('content.trivia.index'));

        $trivia = Trivia::query()->firstOrFail();
        Storage::disk('public')->assertExists($trivia->image);

        $this->put(route('content.trivia.update', $trivia), [
            'title' => 'Trivia Revisi',
            'description' => 'Deskripsi revisi',
        ])->assertRedirect(route('content.trivia.index'));

        $trivia->refresh();
        $this->assertSame('Trivia Revisi', $trivia->title);

        $image = $trivia->image;

        $this->delete(route('content.trivia.destroy', $trivia))
            ->assertRedirect(route('content.trivia.index'));

        $this->assertDatabaseMissing('trivias', ['id' => $trivia->id]);
        Storage::disk('public')->assertMissing($image);
    }
}
