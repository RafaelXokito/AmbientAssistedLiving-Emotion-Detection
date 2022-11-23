<?php

namespace App\Http\Requests\EmotionExpression;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmotionExpressionRequest extends FormRequest
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
            'expression_name'              => ['required', 'string']
        ];
    }

    public function messages(){
        return [
            'expression_name.required' => "Expression's name is required",
            'expression_name.string' => "Expression's name must be a string",
        ];
    }
}
