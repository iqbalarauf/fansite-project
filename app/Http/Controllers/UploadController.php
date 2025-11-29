<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function storeImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp,avif|max:5120',
                'folder' => 'nullable|string',
            ]);

            $folder = trim($request->input('folder', 'uploads'), '/');
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            $file = $request->file('image');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $file->getClientOriginalExtension();
            $safeBase = Str::slug($originalName) ?: 'image';
            $uniqueName = $safeBase.'-'.Str::random(8).'.'.$ext;
            $path = $file->storeAs($folder, $uniqueName, 'public');

            if (!$path) {
                return response()->json(['error' => 'Failed to store image'], 500);
            }

            return response()->json([
                'url' => Storage::url($path),
                'path' => $path,
                'filename' => $uniqueName,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->getMessage(), 'messages' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Unexpected error uploading image'], 500);
        }
    }
}
