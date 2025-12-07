<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LiveStreamController extends Controller
{
    /**
     * Check if a user is currently live streaming on IDN Live
     *
     * @param Request $request
     * @param string $username
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkLiveStatus(Request $request, $username)
    {
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
                    'source' => 'api.idn.app/v2'
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
                        'source' => $endpoint
                    ]);
                }
            }

            // If all endpoints fail, assume offline
            return response()->json([
                'username' => $username,
                'is_live' => false,
                'message' => 'Unable to check live status - all endpoints failed',
                'timestamp' => now()->toISOString(),
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
                'error' => true
            ], 500);
        }
    }

    /**
     * Check live status for multiple usernames
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMultipleLiveStatus(Request $request)
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
                    'checked_at' => now()->toISOString()
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'username' => $username,
                    'is_live' => false,
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
