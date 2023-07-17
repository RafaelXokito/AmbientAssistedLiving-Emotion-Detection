<?php

namespace App\Http\Requests\ResponseQuestionnaire;

use Illuminate\Foundation\Http\FormRequest;

class CreateResponseGeriatricQuestionnaire extends FormRequest
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
            'response' =>  ['required', 'string'],
            'is_why' => ['required', 'boolean'],
            'question' => ['required', 'integer','min:1', 'max:15'],
            'speech_id' => ['required', 'integer', 'exists:speeches,id']
        ];
    }

    public function messages()
    {
        return [
            "response.required"       => "Response's text is required",
            'response.string'      => "Response's text must be a string",
            'is_why.required'      => "Response's is_why is required",
            'is_why.boolean'      => "Response's is_why must be a boolean",
            'question.required' =>  "Response's question is required", 
            'question.integer' =>  "Response's question must be a integer", 
            'question.min' =>  "Response's question must be between 1 and 15", 
            'question.max' =>  "Response's question must be between 1 and 15",
            'speech_id.required' => "Response's speech id is required",
            'speech_id.integer' => "Response's speech id must be a integer",
            'speech_id.exists' => "Response's speech id does not exists",
        ];
    }
}
