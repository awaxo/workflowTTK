<?php

namespace App\Providers;

use App\Http\Composers\MenuComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Class MenuServiceProvider
 *
 * This service provider is responsible for composing the vertical menu view with necessary data.
 * It uses a view composer to inject data into the 'layouts.sections.menu.verticalMenu' view.
 */
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
        View::composer('layouts.sections.menu.verticalMenu', MenuComposer::class);
    }
}
