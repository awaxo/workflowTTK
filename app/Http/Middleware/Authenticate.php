<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Log;
use Closure;

/**
 * Middleware to ensure the user is authenticated.
 * Redirects unauthenticated users to the login page or returns a JSON response.
 *
 * This middleware checks if the user is authenticated and handles the redirection
 * or response accordingly. It can be used with different authentication guards.
 */
class Authenticate extends Middleware
{
    /**
     * The path to redirect to when the user is not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param mixed ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if ($this->auth->guard($guards[0] ?? null)->guest()) {
            Log::info('User is not authenticated', [
                'guards' => $guards,
                'user' => $this->auth->guard($guards[0] ?? null)->user(),
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}
