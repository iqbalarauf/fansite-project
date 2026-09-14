<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\TriviaRequest;
use App\Models\Trivia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TriviaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $trivias = Trivia::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('content.trivia.index', [
            'trivias' => $trivias,
            'search' => $search,
        ]);
    }

    public function store(TriviaRequest $request): RedirectResponse
    {
        Trivia::query()->create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'image' => $request->hasFile('image') ? $request->file('image')->store('trivia', 'public') : null,
        ]);

        return redirect()->route('content.trivia.index')
            ->with('success', 'Trivia berhasil ditambahkan.');
    }

    public function update(TriviaRequest $request, Trivia $trivia): RedirectResponse
    {
        $data = [
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
        ];

        if ($request->hasFile('image')) {
            if ($trivia->image) {
                Storage::disk('public')->delete($trivia->image);
            }

            $data['image'] = $request->file('image')->store('trivia', 'public');
        }

        $trivia->update($data);

        return redirect()->route('content.trivia.index')
            ->with('success', 'Trivia berhasil diupdate.');
    }

    public function destroy(Trivia $trivia): RedirectResponse
    {
        if ($trivia->image) {
            Storage::disk('public')->delete($trivia->image);
        }

        $trivia->delete();

        return redirect()->route('content.trivia.index')
            ->with('success', 'Trivia berhasil dihapus.');
    }
}
