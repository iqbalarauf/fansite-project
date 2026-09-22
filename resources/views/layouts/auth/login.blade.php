@props(['title' => null])

@php
    $appSettings = DB::table('app_settings')->pluck('value', 'key')->all();
    $appName = $appSettings['app_name'] ?? config('app.name', 'Laravel');
    $appLogo = $appSettings['app_logo'] ?? null;
    $loginImage = $appSettings['login_image'] ?? null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="bg-slate-50 antialiased dark:bg-slate-950">
        <div class="flex min-h-dvh flex-col lg:flex-row">
            <div class="flex w-full flex-col justify-center px-6 py-10 sm:px-10 lg:w-1/2 lg:px-16 xl:px-24">
                <div class="mx-auto flex w-full max-w-md flex-col gap-8">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 self-start text-sm font-medium text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100" wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Kembali ke Beranda') }}
                    </a>

                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <div class="relative hidden w-full overflow-hidden lg:flex lg:w-1/2">
                @if ($loginImage)
                    <img src="{{ Storage::url($loginImage) }}" alt="{{ $appName }}" class="absolute inset-0 h-full w-full object-cover" loading="eager" fetchpriority="high" decoding="async" />
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/85 via-slate-950/70 to-violet-950/80"></div>
                @else
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-950 via-slate-900 to-violet-950"></div>
                    <div class="absolute inset-0 opacity-[0.12]" style="background-image: linear-gradient(to right, rgba(255,255,255,.6) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,.6) 1px, transparent 1px); background-size: 48px 48px;"></div>
                @endif

                <div class="relative z-10 flex h-full w-full flex-col items-center justify-end p-12 text-white">
                    <div class="flex flex-row items-center gap-3">
                        @if ($appLogo)
                            <img src="{{ Storage::url($appLogo) }}" alt="{{ $appName }}" class="h-16 w-16 object-cover" />
                        @endif
                        <h2 class="text-xl font-black leading-tight sm:text-2xl">{{ $appName }}</h2>
                    </div>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
