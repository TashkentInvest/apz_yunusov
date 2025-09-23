<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NumberToTextService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(NumberToTextService::class, function ($app) {
            return new NumberToTextService();
        });
    }

    public function boot()
    {
        // No need to declare global functions here anymore.
    }
}
