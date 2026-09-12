<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\GalleryVideo;
use App\Support\SettingBag;
use Illuminate\View\View;

class PublicGalleryController extends Controller
{
    public function index(): View
    {
        $mode = SettingBag::galleryMode();

        return view('gallery.index', [
            'mode' => $mode,
            'photos' => in_array($mode, ['photos', 'both'], true)
                ? GalleryPhoto::query()->latest()->paginate(12, ['*'], 'photo_page')
                : null,
            'videos' => in_array($mode, ['videos', 'both'], true)
                ? GalleryVideo::query()->latest()->paginate(9, ['*'], 'video_page')
                : null,
        ]);
    }
}
