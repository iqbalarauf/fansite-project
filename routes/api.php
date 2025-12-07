<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowroomProxyController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Showroom Live status check
Route::get('/showroom/live/{roomId}', [ShowroomProxyController::class, 'getLiveStatus']);

// IDN Live status check
Route::get('/idn-live/{username?}', [ShowroomProxyController::class, 'checkIdnLiveStatus']);
Route::post('/idn-live/check-multiple', [ShowroomProxyController::class, 'checkMultipleIdnLiveStatus']);
