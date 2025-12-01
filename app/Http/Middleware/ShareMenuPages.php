<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CustomPage;
use Inertia\Inertia;

class ShareMenuPages
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $menuPages = CustomPage::where('status', 'published')
            ->where('show_in_menu', true)
            ->orderBy('menu_order')
            ->orderBy('title')
            ->get(['title', 'slug'])
            ->map(fn($page) => [
                'title' => $page->title,
                'slug' => $page->slug,
            ]);

        Inertia::share('menuPages', $menuPages);

        return $next($request);
    }
}
