<?php

namespace App\Http\Controllers;

use App\Models\ShowTeater;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowTeaterController extends Controller
{
    /**
     * Display a listing of the show teater records.
     */
    public function index(Request $request): View
    {
        $query = ShowTeater::query();

        if ($request->filled('show')) {
            $query->where('show_id', $request->input('show'));
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $from = $request->input('date_from');
            $to = $request->input('date_to');

            if ($from && $to) {
                $query->whereBetween('show_date', [$from, $to]);
            } elseif ($from) {
                $query->where('show_date', '>=', $from);
            } elseif ($to) {
                $query->where('show_date', '<=', $to);
            }
        }

        $sort = $request->input('sort', 'asc') === 'desc' ? 'desc' : 'asc';

        $showTeaters = $query
            ->orderBy('show_id', $sort)
            ->paginate(10)
            ->withQueryString();

        return view('show-teater.index', compact('showTeaters'));
    }
}
