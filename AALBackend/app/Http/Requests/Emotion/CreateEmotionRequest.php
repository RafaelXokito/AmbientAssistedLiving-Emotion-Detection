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
            'display_name'      => ['required', 'string'],
            'weight'             => ['required', 'numeric', 'min:-1', 'max:1'],
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
            'display_name.required' => "Emotion's display name is required",
            'display_name.string' => "Emotion's display name must be a string",
            'weight.required' => "Emotion's weight is required",
            'weight.numeric' => "Emotion's weight must be numeric between [-1;1]",
            'weight.min' => "Emotion's weight must be bigger than -1",
            'weight.max' => "Emotion's weight must be smaller than 1",
        ];
    }
}
