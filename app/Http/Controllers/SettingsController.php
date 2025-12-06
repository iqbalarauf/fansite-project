<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function edit()
    {
        return Inertia::render('Settings/Index', [
            'settings' => [
                'app_name' => Setting::get('app_name', ''),
                'sidebar_name' => Setting::get('sidebar_name', ''),
                'desc_app' => Setting::get('desc_app', ''),
                'app_logo' => Setting::get('app_logo', ''),
                'hero_image' => Setting::get('hero_image', ''),
                'login_image' => Setting::get('login_image', ''),
                'showroom_room_id' => Setting::get('showroom_room_id', '416491'),
                'showroom_link' => Setting::get('showroom_link', 'https://www.showroom-live.com/'),
                'instagram_url' => Setting::get('instagram_url', ''),
                'twitter_url' => Setting::get('twitter_url', ''),
                'tiktok_url' => Setting::get('tiktok_url', ''),
                'hero_button_1_text' => Setting::get('hero_button_1_text', 'Info Lebih Lanjut'),
                'hero_button_1_link' => Setting::get('hero_button_1_link', '/blog'),
                'hero_button_2_text' => Setting::get('hero_button_2_text', 'Temukan Kami'),
                'hero_button_2_link' => Setting::get('hero_button_2_link', '/blog'),
                'show_youtube_playlist' => Setting::get('show_youtube_playlist', 'false'),
                'youtube_playlist_url' => Setting::get('youtube_playlist_url', ''),
                'show_gallery_carousel' => Setting::get('show_gallery_carousel', 'true'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'app_name' => ['nullable', 'string', 'max:255'],
            'sidebar_name' => ['nullable', 'string', 'max:64'],
            'desc_app' => ['nullable', 'string', 'max:500'],
            'app_logo' => ['nullable', 'image', 'max:2048'],
            'hero_image' => ['nullable', 'image', 'max:5120'],
            'login_image' => ['nullable', 'image', 'max:5120'],
            'showroom_room_id' => ['nullable', 'string', 'max:255'],
            'showroom_link' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'twitter_url' => ['nullable', 'url', 'max:500'],
            'tiktok_url' => ['nullable', 'url', 'max:500'],
            'hero_button_1_text' => ['nullable', 'string', 'max:255'],
            'hero_button_1_link' => ['nullable', 'string', 'max:500'],
            'hero_button_2_text' => ['nullable', 'string', 'max:255'],
            'hero_button_2_link' => ['nullable', 'string', 'max:500'],
            'show_youtube_playlist' => ['nullable', 'string', 'in:true,false'],
            'youtube_playlist_url' => ['nullable', 'url', 'max:500'],
            'show_gallery_carousel' => ['nullable', 'string', 'in:true,false'],
        ]);

        // Update app_name
        Setting::set('app_name', $data['app_name'] ?? '');

        // Update sidebar_name (allow empty to clear)
        Setting::set('sidebar_name', $data['sidebar_name'] ?? '');

        // Update desc_app
        Setting::set('desc_app', $data['desc_app'] ?? '');

        // Update showroom settings
        Setting::set('showroom_room_id', $data['showroom_room_id'] ?? '416491');
        Setting::set('showroom_link', $data['showroom_link'] ?? 'https://www.showroom-live.com/');

        // Update social media URLs
        Setting::set('instagram_url', $data['instagram_url'] ?? '');
        Setting::set('twitter_url', $data['twitter_url'] ?? '');
        Setting::set('tiktok_url', $data['tiktok_url'] ?? '');

        // Update hero button settings
        Setting::set('hero_button_1_text', $data['hero_button_1_text'] ?? 'Info Lebih Lanjut');
        Setting::set('hero_button_1_link', $data['hero_button_1_link'] ?? '/blog');
        Setting::set('hero_button_2_text', $data['hero_button_2_text'] ?? 'Temukan Kami');
        Setting::set('hero_button_2_link', $data['hero_button_2_link'] ?? '/blog');

        // Update YouTube playlist settings
        Setting::set('show_youtube_playlist', $data['show_youtube_playlist'] ?? 'false');
        Setting::set('youtube_playlist_url', $data['youtube_playlist_url'] ?? '');

        // Update Gallery carousel settings
        Setting::set('show_gallery_carousel', $data['show_gallery_carousel'] ?? 'true');

        // Handle app_logo upload
        if ($request->hasFile('app_logo')) {
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists(str_replace('/storage/', '', $oldLogo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogo));
            }
            $path = $request->file('app_logo')->store('settings', 'public');
            Setting::set('app_logo', '/storage/' . $path);
        }

        // Handle hero_image upload
        if ($request->hasFile('hero_image')) {
            $oldHero = Setting::get('hero_image');
            if ($oldHero && Storage::disk('public')->exists(str_replace('/storage/', '', $oldHero))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldHero));
            }
            $path = $request->file('hero_image')->store('settings', 'public');
            Setting::set('hero_image', '/storage/' . $path);
        }

        // Handle login_image upload
        if ($request->hasFile('login_image')) {
            $oldLogin = Setting::get('login_image');
            if ($oldLogin && Storage::disk('public')->exists(str_replace('/storage/', '', $oldLogin))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogin));
            }
            $path = $request->file('login_image')->store('settings', 'public');
            Setting::set('login_image', '/storage/' . $path);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated');
    }
}
