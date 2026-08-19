<?php

namespace App\Http\Controllers;

use App\Models\CustomPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CustomPageController extends Controller
{
    public function index(): View
    {
        return view('pages.index', [
            'pages' => CustomPage::query()->latest('updated_at')->get(),
        ]);
    }

    public function edit(CustomPage $customPage): View
    {
        return view('custom-pages.admin', [
            'pageId' => $customPage->id,
        ]);
    }

    public function destroy(CustomPage $customPage): RedirectResponse
    {
        $customPage->delete();

        return to_route('pages.index')->with('success', __('Page deleted.'));
    }

    public function show(CustomPage $customPage): View
    {
        abort_unless($customPage->status === 'published', 404);

        return view('custom-pages.show', [
            'page' => $customPage,
        ]);
    }
}
