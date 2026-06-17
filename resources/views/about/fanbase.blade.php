<x-public-layout>
    <div class="space-y-8">
        <div class="grid gap-8 md:grid-cols-[250px_1fr] items-start">
            @if ($settings['fanbase_logo'])
                <img src="{{ $settings['fanbase_logo'] }}" alt="{{ $settings['fanbase_name'] }}" class="w-full rounded-3xl shadow-lg object-contain" />
            @endif

            <div class="space-y-4">
                <h1 class="text-4xl font-bold text-gray-900">{{ $settings['fanbase_name'] ?? 'Fanbase' }}</h1>

                @if ($settings['fanbase_description'])
                    <p class="text-gray-700 whitespace-pre-line">{{ $settings['fanbase_description'] }}</p>
                @endif
            </div>
        </div>

        @if ($settings['fanbase_activities'])
            <section class="rounded-3xl bg-white p-8 shadow-sm border border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-900">Activities</h2>
                <p class="mt-4 whitespace-pre-line text-gray-700">{{ $settings['fanbase_activities'] }}</p>
            </section>
        @endif

        @if (!empty($settings['fanbase_gallery']))
            <section class="rounded-3xl bg-white p-8 shadow-sm border border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-900">Gallery</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($settings['fanbase_gallery'] as $galleryImage)
                        @if ($galleryImage)
                            <img src="{{ $galleryImage }}" alt="Fanbase gallery image" class="w-full h-60 object-cover rounded-3xl shadow-sm" />
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @if (!empty($settings['fanbase_cta_enabled']) && $settings['fanbase_cta_enabled'] !== 'false')
            <section class="rounded-3xl overflow-hidden shadow-sm border border-gray-200" style="background-image: url('{{ $settings['fanbase_cta_background'] ?? '' }}'); background-size: cover; background-position: center;">
                <div class="bg-black/60 p-10">
                    <h2 class="text-3xl font-semibold text-white">{{ $settings['fanbase_cta_title'] }}</h2>
                    <p class="mt-4 text-white/90 whitespace-pre-line">{{ $settings['fanbase_cta_description'] }}</p>
                    @if ($settings['fanbase_cta_button1_text'] && $settings['fanbase_cta_button1_link'])
                        <a href="{{ $settings['fanbase_cta_button1_link'] }}" class="inline-flex mt-6 px-6 py-3 rounded-full bg-white text-gray-900 font-semibold">{{ $settings['fanbase_cta_button1_text'] }}</a>
                    @endif
                    @if ($settings['fanbase_cta_button2_text'] && $settings['fanbase_cta_button2_link'])
                        <a href="{{ $settings['fanbase_cta_button2_link'] }}" class="inline-flex mt-6 px-6 py-3 rounded-full border border-white text-white font-semibold">{{ $settings['fanbase_cta_button2_text'] }}</a>
                    @endif
                </div>
            </section>
        @endif
    </div>
</x-public-layout>
