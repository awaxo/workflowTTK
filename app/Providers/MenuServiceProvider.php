<?php

namespace App\Providers;

use App\Http\Composers\MenuComposer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson);

        // Share all menuData to all the views
        View::share('menuData', [$verticalMenuData]);
        
        // Register the view composer
        //View::composer('*', MenuComposer::class);
    }
}
