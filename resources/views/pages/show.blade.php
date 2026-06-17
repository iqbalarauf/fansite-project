<x-public-layout>
    <div class="space-y-8">
        <div class="rounded-3xl overflow-hidden bg-white shadow-sm border border-gray-200">
            @if ($page['hero_image'])
                <img src="{{ $page['hero_image'] }}" alt="{{ $page['title'] }}" class="w-full object-cover" />
            @endif
            <div class="p-8">
                <h1 class="text-4xl font-bold text-gray-900">{{ $page['title'] }}</h1>
                <div class="mt-4 prose max-w-none text-gray-700">{!! nl2br(e($page['body'])) !!}</div>
            </div>
        </div>

        @if ($page['has_cta_section'])
            <div class="rounded-3xl overflow-hidden bg-indigo-600 text-white shadow-sm">
                @if ($page['cta_bg_image'])
                    <div class="h-56 bg-cover bg-center" style="background-image: url('{{ $page['cta_bg_image'] }}')"></div>
                @endif
                <div class="p-8">
                    <h2 class="text-3xl font-semibold">{{ $page['cta_title'] }}</h2>
                    <p class="mt-4 text-lg">{{ $page['cta_description'] }}</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        @if ($page['cta_button_url'])
                            <a href="{{ $page['cta_button_url'] }}" class="inline-flex items-center rounded-full bg-white px-5 py-3 text-sm font-semibold text-indigo-700">{{ $page['cta_button_text'] ?? 'Learn More' }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-public-layout>
