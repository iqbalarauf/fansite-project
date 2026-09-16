<?php

namespace App\Http\Controllers;

use App\Models\Photobooth;
use App\Support\PhotoboothSchedule;
use Illuminate\View\View;

class PublicPhotoboothController extends Controller
{
    public function show(?string $slug = null): View
    {
        $photobooth = $slug
            ? Photobooth::query()->where('slug', $slug)->firstOrFail()
            : Photobooth::current();

        abort_if($photobooth === null, 404);

        return view('photobooth.show', [
            'photobooth' => $photobooth,
            'schedule' => PhotoboothSchedule::for($photobooth),
        ]);
    }
}
