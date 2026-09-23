@php
    use App\Support\CustomPageTheme;
    use Illuminate\Support\Facades\Storage;

    $backgroundValue = $page->background_color ?? 'slate';
    $hasInlineBackground = (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $backgroundValue);
    $background = $hasInlineBackground ? '' : match ($backgroundValue) {
        'white' => 'bg-white',
        'indigo' => 'bg-indigo-50',
        'transparent' => '',
        default => 'bg-slate-100',
    };
    $titleAlignment = match ($page->title_alignment ?? 'left') {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
    $titleColor = CustomPageTheme::titleColor($backgroundValue);
    $heroImageSrc = ($page->hero_enabled && filled($page->hero_image)) ? Storage::url($page->hero_image) : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page->title }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen {{ $background }} text-slate-800 antialiased" @if ($hasInlineBackground) style="background-color: {{ $backgroundValue }}" @endif>
        <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6 lg:py-16">
            <header class="border-b border-slate-200 pb-6">
                <h1 class="text-4xl font-black tracking-tight {{ $titleAlignment }} {{ $titleColor ? '' : 'text-slate-900' }} sm:text-5xl" @if ($titleColor) style="color: {{ $titleColor }}" @endif>{{ $page->title }}</h1>
            </header>

            @if ($heroImageSrc)
                <img src="{{ $heroImageSrc }}" alt="{{ $page->title }}" class="w-full rounded-2xl object-cover shadow-sm" loading="eager" fetchpriority="high" decoding="async" />
            @endif

            <div class="space-y-5">
                @foreach ($page->blocks ?? [] as $block)
                    <x-custom-page-block :block="$block" />
                @endforeach
            </div>
        </main>
    </body>
</html>
