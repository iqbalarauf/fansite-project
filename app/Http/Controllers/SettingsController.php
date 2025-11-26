<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            'remove_sidebar_name' => 'nullable|boolean',
        ]);

        if (isset($data['app_name'])) {
            Setting::set('app_name', $data['app_name']);
        }

        if (isset($data['sidebar_name'])) {
            Setting::set('sidebar_name', $data['sidebar_name']);
        }

        // explicit removal for sidebar_name
        if ($request->boolean('remove_sidebar_name')) {
            Setting::set('sidebar_name', null);
        }

        return back(303)->with('success', 'Settings updated.');
    }
}
