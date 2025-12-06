<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AboutSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AboutSettingsController extends Controller
{
    public function edit()
    {
        // Get photo paths
        $idolPhoto = AboutSetting::get('idol_photo', '');
        $fanbaseLogo = AboutSetting::get('fanbase_logo', '');
        $ctaBackground = AboutSetting::get('fanbase_cta_background', '');

        // Get gallery and decode JSON
        $galleryJson = AboutSetting::get('fanbase_gallery', '[]');
        $gallery = json_decode($galleryJson, true) ?: [];

        // Transform storage paths to URLs
        $galleryUrls = array_map(function($path) {
            return $path ? Storage::url($path) : null;
        }, $gallery);

        $settings = [
            // Idol Settings
            'idol_name' => AboutSetting::get('idol_name', ''),
            'idol_photo' => $idolPhoto ? Storage::url($idolPhoto) : null,
            'idol_description' => AboutSetting::get('idol_description', ''),
            'idol_achievements' => AboutSetting::get('idol_achievements', ''),
            'idol_discography' => AboutSetting::get('idol_discography', ''),
            'idol_jikoshoukai' => AboutSetting::get('idol_jikoshoukai', ''),
            'idol_birth_date' => AboutSetting::get('idol_birth_date', ''),
            'idol_birth_place' => AboutSetting::get('idol_birth_place', ''),
            'idol_blood_type' => AboutSetting::get('idol_blood_type', ''),
            'idol_height' => AboutSetting::get('idol_height', ''),
            'idol_social_media_instagram' => AboutSetting::get('idol_social_media_instagram', ''),
            'idol_social_media_tiktok' => AboutSetting::get('idol_social_media_tiktok', ''),
            'idol_social_media_twitter' => AboutSetting::get('idol_social_media_twitter', ''),
            'idol_show_on_welcome' => AboutSetting::get('idol_show_on_welcome', 'false'),

            // Fanbase Settings
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

        return Inertia::render('About/Settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'idol_name' => 'nullable|string|max:255',
            'idol_description' => 'nullable|string',
            'idol_achievements' => 'nullable|string',
            'idol_discography' => 'nullable|string',
            'idol_jikoshoukai' => 'nullable|string',
            'idol_birth_date' => 'nullable|date',
            'idol_birth_place' => 'nullable|string|max:255',
            'idol_blood_type' => 'nullable|string|in:A,B,AB,O',
            'idol_height' => 'nullable|numeric|min:0|max:300',
            'idol_social_media_instagram' => 'nullable|string',
            'idol_social_media_tiktok' => 'nullable|string',
            'idol_social_media_twitter' => 'nullable|string',
            'idol_show_on_welcome' => 'nullable|boolean',
            'fanbase_name' => 'nullable|string|max:255',
            'fanbase_description' => 'nullable|string',
            'fanbase_activities' => 'nullable|string',
            'fanbase_cta_enabled' => 'nullable|boolean',
            'fanbase_cta_title' => 'nullable|string|max:255',
            'fanbase_cta_description' => 'nullable|string',
            'fanbase_cta_button1_text' => 'nullable|string|max:255',
            'fanbase_cta_button1_link' => 'nullable|url',
            'fanbase_cta_button2_text' => 'nullable|string|max:255',
            'fanbase_cta_button2_link' => 'nullable|url',
        ]);

        // Handle image uploads
        if ($request->hasFile('idol_photo')) {
            $path = $request->file('idol_photo')->store('about/idol', 'public');
            AboutSetting::set('idol_photo', $path);
        }

        if ($request->hasFile('fanbase_logo')) {
            $path = $request->file('fanbase_logo')->store('about/fanbase', 'public');
            AboutSetting::set('fanbase_logo', $path);
        }

        if ($request->hasFile('fanbase_cta_background')) {
            $path = $request->file('fanbase_cta_background')->store('about/fanbase', 'public');
            AboutSetting::set('fanbase_cta_background', $path);
        }

        // Handle gallery images
        if ($request->hasFile('fanbase_gallery')) {
            $gallery = json_decode(AboutSetting::get('fanbase_gallery', '[]'), true) ?: [];

            foreach ($request->file('fanbase_gallery', []) as $file) {
                if (count($gallery) < 5) {
                    $path = $file->store('about/fanbase/gallery', 'public');
                    $gallery[] = $path;
                }
            }

            AboutSetting::set('fanbase_gallery', json_encode($gallery));
        }

        // Save all text settings
        foreach ($validated as $key => $value) {
            if ($value !== null && !str_starts_with($key, 'fanbase_gallery')) {
                AboutSetting::set($key, is_bool($value) ? ($value ? 'true' : 'false') : $value);
            }
        }

        // Auto-generate slugs from names
        if (isset($validated['idol_name']) && $validated['idol_name']) {
            $slug = Str::slug($validated['idol_name']);
            AboutSetting::set('idol_slug', $slug);
        }

        if (isset($validated['fanbase_name']) && $validated['fanbase_name']) {
            $slug = Str::slug($validated['fanbase_name']);
            AboutSetting::set('fanbase_slug', $slug);
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    public function deleteGalleryImage($index)
    {
        $gallery = json_decode(AboutSetting::get('fanbase_gallery', '[]'), true) ?: [];

        if (isset($gallery[$index])) {
            Storage::disk('public')->delete($gallery[$index]);
            unset($gallery[$index]);
            $gallery = array_values($gallery);
            AboutSetting::set('fanbase_gallery', json_encode($gallery));
        }

        return redirect()->back()->with('success', 'Image deleted successfully');
    }
}
