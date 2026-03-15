<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

class LoginAction
{
    /**
     * Authenticate the given credentials and return the authenticated user with an access token.
     *
     * @param array $credentials Authentication credentials (for example, ['email' => '...', 'password' => '...']).
     * @return array|null An associative array with keys `user` (the authenticated user model) and `token` (the access token string), or `null` if authentication fails.
     */
    public function handle(array $credentials): ?array
    {
        if (!Auth::attempt($credentials)) {
            return null;
        }

        $user  = Auth::user();
        $token = $user->createToken('AuthToken')->accessToken;

        return ['user' => $user, 'token' => $token];
    }
}
