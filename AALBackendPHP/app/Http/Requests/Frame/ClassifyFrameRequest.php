<?php

namespace App\Http\Requests\Frame;

use Illuminate\Foundation\Http\FormRequest;

class ClassifyFrameRequest extends FormRequest
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
            'name'              => ['required', 'string', 'exists:emotions,name'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => "Emotion's name is required",
            'name.string' => "Emotion's name must be a string",
            'name.exists' => 'The emotion name dont exists',
        ];
    }
}
