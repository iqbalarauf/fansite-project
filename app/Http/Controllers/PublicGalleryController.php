<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PublicGalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::where('is_published', true)
            ->orderBy('order', 'asc')
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
                    'created_at' => $item->created_at,
                ];
            });

        return Inertia::render('Public/Gallery', [
            'galleries' => $galleries,
        ]);
    }
}
