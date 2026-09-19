<?php

namespace App\Http\Controllers;

use App\Support\AboutPageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function show(AboutPageData $page, ?string $slug = null): View
    {
        $idol = $page->idol();
        $fansite = $page->fansite();

        if ($slug === null || $slug === '') {
            return view('about.idol', $idol);
        }

        if ($this->matchesSlug($idol['idolSlug'] ?? '', $slug)) {
            return view('about.idol', $idol);
        }

        if ($this->matchesSlug($fansite['fanbaseSlug'] ?? '', $slug)) {
            return view('about.fansite', $fansite);
        }

        abort(404);
    }

    /**
     * Legacy /about/fansite URL: redirect to the slug-based fansite URL.
     */
    public function fansite(AboutPageData $page): RedirectResponse
    {
        $slug = (string) ($page->fansite()['fanbaseSlug'] ?? '');

        return $slug !== '' ? redirect()->route('about.show', $slug) : redirect()->route('about.show');
    }

    private function matchesSlug(mixed $expected, string $slug): bool
    {
        $expected = trim((string) $expected);

        return $expected !== '' && strcasecmp($expected, $slug) === 0;
    }
}
