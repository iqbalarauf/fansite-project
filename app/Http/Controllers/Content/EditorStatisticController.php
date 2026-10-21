<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Support\CustomPageStatistic;
use App\Support\StatisticMetric;
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
        $validated = $request->validate([
            'metric' => ['required', Rule::in(StatisticMetric::values())],
            'label' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'setlist' => ['nullable', 'string', 'max:255'],
            'unit_song' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(CustomPageStatistic::result($validated));
    }
}
