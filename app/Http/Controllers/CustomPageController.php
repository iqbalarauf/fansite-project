<?php

namespace App\Http\Controllers;

use App\Models\CustomPage;
use App\Support\ListingQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomPageController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListingQuery::from($request, ['title', 'updated_at'], 'updated_at');

        $pages = CustomPage::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('slug', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('pages.index', [
            'pages' => $pages,
            'filters' => $filters,
        ]);
    }

    public function edit(CustomPage $customPage): View
    {
        Gate::authorize('view', $customPage);

        return view('custom-pages.admin', [
            'pageId' => $customPage->id,
        ]);
    }

    public function destroy(CustomPage $customPage): RedirectResponse
    {
        Gate::authorize('delete', $customPage);

        $customPage->delete();

        return to_route('pages.index')->with('success', __('Page deleted.'));
    }

    public function show(CustomPage $customPage): View
    {
        abort_unless($customPage->status === 'published', 404);

        $view = ($customPage->display_mode ?? 'full') === 'welcome'
            ? 'custom-pages.show-welcome'
            : 'custom-pages.show';

        return view($view, [
            'page' => $customPage,
        ]);
    }
}
