<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginBasic extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email-username', 'password');

        if (Auth::guard('dynamic')->attempt(['email' => $request['email-username'], 'password' => $request['password']])) {
            $user = Auth::guard('dynamic')->user();
            if ($user->roles->isEmpty() || $user->deleted) {
                Auth::guard('dynamic')->logout();
                return back()->withErrors([
                    'email-username' => 'Nincs jogosultsága a belépéshez',
                ])->onlyInput('email-username');
            }
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email-username' => 'A megadott adatok hibásak.',
        ])->onlyInput('email-username');
    }

    public function logout(Request $request)
    {
        Auth::guard('dynamic')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
