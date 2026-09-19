<?php

namespace Tests\Feature;

use App\Support\YoutubePlaylist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YoutubePlaylistTest extends TestCase
{
    public function test_it_uses_rss_when_available(): void
    {
        Cache::flush();

        Http::fake([
            'www.youtube.com/feeds/videos.xml*' => Http::response($this->feed(), 200),
        ]);

        $videos = YoutubePlaylist::videos('https://www.youtube.com/playlist?list=PL123', 7);

        $this->assertSame('Video Terbaru', $videos[0]['title']);
        $this->assertSame('Deskripsi video terbaru', $videos[0]['description']);

        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), '/playlist?'));
    }

    public function test_it_falls_back_to_the_playlist_page_when_rss_is_unavailable(): void
    {
        Cache::flush();

        Http::fake([
            'www.youtube.com/feeds/videos.xml*' => Http::response('', 404),
            'www.youtube.com/playlist*' => Http::response($this->playlistHtml(), 200),
        ]);

        $videos = YoutubePlaylist::videos('https://www.youtube.com/playlist?list=PL123', 7);

        $this->assertCount(2, $videos);
        $this->assertSame('Video Halaman Baru', $videos[0]['title']);
        $this->assertSame('https://www.youtube.com/watch?v=PAGENEWAAAA', $videos[0]['url']);
        $this->assertSame('https://i.ytimg.com/vi/PAGENEWAAAA/hqdefault.jpg', $videos[0]['thumbnail']);
        $this->assertSame('', $videos[0]['description']);
    }

    public function test_it_limits_the_fallback_results(): void
    {
        Cache::flush();

        Http::fake([
            'www.youtube.com/feeds/videos.xml*' => Http::response('', 404),
            'www.youtube.com/playlist*' => Http::response($this->playlistHtml(), 200),
        ]);

        $this->assertCount(1, YoutubePlaylist::videos('https://www.youtube.com/playlist?list=PL123', 1));
    }

    public function test_it_caches_successful_results(): void
    {
        Cache::flush();

        Http::fake([
            'www.youtube.com/feeds/videos.xml*' => Http::response($this->feed(), 200),
        ]);

        YoutubePlaylist::videos('https://www.youtube.com/playlist?list=PL123', 7);
        YoutubePlaylist::videos('https://www.youtube.com/playlist?list=PL123', 7);

        Http::assertSentCount(1);
    }

    private function feed(): string
    {
        return <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xmlns:yt="http://www.youtube.com/xml/schemas/2015">
            <entry>
                <yt:videoId>VIDNEW</yt:videoId>
                <title>Video Terbaru</title>
                <published>2026-02-01T00:00:00+00:00</published>
                <media:group>
                    <media:title>Video Terbaru</media:title>
                    <media:description>Deskripsi video terbaru</media:description>
                    <media:thumbnail url="https://i.ytimg.com/vi/VIDNEW/hqdefault.jpg" width="480" height="360"/>
                </media:group>
            </entry>
        </feed>
        XML;
    }

    private function playlistHtml(): string
    {
        $lockup = fn (string $id, string $title): array => [
            'lockupViewModel' => [
                'contentId' => $id,
                'metadata' => ['lockupMetadataViewModel' => ['title' => ['content' => $title]]],
                'contentImage' => ['thumbnailViewModel' => ['image' => ['sources' => [['url' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg"]]]]],
            ],
        ];

        $payload = [
            'contents' => [
                'twoColumnBrowseResultsRenderer' => [
                    'tabs' => [[
                        'tabRenderer' => [
                            'content' => [
                                'sectionListRenderer' => [
                                    'contents' => [[
                                        'itemSectionRenderer' => [
                                            'contents' => [[
                                                'playlistVideoListRenderer' => [
                                                    'contents' => [
                                                        $lockup('PAGENEWAAAA', 'Video Halaman Baru'),
                                                        $lockup('PAGEOLDAAAA', 'Video Halaman Lama'),
                                                    ],
                                                ],
                                            ]],
                                        ],
                                    ]],
                                ],
                            ],
                        ],
                    ]],
                ],
            ],
        ];

        return '<html><head><script>var ytInitialData = '.json_encode($payload).';</script></head><body></body></html>';
    }
}
