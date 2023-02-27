<?php

namespace App\Http\Resources\GeriatricQuestionnaire;

use App\Http\Resources\ResponseGeriatricQuestionnaire\ResponseGeriatricQuestionnaireCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class GeriatricQuestionnaireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'points' => $this->points,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'client_id' => $this->client_id,
            'responses' => new ResponseGeriatricQuestionnaireCollection($this->responses)
        ];
    }
}
