<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.site-head', ['title' => $title ?? null, 'metaDescription' => $metaDescription ?? null, 'ogImage' => $ogImage ?? null])
    </head>
    <body class="min-h-dvh bg-slate-950 text-slate-100 antialiased">
        @yield('content')
    </body>
</html>
