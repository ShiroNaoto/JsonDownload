<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name!',
            'username.required' => 'Please enter your username!',
            'username.unique' => 'The username has already been taken!',
            'email.required' => 'Please enter your email!',
            'email.unique' => 'The email has already been taken!',
            'email.email' => 'The email must be a valid email address.',
            'password.required' => 'Please enter your password!',
            'password.min' => 'The password must be at least :min characters!',
            'password_confirmation.required' => 'Please re enter your password!',
            'password_confirmation.same' => 'The password does not match!',
        ];
    }

    public function withValidator($validator)
    {
        $validator->stopOnFirstFailure();
    }

    protected function failedValidation($validator)
    {
        $errors = $validator->errors()->toArray();
        $firstErrorKey = array_key_first($errors);
        $firstErrorMessage = $errors[$firstErrorKey][0];

        $response = response()->json([
            'message' => $firstErrorMessage,
            'field' => $firstErrorKey,
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
