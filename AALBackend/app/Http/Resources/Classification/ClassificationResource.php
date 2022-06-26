<?php

namespace App\Http\Resources\Classification;

use App\Http\Resources\Emotion\EmotionResource;
use App\Models\Emotion;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassificationResource extends JsonResource
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
            'emotion' => new EmotionResource($this->emotion ?? new Emotion()),
            'accuracy' => $this->accuracy,
        ];
    }
}
