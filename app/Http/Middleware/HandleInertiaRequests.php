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
        // share app settings with every Inertia response
        $appSettings = Setting::allKeyValues();

        // Get statistics from show_teater table
        $teaterStats = [
            'total_shows' => \DB::table('show_teater')->count(),
            'unique_setlists' => \DB::table('show_teater')->distinct()->count('setlist'),
            'unique_unit_songs' => \DB::table('show_teater')->distinct()->count('unit_song'),
        ];

        return [
            ...parent::share($request),
            'appSettings' => [
                'app_name' => $appSettings['app_name'] ?? config('app.name'),
                'sidebar_name' => $appSettings['sidebar_name'] ?? null,
                'desc_app' => $appSettings['desc_app'] ?? null,
                'app_logo' => $appSettings['app_logo'] ?? null,
                'hero_image' => $appSettings['hero_image'] ?? null,
                'login_image' => $appSettings['login_image'] ?? null,
                'showroom_room_id' => $appSettings['showroom_room_id'] ?? '416491',
                'showroom_link' => $appSettings['showroom_link'] ?? 'https://www.showroom-live.com/r/48_KOKOHA_EGUCHI',
            ],
            'teaterStats' => $teaterStats,
            // share whether current user can manage settings
            'can' => [
                'manageSettings' => $request->user() ? $request->user()->can('manage-settings') : false,
            ],
        ];
    }
}
