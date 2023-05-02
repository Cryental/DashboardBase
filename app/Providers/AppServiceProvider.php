<?php

namespace App\Providers;

use Creativeorange\Gravatar\Gravatar;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use TwigBridge\Facade\Twig;

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
        Paginator::useBootstrapFive();

        $gravatar = new Gravatar();
        Twig::addGlobal('gravatar', $gravatar);
        Twig::addGlobal('captcha_src', captcha_src());
    }
}
