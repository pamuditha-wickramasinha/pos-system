<?php

namespace App\Providers;

use App\Models\User;
use App\Support\SiteContext;
use App\View\Composers\AppDataComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SiteContext::class);
    }

    public function boot(): void
    {
        View::composer('*', AppDataComposer::class);

        Gate::before(function (User $user) {
            return $user->isSuperAdmin() ?: null;
        });
    }
}
