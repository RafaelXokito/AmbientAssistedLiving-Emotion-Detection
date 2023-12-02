<?php

namespace App\Http\Requests\ResponseQuestionnaire;

use Illuminate\Foundation\Http\FormRequest;

class CreateResponseQuestionnaire extends FormRequest
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
            'question' => ['required', 'integer','min:1', 'max:29'],
            'accuracy' => ['numeric','between:0.00,100.00'],
            'created_at' => ['date_format:Y-m-d H:i:s'],
            'iteration_id' => ['integer','exists:iterations,id'],
            'iteration_usage_id' => ['string','exists:iterations,usage_id'],
        ];
    }

    public function messages()
    {
        return [
            'response.required'       => "Response's text is required",
            'response.string'      => "Response's text must be a string",
            'is_why.required'      => "Response's is_why is required",
            'is_why.boolean'      => "Response's is_why must be a boolean",
            'question.required' =>  "Response's question is required", 
            'question.integer' =>  "Response's question must be a integer", 
            'question.min' =>  "Response's question must be between 1 and 29", 
            'question.max' =>  "Response's question must be between 1 and 29",
            'accuracy.numeric' => "The accuracy value must be numeric",
            'accuracy.between' => "The accuracy value must be between 0 and 100",
            'created_at.date_format' => 'The created date must follow the pattern: Y-m-d H:i:s'
        ];
    }
}
