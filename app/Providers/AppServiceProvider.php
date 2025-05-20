<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
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
        Http::macro('user', function()
        {
            return Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . request()->bearerToken(),
            ])->baseUrl(env('USER_SERVICE_URL'));
        });

        Http::macro('kursus', function()
        {
            return Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . request()->bearerToken(),
            ])->baseUrl(env('KURSUS_SERVICE_URL'));
        });

        Model::unguard();
        Model::preventLazyLoading(!app()->isProduction());
    }
}
