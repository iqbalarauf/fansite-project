<x-public-layout>
    <div class="space-y-8">
        <div class="grid gap-8 md:grid-cols-[320px_1fr] items-start">
            @if ($settings['idol_photo'])
                <img src="{{ $settings['idol_photo'] }}" alt="{{ $settings['idol_name'] }}" class="w-full rounded-3xl shadow-lg object-cover" />
            @endif

            <div class="space-y-4">
                <h1 class="text-4xl font-bold text-gray-900">{{ $settings['idol_name'] ?? 'Idol' }}</h1>

                @if ($settings['idol_jikoshoukai'])
                    <div class="rounded-3xl bg-white p-6 shadow-sm border border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Jikoshoukai</h2>
                        <p class="mt-3 whitespace-pre-line text-gray-700">{{ $settings['idol_jikoshoukai'] }}</p>
                    </div>
                @endif

                <div class="grid gap-3 sm:grid-cols-2">
                    @if ($settings['idol_birth_date'] || $settings['idol_birth_place'])
                        <div class="rounded-3xl bg-white p-6 shadow-sm border border-gray-200">
                            <h3 class="font-semibold text-gray-900">Kelahiran</h3>
                            <p class="mt-2 text-gray-700">
                                @if ($settings['idol_birth_place'])
                                    {{ $settings['idol_birth_place'] }}
                                @endif
                                @if ($settings['idol_birth_place'] && $settings['idol_birth_date'])
                                    ,
                                @endif
                                @if ($settings['idol_birth_date'])
                                    {{ \Carbon\Carbon::parse($settings['idol_birth_date'])->translatedFormat('j F Y') }}
                                @endif
                            </p>
                        </div>
                    @endif

                    @if ($settings['idol_blood_type'] || $settings['idol_height'])
                        <div class="rounded-3xl bg-white p-6 shadow-sm border border-gray-200">
                            <h3 class="font-semibold text-gray-900">Biodata</h3>
                            <div class="mt-2 space-y-2 text-gray-700">
                                @if ($settings['idol_blood_type'])
                                    <div><span class="font-semibold">Golongan darah:</span> {{ $settings['idol_blood_type'] }}</div>
                                @endif
                                @if ($settings['idol_height'])
                                    <div><span class="font-semibold">Tinggi badan:</span> {{ $settings['idol_height'] }} cm</div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($settings['idol_description'])
            <section class="rounded-3xl bg-white p-8 shadow-sm border border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-900">Description</h2>
                <p class="mt-4 whitespace-pre-line text-gray-700">{{ $settings['idol_description'] }}</p>
            </section>
        @endif

        <div class="grid gap-8 md:grid-cols-2">
            @if ($settings['idol_achievements'])
                <section class="rounded-3xl bg-white p-8 shadow-sm border border-gray-200">
                    <h2 class="text-2xl font-semibold text-gray-900">Achievements</h2>
                    <p class="mt-4 whitespace-pre-line text-gray-700">{{ $settings['idol_achievements'] }}</p>
                </section>
            @endif

            @if ($settings['idol_discography'])
                <section class="rounded-3xl bg-white p-8 shadow-sm border border-gray-200">
                    <h2 class="text-2xl font-semibold text-gray-900">Discography</h2>
                    <p class="mt-4 whitespace-pre-line text-gray-700">{{ $settings['idol_discography'] }}</p>
                </section>
            @endif
        </div>

        @if ($settings['idol_social_media_instagram'] || $settings['idol_social_media_tiktok'] || $settings['idol_social_media_twitter'])
            <section class="rounded-3xl bg-white p-8 shadow-sm border border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-900">Follow on Social Media</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    @if ($settings['idol_social_media_instagram'])
                        <a href="{{ $settings['idol_social_media_instagram'] }}" target="_blank" rel="noopener noreferrer" class="rounded-3xl bg-gray-50 p-6 text-center hover:bg-gray-100">
                            <div class="text-xl font-semibold text-gray-900">Instagram</div>
                            <div class="mt-2 text-gray-600">Visit profile</div>
                        </a>
                    @endif
                    @if ($settings['idol_social_media_tiktok'])
                        <a href="{{ $settings['idol_social_media_tiktok'] }}" target="_blank" rel="noopener noreferrer" class="rounded-3xl bg-gray-50 p-6 text-center hover:bg-gray-100">
                            <div class="text-xl font-semibold text-gray-900">TikTok</div>
                            <div class="mt-2 text-gray-600">Visit profile</div>
                        </a>
                    @endif
                    @if ($settings['idol_social_media_twitter'])
                        <a href="{{ $settings['idol_social_media_twitter'] }}" target="_blank" rel="noopener noreferrer" class="rounded-3xl bg-gray-50 p-6 text-center hover:bg-gray-100">
                            <div class="text-xl font-semibold text-gray-900">Twitter / X</div>
                            <div class="mt-2 text-gray-600">Visit profile</div>
                        </a>
                    @endif
                </div>
            </section>
        @endif
    </div>
</x-public-layout>
