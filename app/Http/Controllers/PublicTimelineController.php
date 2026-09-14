<?php

namespace App\Http\Controllers;

use App\Models\Timeline;
use Illuminate\View\View;

class PublicTimelineController extends Controller
{
    public function index(): View
    {
        return view('timeline.index', [
            'timelines' => Timeline::query()->orderBy('date')->orderBy('id')->get(),
        ]);
    }
}
