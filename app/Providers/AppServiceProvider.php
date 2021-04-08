<?php

namespace App\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        JsonResource::withoutWrapping();

        // Reduce default string length to avoid mysql 5.6.x innodb errors
        // due to maximal string index length of 767 bytes with mb4 encoding
        Schema::defaultStringLength(191);

        // Add additional Validator
        Validator::extend('checkdomains', 'App\Rules\NotBlacklistedDomain@passes');
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        if ('local' === $this->app->environment()) {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }
}
