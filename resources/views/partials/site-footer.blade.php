@php
    use App\Support\SettingBag;
    use Illuminate\Support\Facades\Storage;

    $__app = SettingBag::app();
    $__about = SettingBag::about();

    $__appName = $__app['app_name'] ?? config('app.name', 'Laravel');
    $__appLogo = $__app['app_logo'] ?? null;
    $__fanbaseName = trim((string) ($__about['fanbase_name'] ?? '')) ?: $__appName;
@endphp
<footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto flex max-w-6xl flex-col items-center gap-3 px-4 py-6 text-sm text-slate-500 dark:text-slate-400 sm:flex-row sm:justify-between sm:gap-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            @if ($__appLogo)
                <img src="{{ Storage::url($__appLogo) }}" alt="{{ $__fanbaseName }}" class="h-10 w-10 shrink-0 object-contain" />
            @else
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-lg font-black text-white">{{ strtoupper(substr($__fanbaseName, 0, 1)) ?: 'F' }}</span>
            @endif
            <span class="font-semibold text-slate-900 dark:text-white">{{ $__fanbaseName }}</span>
        </div>

        <p>© {{ now()->format('Y') }} {{ $__appName }}</p>
    </div>
</footer>
