<?php

namespace App\Providers;

use bunq\Context\ApiContext;
use bunq\Context\BunqContext;
use Illuminate\Support\ServiceProvider;

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
        BunqContext::loadApiContext(ApiContext::restore(base_path('bunq.conf')));
    }
}
