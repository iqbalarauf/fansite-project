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

        // Get statistics from show_teater table
        $today = now()->startOfDay();

        // Get the latest show_date that is before today
        $lastUpdateDate = \DB::table('show_teater')
            ->get()
            ->filter(function ($show) use ($today) {
                // Try to parse various date formats
                $date = $this->parseShowDate($show->show_date);
                return $date && $date->lt($today);
            })
            ->sortByDesc(function ($show) {
                return $this->parseShowDate($show->show_date);
            })
            ->first()?->show_date;

        // Filter and count shows before today
        $pastShows = \DB::table('show_teater')
            ->get()
            ->filter(function ($show) use ($today) {
                $date = $this->parseShowDate($show->show_date);
                return $date && $date->lt($today);
            });

        $teaterStats = [
            'total_shows' => $pastShows->count(),
            'unique_setlists' => \DB::table('show_teater_categories')->where('type', 'setlist')->count(),
            'unique_unit_songs' => \DB::table('show_teater_categories')->where('type', 'unit_song')->count(),
            'last_update' => $lastUpdateDate,
        ];

        // Get idol photo and transform to URL
        $idolPhoto = AboutSetting::get('idol_photo', '');
        $idolPhotoUrl = $idolPhoto ? \Storage::url($idolPhoto) : null;

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
                'showroom_link' => $appSettings['showroom_link'] ?? 'https://www.showroom-live.com/',
                'instagram_url' => $appSettings['instagram_url'] ?? null,
                'twitter_url' => $appSettings['twitter_url'] ?? null,
                'tiktok_url' => $appSettings['tiktok_url'] ?? null,
            ],
            'teaterStats' => $teaterStats,
            'aboutSettings' => [
                'idol_name' => AboutSetting::get('idol_name', ''),
                'idol_slug' => AboutSetting::get('idol_slug', ''),
                'idol_photo' => $idolPhotoUrl,
                'idol_description' => AboutSetting::get('idol_description', ''),
                'idol_social_media_instagram' => AboutSetting::get('idol_social_media_instagram', ''),
                'idol_social_media_tiktok' => AboutSetting::get('idol_social_media_tiktok', ''),
                'idol_social_media_twitter' => AboutSetting::get('idol_social_media_twitter', ''),
                'idol_show_on_welcome' => AboutSetting::get('idol_show_on_welcome', 'false'),
                'fanbase_name' => AboutSetting::get('fanbase_name', ''),
                'fanbase_slug' => AboutSetting::get('fanbase_slug', ''),
            ],
            // share whether current user can manage settings
            'can' => [
                'manageSettings' => $request->user() ? $request->user()->can('manage-settings') : false,
            ],
        ];
    }

    /**
     * Parse show_date from various formats
     */
    private function parseShowDate($dateString)
    {
        try {
            // Try yyyy-mm-dd format first
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $dateString);
            }

            // Try dd.mm.yyyy format
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dateString)) {
                $parts = explode('.', $dateString);
                return \Carbon\Carbon::createFromFormat('Y-m-d', $parts[2] . '-' . $parts[1] . '-' . $parts[0]);
            }

            // Try Indonesian format: "Jumat, 28 November 2024"
            $months = [
                'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
                'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
                'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
            ];

            foreach ($months as $monthName => $monthNum) {
                if (strpos($dateString, $monthName) !== false) {
                    if (preg_match('/(\d{1,2})\s+' . $monthName . '\s+(\d{4})/', $dateString, $matches)) {
                        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                        $year = $matches[2];
                        return \Carbon\Carbon::createFromFormat('Y-m-d', $year . '-' . $monthNum . '-' . $day);
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
