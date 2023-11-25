<?php

namespace App\Http\Resources\QuestionnaireType;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ResultMapping\ResultMappingCollection;
use App\Http\Resources\Question\QuestionCollection;

class QuestionnaireTypeResource extends JsonResource
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
            'name' => $this->name,
            'display_name' => $this->display_name,   
            'points_min' => $this->points_min,  
            'points_max' => $this->points_max,
            'questionnairable_model_name' => $this->questionnairable_model_name,
            'questions' => new QuestionCollection($this->questions),
            'results_mappings' => new ResultMappingCollection($this->result_mappings)
        ];
    }
}
