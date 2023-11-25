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
        if($request->input('details') == null){
            return [
                'id' => $this->id,
                'questionnairable_id' => $this->questionnaire->id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'points' => $this->questionnaire->points,
                'client_id' => $this->questionnaire->client_id
            ];
        }else{
            return [
                'id' => $this->id,
                'questionnairable_id' => $this->questionnaire->id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'points' => $this->questionnaire->points,
                'client_id' => $this->questionnaire->client_id,
                'responses' => $this->questionnaire->responses
            ];
        }
        
    }
}
