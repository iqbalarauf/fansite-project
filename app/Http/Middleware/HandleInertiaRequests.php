<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;
use App\Models\AboutSetting;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // share app settings with every Inertia response
        $appSettings = Setting::allKeyValues();

        return [
            ...parent::share($request),
            // Only share essential app settings needed across all pages (header, footer, navigation)
            'appSettings' => [
                'app_name' => $appSettings['app_name'] ?? config('app.name'),
                'sidebar_name' => $appSettings['sidebar_name'] ?? null,
                'app_logo' => $appSettings['app_logo'] ?? null,
                'login_image' => $appSettings['login_image'] ?? null,
                'instagram_url' => $appSettings['instagram_url'] ?? null,
                'twitter_url' => $appSettings['twitter_url'] ?? null,
                'tiktok_url' => $appSettings['tiktok_url'] ?? null,
            ],
            // Only share navigation-related about settings (for menu/header)
            'aboutSettings' => [
                'idol_name' => AboutSetting::get('idol_name', ''),
                'idol_slug' => AboutSetting::get('idol_slug', ''),
                'fanbase_name' => AboutSetting::get('fanbase_name', ''),
                'fanbase_slug' => AboutSetting::get('fanbase_slug', ''),
            ],
            // share whether current user can manage settings
            'can' => [
                'manageSettings' => $request->user() ? $request->user()->can('manage-settings') : false,
            ],
        ];
    }
}
