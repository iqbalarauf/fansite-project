<?php

namespace Tests\Feature;

use App\Models\ShowTeater;
use App\Models\TheaterReference;
use App\Support\Timezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TheaterReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_reset_keeps_references_used_by_upcoming_shows(): void
    {
        $now = Timezone::nowLocal();
        $previousMonth = $now->copy()->subMonthNoOverflow();

        TheaterReference::query()->create([
            'reference_code' => 'FUTURE1',
            'month' => $previousMonth->month,
            'year' => $previousMonth->year,
            'processed_at' => now(),
        ]);

        TheaterReference::query()->create([
            'reference_code' => 'PAST1',
            'month' => $previousMonth->month,
            'year' => $previousMonth->year,
            'processed_at' => now(),
        ]);

        DB::table('show_teater')->insert([
            [
                'show_id' => 9001,
                'show_date' => $now->copy()->addDay()->toDateString(),
                'setlist' => 'Upcoming Setlist',
                'reference_code' => 'FUTURE1',
            ],
            [
                'show_id' => 9002,
                'show_date' => $now->copy()->subDay()->toDateString(),
                'setlist' => 'Past Setlist',
                'reference_code' => 'PAST1',
            ],
        ]);

        TheaterReference::deleteOldReferences($now->month, $now->year);

        // Reference untuk show yang belum terlewat tetap ada.
        $this->assertDatabaseHas('theater_references', ['reference_code' => 'FUTURE1']);
        $this->assertSame('FUTURE1', DB::table('show_teater')->where('show_id', 9001)->value('reference_code'));

        // Reference untuk show yang sudah lewat dihapus, dan reference_code show jadi null.
        $this->assertDatabaseMissing('theater_references', ['reference_code' => 'PAST1']);
        $this->assertNull(DB::table('show_teater')->where('show_id', 9002)->value('reference_code'));
    }

    public function test_upcoming_theater_show_card_links_to_purchase_page(): void
    {
        DB::table('theater_references')->insert([
            'reference_code' => 'SH79AC',
            'month' => Timezone::nowLocal()->month,
            'year' => Timezone::nowLocal()->year,
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ShowTeater::query()->create([
            'show_id' => 1,
            'show_date' => Timezone::nowLocal()->addDays(5)->toDateString(),
            'setlist' => 'Cara Meminum Ramune',
            'reference_code' => 'SH79AC',
            'is_scraped_data' => 1,
            'is_member_show' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://jkt48.com/purchase/schedule/show?code=SH79AC', false);
    }
}
