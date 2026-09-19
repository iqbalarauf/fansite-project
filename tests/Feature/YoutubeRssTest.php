<?php

namespace Tests\Feature;

use App\Support\YoutubeRss;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YoutubeRssTest extends TestCase
{
    public function test_it_parses_playlist_feed_sorted_by_newest(): void
    {
        Http::fake([
            'www.youtube.com/feeds/videos.xml*' => Http::response($this->feed(), 200),
        ]);

        $videos = YoutubeRss::videos('https://www.youtube.com/playlist?list=PL123', 7);

        $this->assertCount(2, $videos);
        $this->assertSame('Video Terbaru', $videos[0]['title']);
        $this->assertSame('https://www.youtube.com/watch?v=VIDNEW', $videos[0]['url']);
        $this->assertSame('Deskripsi video terbaru', $videos[0]['description']);
        $this->assertSame('https://i.ytimg.com/vi/VIDNEW/hqdefault.jpg', $videos[0]['thumbnail']);
        $this->assertSame('Video Lama', $videos[1]['title']);
    }

    public function test_it_returns_empty_without_a_playlist_id(): void
    {
        $this->assertSame([], YoutubeRss::videos('https://www.youtube.com/watch?v=abcdef', 7));
        $this->assertNull(YoutubeRss::playlistId('https://www.youtube.com/watch?v=abcdef'));
        $this->assertSame('PL123', YoutubeRss::playlistId('https://www.youtube.com/watch?v=abcdef&list=PL123'));
    }

    public function test_it_limits_the_number_of_videos(): void
    {
        Http::fake([
            'www.youtube.com/feeds/videos.xml*' => Http::response($this->feed(), 200),
        ]);

        $this->assertCount(1, YoutubeRss::videos('https://www.youtube.com/playlist?list=PL123', 1));
    }

    public function test_it_returns_empty_on_failed_response(): void
    {
        Http::fake([
            'www.youtube.com/feeds/videos.xml*' => Http::response('', 500),
        ]);

        $this->assertSame([], YoutubeRss::videos('https://www.youtube.com/playlist?list=PL123', 7));
    }

    private function feed(): string
    {
        return <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xmlns:yt="http://www.youtube.com/xml/schemas/2015">
            <entry>
                <yt:videoId>VIDOLD</yt:videoId>
                <title>Video Lama</title>
                <published>2026-01-01T00:00:00+00:00</published>
                <media:group>
                    <media:title>Video Lama</media:title>
                    <media:description>Deskripsi video lama</media:description>
                    <media:thumbnail url="https://i.ytimg.com/vi/VIDOLD/hqdefault.jpg" width="480" height="360"/>
                </media:group>
            </entry>
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
}
