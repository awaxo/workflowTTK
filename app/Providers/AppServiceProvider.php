<?php

namespace App\Providers;

use App\Services\Import\ImportManager;
use App\Services\Interfaces\IDelegationService;
use App\Services\PdfService;
use Illuminate\Support\ServiceProvider;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

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

        $this->app->bind(IDelegationService::class, DelegationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
