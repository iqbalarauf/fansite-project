<?php

namespace App\Http\Controllers;

use App\Support\AboutPageData;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function idol(AboutPageData $page, ?string $idol = null): View
    {
        $data = $page->idol();
        $slug = (string) ($data['idolSlug'] ?? '');

        if ($idol !== null && $slug !== '' && strcasecmp($slug, $idol) !== 0) {
            abort(404);
        }

        return view('about.idol', $data);
    }

    public function fansite(AboutPageData $page): View
    {
        return view('about.fansite', $page->fansite());
    }
}
