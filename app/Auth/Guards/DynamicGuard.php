<?php

namespace App\Auth\Guards;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class DynamicGuard implements Guard {

    protected $guard;

    public function __construct() {
        $this->guard = $this->resolveGuard();
    }

    public function resolveGuard() {
        if (Config::get('auth.use_imap_authentication')) {
            return Auth::guard('imap');
        }
        return Auth::guard('web');
    }

    public function check() {
        return $this->guard->check();
    }

    public function guest() {
        return $this->guard->guest();
    }

    public function user() {
        return $this->guard->user();
    }

    public function id() {
        return $this->guard->id();
    }

    public function validate(array $credentials = []) {
        return $this->guard->validate($credentials);
    }

    public function hasUser()
    {
        return $this->guard->hasUser();
    }

    public function setUser(Authenticatable $user) {
        $this->guard->setUser($user);
    }

    public function attempt(array $credentials = [], $remember = false) {
        if ($this->guard instanceof StatefulGuard) {
            return $this->guard->attempt($credentials, $remember);
        }

        throw new \Exception("Guard does not implement StatefulGuard");
    }

    public function logout() {
        if ($this->guard instanceof StatefulGuard) {
            return $this->guard->logout();
        }

        throw new \Exception("Guard does not implement StatefulGuard");
    }
}
