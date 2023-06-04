<?php

namespace Patslaf\DigitalAcorn\Zoho20;

use Illuminate\Support\ServiceProvider;

class Zoho20ServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
