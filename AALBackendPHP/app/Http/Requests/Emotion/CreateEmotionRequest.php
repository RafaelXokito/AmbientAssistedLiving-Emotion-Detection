<?php

namespace App\Http\Requests\Emotion;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmotionRequest extends FormRequest
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
            'name'              => ['required', 'string', 'unique:App\Models\Emotion,name'],
            'group'             => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => "Emotion's name is required",
            'name.string' => "Emotion's name must be a string",
            'name.unique:App\Models\Emotion,name' => 'The emotion name has already been taken',
            'group.required' => "Emotion's group is required",
            'group.string' => "Emotion's group must be a string",
        ];
    }
}
