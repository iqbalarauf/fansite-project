<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorImageController extends Controller
{
    /**
     * Unggah gambar dari rich text editor dan kembalikan URL publiknya.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ], [
            'image.required' => 'Gambar wajib diunggah.',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        $path = $validated['image']->store('content/editor', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
