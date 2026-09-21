<?php

namespace Tests\Feature;

use App\Models\ShowTeaterCategories;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_role_defaults_to_view_only(): void
    {
        DB::table('users')->insert([
            'name' => 'Default Role',
            'email' => 'default-role@example.com',
            'password' => 'secret',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('view_only', DB::table('users')->where('email', 'default-role@example.com')->value('role'));
    }

    public function test_deleting_a_user_cascades_their_sessions(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'session-1',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => 'x',
            'last_activity' => now()->timestamp,
        ]);

        $this->assertDatabaseHas('sessions', ['id' => 'session-1']);

        $user->delete();

        $this->assertDatabaseMissing('sessions', ['id' => 'session-1']);
    }

    public function test_show_teater_has_performance_indexes(): void
    {
        $indexedColumns = collect(Schema::getIndexes('show_teater'))
            ->pluck('columns')
            ->flatten()
            ->all();

        $this->assertContains('show_date', $indexedColumns);
        $this->assertContains('setlist', $indexedColumns);
        $this->assertContains('unit_song', $indexedColumns);
        $this->assertContains('deleted_at', $indexedColumns);
    }

    public function test_show_teater_categories_flags_are_indexed(): void
    {
        $indexedColumns = collect(Schema::getIndexes('show_teater_categories'))
            ->pluck('columns')
            ->flatten()
            ->all();

        $this->assertContains('type', $indexedColumns);
    }

    public function test_show_teater_categories_relations_and_scopes(): void
    {
        $setlist = ShowTeaterCategories::factory()->create(['name' => 'Pajama Drive']);
        $unit = ShowTeaterCategories::factory()->unitSong($setlist)->create(['name' => 'Tenshi no Shippo']);

        $this->assertTrue($setlist->is_active);
        $this->assertTrue($unit->setlist->is($setlist));
        $this->assertTrue($setlist->unitSongs()->first()->is($unit));

        $this->assertSame(1, ShowTeaterCategories::query()->setlists()->count());
        $this->assertSame(1, ShowTeaterCategories::query()->unitSongs()->count());
        $this->assertSame(2, ShowTeaterCategories::query()->active()->count());

        ShowTeaterCategories::factory()->inactive()->create();

        $this->assertSame(2, ShowTeaterCategories::query()->active()->count());
    }
}
