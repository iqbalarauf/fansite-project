<?php

namespace App\Http\Controllers;

use App\Models\Magazine;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMagazineController extends Controller
{
    public function index(): View
    {
        $main = Magazine::query()->main()->latest('created_at')->first()
            ?? Magazine::query()->latest('created_at')->first();

        $archive = Magazine::query()
            ->when($main !== null, fn ($query) => $query->whereKeyNot($main->id))
            ->latest('created_at')
            ->get();

        return view('magazines.public.index', [
            'main' => $main,
            'archive' => $archive,
        ]);
    }

    public function show(Magazine $magazine): View
    {
        $magazine->increment('views');

        return view('magazines.public.show', [
            'magazine' => $magazine,
        ]);
    }

    public function download(Magazine $magazine): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($magazine->file_path), 404);

        $magazine->increment('downloads');

        return Storage::disk('public')->download(
            $magazine->file_path,
            $magazine->original_name ?? $magazine->slug.'.pdf',
        );
    }
}
