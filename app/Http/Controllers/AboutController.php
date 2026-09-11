<?php

namespace App\Http\Controllers;

use App\Support\AboutPageData;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function idol(AboutPageData $page): View
    {
        return view('about.idol', $page->idol());
    }

    public function fansite(AboutPageData $page): View
    {
        return view('about.fansite', $page->fansite());
    }
}
