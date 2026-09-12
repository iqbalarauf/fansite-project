<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\GalleryPhotoRequest;
use App\Http\Requests\GalleryVideoRequest;
use App\Models\GalleryPhoto;
use App\Models\GalleryVideo;
use App\Support\GalleryVideoEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab') === 'videos' ? 'videos' : 'photos';

        return view('content.gallery.index', [
            'tab' => $tab,
            'photos' => GalleryPhoto::query()->latest()->paginate(12, ['*'], 'photo_page')->withQueryString(),
            'videos' => GalleryVideo::query()->latest()->paginate(12, ['*'], 'video_page')->withQueryString(),
        ]);
    }

    public function storePhoto(GalleryPhotoRequest $request): RedirectResponse
    {
        GalleryPhoto::query()->create([
            'photo' => $request->file('photo')->store('gallery/photos', 'public'),
            'description' => $request->validated('description'),
            'credit_photographer' => $request->validated('credit_photographer'),
        ]);

        return redirect()->route('content.gallery.index', ['tab' => 'photos'])
            ->with('success', 'Foto berhasil ditambahkan.');
    }

    public function updatePhoto(GalleryPhotoRequest $request, GalleryPhoto $galleryPhoto): RedirectResponse
    {
        $data = [
            'description' => $request->validated('description'),
            'credit_photographer' => $request->validated('credit_photographer'),
        ];

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($galleryPhoto->photo);
            $data['photo'] = $request->file('photo')->store('gallery/photos', 'public');
        }

        $galleryPhoto->update($data);

        return redirect()->route('content.gallery.index', ['tab' => 'photos'])
            ->with('success', 'Foto berhasil diupdate.');
    }

    public function destroyPhoto(GalleryPhoto $galleryPhoto): RedirectResponse
    {
        Storage::disk('public')->delete($galleryPhoto->photo);
        $galleryPhoto->delete();

        return redirect()->route('content.gallery.index', ['tab' => 'photos'])
            ->with('success', 'Foto berhasil dihapus.');
    }

    public function storeVideo(GalleryVideoRequest $request): RedirectResponse
    {
        $url = (string) $request->validated('url');

        GalleryVideo::query()->create([
            'platform' => GalleryVideoEmbed::detect($url),
            'url' => $url,
            'title' => $request->validated('title'),
            'credit_account' => $request->validated('credit_account'),
        ]);

        return redirect()->route('content.gallery.index', ['tab' => 'videos'])
            ->with('success', 'Video berhasil ditambahkan.');
    }

    public function updateVideo(GalleryVideoRequest $request, GalleryVideo $galleryVideo): RedirectResponse
    {
        $url = (string) $request->validated('url');

        $galleryVideo->update([
            'platform' => GalleryVideoEmbed::detect($url),
            'url' => $url,
            'title' => $request->validated('title'),
            'credit_account' => $request->validated('credit_account'),
        ]);

        return redirect()->route('content.gallery.index', ['tab' => 'videos'])
            ->with('success', 'Video berhasil diupdate.');
    }

    public function destroyVideo(GalleryVideo $galleryVideo): RedirectResponse
    {
        $galleryVideo->delete();

        return redirect()->route('content.gallery.index', ['tab' => 'videos'])
            ->with('success', 'Video berhasil dihapus.');
    }
}
