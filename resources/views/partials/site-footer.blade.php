@php
    use App\Support\SettingBag;

    $__appName = SettingBag::app()['app_name'] ?? config('app.name', 'Laravel');
@endphp
<footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm text-slate-500 dark:text-slate-400 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
        <div class="font-semibold text-slate-900 dark:text-white">{{ $__appName }}</div>
        <p>© {{ now()->format('Y') }} {{ $__appName }}</p>
    </div>
</footer>