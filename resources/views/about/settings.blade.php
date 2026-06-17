<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">About Page Settings</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-3xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900">Idol Settings</h3>
                <form action="{{ route('about.settings.update') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-6">
                    @csrf
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Idol Name</label>
                            <input name="idol_name" value="{{ old('idol_name', $settings['idol_name']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Birth Date</label>
                            <input type="date" name="idol_birth_date" value="{{ old('idol_birth_date', $settings['idol_birth_date']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Birth Place</label>
                            <input name="idol_birth_place" value="{{ old('idol_birth_place', $settings['idol_birth_place']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Blood Type</label>
                            <input name="idol_blood_type" value="{{ old('idol_blood_type', $settings['idol_blood_type']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Height</label>
                            <input name="idol_height" value="{{ old('idol_height', $settings['idol_height']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Idol Photo</label>
                        @if ($settings['idol_photo'])
                            <div class="mt-2">
                                <img src="{{ $settings['idol_photo'] }}" alt="Idol photo" class="h-40 rounded-3xl object-cover" />
                            </div>
                        @endif
                        <input type="file" name="idol_photo" class="mt-2 block w-full text-gray-700" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="idol_description" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('idol_description', $settings['idol_description']) }}</textarea>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Achievements</label>
                            <textarea name="idol_achievements" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('idol_achievements', $settings['idol_achievements']) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Discography</label>
                            <textarea name="idol_discography" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('idol_discography', $settings['idol_discography']) }}</textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Self Introduction</label>
                        <textarea name="idol_jikoshoukai" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('idol_jikoshoukai', $settings['idol_jikoshoukai']) }}</textarea>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Instagram</label>
                            <input name="idol_social_media_instagram" value="{{ old('idol_social_media_instagram', $settings['idol_social_media_instagram']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">TikTok</label>
                            <input name="idol_social_media_tiktok" value="{{ old('idol_social_media_tiktok', $settings['idol_social_media_tiktok']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Twitter</label>
                            <input name="idol_social_media_twitter" value="{{ old('idol_social_media_twitter', $settings['idol_social_media_twitter']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Show Idol on Welcome?</label>
                            <select name="idol_show_on_welcome" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                                <option value="true" {{ old('idol_show_on_welcome', $settings['idol_show_on_welcome']) === 'true' ? 'selected' : '' }}>Yes</option>
                                <option value="false" {{ old('idol_show_on_welcome', $settings['idol_show_on_welcome']) === 'false' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="text-right">
                            <x-primary-button>Save Idol Settings</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900">Fanbase Settings</h3>
                <form action="{{ route('about.settings.update') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-6">
                    @csrf
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fanbase Name</label>
                            <input name="fanbase_name" value="{{ old('fanbase_name', $settings['fanbase_name']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fanbase Logo</label>
                            @if ($settings['fanbase_logo'])
                                <div class="mt-2">
                                    <img src="{{ $settings['fanbase_logo'] }}" alt="Fanbase logo" class="h-20 rounded-xl object-contain" />
                                </div>
                            @endif
                            <input type="file" name="fanbase_logo" class="mt-2 block w-full text-gray-700" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="fanbase_description" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('fanbase_description', $settings['fanbase_description']) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Activities</label>
                        <textarea name="fanbase_activities" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('fanbase_activities', $settings['fanbase_activities']) }}</textarea>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CTA Enabled</label>
                            <select name="fanbase_cta_enabled" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                                <option value="true" {{ old('fanbase_cta_enabled', $settings['fanbase_cta_enabled']) === 'true' ? 'selected' : '' }}>Yes</option>
                                <option value="false" {{ old('fanbase_cta_enabled', $settings['fanbase_cta_enabled']) === 'false' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CTA Background Image</label>
                            @if ($settings['fanbase_cta_background'])
                                <div class="mt-2">
                                    <img src="{{ $settings['fanbase_cta_background'] }}" alt="CTA background" class="h-20 w-full rounded-xl object-cover" />
                                </div>
                            @endif
                            <input type="file" name="fanbase_cta_background" class="mt-2 block w-full text-gray-700" />
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CTA Title</label>
                            <input name="fanbase_cta_title" value="{{ old('fanbase_cta_title', $settings['fanbase_cta_title']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CTA Button 1 Link</label>
                            <input name="fanbase_cta_button1_link" value="{{ old('fanbase_cta_button1_link', $settings['fanbase_cta_button1_link']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CTA Description</label>
                            <textarea name="fanbase_cta_description" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('fanbase_cta_description', $settings['fanbase_cta_description']) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CTA Button 2 Link</label>
                            <input name="fanbase_cta_button2_link" value="{{ old('fanbase_cta_button2_link', $settings['fanbase_cta_button2_link']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                        </div>
                    </div>

                    <div class="text-right">
                        <x-primary-button>Save Fanbase Settings</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900">Fanbase Gallery</h3>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($settings['fanbase_gallery'] as $index => $imageUrl)
                        <div class="rounded-3xl overflow-hidden border border-gray-200 bg-white shadow-sm">
                            <img src="{{ $imageUrl }}" alt="Fanbase gallery image" class="h-48 w-full object-cover" />
                        </div>
                    @empty
                        <div class="rounded-3xl border border-gray-200 bg-gray-50 p-6 text-gray-600">No gallery images uploaded.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
