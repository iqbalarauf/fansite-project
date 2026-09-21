<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'block-view-only'])->group(function () {
    Route::redirect('settings', 'settings/security');

    Route::livewire('profile', 'pages::profile')->name('profile.edit');

    Route::middleware(['verified'])->group(function () {
        Route::livewire('appearance', 'pages::appearance.index')->name('appearance.edit');

        Route::middleware(['role:super_admin'])->group(function () {
            Route::livewire('features', 'pages::features.index')->name('features.edit');
            Route::livewire('header-menu', 'pages::header-menu.index')->name('header-menu.edit');
            Route::livewire('settings/photobooth', 'pages::photobooth.manage')->name('photobooth.edit');
        });

        Route::livewire('settings/security', 'pages::settings.security')
            ->middleware([
                'password.confirm',
            ])
            ->name('security.edit');
    });
});
