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
    public static $format = "default";
    public function toArray($request)
    {
        switch (SpeechResource::$format) {
            case 'extended':
                return [
                    'id' => $this->id,
                    'text' => $this->text,
                    'emotion' => new EmotionResource($this->content->emotion ?? new Emotion()),
                    'createDate' => $this->content->createdate,
                    'emotionIteration' => new EmotionResource($this->content->iteration->emotion ?? new Emotion()),
                    'predictions' => new ClassificationCollection($this->content->classifications),
                ];
            default:
                return [
                    'id' => $this->id,
                    'text' => $this->text,
                    'emotion' => $this->content->emotion->name ?? "",
                    'emotion_iteration' => $this->content->iteration->emotion->name ?? "",
                    'createDate' => $this->content->createdate
                ];

        }

    }
}
