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
            'regulation_mechanism' => ['required','string','exists:regulation_mechanisms,name']
        ];
    }

    public function messages(){
        return [

            'emotion.required' => "Emotion Regulation Mechanisms's emotion name is required",
            'emotion.string' => "Emotion Regulation Mechanisms's emotion name must be a string",
            'emotion.exists' => "Emotion Regulation Mechanisms's emotion name doesnt exists",


            'regulation_mechanism.required' => "Emotion Regulation Mechanisms's regulation mechanism name is required",
            'regulation_mechanism.string' => "Emotion Regulation Mechanisms's regulation mechanism name must be a string",
            'regulation_mechanism.exists' => "Emotion Regulation Mechanisms's regulation mechanism name doesnt exists",
        ];
    }
}
