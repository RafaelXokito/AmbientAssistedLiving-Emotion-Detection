<?php

namespace App\Http\Resources\MultiModalEmotion;

use Illuminate\Http\Resources\Json\JsonResource;

class MultiModalEmotionResource extends JsonResource
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
            'emotion_name' => $this->emotion->name ?? "",
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
