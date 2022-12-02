<?php

namespace App\Http\Resources\Speech;

use App\Models\Emotion;
use App\Http\Resources\Emotion\EmotionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Classification\ClassificationCollection;

class SpeechResource extends JsonResource
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
            'text' => $this->text,
            'emotion' => new EmotionResource($this->content->emotion ?? new Emotion()),
            'createDate' => $this->content->createdate,
            'emotionIteration' => new EmotionResource($this->content->iteration->emotion ?? new Emotion()),
            'predictions' => new ClassificationCollection($this->content->classifications),
        ];
    }
}
