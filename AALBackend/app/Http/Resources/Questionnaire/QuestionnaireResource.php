<?php

namespace App\Http\Resources\Questionnaire;

use App\Http\Resources\ResponseQuestionnaire\ResponseQuestionnaireCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionnaireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $questionnaire = $this->questionnaire;
        return [
            'id' => $this->id,
            'points' => $questionnaire->points,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'client_id' => $questionnaire->client_id,
            'responses' => new ResponseQuestionnaireCollection($questionnaire->responses)
        ];
    }
}
