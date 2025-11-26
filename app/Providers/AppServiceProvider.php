<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\Setting;
use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // register model policies
        Gate::policy(Post::class, PostPolicy::class);

        // Define a simple manage-settings gate. By default allow user with id=1
        // or the ADMIN_EMAIL environment variable when set.
        Gate::define('manage-settings', function ($user = null) {
            if (!$user) return false;

            $adminEmail = env('ADMIN_EMAIL');
            if ($adminEmail && $user->email === $adminEmail) return true;

            return $user->id === 1;
        });

        // Attempt to set the runtime application name from DB settings if available.
        // This is safe because we first check that the app_settings table exists
        // and catch any errors so boot doesn't crash during migrations or early
        // bootstrap when the database isn't available.
        try {
            if (Schema::hasTable('app_settings')) {
                $name = Setting::get('app_name', null);
                if (!empty($name)) {
                    config(['app.name' => $name]);
                }

                // runtime sidebar name used in titles / small UI label
                $sidebar = Setting::get('sidebar_name', null);
                if (!empty($sidebar)) {
                    config(['app.sidebar_name' => $sidebar]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore DB errors during boot (for example: during migrations or
            // when running artisan commands that run before DB is ready).
        }
    }
}
