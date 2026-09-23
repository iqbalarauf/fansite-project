@extends('layouts.public', ['title' => $page->title, 'active' => ''])

@section('content')
    @php
        $backgroundValue = $page->background_color ?? 'slate';
        $hasInlineBackground = (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $backgroundValue);
        $background = $hasInlineBackground ? '' : match ($backgroundValue) {
            'white' => 'bg-white',
            'indigo' => 'bg-indigo-50',
            'transparent' => '',
            default => 'bg-slate-100 dark:bg-slate-950',
        };
        $titleAlignment = match ($page->title_alignment ?? 'left') {
            'center' => 'text-center',
            'right' => 'text-right',
            default => 'text-left',
        };
    @endphp

    <div class="{{ $background }}" @if ($hasInlineBackground) style="background-color: {{ $backgroundValue }}" @endif>
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6 lg:py-16">
            <header class="border-b border-slate-200 pb-6 dark:border-slate-800">
                <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white {{ $titleAlignment }} sm:text-5xl">{{ $page->title }}</h1>
            </header>
            <div class="space-y-5">
                @foreach ($page->blocks ?? [] as $block)
                    <x-custom-page-block :block="$block" />
                @endforeach
            </div>
        </div>
    </div>
@endsection
