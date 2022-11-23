<?php

namespace App\Http\Requests\EmotionExpression;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmotionExpressionRequest extends FormRequest
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
            'expression_name'              => ['required', 'string'],
            'emotion_name' => ['required','string','exists:emotions,name']
        ];
    }

    public function messages(){
        return [
            'expression_name.required' => "Expression's name is required",
            'expression_name.string' => "Expression's name must be a string",

            'emotion_name.required' => "Emotion Notification's emotion name is required",
            'emotion_name.string' => "Emotion Notification's emotion name must be a string",
            'emotion_name.exists' => "Emotion Notification's emotion name doesnt exists",
        ];
    }
}
