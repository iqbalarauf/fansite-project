<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AboutSetting;
use Illuminate\Support\Facades\Storage;

class AboutController extends Controller
{
    public function idol($slug = null)
    {
        // Verify slug matches
        $expectedSlug = AboutSetting::get('idol_slug', '');
        if ($slug && $expectedSlug && $slug !== $expectedSlug) {
            abort(404);
        }

        $idolPhoto = AboutSetting::get('idol_photo', '');

        $settings = [
            'idol_name' => AboutSetting::get('idol_name', ''),
            'idol_photo' => $idolPhoto ? Storage::url($idolPhoto) : null,
            'idol_description' => AboutSetting::get('idol_description', ''),
            'idol_achievements' => AboutSetting::get('idol_achievements', ''),
            'idol_discography' => AboutSetting::get('idol_discography', ''),
            'idol_social_media' => AboutSetting::get('idol_social_media', ''),
        ];

        return Inertia::render('About/Idol', [
            'settings' => $settings,
        ]);
    }

    public function fanbase($slug = null)
    {
        // Verify slug matches
        $expectedSlug = AboutSetting::get('fanbase_slug', '');
        if ($slug && $expectedSlug && $slug !== $expectedSlug) {
            abort(404);
        }

        $fanbaseLogo = AboutSetting::get('fanbase_logo', '');
        $ctaBackground = AboutSetting::get('fanbase_cta_background', '');
        $galleryJson = AboutSetting::get('fanbase_gallery', '[]');
        $gallery = json_decode($galleryJson, true) ?: [];

        $galleryUrls = array_map(function($path) {
            return $path ? Storage::url($path) : null;
        }, $gallery);

        $settings = [
            'fanbase_name' => AboutSetting::get('fanbase_name', ''),
            'fanbase_logo' => $fanbaseLogo ? Storage::url($fanbaseLogo) : null,
            'fanbase_description' => AboutSetting::get('fanbase_description', ''),
            'fanbase_activities' => AboutSetting::get('fanbase_activities', ''),
            'fanbase_gallery' => $galleryUrls,
            'fanbase_cta_enabled' => AboutSetting::get('fanbase_cta_enabled', 'false'),
            'fanbase_cta_background' => $ctaBackground ? Storage::url($ctaBackground) : null,
            'fanbase_cta_title' => AboutSetting::get('fanbase_cta_title', ''),
            'fanbase_cta_description' => AboutSetting::get('fanbase_cta_description', ''),
            'fanbase_cta_button1_text' => AboutSetting::get('fanbase_cta_button1_text', ''),
            'fanbase_cta_button1_link' => AboutSetting::get('fanbase_cta_button1_link', ''),
            'fanbase_cta_button2_text' => AboutSetting::get('fanbase_cta_button2_text', ''),
            'fanbase_cta_button2_link' => AboutSetting::get('fanbase_cta_button2_link', ''),
        ];

        return Inertia::render('About/Fanbase', [
            'settings' => $settings,
        ]);
    }
}
