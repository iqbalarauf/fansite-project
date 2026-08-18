<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowroomProxyController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// JKT48Connect live status check
Route::get('/live-status', [ShowroomProxyController::class, 'getJkt48ConnectLiveStatus']);
