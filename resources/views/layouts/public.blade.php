<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col">
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ url('/') }}" class="text-lg font-semibold text-gray-900">{{ config('app.name', 'Laravel') }}</a>
                    <nav class="flex flex-wrap items-center gap-3 text-sm text-gray-700">
                        <a href="{{ url('/') }}" class="hover:text-gray-900">Home</a>
                        <a href="{{ route('blog.index') }}" class="hover:text-gray-900">Blog</a>
                        <a href="{{ route('public.gallery') }}" class="hover:text-gray-900">Gallery</a>
                        <a href="{{ route('about.idol') }}" class="hover:text-gray-900">About Idol</a>
                        <a href="{{ route('about.fanbase') }}" class="hover:text-gray-900">Fanbase</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="hover:text-gray-900">Dashboard</a>
                        @endauth
                        @guest
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="hover:text-gray-900">Log in</a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="hover:text-gray-900">Register</a>
                            @endif
                        @endguest
                    </nav>
                </div>
            </header>

            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                    {{ $slot }}
                </div>
            </main>

            <footer class="bg-white border-t border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                </div>
            </footer>
        </div>
    </body>
</html>
