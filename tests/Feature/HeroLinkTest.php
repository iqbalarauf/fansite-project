<?php

namespace Tests\Feature;

use App\Support\HeroLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HeroLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_pages_exposes_idol_and_fansite_about_options(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Cornelia Vanisa'],
            ['key' => 'idol_slug', 'value' => 'oniel'],
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara'],
            ['key' => 'fanbase_slug', 'value' => 'wota-nusantara'],
        ], ['key'], ['value', 'updated_at']);

        Cache::forget('about_settings');

        $pages = HeroLink::listPages();

        $this->assertArrayHasKey('about.idol', $pages);
        $this->assertArrayHasKey('about.fansite', $pages);
        $this->assertStringContainsString('/about/oniel', $pages['about.idol']);
        $this->assertStringContainsString('/about/wota-nusantara', $pages['about.fansite']);

        $this->assertSame(route('about.show', 'oniel'), HeroLink::resolve('list', 'about.idol'));
        $this->assertSame(route('about.show', 'wota-nusantara'), HeroLink::resolve('list', 'about.fansite'));
    }

    public function test_about_options_fall_back_to_slugs_derived_from_names(): void
    {
        DB::table('about_settings')->upsert([
            ['key' => 'idol_name', 'value' => 'Cornelia Vanisa'],
            ['key' => 'fanbase_name', 'value' => 'Wota Nusantara'],
        ], ['key'], ['value', 'updated_at']);

        Cache::forget('about_settings');

        $this->assertSame(route('about.show', 'cornelia-vanisa'), HeroLink::resolve('list', 'about.idol'));
        $this->assertSame(route('about.show', 'wota-nusantara'), HeroLink::resolve('list', 'about.fansite'));
    }
}
