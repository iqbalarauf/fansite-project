<?php

namespace App\Http\Controllers;

use App\Support\WelcomePageData;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(WelcomePageData $page): View
    {
        return view('welcome', $page->toArray());
    }
}
