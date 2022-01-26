<?php

namespace Humans\SingletonRoutes;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SingletonRoutesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::macro('singleton', function (string $name, string $controller) {
            return new SingletonRoute($name, $controller);
        });
    }
}