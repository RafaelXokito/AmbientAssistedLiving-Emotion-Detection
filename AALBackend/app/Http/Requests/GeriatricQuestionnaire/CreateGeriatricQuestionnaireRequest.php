<?php

namespace App\Http\Requests\GeriatricQuestionnaire;

use Illuminate\Foundation\Http\FormRequest;

class CreateGeriatricQuestionnaireRequest extends FormRequest
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
            'points' =>  ['required','numeric','between:0.0,15.0'],
            'responses' => ['required','array','size:30'],
            'responses.*' => ['required','json']
        ];
    }

    public function messages()
    {
        return [
            "points.required"       => "Questionnaire's points are required",
            'duration.numeric'      => "Questionnaire's points must be numeric",
            'duration.between'      => "Questionnaire's points must be between 0.0 and 15.0",
            'responses.required'    => "Questionnaire's responses are required",
            'responses.array'       => "Questionnaire's responses must be an array",
            'responses.size'        => "Questionnaire must have 30 responses"
        ];
    }
}
