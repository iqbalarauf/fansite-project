<?php

namespace App\Http\Controllers;

use App\Models\Trivia;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTriviaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        $trivias = Trivia::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->inRandomOrder()
            ->paginate(12)
            ->withQueryString();

        return view('trivia.index', [
            'trivias' => $trivias,
            'search' => $search,
        ]);
    }
}
