<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShowroomProxyController extends Controller
{
    public function getJkt48ConnectLiveStatus()
    {
        $apiKey = env('JKT48CONNECT_API_KEY');
        $endpoint = rtrim(env('JKT48CONNECT_LIVE_URL', 'https://jkt48connect.com/api/v1/live'), '/');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-API-Key' => $apiKey,
                'Authorization' => $apiKey ? 'Bearer ' . $apiKey : null,
            ])->timeout(15)->get($endpoint);

            if (!$response->successful()) {
                return response()->json([
                    'showroom_is_live' => false,
                    'idn_is_live' => false,
                    'showroom_live_url' => null,
                    'idn_live_url' => null,
                    'source' => $endpoint,
                    'error' => 'Failed to fetch JKT48 Connect live status',
                    'status_code' => $response->status(),
                ], 200);
            }

            $items = $response->json('data') ?? $response->json() ?? [];
            $showroomOnline = false;
            $idnOnline = false;
            $showroomLiveUrl = null;
            $idnLiveUrl = null;

            if (is_array($items)) {
                foreach ($items as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $platform = strtolower((string) ($item['platform'] ?? ''));
                    $urlKey = strtolower((string) ($item['url_key'] ?? ''));
                    $streaming = $item['streaming'] ?? [];
                    $streamingUrl = is_array($streaming) ? (string) ($streaming['url'] ?? '') : '';

                    if ($urlKey !== 'jkt48_oniel') {
                        continue;
                    }

                    if ($platform === 'showroom' && $streamingUrl !== '') {
                        $showroomOnline = true;
                        $showroomLiveUrl = $streamingUrl;
                    }

                    if ($platform === 'idn' && $streamingUrl !== '') {
                        $idnOnline = true;
                        $idnLiveUrl = $streamingUrl;
                    }
                }
            }

            return response()->json([
                'showroom_is_live' => $showroomOnline,
                'idn_is_live' => $idnOnline,
                'showroom_live_url' => $showroomLiveUrl,
                'idn_live_url' => $idnLiveUrl,
                'source' => $endpoint,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'showroom_is_live' => false,
                'idn_is_live' => false,
                'showroom_live_url' => null,
                'idn_live_url' => null,
                'source' => $endpoint,
                'error' => 'Error fetching JKT48 Connect live status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
