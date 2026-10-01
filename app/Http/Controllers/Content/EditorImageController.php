<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Support\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

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

        try {
            $path = ImageOptimizer::store($validated['image'], 'content/editor');
        } catch (Throwable $exception) {
            Log::error('Gagal menyimpan gambar dari editor.', [
                'directory' => 'content/editor',
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal menyimpan gambar. Silakan hubungi administrator.',
            ], 500);
        }

        if (! Storage::disk('public')->exists($path)) {
            Log::error('Gambar editor tidak ditemukan setelah disimpan.', ['path' => $path]);

            return response()->json([
                'message' => 'Gagal menyimpan gambar. Silakan hubungi administrator.',
            ], 500);
        }

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
