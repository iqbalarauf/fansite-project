<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'title' => $item->title,
                    'description' => $item->description,
                    'credit' => $item->credit,
                    'image_path' => $item->image_path ? Storage::url($item->image_path) : null,
                    'video_url' => $item->video_url,
                    'order' => $item->order,
                    'is_published' => $item->is_published,
                    'created_at' => $item->created_at,
                ];
            });

        return Inertia::render('Gallery/Index', [
            'galleries' => $galleries,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:photo,video',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'credit' => 'nullable|string|max:255',
            'image' => 'required_if:type,photo|nullable|image|max:5120',
            'video_url' => 'required_if:type,video|nullable|url|max:500',
            'order' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
        ]);

        $data = [
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'credit' => $validated['credit'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_published' => $validated['is_published'] ?? true,
        ];

        if ($validated['type'] === 'photo' && $request->hasFile('image')) {
            $path = $request->file('image')->store('gallery', 'public');
            $data['image_path'] = $path;
        } elseif ($validated['type'] === 'video') {
            $data['video_url'] = $validated['video_url'];
        }

        Gallery::create($data);

        return redirect()->route('gallery.index')->with('success', 'Gallery item created successfully');
    }

    public function update(Request $request, Gallery $gallery)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'credit' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120',
            'video_url' => 'required_if:type,video|nullable|url|max:500',
            'order' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'credit' => $validated['credit'] ?? null,
            'order' => $validated['order'] ?? $gallery->order,
            'is_published' => $validated['is_published'] ?? $gallery->is_published,
        ];

        if ($gallery->type === 'photo' && $request->hasFile('image')) {
            // Delete old image
            if ($gallery->image_path && Storage::disk('public')->exists($gallery->image_path)) {
                Storage::disk('public')->delete($gallery->image_path);
            }
            $path = $request->file('image')->store('gallery', 'public');
            $data['image_path'] = $path;
        } elseif ($gallery->type === 'video') {
            $data['video_url'] = $validated['video_url'];
        }

        $gallery->update($data);

        return redirect()->route('gallery.index')->with('success', 'Gallery item updated successfully');
    }

    public function destroy(Gallery $gallery)
    {
        if ($gallery->type === 'photo' && $gallery->image_path && Storage::disk('public')->exists($gallery->image_path)) {
            Storage::disk('public')->delete($gallery->image_path);
        }

        $gallery->delete();

        return redirect()->route('gallery.index')->with('success', 'Gallery item deleted successfully');
    }
}
