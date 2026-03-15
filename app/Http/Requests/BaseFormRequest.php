<?php

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{
    /**
     * Allow all incoming requests to be treated as authorized by default.
     *
     * @return bool `true` if the request is authorized, `false` otherwise.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Convert validation failures into a standardized API error response by throwing an HttpResponseException.
     *
     * @param Validator $validator The validator instance containing the validation errors.
     * @throws HttpResponseException Thrown with a standardized API validation error response produced from the validator's errors.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors())
        );
    }
}
