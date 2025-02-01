<?php

namespace App\Providers;

use App\Events\RealTimeMessageEvent;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RealTimeMessageEvent::class, function ($app) {
            return new RealTimeMessageEvent("");
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
