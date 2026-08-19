<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page->title }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-900 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-4xl flex-col gap-6 px-4 py-10 sm:px-6 lg:py-16">
            <header class="border-b border-zinc-200 pb-6">
                <h1 class="text-4xl font-black tracking-tight sm:text-5xl">{{ $page->title }}</h1>
            </header>
            <div class="space-y-5">
                @foreach ($page->blocks ?? [] as $block)
                    @switch($block['type'] ?? '')
                        @case('container')
                            @php
                                $background = match ($block['data']['background'] ?? 'white') {
                                    'soft' => 'bg-zinc-200',
                                    'accent' => 'bg-indigo-600 text-white',
                                    default => 'bg-white',
                                };
                                $padding = match ($block['data']['padding'] ?? 'medium') {
                                    'small' => 'p-4',
                                    'large' => 'p-10',
                                    default => 'p-6',
                                };
                            @endphp
                            <section class="rounded-2xl {{ $background }} {{ $padding }}">{{ __('Container') }}</section>
                            @break
                        @case('text')
                            <p class="whitespace-pre-line text-lg leading-8 text-zinc-700">{{ $block['data']['text'] ?? '' }}</p>
                            @break
                        @case('image')
                            <img src="{{ $block['data']['url'] ?? '' }}" alt="{{ $block['data']['alt'] ?? '' }}" class="max-h-[560px] w-full rounded-2xl object-cover shadow-sm">
                            @break
                        @case('video')
                            <video controls class="w-full rounded-2xl shadow-sm" aria-label="{{ $block['data']['title'] ?? '' }}"><source src="{{ $block['data']['url'] ?? '' }}"></video>
                            @break
                        @case('button')
                            <a href="{{ $block['data']['url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex rounded-full bg-indigo-600 px-6 py-3 font-bold text-white transition hover:bg-indigo-700">{{ $block['data']['label'] ?? '' }}</a>
                            @break
                    @endswitch
                @endforeach
            </div>
        </main>
    </body>
</html>
