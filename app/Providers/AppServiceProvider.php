<?php

namespace App\Providers;

use Creativeorange\Gravatar\Gravatar;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str as IlluminateStr;
use Twig\TwigFilter;
use Twig\TwigFunction;
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
        Twig::addGlobal('captcha_src', captcha_src());
        Twig::addGlobal('gravatar', $gravatar);
    }
}
