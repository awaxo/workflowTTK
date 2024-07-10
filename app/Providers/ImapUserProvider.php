<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use App\Models\User;

class ImapUserProvider implements UserProvider {

    public function retrieveById($identifier) {
        return User::find($identifier);
    }

    public function retrieveByToken($identifier, $token) {
        return User::where('remember_token', $token)->first();
    }

    public function updateRememberToken(Authenticatable $user, $token) {
        $user = User::find($user->getAuthIdentifier());
        $user->setRememberToken($token);
        $user->save();
    }

    public function retrieveByCredentials(array $credentials) {
        $username = str_replace('@ttk.hu', '', $credentials['email']);
        $user = User::where('email', $credentials['email'])->first();

        if ($user) {
            $user->connectToImap($username);
            if ($user->checkImapConnection()) {
                return $user;
            }
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials) {
        if ($user instanceof User) {
            $username = str_replace('@ttk.hu', '', $user->email);

            $user->connectToImap($username);
            return $user->checkImapConnection();
        }
        return false;
    }
}
