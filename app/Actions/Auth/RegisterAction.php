<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    /**
     * Register a new user and create an authentication token.
     *
     * Creates a User record from the provided attributes and issues an access token for that user.
     *
     * @param array $data Associative array with keys:
     *                    - 'first_name' (string) User's first name.
     *                    - 'last_name'  (string) User's last name.
     *                    - 'email'      (string) User's email address.
     *                    - 'password'   (string) Plain-text password to be hashed.
     * @return array{user:\App\Models\User,token:string} An array containing the created `User` under 'user' and the access token string under 'token'.
     */
    public function handle(array $data): array
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
        ]);

        $token = $user->createToken('AuthToken')->accessToken;

        return ['user' => $user, 'token' => $token];
    }
}
