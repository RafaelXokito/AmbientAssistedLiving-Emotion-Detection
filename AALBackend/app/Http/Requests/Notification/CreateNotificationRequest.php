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
            'userId' => ['required','int','exists:clients,id'],
            "title" => ['required','string'],
            "content" => ['required','string'],
            "created_at" => ['required','string','date_format:H:i d/m/Y']
        ];
    }

    public function messages()
    {
        return [
            "userId.required" => "Notifications's user id is required",
            'userId.string' => "Notification's user id must be a integer",
            'userId.exists' => "Notification's user id must be a valid client",

            "title.required" => "Notifications's title is required",
            'title.string' => "Notification's title must be a string",
        
            "content.required" => "Notifications's content is required",
            'content.string' => "Notification's content must be a string",    

            "created_at.required" => "Notifications's creation date is required",
            'created_at.string' => "Notification's creation date must be a string",
            'created_at.date_format' => "Notification's creation date must be in the format of 'H:i d/m/Y'"
        ];
    }
}
