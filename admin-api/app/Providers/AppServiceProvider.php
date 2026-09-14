<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

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
        // Force Laravel Request to capture Authorization header on Vercel
        Request::macro('getAuthorizationHeader', function () {
            return request()->header('Authorization') 
                ?? request()->server('HTTP_AUTHORIZATION') 
                ?? request()->server('REDIRECT_HTTP_AUTHORIZATION');
        });
    }
}
