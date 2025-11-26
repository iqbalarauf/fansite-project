<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            // Prefer app name and sidebar short name from DB (settings) if available,
            // otherwise fall back to config.
            $appName = \App\Models\Setting::get('app_name', config('app.name', 'Laravel'));
            $sidebarName = \App\Models\Setting::get('sidebar_name', null);
            $favicon = \App\Models\Setting::get('logo', null);
        @endphp

        @if(isset($page))
            <title inertia>{{ $sidebarName ?? $appName }}</title>
        @else
            <title>{{ $sidebarName ?? $appName }}</title>
        @endif

        @if($favicon)
            <link rel="icon" type="image/png" href="{{ $favicon }}" />
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @if(isset($page))
            @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
            @inertiaHead
        @else
            @vite(['resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans antialiased">
        @if(isset($page))
            @inertia
        @else
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="/" class="text-lg font-semibold">{{ $appName }}</a>
                        <a href="/blog" class="text-sm text-gray-700">Blog</a>
                        @auth
                            <a href="{{ route('posts.manage') }}" class="text-sm text-gray-700">My Posts</a>
                        @endauth
                    </div>

                    <div class="flex items-center space-x-4">
                        @guest
                            <a href="{{ route('login') }}" class="text-sm text-gray-700">Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-sm text-gray-700">Register</a>
                            @endif
                        @else
                            <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm text-gray-700">Logout</button>
                            </form>
                        @endguest
                    </div>
                </div>
            </header>

            @include('partials.flash')

            <main class="py-6">
                @yield('content')
            </main>
        @endif
    </body>
</html>
