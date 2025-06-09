<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * LoginBasic handles the basic login functionality.
 * It uses a dynamic guard to switch between different authentication methods.
 */
class LoginBasic extends Controller
{
    /**
     * Display the login page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
    }

    /**
     * Handle the login request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email-username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'email' => $request->input('email-username'),
            'password' => $request->input('password'),
        ];

        if (Auth::guard('dynamic')->attempt($credentials)) {
            $user = Auth::guard('dynamic')->user();
            if ($user->roles->isEmpty() || $user->deleted) {
                Auth::guard('dynamic')->logout();
                return back()->withErrors([
                    'email-username' => 'Nincs jogosultsága a belépéshez',
                ])->onlyInput('email-username');
            }

            $request->session()->regenerate();

            return redirect()->intended('folyamatok');
        }

        return back()->withErrors([
            'email-username' => 'A megadott adatok hibásak.',
        ])->onlyInput('email-username');
    }

    /*
     * Handle the logout request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        if (auth()->check()) {
            Auth::guard('dynamic')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
