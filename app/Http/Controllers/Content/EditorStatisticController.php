<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Support\CustomPageStatistic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EditorStatisticController extends Controller
{
    /**
     * Hitung nilai statistic card untuk rich text editor (nilai disimpan statis).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $metrics = [
            'show_teater_all', 'show_teater_date_range', 'show_teater_setlist',
            'unit_song_all', 'unit_song_date_range', 'unit_song_setlist',
            'center_unit_song_all', 'center_unit_song_unit_song', 'center_unit_song_setlist', 'center_unit_song_date_range',
            'global_center_date_range', 'global_center_setlist',
        ];

        $validated = $request->validate([
            'metric' => ['required', Rule::in($metrics)],
            'label' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'setlist' => ['nullable', 'string', 'max:255'],
            'unit_song' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'value' => CustomPageStatistic::value($validated),
        ]);
    }
}
