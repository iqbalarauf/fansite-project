<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShowroomProxyController extends Controller
{
    public function getLiveStatus($roomId)
    {
        try {
            $response = Http::get("https://www.showroom-live.com/api/live/live_info", [
                'room_id' => $roomId
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch live status',
                'live_status' => 0
            ], 500);
        }
    }

    /**
     * Check if a user is currently live streaming on IDN Live
     */
    public function checkIdnLiveStatus(Request $request, $username = null)
    {
        // If no username provided, get from settings or use default
        if (!$username) {
            $username = \App\Models\Setting::get('idn_live_username', 'jkt48_oniel');
        }

        try {
            // Try the main endpoint first
            $response = Http::timeout(10)->get("https://api.idn.app/v2/users/{$username}/live");

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'username' => $username,
                    'is_live' => $data['is_live'] ?? false,
                    'message' => $data['message'] ?? 'Status checked',
                    'timestamp' => now()->toISOString(),
                    'source' => 'api.idn.app/v2',
                    'live_url' => $data['is_live'] ? "https://www.idn.app/live/{$username}" : null
                ]);
            }

            // If main endpoint fails, try alternative endpoints
            $endpoints = [
                "https://api.idn.app/v1/users/{$username}/stream",
                "https://api.idn.app/users/{$username}/live",
                "https://api.idn.app/v1/live?username={$username}",
                "https://api.idn.app/v1/user/{$username}"
            ];

            foreach ($endpoints as $endpoint) {
                $response = Http::timeout(10)->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();

                    return response()->json([
                        'username' => $username,
                        'is_live' => $data['is_live'] ?? false,
                        'message' => $data['message'] ?? 'Status checked',
                        'timestamp' => now()->toISOString(),
                        'source' => $endpoint,
                        'live_url' => ($data['is_live'] ?? false) ? "https://www.idn.app/live/{$username}" : null
                    ]);
                }
            }

            // If all endpoints fail, assume offline
            return response()->json([
                'username' => $username,
                'is_live' => false,
                'message' => 'Unable to check live status - all endpoints failed',
                'timestamp' => now()->toISOString(),
                'live_url' => null,
                'debug_info' => [
                    'main_endpoint_status' => $response->status(),
                    'endpoints_tried' => count($endpoints) + 1
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'username' => $username,
                'is_live' => false,
                'message' => 'Error checking live status: ' . $e->getMessage(),
                'timestamp' => now()->toISOString(),
                'live_url' => null,
                'error' => true
            ], 500);
        }
    }

    /**
     * Check live status for multiple usernames
     */
    public function checkMultipleIdnLiveStatus(Request $request)
    {
        $usernames = $request->input('usernames', []);

        if (empty($usernames) || !is_array($usernames)) {
            return response()->json([
                'error' => 'Please provide usernames array',
                'example' => ['username1', 'username2']
            ], 400);
        }

        $results = [];
        foreach ($usernames as $username) {
            try {
                $response = Http::timeout(10)->get("https://api.idn.app/v2/users/{$username}/live");

                $results[] = [
                    'username' => $username,
                    'is_live' => $response->successful() ? ($response->json()['is_live'] ?? false) : false,
                    'live_url' => $response->successful() && ($response->json()['is_live'] ?? false) ? "https://www.idn.app/live/{$username}" : null,
                    'checked_at' => now()->toISOString()
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'username' => $username,
                    'is_live' => false,
                    'live_url' => null,
                    'error' => $e->getMessage(),
                    'checked_at' => now()->toISOString()
                ];
            }
        }

        return response()->json([
            'results' => $results,
            'total_checked' => count($results),
            'live_count' => count(array_filter($results, fn($r) => $r['is_live'])),
            'timestamp' => now()->toISOString()
        ]);
    }
}
