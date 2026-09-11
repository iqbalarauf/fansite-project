<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.site-head', ['title' => $title ?? null, 'metaDescription' => $metaDescription ?? null, 'ogImage' => $ogImage ?? null])
    </head>
    <body class="flex min-h-dvh flex-col bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
        @include('partials.site-header', ['active' => $active ?? 'home'])

        <main class="flex-1">
            @yield('content')
        </main>

        @include('partials.site-footer')
    </body>
</html>