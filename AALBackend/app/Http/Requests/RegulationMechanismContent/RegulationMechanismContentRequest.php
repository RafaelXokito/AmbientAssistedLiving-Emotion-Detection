<?php

namespace App\Http\Requests\RegulationMechanismContent;

use Illuminate\Foundation\Http\FormRequest;

class RegulationMechanismContentRequest extends FormRequest
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
            'emotion_regulation_mechanism' => ['required','int','exists:emotions_regulation_mechanisms,id'],
            'content_type' => ['required', 'string'],
            'text' => ['nullable', 'string'],
            'file' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            'emotion_regulation_mechanism.required' => "Content's emotion regulation mechanism mechanism is required",
            'emotion_regulation_mechanism.int' => "Content's emotion regulation mechanism id must be a integer",
            'emotion_regulation_mechanism.exists' => "Content's emotion regulation mechanism mechanism doesnt exists",
            
            'content_type.required' => "Content's type is required",
            'content_type.string' => "Content's type must be a string"
        ];
    }
}
