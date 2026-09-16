<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'block-view-only'])->group(function () {
    Route::redirect('settings', 'settings/appearance');

    Route::livewire('profile', 'pages::profile')->name('profile.edit');

    Route::middleware(['verified'])->group(function () {
        Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

        Route::middleware(['role:super_admin'])->group(function () {
            Route::livewire('settings/about', 'pages::settings.about')->name('about.edit');
            Route::livewire('settings/app-settings', 'pages::settings.app-settings')->name('app-settings.edit');
            Route::livewire('settings/features', 'pages::settings.features')->name('features.edit');
            Route::livewire('settings/header-menu', 'pages::settings.header-menu')->name('header-menu.edit');
            Route::livewire('settings/photobooth', 'pages::settings.photobooth')->name('photobooth.edit');
        });

        Route::livewire('settings/security', 'pages::settings.security')
            ->middleware([
                'password.confirm',
            ])
            ->name('security.edit');
    });
});
