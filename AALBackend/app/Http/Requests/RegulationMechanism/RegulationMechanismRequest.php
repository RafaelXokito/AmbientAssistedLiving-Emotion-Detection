<?php

namespace App\Http\Requests\RegulationMechanism;

use Illuminate\Foundation\Http\FormRequest;

class RegulationMechanismRequest extends FormRequest
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
            'description'      => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'description.required' => "Regulation mechanism's description is required",
            'description.string' => "Regulation mechanism's description must be a string"
        ];
    }
}
