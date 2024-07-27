<?php

namespace App\Http\Resources\EmotionRegulationMechanism;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RegulationMechanismContent\RegulationMechanismContentCollection;

class EmotionRegulationMechanismResource extends JsonResource
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
            "id" => $this->id,
            "client_id" => $this->client_id,
            "emotion" => $this->emotionToRegulate->display_name,
            "contents" => new RegulationMechanismContentCollection($this->regulationMechanismsContents),
            "threshold" => $this->threshold,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at
        ];
    }
}
