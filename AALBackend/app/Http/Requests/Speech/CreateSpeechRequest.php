<?php

namespace App\Http\Requests\Speech;

use Illuminate\Foundation\Http\FormRequest;

class CreateSpeechRequest extends FormRequest
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
            'iteration_id' => ['required','integer','exists:iterations,id'],
            'iteration_usage_id' => ['required','string','exists:iterations,usage_id'],
            'datesSpeeches'              => ['required', 'array'],
            'datesSpeeches.*'              => ['required', 'date_format:Y-m-d H:i:s'],
            'accuraciesSpeeches'              => ['required', 'array'],
            'accuraciesSpeeches.*'              => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
            'textsSpeeches'              => ['required', 'array'],
            'textsSpeeches.*'              => ['required', 'string'],
            'preditionsSpeeches'              => ['required', 'array'],
            'preditionsSpeeches.*'              => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'datesSpeeches.required' => "Speech's dates is required",
            'datesSpeeches.array' => "Speech's dates must be an array",
            'accuraciesSpeeches.required' => "Speech's accuracies is required",
            'accuraciesSpeeches.array' => "Speech's accuracies must be an array",
            'textsSpeeches.required' => "Speech's texts is required",
            'textsSpeeches.array' => "Speech's texts must be an array",
            'preditionsSpeeches.required' => "Speech's predictions is required",
            'preditionsSpeeches.array' => "Speech's predictions must be an array",
        ];
    }
}
