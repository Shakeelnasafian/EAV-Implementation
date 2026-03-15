<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{
    /**
     * Validation rules for the login request input.
     *
     * @return array An associative array mapping field names to validation rules: `'email'` must be present and a valid email address, `'password'` must be present and a string.
     */
    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string',
        ];
    }
}
