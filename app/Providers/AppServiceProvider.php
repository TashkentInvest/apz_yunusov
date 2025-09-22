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
        if (!function_exists('number_to_uzbek_text')) {
            function number_to_uzbek_text($number) {
                return app(\App\Services\NumberToTextService::class)->convert($number);
            }
        }
    }
}
