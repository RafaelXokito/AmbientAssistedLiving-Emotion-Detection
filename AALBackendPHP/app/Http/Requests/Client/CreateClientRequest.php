<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class CreateClientRequest extends FormRequest
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
            'password'          => ['required', 'string'],
            'birthdate'         => ['required', 'bail', 'date', 'before:today'],
            'contact'           => ['required', function ($attribute, $value, $fail) {
                if (!preg_match("/^([9][1236])[0-9]*?$/", $value)) {
                    $fail('The phone number need to follow the portuguese number.');
                }
            }]
        ];
    }

    public function messages()
    {
        return [
            'name.required' => "Client's name is required",
            'name.string' => "Client's name must be a string",
            'email.required' => "Client's email is required",
            'email.email' => 'Wrong format for an email',
            'email.unique:App\Models\User,email' => 'The email has already been taken',
            'password.required' => "Client's password is required",
            'password.string' => "Client's password must be a string",
            'birthdate.required' => "Client's birth date is required",
            'birthdate.date' => "Client's birth date must be a date",
            'birthdate.before' => "Client's birth date must be before today",
            'contact.required' => "Client's phone number is required",
        ];
    }
}
