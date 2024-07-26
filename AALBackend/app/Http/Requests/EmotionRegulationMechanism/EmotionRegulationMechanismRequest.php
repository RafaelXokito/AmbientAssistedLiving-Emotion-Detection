<?php

namespace App\Http\Requests\EmotionRegulationMechanism;

use Illuminate\Foundation\Http\FormRequest;

class EmotionRegulationMechanismRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'emotion' => ['required','string','exists:emotions,name'],
            'threshold' => ['required','integer','between:1,100']
        ];
    }

    public function messages(){
        return [

            'emotion.required' => "Emotion Regulation Mechanisms's emotion name is required",
            'emotion.string' => "Emotion Regulation Mechanisms's emotion name must be a string",
            'emotion.exists' => "Emotion Regulation Mechanisms's emotion name doesnt exists",
        ];
    }
}
