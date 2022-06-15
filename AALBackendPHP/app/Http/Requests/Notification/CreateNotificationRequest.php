<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class CreateNotificationRequest extends FormRequest
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
            "duration" => ['required','integer','min:1'],
            "accuracy" => ['required','numeric','between:0.00,100.00'],
            "emotion_name" => ['required','string','exists:emotions,name'],
            'file' => ['required', 'image', 'mimes:jpg,bmp,png', 'max:512'],
        ];
    }

    public function messages()
    {
        return [
            "duration.required" => "Notifications's duration is required",
            'duration.integer' => "Notification's duration must be a integer",
            'duration.min' => "Notification's duration must be bigger than 0",

            'emotion_name.required' => "Notification's emotion name is required",
            'emotion_name.string' => "Notification's emotion name must be a string",
            'emotion_name.exists' => "Notification's emotion name doesnt exists"
        ];
    }
}
