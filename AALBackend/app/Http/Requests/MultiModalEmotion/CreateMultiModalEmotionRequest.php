<?php

namespace App\Http\Requests\MultiModalEmotion;

use Illuminate\Foundation\Http\FormRequest;

class CreateMultiModalEmotionRequest extends FormRequest
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
            'emotion_name' => ['required','string','exists:emotions,name']
        ];
    }


    public function messages(){
        return [
            'emotion_name.required' => "Multi modal emotion name is required",
            'emotion_name.string' => "Multi modal emotion name must be a string",
            'emotion_name.exists' => "Multi modal emotion name doesnt exists",
        ];
    }
}
