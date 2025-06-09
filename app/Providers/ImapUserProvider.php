<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/*
 * Custom UserProvider for IMAP authentication.
 * This provider retrieves users from the database and checks their IMAP credentials.
 */
class ImapUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     * @return Authenticatable|null
     */
    public function retrieveById($identifier) {
        return User::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param mixed $identifier
     * @param string $token
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, $token) {
        return User::where('remember_token', $token)->first();
    }

    /**
     * Update the "remember me" token for the user.
     *
     * @param Authenticatable $user
     * @param string $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token) {
        $user = User::find($user->getAuthIdentifier());
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * Retrieve a user by their credentials.
     *
     * @param array $credentials
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials) {
        $username = str_replace('@ttk.hu', '', $credentials['email']);
        $user = User::where('email', $credentials['email'])->first();

        if ($user) {
            $user->connectToImap($username, $credentials['password']);
            if ($user->checkImapConnection()) {
                return $user;
            }
        }

        return null;
    }

    /*
     * Validate the user's credentials.
     *
     * @param Authenticatable $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials) {
        if ($user instanceof User) {
            $username = str_replace('@ttk.hu', '', $user->email);
            $user->connectToImap($username, $credentials['password']);
            return $user->checkImapConnection();
        }
        return false;
    }
}
