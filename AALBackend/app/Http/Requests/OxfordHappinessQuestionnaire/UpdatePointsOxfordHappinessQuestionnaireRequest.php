<?php

namespace App\Http\Requests\OxfordHappinessQuestionnaire;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePointsOxfordHappinessQuestionnaireRequest extends FormRequest
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
            'points' =>  ['required','numeric','between:1.0,6.0']
        ];
    }

    public function messages()
    {
        return [
            "points.required"       => "Questionnaire's points are required",
            'points.numeric'      => "Questionnaire's points must be numeric",
            'points.between'      => "Questionnaire's points must be between 1.0 and 6.0"
        ];
    }
}
