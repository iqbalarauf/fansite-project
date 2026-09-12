@foreach ($items as $item)
    @php $hasChildren = ! empty($item['children']); @endphp

    @if ($level === 0)
        @if ($hasChildren)
            <details class="relative">
                <summary class="flex cursor-pointer list-none items-center gap-1 rounded-full px-4 py-2 transition text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400 [&::-webkit-details-marker]:hidden">
                    <span>{{ $item['label'] }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" /></svg>
                </summary>
                <div class="absolute right-0 top-full mt-2 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                    @include('partials.header-menu', ['items' => $item['children'], 'level' => 1])
                </div>
            </details>
        @elseif ($item['url'])
            <a href="{{ $item['url'] }}" @if ($item['external']) target="_blank" rel="noopener" @endif
               class="rounded-full px-4 py-2 transition text-slate-600 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400">{{ $item['label'] }}</a>
        @else
            <span class="rounded-full px-4 py-2 text-slate-400 dark:text-slate-500">{{ $item['label'] }}</span>
        @endif
    @else
        @if ($hasChildren)
            <div class="px-3 py-2">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ $item['label'] }}</p>
                <div class="mt-1 space-y-1 border-l border-slate-200 pl-3 dark:border-slate-700">
                    @include('partials.header-menu', ['items' => $item['children'], 'level' => $level + 1])
                </div>
            </div>
        @elseif ($item['url'])
            <a href="{{ $item['url'] }}" @if ($item['external']) target="_blank" rel="noopener" @endif
               class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm transition text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400">{{ $item['label'] }}</a>
        @else
            <span class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm text-slate-400 dark:text-slate-500">{{ $item['label'] }}</span>
        @endif
    @endif
@endforeach
