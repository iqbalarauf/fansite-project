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
                'showroom_link' => Setting::get('showroom_link', 'https://www.showroom-live.com/r/48_KOKOHA_EGUCHI'),
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
        ]);

        // Update app_name
        Setting::set('app_name', $data['app_name'] ?? '');

        // Update sidebar_name (allow empty to clear)
        Setting::set('sidebar_name', $data['sidebar_name'] ?? '');

        // Update desc_app
        Setting::set('desc_app', $data['desc_app'] ?? '');

        // Update showroom settings
        Setting::set('showroom_room_id', $data['showroom_room_id'] ?? '416491');
        Setting::set('showroom_link', $data['showroom_link'] ?? 'https://www.showroom-live.com/r/48_KOKOHA_EGUCHI');

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
