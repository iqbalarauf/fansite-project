<?php

namespace Tests\Feature;

use App\Models\LiveStreaming;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchStreamingInfoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.jkt48connect.url' => 'https://jkt48connect.test',
            'services.jkt48connect.key' => 'test-key',
        ]);
    }

    public function test_it_fails_when_the_api_is_not_configured(): void
    {
        config(['services.jkt48connect.url' => '', 'services.jkt48connect.key' => '']);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutput('JKT48Connect API is not configured (JKT48CONNECT_LIVE_URL / JKT48CONNECT_API_KEY).')
            ->assertFailed();
    }

    public function test_it_outputs_error_if_idol_shortname_is_missing(): void
    {
        DB::table('about_settings')->where('key', 'idol_shortname')->delete();

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutput('Idol shortname not found in about_settings.')
            ->assertFailed();
    }

    public function test_it_saves_an_idn_live_with_detail_data_from_the_gifts_endpoint(): void
    {
        $this->setShortname('Oniel');
        $this->fakeApi([$this->item()], ['idn-oniel-1' => $this->gifts()]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutputToContain('1 data live streaming ditambahkan')
            ->expectsOutput('Fetch completed.')
            ->assertExitCode(0);

        $live = LiveStreaming::query()->where('live_id', 'idn-oniel-1')->firstOrFail();

        $this->assertSame('IDN App', $live->platform);
        $this->assertSame('2026-09-24', $live->live_date->toDateString());
        $this->assertSame(62, $live->duration);
        $this->assertSame(10244, $live->max_viewers);
        $this->assertSame(18, $live->comment_count);
        $this->assertSame(11, $live->gift_count);
        $this->assertSame(1038, $live->total_gold);
        $this->assertSame('saya kembali', $live->additional_info);
        $this->assertSame('https://youtu.be/wgE7TF1tklk', $live->youtube_url);
        $this->assertCount(2, $live->gifts);
        $this->assertSame('Angklung', $live->gifts[0]['name']);
        $this->assertSame(203, $live->gifts[0]['total_gold']);
        $this->assertCount(2, $live->top_senders);
        $this->assertSame('Wetee .', $live->top_senders[0]['name']);
        $this->assertSame(220, $live->top_senders[0]['total_gold']);
    }

    public function test_it_uses_the_recent_list_and_gifts_endpoints_for_the_matched_username(): void
    {
        $this->setShortname('Oniel');
        $this->fakeApi([$this->item()], ['idn-oniel-1' => $this->gifts()]);

        $this->artisan('app:fetch-streaming-info')->assertExitCode(0);

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/api/recent?page=1&perpage=25');
        });

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/api/recent/idn-oniel-1/gifts');
        });
    }

    public function test_it_saves_a_showroom_live_and_uses_the_nickname_when_username_is_missing(): void
    {
        $this->setShortname('Nayla');
        $this->fakeApi([$this->showroomItem()], ['sr-510064-23658939' => $this->showroomGifts()]);

        $this->artisan('app:fetch-streaming-info')->assertExitCode(0);

        $live = LiveStreaming::query()->where('live_id', 'sr-510064-23658939')->firstOrFail();

        $this->assertSame('Showroom', $live->platform);
        $this->assertSame('Nayla Suji — Live bersama Nayla Suji', $live->additional_info);
        $this->assertSame('https://youtu.be/pAdv6yigFoE', $live->youtube_url);
        $this->assertSame(4615, $live->max_viewers);
        $this->assertSame(100, $live->gift_count);
        $this->assertSame(72783, $live->total_gold);
        $this->assertSame('MUKTAFI ', $live->top_senders[0]['name']);
        $this->assertSame(90, $live->top_senders[0]['total_gold']);
    }

    public function test_it_does_not_match_a_different_username_even_when_the_nickname_matches(): void
    {
        $this->setShortname('Oniel');
        $this->fakeApi([
            $this->item([
                'idn' => ['username' => 'jkt48_lulu', 'title' => 'mlm'],
                'member' => ['name' => 'Cornelia Vanisa', 'nickname' => 'Oniel'],
            ]),
        ]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutput('Tidak ada live streaming dari Oniel JKT48 hari ini')
            ->assertExitCode(0);

        $this->assertSame(0, LiveStreaming::query()->count());
    }

    public function test_it_skips_unknown_platforms(): void
    {
        $this->setShortname('Oniel');
        $this->fakeApi([
            $this->item(['type' => 'youtube']),
        ], ['idn-oniel-1' => $this->gifts()]);

        $this->artisan('app:fetch-streaming-info')->assertExitCode(0);

        $this->assertSame(0, LiveStreaming::query()->count());
    }

    public function test_it_reports_when_there_is_no_streaming_data(): void
    {
        $this->setShortname('Oniel');
        $this->fakeApi([]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutput('Tidak ada live streaming dari Oniel JKT48 hari ini')
            ->expectsOutput('Fetch completed.')
            ->assertExitCode(0);
    }

    public function test_it_does_not_duplicate_an_existing_live_and_skips_the_gifts_request(): void
    {
        $this->setShortname('Oniel');

        LiveStreaming::query()->create([
            'live_id' => 'idn-oniel-1',
            'platform' => 'IDN App',
            'live_date' => '2026-09-24',
            'duration' => 10,
        ]);

        $this->fakeApi([$this->item()], ['idn-oniel-1' => $this->gifts()]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutputToContain('sudah ada, dilewati')
            ->assertExitCode(0);

        $this->assertSame(1, LiveStreaming::query()->count());
        $this->assertSame(10, LiveStreaming::query()->firstOrFail()->duration);

        Http::assertNotSent(function ($request): bool {
            return str_contains($request->url(), '/gifts');
        });
    }

    public function test_it_does_not_insert_when_the_gifts_request_fails(): void
    {
        $this->setShortname('Oniel');

        Http::fake([
            'https://jkt48connect.test/api/recent?page=1&perpage=25' => Http::response([
                'recents' => [$this->item()],
                'page' => 1,
                'perpage' => 25,
                'total_count' => 1,
            ], 200),
            'https://jkt48connect.test/api/recent/*/gifts' => Http::response([], 500),
        ]);

        $this->artisan('app:fetch-streaming-info')
            ->expectsOutputToContain('Gagal mengambil data gifts')
            ->assertExitCode(0);

        $this->assertSame(0, LiveStreaming::query()->count());
    }

    public function test_it_falls_back_to_the_gold_summary_when_points_are_missing(): void
    {
        $this->setShortname('Oniel');
        $this->fakeApi([
            $this->item(['points' => null]),
        ], ['idn-oniel-1' => $this->gifts(['gold_summary' => ['total_gold' => 777]])]);

        $this->artisan('app:fetch-streaming-info')->assertExitCode(0);

        $this->assertSame(777, LiveStreaming::query()->firstOrFail()->total_gold);
    }

    public function test_fetch_manually_endpoint_returns_the_command_output(): void
    {
        $this->actingAs(User::factory()->create());
        $this->setShortname('Oniel');
        $this->fakeApi([$this->item()], ['idn-oniel-1' => $this->gifts()]);

        $response = $this->post(route('live-streaming.fetch-manually'));

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertStringContainsString('1 data live streaming ditambahkan', (string) $response->json('message'));
    }

    private function setShortname(string $value): void
    {
        DB::table('about_settings')->updateOrInsert(
            ['key' => 'idol_shortname'],
            ['value' => $value, 'updated_at' => now()],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $recents
     * @param  array<string, array<string, mixed>>  $giftsByDataId
     */
    private function fakeApi(array $recents, array $giftsByDataId = []): void
    {
        Http::fake([
            'https://jkt48connect.test/api/recent?page=1&perpage=25' => Http::response([
                'recents' => $recents,
                'page' => 1,
                'perpage' => 25,
                'total_count' => count($recents),
            ], 200),
            'https://jkt48connect.test/api/recent/*/gifts' => function ($request) use ($giftsByDataId) {
                preg_match('#/api/recent/([^/]+)/gifts#', $request->url(), $matches);
                $dataId = $matches[1] ?? '';

                return Http::response($giftsByDataId[$dataId] ?? [], 200);
            },
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function item(array $overrides = []): array
    {
        return array_merge([
            'data_id' => 'idn-oniel-1',
            'type' => 'idn',
            'idn' => [
                'username' => 'jkt48_oniel',
                'slug' => 'saya-kembali-260924180957',
                'title' => 'saya kembali',
            ],
            'member' => [
                'name' => 'Cornelia Vanisa',
                'nickname' => 'Oniel',
            ],
            'live_info' => [
                'duration' => 3735000,
                'viewers' => ['num' => 10244],
                'date' => [
                    'start' => '2026-09-24T11:09:57.000Z',
                    'end' => '2026-09-24T12:10:03.000Z',
                ],
            ],
            'playback' => [
                'youtube_url' => 'https://youtu.be/wgE7TF1tklk',
            ],
            'youtube_replay' => ['videos' => []],
            'points' => 1038,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function showroomItem(): array
    {
        return [
            'data_id' => 'sr-510064-23658939',
            'type' => 'showroom',
            'idn' => [],
            'member' => [
                'name' => 'Nayla Suji',
                'nickname' => 'Nayla',
            ],
            'live_info' => [
                'duration' => 5784000,
                'viewers' => ['num' => 4615],
                'date' => [
                    'start' => '2026-11-07T16:08:33.000Z',
                    'end' => '2026-11-07T17:44:57.211Z',
                ],
            ],
            'playback' => [
                'youtube_url' => null,
            ],
            'youtube_replay' => [
                'videos' => [
                    [
                        'title' => 'Nayla Suji — Live bersama Nayla Suji',
                        'url' => 'https://youtu.be/pAdv6yigFoE',
                        'youtube_url' => 'https://www.youtube.com/watch?v=pAdv6yigFoE',
                    ],
                ],
            ],
            'points' => 72783,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function gifts(array $overrides = []): array
    {
        return array_merge([
            'gift_summary' => ['status' => 'partial'],
            'gold_summary' => ['total_gold' => 1038],
            'gifts' => [
                [
                    'user_id' => 'u1',
                    'gifts' => [
                        ['name' => 'Angklung', 'point' => 29, 'num' => 7, 'send_count' => 1],
                        ['name' => 'Panjat', 'point' => 50, 'num' => 4, 'send_count' => 1],
                    ],
                ],
            ],
            'list' => [
                ['name' => 'Angklung', 'img' => 'https://cdn.test/angklung.png', 'point' => 29, 'num' => 7, 'send_count' => 1],
                ['name' => 'Panjat', 'img' => 'https://cdn.test/panjat.png', 'point' => 50, 'num' => 4, 'send_count' => 1],
            ],
            'users' => [
                ['id' => 'u1', 'name' => 'Wetee .', 'avatar_url' => 'https://cdn.test/wetee.webp', 'comments' => 13],
                ['id' => 'u2', 'name' => 'Laila Akrima -', 'avatar_url' => 'https://cdn.test/laila.webp', 'comments' => 5],
            ],
            'fans' => [
                ['id' => 'u1', 'name' => 'Wetee .', 'avatar_url' => 'https://cdn.test/wetee.webp', 'gold' => 220],
                ['id' => 'u2', 'name' => 'Laila Akrima -', 'avatar_url' => 'https://cdn.test/laila.webp', 'gold' => 187],
            ],
            'total_count' => 2,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function showroomGifts(): array
    {
        return [
            'gift_summary' => ['status' => 'partial'],
            'gifts' => [
                [
                    'user_id' => '5400794',
                    'gifts' => [
                        ['name' => 'Twinkle star', 'point' => 1, 'num' => 60, 'send_count' => 6],
                        ['name' => 'Heart(pink)(anime)', 'point' => 3, 'num' => 10, 'send_count' => 1],
                    ],
                ],
                [
                    'user_id' => '4756820',
                    'gifts' => [
                        ['name' => 'Twinkle star', 'point' => 1, 'num' => 30, 'send_count' => 3],
                    ],
                ],
            ],
            'list' => [
                ['name' => 'Twinkle star', 'img' => 'https://cdn.test/twinkle.png', 'point' => 1, 'num' => 90, 'send_count' => 9],
                ['name' => 'Heart(pink)(anime)', 'img' => 'https://cdn.test/heart.png', 'point' => 3, 'num' => 10, 'send_count' => 1],
            ],
            'users' => [
                ['id' => '5400794', 'name' => 'MUKTAFI ', 'avatar_url' => 'https://cdn.test/muktafi.png', 'comments' => 43],
                ['id' => '4756820', 'name' => 'Fadhil', 'avatar_url' => 'https://cdn.test/fadhil.png', 'comments' => 0],
            ],
            'fans' => [],
            'total_count' => 190,
        ];
    }
}
