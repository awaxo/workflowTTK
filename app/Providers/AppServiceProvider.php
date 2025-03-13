<?php

namespace App\Providers;

use App\Services\Import\ImportManager;
use App\Services\PdfService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImportManager::class, function ($app) {
            return new ImportManager();
        });
        // Register PDF service
        $this->app->singleton(PdfService::class, function ($app) {
            return new PdfService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
