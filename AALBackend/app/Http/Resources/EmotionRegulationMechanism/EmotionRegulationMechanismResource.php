<?php

namespace App\Http\Resources\EmotionRegulationMechanism;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'id'=> $this->id,
            'regulation_mechanism'=> $this->regulationMechanism->display_name,
            'emotion'=> $this->emotionToRegulate->display_name,
            'is_default'=> $this->is_default,
            'created_at'=> $this->created_at
        ];
    }
}
