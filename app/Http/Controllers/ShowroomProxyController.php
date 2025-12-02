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
}
