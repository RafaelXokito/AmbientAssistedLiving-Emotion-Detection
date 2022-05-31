<?php

namespace App\Http\Requests\Frame;

use Illuminate\Foundation\Http\FormRequest;

class CreateFrameRequest extends FormRequest
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
            'macAddress'              => ['required', 'string'],
            'emotion'              => ['required', 'string'],
            'file'              => ['required', 'array'],
            'file.*'              => ['required', 'image', 'mimes:jpg,bmp,png', 'max:512'],
            'datesFrames'              => ['required', 'array'],
            'datesFrames.*'              => ['required', 'date_format:Y-m-d H:i:s'],
            'accuraciesFrames'              => ['required', 'array'],
            'accuraciesFrames.*'              => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
            'preditionsFrames'              => ['required', 'array'],
            'preditionsFrames.*'              => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'macAddress.required' => "Frame's macAddress is required",
            'macAddress.string' => "Frame's macAddress must be a string",
            'emotion.required' => "Frame's emotion is required",
            'emotion.string' => "Frame's emotion must be a string",
            'file.required' => "Frame's files is required",
            'file.array' => "Frame's file must be an array",
            'datesFrames.required' => "Frame's dates is required",
            'datesFrames.array' => "Frame's dates must be an array",
            'accuraciesFrames.required' => "Frame's accuracies is required",
            'accuraciesFrames.array' => "Frame's accuracies must be an array",
            'preditionsFrames.required' => "Frame's predictions is required",
            'preditionsFrames.array' => "Frame's predictions must be an array",
        ];
    }
}
