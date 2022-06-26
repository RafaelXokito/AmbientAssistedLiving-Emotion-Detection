<?php

namespace App\Http\Requests\Administrator;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdministratorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'              => ['required', 'string'],
            'email'             => ['required', 'email', 'unique:App\Models\User,email'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => "Administrator's name is required",
            'name.string' => "Administrator's name must be a string",
            'email.required' => "Administrator's email is required",
            'email.email' => 'Wrong format for an email',
            'email.unique:App\Models\User,email' => 'The email has already been taken',
        ];
    }
}
