<?php

namespace App\Http\Resources\ResponseQuestionnaire;

use Illuminate\Http\Resources\Json\JsonResource;

class ResponseQuestionnaireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this->is_why == FALSE){
            return [
                'id' => $this->id,
                'questionnaire_id' => $this->questionnaire_id,
                'response' => $this->response,
                'is_why' => $this->is_why,
                'question' => $this->question,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
        }
        return [
            'id' => $this->id,
            'questionnaire_id' => $this->questionnaire_id,
            'response' => $this->response,
            'is_why' => $this->is_why,
            'question' => $this->question,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'emotion' => $this->content->emotion->display_name ?? "",
            'accuracy' => $this->content->accuracy           
        ];
    }
}