@php
    use App\Support\SettingBag;
    use Illuminate\Support\Facades\Storage;

    $code = $code ?? 404;
    $message = $message ?? '';
    $title = $title ?? ('Error '.$code);

    try {
        $appName = (string) (SettingBag::app()['app_name'] ?? '');
    } catch (\Throwable) {
        $appName = '';
    }

    if ($appName === '') {
        $appName = config('app.name', 'Laravel');
    }

    $publicDisk = Storage::disk('public');
    $lightImage = $publicDisk->exists("app/error/{$code}.svg") ? "app/error/{$code}.svg" : null;
    $darkImage = $publicDisk->exists("app/error/{$code}-dark.svg") ? "app/error/{$code}-dark.svg" : null;

    if ($lightImage === null) {
        $lightImage = $publicDisk->exists('app/error/404.svg') ? 'app/error/404.svg' : null;
        $darkImage = $publicDisk->exists('app/error/404-dark.svg') ? 'app/error/404-dark.svg' : null;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title.' — '.$appName }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                var stored = localStorage.getItem('fansite-theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (stored ? stored === 'dark' : prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
    </head>
    <body class="bg-white text-gray-800 antialiased dark:bg-gray-900 dark:text-gray-200">
        <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden p-6">
            <div class="error-grid-bg pointer-events-none absolute inset-0"></div>

            <div class="relative z-10 mx-auto w-full max-w-[242px] text-center sm:max-w-[472px]">
                <h1 class="mb-8 text-2xl font-bold text-gray-800 dark:text-white/90">ERROR</h1>

                @hasSection('illustration')
                    @yield('illustration')
                @else
                    @if ($lightImage)
                        <img src="{{ Storage::disk('public')->url($lightImage) }}" alt="{{ $code }}" class="mx-auto dark:hidden" />
                    @endif

                    @if ($darkImage)
                        <img src="{{ Storage::disk('public')->url($darkImage) }}" alt="{{ $code }}" class="mx-auto hidden dark:block" />
                    @endif
                @endif

                <p class="mt-10 mb-6 text-base text-gray-700 dark:text-gray-400 sm:text-lg">
                    {{ $message }}
                </p>

                @yield('actions')
            </div>

            <p class="absolute bottom-6 left-1/2 -translate-x-1/2 text-center text-sm text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} — {{ $appName }}
            </p>
        </div>
    </body>
</html>
