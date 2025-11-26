<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;

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
        // share app settings with every Inertia response so Vue can read logo/app name/hero/login images
        $appSettings = Setting::allKeyValues();

        return [
            ...parent::share($request),
            'appSettings' => [
                'app_name' => $appSettings['app_name'] ?? config('app.name'),
                'sidebar_name' => $appSettings['sidebar_name'] ?? null,
                'logo' => $appSettings['logo'] ?? null,
                'hero_image' => $appSettings['hero_image'] ?? null,
                'login_image' => $appSettings['login_image'] ?? null,
            ],
            // share whether current user can manage settings
            'can' => [
                'manageSettings' => $request->user() ? $request->user()->can('manage-settings') : false,
            ],
        ];
    }
}
