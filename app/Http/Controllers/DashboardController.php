<?php

namespace App\Http\Controllers;

use App\Support\DashboardAssembler;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardAssembler $dashboard): View
    {
        return view('dashboard', $dashboard->assemble($request));
    }
}
