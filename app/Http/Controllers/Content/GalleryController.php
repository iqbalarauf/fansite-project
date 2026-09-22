<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\GalleryPhotoRequest;
use App\Http\Requests\GalleryVideoRequest;
use App\Models\GalleryPhoto;
use App\Models\GalleryVideo;
use App\Support\GalleryVideoEmbed;
use App\Support\ImageOptimizer;
use App\Support\ListingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab') === 'videos' ? 'videos' : 'photos';

        $filters = ListingQuery::from($request, ['created_at'], 'created_at');

        $photos = GalleryPhoto::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('description', 'like', "%{$filters['search']}%")
                        ->orWhere('credit_photographer', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'], ['*'], 'photo_page')
            ->withQueryString();

        $videos = GalleryVideo::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('credit_account', 'like', "%{$filters['search']}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'], ['*'], 'video_page')
            ->withQueryString();

        return view('content.gallery.index', [
            'tab' => $tab,
            'photos' => $photos,
            'videos' => $videos,
            'filters' => $filters,
        ]);
    }

    public function storePhoto(GalleryPhotoRequest $request): RedirectResponse
    {
        GalleryPhoto::query()->create([
            'photo' => ImageOptimizer::store($request->file('photo'), 'gallery/photos'),
            'description' => $request->validated('description'),
            'credit_photographer' => $request->validated('credit_photographer'),
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ]);

        return redirect()->route('content.gallery.index', ['tab' => 'photos'])
            ->with('success', 'Foto berhasil ditambahkan.');
    }

    public function updatePhoto(GalleryPhotoRequest $request, GalleryPhoto $galleryPhoto): RedirectResponse
    {
        $data = [
            'description' => $request->validated('description'),
            'credit_photographer' => $request->validated('credit_photographer'),
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
        ];

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($galleryPhoto->photo);
            $data['photo'] = ImageOptimizer::store($request->file('photo'), 'gallery/photos');
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
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
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
            'sort_order' => (int) ($request->validated('sort_order') ?? 0),
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
