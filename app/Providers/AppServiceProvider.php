<?php

namespace App\Providers;

use App\Contracts\GoogleSheetsClient;
use App\Models\BlogPost;
use App\Models\CustomPage;
use App\Models\Magazine;
use App\Models\NewsPost;
use App\Models\Post;
use App\Policies\CustomPagePolicy;
use App\Policies\MagazinePolicy;
use App\Policies\PostPolicy;
use App\Services\Google\GoogleApiSheetsClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GoogleSheetsClient::class, GoogleApiSheetsClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerContentPolicies();
    }

    /**
     * Daftarkan Policy konten agar aturan peran & kepemilikan terpusat.
     */
    protected function registerContentPolicies(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(NewsPost::class, PostPolicy::class);
        Gate::policy(BlogPost::class, PostPolicy::class);
        Gate::policy(Magazine::class, MagazinePolicy::class);
        Gate::policy(CustomPage::class, CustomPagePolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        $locale = (string) config('app.locale', 'id');
        CarbonImmutable::setLocale($locale);
        Date::setLocale($locale);
        setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'id');

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
