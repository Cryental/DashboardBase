<?php

namespace App\Providers;

use App\Helpers\NavHelper;
use Illuminate\Support\ServiceProvider;


class NavServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->scoped('Nav', function ($app) {
            return $app->make(NavHelper::class);
        });
    }
}
