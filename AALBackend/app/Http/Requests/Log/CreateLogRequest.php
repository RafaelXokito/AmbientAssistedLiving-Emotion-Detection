<?php

namespace App\Http\Requests\Log;

use Illuminate\Foundation\Http\FormRequest;

class CreateLogRequest extends FormRequest
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
            "macAddress" => ["required", "string"],
            "process" => ["required", "string"],
            "content" => ["required", "string"],
        ];
    }

    public function messages()
    {
        return [
            'macAddress.required' => "Logs's MAC Address is required",
            'macAddress.string' => "Logs's MAC Address must be a string",
            'process.required' => "Logs's Process is required",
            'process.string' => "Log's Process must be a string",
            'content.required' => "Log's Content is required",
            'content.string' => "Log's content must be a string"
        ];
    }
}

