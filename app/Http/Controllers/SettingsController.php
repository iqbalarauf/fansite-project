<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = AppSetting::query()->latest()->first();

        return Inertia::render('Settings/Index', [
            'settings' => $settings ? [
                'app_name' => $settings->app_name,
                'sidebar_name' => $settings->sidebar_name,
            ] : [],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'app_name' => ['nullable', 'string', 'max:255'],
            'sidebar_name' => ['nullable', 'string', 'max:64'],
            'remove_sidebar_name' => ['nullable', 'boolean'],
        ]);

        $settings = AppSetting::query()->latest()->first() ?? new AppSetting();

        // Basic fields
        $settings->app_name = $data['app_name'] ?? $settings->app_name;
        if (($data['remove_sidebar_name'] ?? false) === true) {
            $settings->sidebar_name = null;
        } else {
            $settings->sidebar_name = $data['sidebar_name'] ?? $settings->sidebar_name;
        }

        $settings->save();

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated');
    }
}
