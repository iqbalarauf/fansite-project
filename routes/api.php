<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiveStreamController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Live stream status check
Route::get('/livestream/{username}', [LiveStreamController::class, 'checkLiveStatus']);
Route::post('/livestream/check-multiple', [LiveStreamController::class, 'checkMultipleLiveStatus']);
