<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $this->authorize('manage-settings');

        $settings = Setting::allKeyValues();

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('manage-settings');
        $data = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'sidebar_name' => 'nullable|string|max:64',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,svg|max:5120',
            'hero_image' => 'nullable|file|mimes:png,jpg,jpeg,svg|max:10240',
            'login_image' => 'nullable|file|mimes:png,jpg,jpeg,svg|max:10240',
            'remove_logo' => 'nullable|boolean',
            'remove_hero_image' => 'nullable|boolean',
            'remove_login_image' => 'nullable|boolean',
            'remove_sidebar_name' => 'nullable|boolean',
        ]);

        if (isset($data['app_name'])) {
            Setting::set('app_name', $data['app_name']);
        }

        if (isset($data['sidebar_name'])) {
            Setting::set('sidebar_name', $data['sidebar_name']);
        }

        // handle file uploads
        foreach (['logo','hero_image','login_image'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = $field . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/settings', $filename);
                // convert to public url
                $url = Storage::url(str_replace('public/','',$path));

                Setting::set($field, $url);
            }
        }

        // handle explicit removal flags (delete file from storage and clear value)
        foreach (['logo','hero_image','login_image'] as $field) {
            $removeFlag = 'remove_' . $field;
            if ($request->boolean($removeFlag)) {
                $current = Setting::get($field);
                if ($current) {
                    $path = preg_replace('#^.*?/storage/#', '', $current);
                    $path = ltrim($path, '/');
                    if ($path && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }

                Setting::set($field, null);
            }
        }

        // explicit removal for sidebar_name
        if ($request->boolean('remove_sidebar_name')) {
            Setting::set('sidebar_name', null);
        }

        return back(303)->with('success', 'Settings updated.');
    }
}
