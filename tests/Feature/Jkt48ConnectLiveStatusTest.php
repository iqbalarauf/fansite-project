<?php

namespace Tests\Feature;

use App\Http\Controllers\ShowroomProxyController;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Jkt48ConnectLiveStatusTest extends TestCase
{
    public function test_it_maps_platforms_to_online_statuses_from_jkt48connect_api(): void
    {
        Http::fake([
            'https://jkt48connect.com/api/v1/live' => Http::response([
                'data' => [
                    ['platform' => 'idn', 'url_key' => 'jkt48_oniel', 'streaming' => ['url' => 'https://www.idn.app/live/jkt48_oniel']],
                    ['platform' => 'showroom', 'url_key' => 'jkt48_oniel', 'streaming' => ['url' => 'https://www.showroom-live.com/room/123']],
                    ['platform' => 'idn', 'url_key' => 'someone_else', 'streaming' => ['url' => 'https://www.idn.app/live/someone_else']],
                ],
            ], 200),
        ]);

        $response = (new ShowroomProxyController())->getJkt48ConnectLiveStatus();
        $payload = $response->getData(true);

        $this->assertTrue($payload['showroom_is_live']);
        $this->assertTrue($payload['idn_is_live']);
        $this->assertSame('https://www.showroom-live.com/room/123', $payload['showroom_live_url']);
        $this->assertSame('https://www.idn.app/live/jkt48_oniel', $payload['idn_live_url']);
    }
}
