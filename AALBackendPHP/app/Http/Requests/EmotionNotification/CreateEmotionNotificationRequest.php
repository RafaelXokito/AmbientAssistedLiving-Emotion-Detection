<?php

namespace App\Http\Requests\EmotionNotification;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmotionNotificationRequest extends FormRequest
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
            'accuracyLimit' => ['required','numeric','between:0.00,100.00'],
            'durationSeconds' => ['required','integer','min:1'],
            'emotion_name' => ['required','string','exists:emotions,name']
        ];
    }

    public function messages(){
        return [
            'accuracy_limit.required' => "Emotion Notification's accuracy limit is required",
            'accuracy_limit.numeric' => "Emotion Notification's accuracy limit must be a number",
            'accuracy_limit.between' => "Emotion Notification's accuracy limit must be between [0;100]",

            'duration_seconds.required' => "Emotion Notification's duration seconds must is required",
            'duration_seconds.integer' => "Emotion Notification's duration seconds must be a integer",
            'duration_seconds.min' => "Emotion Notification's duration seconds must be bigger than 0",

            'emotion_name.required' => "Emotion Notification's emotion name is required",
            'emotion_name.string' => "Emotion Notification's emotion name must be a string",
            'emotion_name.exists' => "Emotion Notification's emotion name doesnt exists",
        ];
    }
}
