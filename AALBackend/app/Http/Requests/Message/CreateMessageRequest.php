<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class CreateMessageRequest extends FormRequest
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
            'isChatbot' => ['required','boolean'],
            'body' => ['required','string']
        ];
    }

    public function messages()
    {
        return [
            'isChatbot.required' => "Message's isChatbot is required",
            'isChatbot.boolean' => "Message's isChatbot must be a boolean",
            'body.required' => "Message's body is required",
            'body.string' => "Message's body must be a string",
        ];
    }
}
