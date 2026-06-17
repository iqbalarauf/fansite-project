<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Site Settings</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 bg-white rounded-3xl shadow-sm border border-gray-200 p-6">
            @if (session('success'))
                <div class="rounded-3xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 mb-6">{{ session('success') }}</div>
            @endif

            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">App Name</label>
                        <input name="app_name" value="{{ old('app_name', $settings['app_name']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sidebar Name</label>
                        <input name="sidebar_name" value="{{ old('sidebar_name', $settings['sidebar_name']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="desc_app" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">{{ old('desc_app', $settings['desc_app']) }}</textarea>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">App Logo</label>
                        @if ($settings['app_logo'])
                            <div class="mt-2">
                                <img src="{{ $settings['app_logo'] }}" alt="App logo" class="h-20 rounded-xl object-contain" />
                            </div>
                        @endif
                        <input type="file" name="app_logo" class="mt-2 block w-full text-gray-700" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hero Image</label>
                        @if ($settings['hero_image'])
                            <div class="mt-2">
                                <img src="{{ $settings['hero_image'] }}" alt="Hero image" class="h-20 w-full rounded-xl object-cover" />
                            </div>
                        @endif
                        <input type="file" name="hero_image" class="mt-2 block w-full text-gray-700" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Login Page Image</label>
                    @if ($settings['login_image'])
                        <div class="mt-2">
                            <img src="{{ $settings['login_image'] }}" alt="Login image" class="h-20 w-full rounded-xl object-cover" />
                        </div>
                    @endif
                    <input type="file" name="login_image" class="mt-2 block w-full text-gray-700" />
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Showroom Room ID</label>
                        <input name="showroom_room_id" value="{{ old('showroom_room_id', $settings['showroom_room_id']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Showroom Link</label>
                        <input name="showroom_link" value="{{ old('showroom_link', $settings['showroom_link']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Instagram URL</label>
                        <input name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Twitter URL</label>
                        <input name="twitter_url" value="{{ old('twitter_url', $settings['twitter_url']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">TikTok URL</label>
                        <input name="tiktok_url" value="{{ old('tiktok_url', $settings['tiktok_url']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hero Button 1 Text</label>
                        <input name="hero_button_1_text" value="{{ old('hero_button_1_text', $settings['hero_button_1_text']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hero Button 1 Link</label>
                        <input name="hero_button_1_link" value="{{ old('hero_button_1_link', $settings['hero_button_1_link']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hero Button 2 Text</label>
                        <input name="hero_button_2_text" value="{{ old('hero_button_2_text', $settings['hero_button_2_text']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hero Button 2 Link</label>
                        <input name="hero_button_2_link" value="{{ old('hero_button_2_link', $settings['hero_button_2_link']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Show YouTube Playlist</label>
                        <select name="show_youtube_playlist" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="true" {{ old('show_youtube_playlist', $settings['show_youtube_playlist']) === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ old('show_youtube_playlist', $settings['show_youtube_playlist']) === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">YouTube Playlist URL</label>
                        <input name="youtube_playlist_url" value="{{ old('youtube_playlist_url', $settings['youtube_playlist_url']) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Show Gallery Carousel</label>
                        <select name="show_gallery_carousel" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="true" {{ old('show_gallery_carousel', $settings['show_gallery_carousel']) === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ old('show_gallery_carousel', $settings['show_gallery_carousel']) === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>

                <div class="text-right">
                    <x-primary-button>Save Settings</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
