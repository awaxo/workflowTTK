<?php

namespace App\Providers;

use App\Auth\Guards\DynamicGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthServiceProvider
 *
 * This service provider is responsible for registering authentication and authorization services.
 * It allows dynamic switching between IMAP and database authentication based on configuration.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        Auth::provider('imap', function($app, array $config) {
            return new ImapUserProvider();
        });
        
        // register the dynamic guard to choose between imap and database authentication
        Auth::extend('dynamic', function($app, $name, array $config) {
            return new DynamicGuard();
        });
    }
}
