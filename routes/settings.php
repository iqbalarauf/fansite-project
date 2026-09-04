<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'block-view-only'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');

    Route::middleware(['verified'])->group(function () {
        Route::livewire('settings/about', 'pages::settings.about')->name('about.edit');

        Route::livewire('settings/app-settings', 'pages::settings.app-settings')->name('app-settings.edit');

        Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

        Route::livewire('settings/add-account', 'pages::settings.add-account')
            ->middleware(['role:super_admin'])
            ->name('add-account.edit');

        Route::livewire('settings/security', 'pages::settings.security')
            ->middleware([
                'password.confirm',
            ])
            ->name('security.edit');
    });
});
