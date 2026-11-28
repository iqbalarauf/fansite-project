<?php

namespace App\Http\Controllers;

use App\Support\LiveStreamingStatsAssembler;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveStreamingDashboardController extends Controller
{
    public function index(Request $request, LiveStreamingStatsAssembler $stats): View
    {
        return view('live-streaming.dashboard', $stats->assemble($request));
    }
}
