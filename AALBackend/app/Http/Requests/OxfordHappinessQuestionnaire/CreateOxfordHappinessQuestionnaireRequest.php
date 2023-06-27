<?php

namespace App\Http\Requests\OxfordHappinessQuestionnaire;

use Illuminate\Foundation\Http\FormRequest;

class CreateOxfordHappinessQuestionnaireRequest extends FormRequest
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
            'points' =>  ['required','numeric','between:1.0,6.0'],
            'responses' => ['required','array','size:58'],
            'responses.*' => ['required','json']
        ];
    }

    public function messages()
    {
        return [
            "points.required"       => "Questionnaire's points are required",
            'duration.numeric'      => "Questionnaire's points must be numeric",
            'duration.between'      => "Questionnaire's points must be between 1.0 and 6.0",
            'responses.required'    => "Questionnaire's responses are required",
            'responses.array'       => "Questionnaire's responses must be an array",
            'responses.size'        => "Questionnaire must have 58 responses"
        ];
    }
}
