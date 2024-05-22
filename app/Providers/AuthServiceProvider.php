<?php

namespace App\Providers;

use App\Auth\Guards\DynamicGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

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
